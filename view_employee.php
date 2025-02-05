<?php include 'db_connect.php'; ?>

<?php
$emp_id = $_GET['id'];
$sql = "SELECT e.*, d.name as dname, p.name as pname FROM employee e 
        INNER JOIN department d ON e.department_id = d.id 
        INNER JOIN position p ON e.position_id = p.id 
        WHERE e.id = ?";
$params = array($emp_id);
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

$emp = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
if (!$emp) {
    die("Employee not found.");
}

foreach ($emp as $k => $v) {
    $$k = $v;
}
?>

<div class="container-fluid">
    <div class="col-md-12">
        <h5><b><small>Employee ID :</small> <?php echo $employee_no; ?></b></h5>
        <h4><b><small>Name: </small><?php echo ucwords($lastname . ", " . $firstname . " " . $middlename); ?></b></h4>
        <p><b>Department : <?php echo ucwords($dname); ?></b></p>
        <p><b>Position : <?php echo ucwords($pname); ?></b></p>
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
                                    WHERE ea.employee_id = ? ORDER BY ea.type ASC, ea.effective_date ASC, a.allowance ASC";
                            $stmt = sqlsrv_query($conn, $sql, $params);
                            $t_arr = array(1 => "Monthly", 2 => "Semi-Monthly", 3 => "Once");
                            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)):
                            ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center alist" data-id="<?php echo $row['id']; ?>">
                                <span>
                                    <p><small><?php echo $row['aname']; ?> Allowance</small></p>
                                    <p><small>Type: <?php echo $t_arr[$row['type']]; ?></small></p>
                                    <?php if ($row['type'] == 3): ?>
                                    <!-- ... previous code ... -->
                                    <?php if ($row['type'] == 3): ?>
                                    <p><small>Effective: <?php echo $row['effective_date']->format("M d, Y"); ?></small></p>
                                    <?php endif; ?>
                                    <!-- ... rest of the code ... -->
                                    <?php endif; ?>
                                </span>
                                <button class="badge badge-danger badge-pill btn remove_allowance" type="button" data-id="<?php echo $row['id']; ?>"><i class="fa fa-trash"></i></button>
                            </li>
                            <?php endwhile; ?>
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
                            $sql = "SELECT ea.*, d.deduction as dname FROM employee_deductions ea 
                                    INNER JOIN deductions d ON d.id = ea.deduction_id 
                                    WHERE ea.employee_id = ? ORDER BY ea.type ASC, ea.effective_date ASC, d.deduction ASC";
                            $stmt = sqlsrv_query($conn, $sql, $params);
                            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)):
                            ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center dlist" data-id="<?php echo $row['id']; ?>">
                                <span>
                                    <p><small><?php echo $row['dname']; ?></small></p>
                                    <p><small>Type: <?php echo $t_arr[$row['type']]; ?></small></p>
                                    <?php if ($row['type'] == 3): ?>
                                    <!-- ... previous code ... -->
                                    <?php if ($row['type'] == 3): ?>
                                    <p><small>Effective: <?php echo $row['effective_date']->format("M d, Y"); ?></small></p>
                                    <?php endif; ?>
                                    <!-- ... rest of the code ... -->
                                    <?php endif; ?>
                                </span>
                                <button class="badge badge-danger badge-pill btn remove_deduction" type="button" data-id="<?php echo $row['id']; ?>"><i class="fa fa-trash"></i></button>
                            </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
