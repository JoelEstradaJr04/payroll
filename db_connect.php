<?php

// Database configuration
$serverName = "ESTRADAJR\SQLEXPRESS";
$database = "payroll";
$username = "sa";
$password = "password"; // Add your password here

// Connection options
$connectionOptions = array(
    "Database" => $database,
    "Uid" => $username,
    "PWD" => $password,
    "Encrypt" => true, // Enable encryption
    "TrustServerCertificate" => true, // Trust server certificate
    "CharacterSet" => "UTF-8"
);

// Create connection
try {
    $conn = sqlsrv_connect($serverName, $connectionOptions);

    if ($conn === false) {
        // Handle connection errors
        $errors = sqlsrv_errors();
        throw new Exception("Connection failed: " . ($errors ? $errors[0]['message'] : 'Unknown error'));
    }

    // Connection successful message
    echo "<div class='alert alert-success' role='alert'>Successfully connected to the database!</div>"; // Or any other way to display success message

    // Optional: Set connection timeout and query timeout
    sqlsrv_configure("ConnectTimeout", 30);
    sqlsrv_configure("QueryTimeout", 30);

} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}

// Function to close the connection
function closeConnection() {
    global $conn;
    if ($conn) {
        sqlsrv_close($conn);
    }
}

// Register shutdown function to ensure connection is closed
register_shutdown_function('closeConnection');

?>