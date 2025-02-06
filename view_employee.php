<?php include 'db_connect.php' ?>

<?php
    // Correct way to query MS SQL Server
    $sql = "SELECT e.*, d.name as dname, p.name as pname FROM employee e 
            INNER JOIN department d ON e.department_id = d.id 
            INNER JOIN position p ON e.position_id = p.id 
            WHERE e.id = ?"; // Use parameterized query to prevent SQL injection

    $params = array($_GET['id']); // Parameter for the query

    $stmt = sqlsrv_query($conn, $sql, $params); // Execute the MS SQL query

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true)); // Handle errors (essential!)
    }

    $emp = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC); // Correct way to fetch data

    if ($emp === null) { // Check if employee was found
        die("Employee not found.");
    }

    foreach($emp as $k=>$v){
        $$k=$v;
    }
    ?>

    <div class="container-fluid">
        <div class="col-md-12">
            <h5><b><small>Employee ID :</small><?php echo $employee_no ?></b></h5>
            <h4><b><small>Name: </small><?php echo ucwords($lastname.", ".$firstname." ",$middlename) ?></b></h4>
            <p><b>Department : <?php echo ucwords($dname) ?></b></p>
            <p><b>Position : <?php echo ucwords($pname) ?></b></p>
            <hr class="divider">
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <span><b>Allowances</b></span>
                            <button class="btn btn-primary btn-sm float-right" style="padding: 3px 5px" type="button" id="new_allowance"><i class="fa fa-plus"></i></button>
                        </div>
                        <div class="card-body">
                            <ul class="list-group">
                                <?php
                                $sql = "SELECT ea.*, a.allowance as aname FROM employee_allowances ea 
                                INNER JOIN allowances a ON a.id = ea.allowance_id 
                                WHERE ea.employee_id = ? AND ea.isdeleted = 0 
                                ORDER BY ea.type ASC, ea.effective_date ASC, a.allowance ASC";                        

                                $params = array($_GET['id']); // Parameter for the query

                                $stmt = sqlsrv_query($conn, $sql, $params); // Use sqlsrv_query()

                                if ($stmt === false) {
                                    die(print_r(sqlsrv_errors(), true)); // Handle errors!
                                }

                                $t_arr = array(1 => "Monthly", 2 => "Semi-Monthly", 3 => "Once");

                                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)): // Use sqlsrv_fetch_array()
                                ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center alist" data-id="<?php echo $row['id'] ?>">
                                    <span>
                                        <p><small><?php echo $row['aname'] ?> Allowance</small></p>
                                        <p><small>Type: <?php echo $t_arr[$row['type']] ?></small></p>
                                        <?php if ($row['type'] == 3): ?>
                                            <p><small>Effective: <?php echo $row['effective_date']->format("M d,Y"); ?></small></p>
                                        <?php endif; ?>
                                    </span>
                                    <button class="badge badge-danger badge-pill btn remove_allowance" type="button" data-id="<?php echo $row['id'] ?>"><i class="fa fa-trash"></i></button>
                                </li>
                                <?php endwhile; 

                                sqlsrv_free_stmt($stmt); // Free the statement resource (good practice)
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <span><b>Deductions</b></span>
                            <button class="btn btn-primary btn-sm float-right" style="padding: 3px 5px" type="button" id="new_deduction"><i class="fa fa-plus"></i></button>
                        </div>
                        <div class="card-body">
                            <ul class="list-group">
                                <?php
                                $sql = "SELECT ed.*, d.deduction as dname FROM employee_deductions ed 
                                INNER JOIN deductions d ON d.id = ed.deduction_id 
                                WHERE ed.employee_id = ? AND ed.isdeleted = 0 
                                ORDER BY ed.type ASC, ed.effective_date ASC, d.deduction ASC";
                        

                                $params = array($_GET['id']);

                                $stmt = sqlsrv_query($conn, $sql, $params);

                                if ($stmt === false) {
                                    die(print_r(sqlsrv_errors(), true));
                                }

                                $t_arr = array(1 => "Monthly", 2 => "Semi-Monthly", 3 => "Once");

                                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)):
                                ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center dlist" data-id="<?php echo $row['id'] ?>">
                                    <span>
                                        <p><small><?php echo $row['dname'] ?></small></p>
                                        <p><small>Type: <?php echo $t_arr[$row['type']] ?></small></p>
                                        <?php if ($row['type'] == 3): ?>
                                            <p><small>Effective: <?php echo $row['effective_date']->format("M d,Y"); ?></small></p>
                                        <?php endif; ?>
                                    </span>
                                    <button class="badge badge-danger badge-pill btn remove_deduction" type="button" data-id="<?php echo $row['id'] ?>"><i class="fa fa-trash"></i></button>
                                </li>
                                <?php endwhile;

                                sqlsrv_free_stmt($stmt);
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style type="text/css">
        .list-group-item>span>p{
            margin:unset;
        }
        .list-group-item>span>p>small{
            font-weight: 700
        }
    </style>

    <script>
        $('#new_allowance').click(function(){
            uni_modal("New Allowance for <?php echo $employee_no.' - '.ucwords($lastname.", ".$firstname." ",$middlename) ?>",'manage_employee_allowances.php?id=<?php echo $_GET['id'] ?>','mid-large')
        })
        $('#new_deduction').click(function(){
            uni_modal("New Deduction for <?php echo $employee_no.' - '.ucwords($lastname.", ".$firstname." ",$middlename) ?>",'manage_employee_deductions.php?id=<?php echo $_GET['id'] ?>','mid-large')
        })
        $('.remove_allowance').click(function(){
            _conf("Are you sure to delete this allowance?","remove_allowance",[$(this).attr('data-id')])
        })
        function remove_allowance(id){
            start_load()
            $.ajax({
                url:'ajax.php?action=delete_employee_allowance',
                method:"POST",
                data:{id:id},
                error:err=>console.log(err),
                success:function(resp){
                    if(resp == 1){
                        alert_toast("Selected allowance successfully deleted","success");
                        $('.alist[data-id="'+id+'"]').remove()
                        $('#confirm_modal').modal('hide')
                        end_load()
                    }
                }
            })
        }
        $('.remove_deduction').click(function(){
            _conf("Are you sure to delete this deduction?","remove_deduction",[$(this).attr('data-id')])
        })
        function remove_deduction(id){
            start_load()
            $.ajax({
                url:'ajax.php?action=delete_employee_deduction',
                method:"POST",
                data:{id:id},
                error:err=>console.log(err),
                success:function(resp){
                    if(resp == 1){
                        alert_toast("Selected deduction successfully deleted","success");
                        $('.dlist[data-id="'+id+'"]').remove()
                        $('#confirm_modal').modal('hide')
                        end_load()
                    }
                }
            })
        }
    </script>
