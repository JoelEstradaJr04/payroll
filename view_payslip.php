<?php
include 'db_connect.php';

// Check if the connection is successful
if ($conn === false) {
    die("ERROR: Could not connect. " . sqlsrv_errors());
}

$sql = "SELECT * FROM payroll_items WHERE payroll_id = ? AND employee_id = ?";
$params = array($_GET['id'], $_GET['eid']);

// Prepare the statement
$stmt = sqlsrv_prepare($conn, $sql, $params);

// Check if the statement was prepared successfully
if ($stmt === false) {
    die("ERROR: Could not prepare query. " . sqlsrv_errors());
}

// Execute the statement
if (sqlsrv_execute($stmt) === false) {
    die("ERROR: Could not execute query. " . sqlsrv_errors());
}

// Fetch the results
$payroll_items = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

// Close the statement and connection
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
<div class="container-fluid">
    <div class="col-lg-12">
        <div class="row">
            <div class="col-md-6">
                <p>Employee: <b><?php echo ucwords($employee['name']) ?></b></p>
                <p>Payroll: <b><?php echo $payroll['ref_no'] ?></b></p>
                <p>Salary: <b><?php echo number_format($payroll_items['salary'],2) ?></b></p>
                <p>Allowance: <b><?php echo number_format($payroll_items['allowance_amount'],2) ?></b></p>
                <p>Deduction: <b><?php echo number_format($payroll_items['deduction_amount'],2) ?></b></p>
                <p>Net: <b><?php echo number_format($payroll_items['net'],2) ?></b></p>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <b>Allowances</b>
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            <?php
                            foreach (json_decode($allowances) as $val):
                            ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo $all_arr[$val->aid]; ?>
                                    <span class="badge badge-primary badge-pill"><?php echo number_format($val->amount, 2); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <div class="card mt-2">
                    <div class="card-header">
                        <b>Deductions</b>
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            <?php
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