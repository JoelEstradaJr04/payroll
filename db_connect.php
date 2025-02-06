<?php
$serverName = "DESKTOP-2A9KFDV\SQLEXPRESS";
$database = "payrollXY";
$username = "sa";
$password = "abc123";

$connectionOptions = array(
    "Database" => $database,
    "Uid" => $username,
    "PWD" => $password,
    "TrustServerCertificate" => true
);

try {
    $conn = sqlsrv_connect($serverName, $connectionOptions);
    if($conn === false) {
        throw new Exception(print_r(sqlsrv_errors(), true));
    }
} catch (Exception $e) {
    die(json_encode(['status' => 0, 'message' => 'Connection failed: ' . $e->getMessage()]));
}
?>