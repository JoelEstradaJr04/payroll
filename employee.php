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
                        $d_arr[0] = "Unset";
                        $p_arr[0] = "Unset";

                        $dept_sql = "SELECT * FROM department ORDER BY name ASC"; // SQL Server query
                        $dept_stmt = sqlsrv_query($conn, $dept_sql);
                        if ($dept_stmt === false) {
                            die(print_r(sqlsrv_errors(), true)); // Error handling
                        }
                        while ($row = sqlsrv_fetch_array($dept_stmt, SQLSRV_FETCH_ASSOC)):
                            $d_arr[$row['id']] = $row['name'];
                        endwhile;
                        sqlsrv_free_stmt($dept_stmt); // Free statement resource


                        $pos_sql = "SELECT * FROM position ORDER BY name ASC"; // SQL Server query
                        $pos_stmt = sqlsrv_query($conn, $pos_sql);
                        if ($pos_stmt === false) {
                            die(print_r(sqlsrv_errors(), true)); // Error handling
                        }
                        while ($row = sqlsrv_fetch_array($pos_stmt, SQLSRV_FETCH_ASSOC)):
                            $p_arr[$row['id']] = $row['name'];
                        endwhile;
                        sqlsrv_free_stmt($pos_stmt); // Free statement resource


                        $employee_sql = "EXEC sp_show_employee"; // SQL Server query
                        $employee_stmt = sqlsrv_query($conn, $employee_sql);
                        if ($employee_stmt === false) {
                            die(print_r(sqlsrv_errors(), true)); // Error handling
                        }

                        while ($row = sqlsrv_fetch_array($employee_stmt, SQLSRV_FETCH_ASSOC)) {
                            ?>
                            <tr>
                                <td><?php echo $row['employee_no']; ?></td>
                                <td><?php echo $row['firstname']; ?></td>
                                <td><?php echo $row['middlename']; ?></td>
                                <td><?php echo $row['lastname']; ?></td>
                                <td><?php echo $row['suffix']; ?></td>
                                <td><?php echo $d_arr[$row['department_id']]; ?></td>
                                <td><?php echo $p_arr[$row['position_id']]; ?></td>
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
                        sqlsrv_free_stmt($employee_stmt); // Free statement resource
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $('#table').DataTable();

        $('.edit_employee').click(function () {
            var $id = $(this).attr('data-id');
            uni_modal("Edit Employee", "manage_employee.php?id=" + $id)

        });
        $('.view_employee').click(function () {
            var $id = $(this).attr('data-id');
            uni_modal("Employee Details", "view_employee.php?id=" + $id, "mid-large")

        });
        $('#new_emp_btn').click(function () {
            uni_modal("New Employee", "manage_employee.php")
        })
        $('.remove_employee').click(function () {
            _conf("Are you sure to delete this employee?", "remove_employee", [$(this).attr('data-id')])
        })
    });

    function remove_employee(id) {
        start_load()
        $.ajax({
            url: 'ajax.php?action=delete_employee',
            method: "POST",
            data: { id: id },
            error: err => console.log(err),
            success: function (resp) {
                if (resp == 1) {
                    alert_toast("Employee's data successfully deleted", "success");
                    setTimeout(function () {
                        location.reload();

                    }, 1000)
                }
            } //! Done
        })
    }
</script>