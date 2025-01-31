<?php include("db_connect.php"); ?>
<?php
$date = explode('_', $id);
$lt_arr = array(1 => "Time-in AM", 2 => "Time-out AM", 3 => "Time-in PM", 4 => "Time-out PM");

// SQL Server date handling:
$dt_obj = DateTime::createFromFormat('Y-m-d', $date[1]); // Create DateTime object
if ($dt_obj === false) {
    die("Invalid date format"); // Handle invalid date format
}
$dt = $dt_obj->format('Y-m-d'); // Format for SQL Server

$emp_sql = "SELECT CONCAT(lastname, ', ', firstname, ' ', middlename) AS enamem, employee_no FROM employee WHERE id = ?"; // Use parameterized query
$emp_stmt = sqlsrv_query($conn, $emp_sql, array($date[0]));

if ($emp_stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

$emp = sqlsrv_fetch_array($emp_stmt, SQLSRV_FETCH_ASSOC);
sqlsrv_free_stmt($emp_stmt);

// SQL Server query:
$qry_sql = "SELECT * FROM attendance WHERE employee_id = ? AND CONVERT(DATE, datetime_log) = ? ORDER BY datetime_log ASC";  // Correct date comparison and ordering
$qry_stmt = sqlsrv_query($conn, $qry_sql, array($date[0], $dt));

if ($qry_stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}


$att = array(); // Initialize $att array

while ($row = sqlsrv_fetch_array($qry_stmt, SQLSRV_FETCH_ASSOC)) {
    if ($row['log_type'] == 1 || $row['log_type'] == 2) {
        if (!isset($att[$row['log_type']])) { // Check if it's already set
            $att[$row['log_type']] = $row;
        }
    } else {
        $att[$row['log_type']] = $row;
    }
}

sqlsrv_free_stmt($qry_stmt);

?>

<div class="container-fluid">
    <div class="col-ld-12">
        <div class="row">
            <h4><b><?php echo ucwords($emp['enamem']) . ' | ' . $emp['employee_no']; ?></b></h4>
        </div>
        <hr>
        <?php if (isset($att) && is_array($att)): // Check if $att is defined and is an array ?>
        <?php foreach ($att as $k => $v): ?>
            <div class="row">
                <p><b><?php echo $lt_arr[$k]; ?></b></p>
            </div>
            <hr>
            <div class="row form-group">
                <div class="col-md-4">
                    <?php if ($v['datetime_log'] instanceof DateTime): ?>
                        <p><?php echo $v['datetime_log']->format('Y-m-d H:i:s'); ?></p>
                    <?php else: ?>
                        <p>No log recorded.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        <?php else: ?>
            <p>No attendance records found for this date.</p>
        <?php endif; ?>
    </div>
</div>

<style>
    #uni_modal .modal-header {
        display: none
    }
</style>