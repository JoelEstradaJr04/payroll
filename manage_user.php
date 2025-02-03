<?php
include('db_connect.php');
if (isset($_GET['id'])) {
    // Use parameterized query to prevent SQL injection
    $query = "SELECT * FROM users WHERE id = ?"; // Your SQL query

    // Prepare the query with sqlsrv_prepare
    $params = array($_GET['id']); // The parameter for the query (id)

    $stmt = sqlsrv_prepare($conn, $query, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true)); // Debugging if preparation fails
    }

    // Execute the query
    if (sqlsrv_execute($stmt)) {
        // Fetch the result as an associative array
        $meta = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        
        if (!$meta) { // Check if user exists
            echo "User not found.";
            exit;
        }

        // Do something with $meta if user exists
        // Example: print_r($meta);
    } else {
        echo "Error executing the query.";
        exit;
    }

    sqlsrv_free_stmt($stmt); // Free the statement
}

?>

<div class="container-fluid">

    <form action="" id="manage-user">
        <input type="hidden" name="id" value="<?php echo isset($meta['id']) ? $meta['id'] : '' ?>">
        <div class="form-group">
            <label for="employee_no">Employee No.</label>
            <input type="text" name="employee_no" id="employee_no" class="form-control" value="<?php echo isset($meta['employee_no']) ? htmlspecialchars($meta['employee_no']) : '' ?>" required>
        </div>
        <div class="form-group">
            <label for="firstname">First Name</label>
            <input type="text" name="firstname" id="firstname" class="form-control" value="<?php echo isset($meta['firstname']) ? htmlspecialchars($meta['firstname']) : '' ?>" required>
        </div>
        <div class="form-group">
            <label for="middlename">Middle Name</label>
            <input type="text" name="middlename" id="middlename" class="form-control" value="<?php echo isset($meta['middlename']) ? htmlspecialchars($meta['middlename']) : '' ?>" required>
        </div>
        <div class="form-group">
            <label for="lastname">Last Name</label>
            <input type="text" name="lastname" id="lastname" class="form-control" value="<?php echo isset($meta['lastname']) ? htmlspecialchars($meta['lastname']) : '' ?>" required>
        </div>
        <div class="form-group">
            <label for="suffix">Suffix</label>
            <input type="text" name="suffix" id="suffix" class="form-control" value="<?php echo isset($meta['suffix']) ? htmlspecialchars($meta['suffix']) : '' ?>" required>
        </div>

        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" class="form-control" value="<?php echo isset($meta['username']) ? htmlspecialchars($meta['username']) : '' ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" class="form-control" value="" <?php echo isset($_GET['id']) ? '' : 'required' ?>>  </input>
            <small><i><?php echo isset($_GET['id']) ? "Leave this blank if you don't want to change the password" : '' ?></i></small>
        </div>
        <div class="form-group">
            <label for="type">User Type</label>
            <select name="type" id="type" class="custom-select">
                <option value="1" <?php echo isset($meta['type']) && $meta['type'] == 1 ? 'selected' : '' ?>>Admin</option>
                <option value="0" <?php echo isset($meta['type']) && $meta['type'] == 0 ? 'selected' : '' ?>>Staff</option>
            </select>
        </div>
    </form>
</div>

<script>
$('#manage-user').submit(function (e) {
    e.preventDefault();
    start_load();
    $.ajax({
        url: 'ajax.php?action=save_user',
        method: 'POST',
        data: $(this).serialize(),
        dataType: 'json', // Expect JSON response
        error: function(err) {
            console.log("AJAX Error:", err);
        },
        success: function(resp) {
            console.log("Server Response:", resp);

            if (resp.success) {
                alert_toast(resp.message, 'success');
                setTimeout(function () {
                    location.reload();
                }, 1500);
            } else {
                alert_toast(resp.message, 'danger');
            }
        }
    });
});

</script>