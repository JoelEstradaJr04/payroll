<?php include 'db_connect.php'; ?>

<?php
    $stmt = $conn->prepare("SELECT p.*, CONCAT(e.lastname, ', ', e.firstname, ' ', e.middlename) AS ename, e.employee_no FROM payroll_items p INNER JOIN employee e ON e.id = p.employee_id WHERE p.id = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $payroll = $result->fetch_assoc();
    
    foreach ($payroll as $key => $value) {
        $$key = $value;
    }
    
    $stmt = $conn->prepare("SELECT * FROM payroll WHERE id = ?");
    $stmt->bind_param("i", $payroll_id);
    $stmt->execute();
    $pay = $stmt->get_result()->fetch_assoc();
    
    $pt = array(1 => "Monthly", 2 => "Semi-Monthly");
?>

<div class="container-fluid">
    <div class="col-md-12">
        <h5><b><small>Employee ID :</small> <?php echo $employee_no; ?></b></h5>
        <h4><b><small>Name: </small> <?php echo ucwords($ename); ?></b></h4>
        <hr class="divider">
        <div class="row">
            <div class="col-md-6">
                <p><b>Payroll Ref : <?php echo $pay['ref_no']; ?></b></p>
                <p><b>Payroll Range : <?php echo date("M d, Y", strtotime($pay['date_from'])) . " - " . date("M d, Y", strtotime($pay['date_to'])); ?></b></p>
                <p><b>Payroll type : <?php echo $pt[$pay['type']]; ?></b></p>
            </div>
            <div class="col-md-6">
                <p><b>Days of Absent : <?php echo $absent; ?></b></p>
                <p><b>Tardy/Undertime (mins) : <?php echo $late; ?></b></p>
                <p><b>Total Allowance Amount : <?php echo number_format($allowance_amount, 2); ?></b></p>
                <p><b>Total Deduction Amount : <?php echo number_format($deduction_amount, 2); ?></b></p>
                <p><b>Net Pay : <?php echo number_format($net, 2); ?></b></p>
            </div>
        </div>
        <hr class="divider">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header"><b>Allowances</b></div>
                    <div class="card-body">
                        <ul class="list-group">
                            <?php
                            $all_qry = $conn->query("SELECT * FROM allowances");
                            $all_arr = [];
                            while ($row = $all_qry->fetch_assoc()) {
                                $all_arr[$row['id']] = $row['allowance'];
                            }
                            foreach (json_decode($allowances) as $val):
                            ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo $all_arr[$val->aid]; ?> Allowance
                                    <span class="badge badge-primary badge-pill"><?php echo number_format($val->amount, 2); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header"><b>Deductions</b></div>
                    <div class="card-body">
                        <ul class="list-group">
                            <?php
                            $ded_qry = $conn->query("SELECT * FROM deductions");
                            $ded_arr = [];
                            while ($row = $ded_qry->fetch_assoc()) {
                                $ded_arr[$row['id']] = $row['deduction'];
                            }
                            foreach (json_decode($deductions) as $val):
                            ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo $ded_arr[$val->did]; ?>
                                    <span class="badge badge-primary badge-pill"><?php echo number_format($val->amount, 2); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-lg-12">
            <button class="btn btn-primary btn-sm btn-block col-md-2 float-right" type="button" data-dismiss="modal">Close</button>
        </div>
    </div>
</div>

<style>
    .list-group-item > span > p {
        margin: unset;
    }
    .list-group-item > span > p > small {
        font-weight: 700;
    }
    #uni_modal .modal-footer {
        display: none;
    }
    .alist, .dlist {
        width: 100%;
    }
</style>

<script>
</script>
