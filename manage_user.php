<?php
include('db_connect.php');
include('admin_class.php');
$current_user_id = $_SESSION['admin_id'] ?? null;
$admin = new Action();
$action = $_GET['action'] ?? '';

if ($action == 'save_user') {
    echo $admin->save_user();
} elseif ($action == 'update_user') {
    echo $admin->update_user();
} else {
    // Fetch user data for editing
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $sql = "SELECT * FROM EmployeeUserView WHERE id = ?";
        $stmt = sqlsrv_query($conn, $sql, array($id));
        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }
        $meta = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        if ($meta === false) {
            die(print_r(sqlsrv_errors(), true));
        }
    }
}
?>

<div class="container-fluid">
    <form action="" id="manage-user">
        <input type="hidden" name="id" value="<?php echo isset($meta['id']) ? $meta['id'] : '' ?>">
        <div class="form-group">
            <label for="employee_no">Employee No.</label>
            <input type="text" name="employee_no" id="employee_no" class="form-control" 
                   value="<?php echo isset($meta['employee_no']) ? htmlspecialchars($meta['employee_no']) : '' ?>" 
                   <?php echo isset($_GET['id']) ? 'readonly' : '' ?> required>
        </div>
        <div class="form-group">
            <label for="firstname">First Name</label>
            <input type="text" name="firstname" id="firstname" class="form-control" 
                   value="<?php echo isset($meta['firstname']) ? htmlspecialchars($meta['firstname']) : '' ?>" 
                   <?php echo isset($_GET['id']) ? 'readonly' : '' ?> required>
        </div>
        <div class="form-group">
            <label for="middlename">Middle Name</label>
            <input type="text" name="middlename" id="middlename" class="form-control" 
                   value="<?php echo isset($meta['middlename']) ? htmlspecialchars($meta['middlename']) : '' ?>" 
                   <?php echo isset($_GET['id']) ? 'readonly' : '' ?> required>
        </div>
        <div class="form-group">
            <label for="lastname">Last Name</label>
            <input type="text" name="lastname" id="lastname" class="form-control" 
                   value="<?php echo isset($meta['lastname']) ? htmlspecialchars($meta['lastname']) : '' ?>" 
                   <?php echo isset($_GET['id']) ? 'readonly' : '' ?> required>
        </div>
        <div class="form-group">
            <label for="suffix">Suffix</label>
            <input type="text" name="suffix" id="suffix" class="form-control" 
                   value="<?php echo isset($meta['suffix']) ? htmlspecialchars($meta['suffix']) : '' ?>" 
                   <?php echo isset($_GET['id']) ? 'readonly' : '' ?> required>
        </div>

        <!-- Username and Password (editable) -->
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" class="form-control" 
                   value="<?php echo isset($meta['username']) ? htmlspecialchars($meta['username']) : '' ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" class="form-control" value="" 
                   <?php echo isset($_GET['id']) ? '' : 'required' ?>>
            <small><i><?php echo isset($_GET['id']) ? "Leave this blank if you don't want to change the password" : '' ?></i></small>
        </div>

        <!-- User Type Dropdown -->
        <div class="form-group">
        <div class="form-group">
            <label for="type">User Type</label>
            <select name="type" id="type" class="custom-select" <?= ($current_user_id && isset($meta['id']) && $meta['id'] == $current_user_id) ? 'disabled' : '' ?>>
            <option value="1" <?= (isset($meta['type']) && $meta['type'] == 1) ? 'selected' : '' ?>>Admin</option>
                <option value="0" <?= (isset($meta['type']) && $meta['type'] == 0) ? 'selected' : '' ?>>Staff</option>
            </select>
            <input type="hidden" name="type" value="<?= isset($meta['type']) ? $meta['type'] : '' ?>">
        </div>


    </form>
</div>

<script>
$('#manage-user').submit(function (e) {
    e.preventDefault();

    let action = $('input[name=id]').val() ? 'update_user' : 'save_user';
    let formData = $(this).serialize();

    $.ajax({
        url: 'ajax.php?action=' + action,
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(resp) {
            if (resp.success) {
                alert_toast(resp.message, 'success');
                setTimeout(() => location.reload(), 1500); // Reload for update
                if (action == 'save_user') {
                  $('#manage-user')[0].reset(); // Clear the form after successful insertion
                }
            } else {
                alert_toast(resp.message, 'danger');
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("AJAX Error:", textStatus, errorThrown);
            alert_toast("An error occurred during the request.", 'danger');
        }
    });
});
</script>