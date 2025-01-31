<?php include('db_connect.php') ?>
<?php
if (isset($_GET['id'])) {
    // Use parameterized query to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM payroll WHERE id = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $pay = $result->fetch_assoc();
    $stmt->close();

    if (!$pay) {
        echo "Payroll not found.";
        exit;
    }
} else {
    echo "Payroll ID not provided.";
    exit;
}

$pt = array(1 => "Monthly", 2 => "Semi-Monthly");
?>
<div class="container-fluid">
    <div class="col-lg-12">
        <br />
        <br />
        <div class="card">
            <div class="card-header">
                <span><b>Payroll : <?php echo $pay['ref_no'] ?></b></span>
                <button class="btn btn-primary btn-sm btn-block col-md-2 float-right" type="button" id="new_payroll_btn"><span class="fa fa-plus"></span> Re-Caclulate Payroll</button>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <p>Payroll Range: <b><?php echo date("M d, Y", strtotime($pay['date_from'])) . " - " . date("M d, Y", strtotime($pay['date_to'])) ?></b></p>
                        <p>Payroll Type: <b><?php echo $pt[$pay['type']] ?></b></p>
                        <button class="btn btn-success btn-sm btn-block col-md-2 float-right" type="button" id="print_btn"><span class="fa fa-print"></span> Print</button>
                    </div>
                </div>
                <hr>
                <table id="table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Absent</th>
                            <th>Late</th>
                            <th>Total Allowance</th>
                            <th>Total Deduction</th>
                            <th>Net</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $payroll_sql = "SELECT p.*, CONCAT(e.lastname, ', ', e.firstname, ' ', e.middlename) AS ename, e.employee_no 
                                        FROM payroll_items p 
                                        INNER JOIN employee e ON e.id = p.employee_id
                                        WHERE p.payroll_id = ?"; // Parameterized query
                        $payroll_stmt = sqlsrv_query($conn, $payroll_sql, array($_GET['id']));

                        if ($payroll_stmt === false) {
                            die(print_r(sqlsrv_errors(), true)); // Handle error
                        }

                        while ($row = sqlsrv_fetch_array($payroll_stmt, SQLSRV_FETCH_ASSOC)) {
                            ?>
                            <tr>
                                <td><?php echo $row['employee_no'] ?></td>
                                <td><?php echo ucwords($row['ename']) ?></td>
                                <td><?php echo $row['absent'] ?></td>
                                <td><?php echo $row['late'] ?></td>
                                <td><?php echo number_format($row['allowance_amount'], 2) ?></td>
                                <td><?php echo number_format($row['deduction_amount'], 2) ?></td>
                                <td><?php echo number_format($row['net'], 2) ?></td>
                                <td>
                                    <center>
                                        <button class="btn btn-sm btn-outline-primary view_payroll" data-id="<?php echo $row['id'] ?>" type="button"><i class="fa fa-eye"></i> View</button>
                                    </center>
                                </td>
                            </tr>
                            <?php
                        }
                        sqlsrv_free_stmt($payroll_stmt);
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

        $('#print_btn').click(function () {
            var nw = window.open("print_payroll.php?id=<?php echo $_GET['id'] ?>", "_blank", "height=500,width=800");
            setTimeout(function () {
                nw.print();
                setTimeout(function () {
                    nw.close();
                }, 500);
            }, 1000);
        });

        $('.view_payroll').click(function () {
            var $id = $(this).attr('data-id');
            uni_modal("Employee Payslip", "view_payslip.php?id=" + $id, "large");
        });

        $('#new_payroll_btn').click(function () {
            start_load();
            $.ajax({
                url: 'ajax.php?action=calculate_payroll',
                method: "POST",
                data: { id: '<?php echo $_GET['id'] ?>' },
                error: err => console.log(err), // Log errors
                success: function (resp) {
                    if (resp == 1) {
                        alert_toast("Payroll successfully computed", "success");
                        setTimeout(function () {
                            location.reload();
                        }, 1000);
                    } else {
                        console.error("Error calculating payroll:", resp); // Log detailed error
                        alert_toast("Error calculating payroll. Check console.", "danger"); // User-friendly message
                    }
                }
            });
        });
    });

    function remove_payroll(id) {
        start_load();
        $.ajax({
            url: 'ajax.php?action=delete_payroll',
            method: "POST",
            data: { id: id },
            error: err => console.log(err), // Log errors
            success: function (resp) {
                if (resp == 1) {
                    alert_toast("Payroll item successfully deleted", "success");
                    setTimeout(function () {
                        location.reload();
                    }, 1000);
                } else {
                    console.error("Error deleting payroll item:", resp); // Log detailed error
                    alert_toast("Error deleting payroll item. Check console.", "danger"); // User-friendly message
                }
            }
        });
    }
</script>