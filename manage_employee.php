<?php
include 'db_connect.php';
if (isset($_GET['id'])) {
    // Use parameterized query to prevent SQL injection
    $qry_sql = "SELECT * FROM employee WHERE id = ?";
    $qry_stmt = sqlsrv_query($conn, $qry_sql, array($_GET['id']));

    if ($qry_stmt === false) {
        die(print_r(sqlsrv_errors(), true)); // Handle query error
    }

    $qry = sqlsrv_fetch_array($qry_stmt, SQLSRV_FETCH_ASSOC);
    sqlsrv_free_stmt($qry_stmt);

    if ($qry) { // Check if a row was returned
        foreach ($qry as $k => $v) {
            $$k = $v;
        }
    } else {
        // Handle the case where no employee is found with that ID
        echo "Employee not found.";
        exit; // Stop further execution
    }
}
?>

<div class="container-fluid">
    <form id='employee_frm'>
        <div class="form-group">
            <label>Firstname</label>
            <input type="hidden" name="id" value="<?php echo isset($id) ? $id : "" ?>" />
            <input type="text" name="firstname" required="required" class="form-control" value="<?php echo isset($firstname) ? $firstname : "" ?>" />
        </div>
        <div class="form-group">
            <label>Middlename</label>
            <input type="text" name="middlename" placeholder="(optional)" class="form-control" value="<?php echo isset($middlename) ? $middlename : "" ?>" />
        </div>
        <div class="form-group">
            <label>Lastname</label>
            <input type="text" name="lastname" required="required" class="form-control" value="<?php echo isset($lastname) ? $lastname : "" ?>" />
        </div>
        <div class="form-group">
            <label>Suffix</label>
            <select name="suffix" class="form-control select2">
                <option value="N/A" <?php echo (isset($suffix) && $suffix == 'N/A') ? 'selected' : '' ?>>N/A</option>
                <option value="Jr." <?php echo (isset($suffix) && $suffix == 'Jr.') ? 'selected' : '' ?>>Jr.</option>
                <option value="Sr." <?php echo (isset($suffix) && $suffix == 'Sr.') ? 'selected' : '' ?>>Sr.</option>
                <option value="II" <?php echo (isset($suffix) && $suffix == 'II') ? 'selected' : '' ?>>II</option>
                <option value="III" <?php echo (isset($suffix) && $suffix == 'III') ? 'selected' : '' ?>>III</option>
                <option value="IV" <?php echo (isset($suffix) && $suffix == 'IV') ? 'selected' : '' ?>>IV</option>
            </select>
        </div>
        <div class="form-group">
            <label>Department</label>
            <select class="custom-select browser-default select2" name="department_id">
                <option value=""></option>
                <?php
                $dept_sql = "SELECT * FROM department ORDER BY name ASC";
                $dept_stmt = sqlsrv_query($conn, $dept_sql);

                if ($dept_stmt === false) {
                    die(print_r(sqlsrv_errors(), true));
                }

                while ($row = sqlsrv_fetch_array($dept_stmt, SQLSRV_FETCH_ASSOC)):
                    ?>
                    <option value="<?php echo $row['id'] ?>" <?php echo isset($department_id) && $department_id == $row['id'] ? "selected" : "" ?>><?php echo $row['name'] ?></option>
                <?php endwhile;
                sqlsrv_free_stmt($dept_stmt);
                ?>
            </select>
        </div>
        <div class="form-group">
            <label>Position</label>
            <select class="custom-select browser-default select2" name="position_id">
                <option value=""></option>
                <?php
                $pos_sql = "SELECT * FROM position ORDER BY name ASC";
                $pos_stmt = sqlsrv_query($conn, $pos_sql);

                if ($pos_stmt === false) {
                    die(print_r(sqlsrv_errors(), true));
                }

                while ($row = sqlsrv_fetch_array($pos_stmt, SQLSRV_FETCH_ASSOC)):
                    ?>
                    <option class="opt" value="<?php echo $row['id'] ?>" data-did="<?php echo $row['department_id'] ?>" <?php echo isset($department_id) && $department_id == $row['department_id'] ? '' : "disabled" ?> <?php echo isset($position_id) && $position_id == $row['id'] ? " selected" : '' ?>><?php echo $row['name'] ?></option>
                <?php endwhile;
                sqlsrv_free_stmt($pos_stmt);
                ?>
            </select>
        </div>
        <div class="form-group">
            <label>Monthly Salary</label>
            <input type="number" name="salary" required="required" class="form-control text-right" step="any" value="<?php echo isset($salary) ? $salary : "" ?>" />
        </div>
    </form>
</div>

<script>
    $(document).ready(function() {
        // Initialize select2 for all select elements
        $('.select2').select2({
            placeholder: "Please Select Here",
            width: "100%"
        });

        // Existing department change handler
        $('[name="department_id"]').change(function() {
            var did = $(this).val()
            $('[name="position_id"] .opt').each(function() {
                if ($(this).attr('data-did') == did) {
                    $(this).attr('disabled', false)
                } else {
                    $(this).attr('disabled', true)
                }
            })
        });

        // Form submission handler
        $('#employee_frm').submit(function(e) {
    e.preventDefault();
    start_load();
    $.ajax({
        url: 'ajax.php?action=save_employee',
        method: "POST",
        data: $(this).serialize(),
        success: function(resp) {
            try {
                var response = JSON.parse(resp);
                if(response.status === 1 || response.status === 2) {
                    alert_toast(response.message, "success");
                    // Instead of location.reload()
                    load_employee_table();
                    $('#uni_modal').modal('hide'); // Close the modal if using bootstrap modal
                } else {
                    alert_toast(response.message, "error");
                }
            } catch(e) {
                console.error("Error parsing response:", e);
                alert_toast("An error occurred", "error");
            }
            end_load();
        },
        error: function(xhr, status, error) {
            console.error("Ajax error:", error);
            alert_toast("An error occurred", "error");
            end_load();
        }
    });
});

// Function to reload table content
function load_employee_table() {
    $.ajax({
        url: 'ajax.php?action=load_employee_table',
        method: 'GET',
        success: function(resp) {
            $('#employee_table_container').html(resp);
        }
    });
}
    })
</script>