<?php
include('db_connect.php');
include('admin_class.php'); // Make sure this path is correct

$admin = new Action(); // Use the correct class name: Action
$action = $_GET['action'] ?? '';

if ($action == 'save_user') {
    echo $admin->save_user();
} elseif ($action == 'update_user') {
    echo $admin->update_user();
} else {
    //echo json_encode(['success' => false, 'message' => 'Invalid action.']);
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