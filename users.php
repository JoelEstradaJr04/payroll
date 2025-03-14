<?php
$current_user_id = $_SESSION['login_id'] ?? null; // Get the current user's ID
include 'db_connect.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <button class="btn btn-primary float-right btn-sm" id="new_user"><i class="fa fa-plus"></i> New user</button>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="card col-lg-12">
            <div class="card-body">
                <table class="table-striped table-bordered col-md-12">
                    <thead>
                        <tr>
                            <th class="text-center">Employee No.</th>
                            <th class="text-center">Full Name</th>
                            <th class="text-center">Username</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $sql = "SELECT * FROM EmployeeUserView"; 
                        $stmt = sqlsrv_query($conn, $sql); // Execute query

                        if ($stmt === false) { // Check for query errors
                            die(print_r(sqlsrv_errors(), true)); // Display errors
                        }

                        $i = 1;
                        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)): // Use sqlsrv_fetch_array
                            $is_current_user = ($current_user_id == $row['id']); // Check if the row belongs to the current user
                    ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['employee_no']); ?></td>
                                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td>
                                    <center>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-primary">Action</button>
                                            <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <span class="sr-only">Toggle Dropdown</span>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item edit_user" href="javascript:void(0)" data-id='<?php echo $row['id']; ?>'>Edit</a>
                                                <div class="dropdown-divider"></div>
                                                <!-- Hide Delete button for the current user -->
                                                <?php if (!$is_current_user): ?>
                                                    <a class="dropdown-item delete_user" href="javascript:void(0)" data-id='<?php echo $row['id']; ?>'>Delete</a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </center>
                                </td>
                            </tr>
                        <?php endwhile;
                        sqlsrv_free_stmt($stmt); // Free the statement resource
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$('#new_user').click(function(){
	uni_modal('New User','manage_user.php')
})
$('.edit_user').click(function(){
	uni_modal('Edit User','manage_user.php?id='+$(this).attr('data-id'))
})
$('.delete_user').click(function(){
		_conf("Are you sure to delete this user?","delete_user",[$(this).attr('data-id')])
	})
    function delete_user($id){
    start_load()
    $.ajax({
        url:'ajax.php?action=delete_user',
        method:'POST',
        data:{id:$id},
        dataType: 'json',
        success:function(resp){
            if(resp.success){
                alert_toast(resp.message,'success')
                setTimeout(function(){
                    location.reload()
                },1500)
            } else {
                alert_toast(resp.message,'error')
            }
            end_load()
        },
        error: function(err) {
            alert_toast("An error occurred",'error')
            console.log(err)
            end_load()
        }
    })
}
</script>