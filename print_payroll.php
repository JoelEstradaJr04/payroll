<style>
    table {
        width: 100%;
        border-collapse: collapse;
    }

    tr,
    td,
    th {
        border: 1px solid black;
        padding: 5px; /* Add some padding for readability */
    }

    th {
        background-color: #f2f2f2; /* Light gray background for headers */
    }

    .text-center {
        text-align: center;
    }

    .text-right {
        text-align: right;
    }
</style>

<?php include('db_connect.php') ?>
<?php
if (isset($_GET['id'])) {
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

<div>
    <h2 class="text-center">Payroll - <?php echo $pay['ref_no'] ?></h2>
    <hr>
</div>

<table>
    <thead>
        <tr>
            <th class="text-center">Employee ID</th>
            <th class="text-center">Employee Name</th>
            <th class="text-center">Monthly Salary</th>
            <th class="text-center">Absent</th>
            <th class="text-center">Tardy/Undertime(mins)</th>
            <th class="text-center">Total Allowance</th>
            <th class="text-center">Total Deduction</th>
            <th class="text-center">Net Pay</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $payroll_sql = "SELECT p.*, CONCAT(e.lastname, ', ', e.firstname, ' ', e.middlename) AS ename, e.employee_no, e.salary 
                        FROM payroll_items p 
                        INNER JOIN employee e ON e.id = p.employee_id
                        WHERE p.payroll_id = ?"; // Parameterized query
        $payroll_stmt = sqlsrv_query($conn, $payroll_sql, array($_GET['id']));

        if ($payroll_stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        while ($row = sqlsrv_fetch_array($payroll_stmt, SQLSRV_FETCH_ASSOC)) {
            ?>
            <tr>
                <td><?php echo $row['employee_no'] ?></td>
                <td><?php echo ucwords($row['ename']) ?></td>
                <td class="text-right"><?php echo number_format($row['salary'], 2) ?></td>
                <td class="text-right"><?php echo $row['absent'] ?></td>
                <td class="text-right"><?php echo $row['late'] ?></td>
                <td class="text-right"><?php echo number_format($row['allowance_amount'], 2) ?></td>
                <td class="text-right"><?php echo number_format($row['deduction_amount'], 2) ?></td>
                <td class="text-right"><?php echo number_format($row['net'], 2) ?></td>
            </tr>
            <?php
        }
        sqlsrv_free_stmt($payroll_stmt);
        ?>
    </tbody>
</table>