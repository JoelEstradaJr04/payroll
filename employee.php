<?php include('db_connect.php') ?>
<div class="container-fluid">
    <div class="col-lg-12">
        <br />
        <br />
        <div class="card">
            <div class="card-header">
                <span><b>Employee List</b></span>
                <button class="btn btn-primary btn-sm btn-block col-md-3 float-right" type="button" id="new_emp_btn"><span class="fa fa-plus"></span> Add Employee</button>
            </div>
            <div class="card-body">
                <table id="table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Employee No</th>
                            <th>Firstname</th>
                            <th>Middlename</th>
                            <th>Lastname</th>
                            <th>Suffix</th>
                            <th>Department</th>
                            <th>Position</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $employee_sql = "SELECT * FROM EmployeeDetailsView ORDER BY employee_no";
                        $employee_stmt = sqlsrv_query($conn, $employee_sql);

                        if ($employee_stmt === false) {
                            die(print_r(sqlsrv_errors(), true));
                        }

                        while ($row = sqlsrv_fetch_array($employee_stmt, SQLSRV_FETCH_ASSOC)) {
                            ?>
                            <tr>
                                <td><?php echo $row['employee_no']; ?></td>
                                <td><?php echo $row['firstname']; ?></td>
                                <td><?php echo $row['middlename']; ?></td>
                                <td><?php echo $row['lastname']; ?></td>
                                <td><?php echo $row['suffix']; ?></td>
                                <td><?php echo $row['department_name']; ?></td>
                                <td><?php echo $row['position_name']; ?></td>
                                <td>
                                    <center>
                                        <button class="btn btn-sm btn-outline-primary view_employee" data-id="<?php echo $row['id']; ?>" type="button"><i class="fa fa-eye"></i></button>
                                        <button class="btn btn-sm btn-outline-primary edit_employee" data-id="<?php echo $row['id']; ?>" type="button"><i class="fa fa-edit"></i></button>
                                        <button class="btn btn-sm btn-outline-danger remove_employee" data-id="<?php echo $row['id']; ?>" type="button"><i class="fa fa-trash"></i></button>
                                    </center>
                                </td>
                            </tr>
                            <?php
                        }
                        sqlsrv_free_stmt($employee_stmt);
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        // Initialize DataTable
        var table = $('#table').DataTable();

        // Use event delegation for dynamically added elements
        $(document).on('click', '.edit_employee', function () {
            var $id = $(this).attr('data-id');
            uni_modal("Edit Employee", "manage_employee.php?id=" + $id)
        });

        $(document).on('click', '.view_employee', function () {
            var $id = $(this).attr('data-id');
            uni_modal("Employee Details", "view_employee.php?id=" + $id, "mid-large")
        });

        $('#new_emp_btn').click(function () {
            uni_modal("New Employee", "manage_employee.php")
        });

        $(document).on('click', '.remove_employee', function () {
            _conf("Are you sure to delete this employee?", "remove_employee", [$(this).attr('data-id')])
        });
    });

    function remove_employee($id) {
        start_load()
        $.ajax({
            url: 'ajax.php?action=delete_employee',
            method: 'POST',
            data: { id: $id },
            success: function (resp) {
                try {
                    var response = JSON.parse(resp);
                    if (response.status === 3) {
                        alert_toast(response.message, "success");
                        setTimeout(function () {
                            location.reload();
                        }, 1500);
                    } else {
                        alert_toast(response.message, "error");
                    }
                } catch (e) {
                    console.error("Error parsing response:", e);
                    alert_toast("An error occurred", "error");
                }
                end_load();
            },
            error: function (xhr, status, error) {
                console.error("Ajax error:", error);
                alert_toast("An error occurred", "error");
                end_load();
            }
        });
    }
</script>