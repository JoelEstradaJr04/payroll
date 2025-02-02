<?php
class Action {
    private $conn;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        $serverName = "DESKTOP-2A9KFDV\SQLEXPRESS";
        $database = "PAYROLL2";
        $username = "sa";
        $password = "abc123"; // Add your password here

        $connectionOptions = array(
            "Database" => $database,
            "Uid" => $username,
            "PWD" => $password,
            "TrustServerCertificate" => true
        );

        try {
            $this->conn = sqlsrv_connect($serverName, $connectionOptions);
            
            if($this->conn === false) {
                $errors = sqlsrv_errors();
                throw new Exception("Connection failed: " . ($errors ? $errors[0]['message'] : 'Unknown error'));
            }
            echo "<div class='alert alert-success' role='alert'>Successfully connected to the database!</div>";
        } catch(Exception $e) {
            echo "<div class='alert alert-danger' role='alert'>" . $e->getMessage() . "</div>";
            die();
        }
    }

    public function __destruct() {
        if($this->conn) {
            sqlsrv_close($this->conn);
        }
    }
	public function login() {
		// Start session if not already started
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}
	
		if (!isset($_POST['username']) || !isset($_POST['password'])) {
			return 3;
		}
	
		$username = $_POST['username'];
		$password = $_POST['password'];
	
		$query = "SELECT id, employee_id, name, username, type 
				  FROM users 
				  WHERE username = ? 
				  AND password = ? 
				  AND isDeleted = 0";
	
		// Prepare the statement with parameters passed by reference
		$stmt = sqlsrv_prepare($this->conn, $query, array(&$username, &$password));
	
		if ($stmt === false) {
			die(print_r(sqlsrv_errors(), true)); // Debugging
			return 3;
		}
	
		if (sqlsrv_execute($stmt)) {
			$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
	
			if ($row) {
				// Store user data in session
				foreach ($row as $key => $value) {
					if ($key != 'password') {
						$_SESSION['login_' . $key] = $value;
					}
				}
	
				// Get employee details
				$emp_query = "SELECT firstname, lastname, department_id, position_id 
							  FROM employee 
							  WHERE id = ? 
							  AND isDeleted = 0";
	
				$emp_stmt = sqlsrv_prepare($this->conn, $emp_query, array(&$row['employee_id']));
	
				if ($emp_stmt === false) {
					die(print_r(sqlsrv_errors(), true)); // Debugging
					return 3;
				}
	
				if (sqlsrv_execute($emp_stmt)) {
					$emp_row = sqlsrv_fetch_array($emp_stmt, SQLSRV_FETCH_ASSOC);
					if ($emp_row) {
						foreach ($emp_row as $key => $value) {
							$_SESSION['login_' . $key] = $value;
						}
					}
				}
	
				sqlsrv_free_stmt($emp_stmt);
				sqlsrv_free_stmt($stmt);
	
				// Set a login ID to check redirect condition
				$_SESSION['login_id'] = $row['id'];
	
				return 1;
			}
		}
	
		sqlsrv_free_stmt($stmt);
		return 3;
	}
	
	function logout(){
		session_destroy();
		foreach ($_SESSION as $key => $value) {
			unset($_SESSION[$key]);
		}
		header("location:login.php");
	}

	// TODO: 
	function save_user(){
	extract($_POST);
	$conn = $this->conn; // Assuming $this->conn is the database connection

	// Prepare stored procedure call using sqlsrv_prepare
	$query = "EXEC sp_save_user ?, ?, ?, ?, ?, ?";
	$params = array($id, 9, $name, $username, $password, $type);

	// Prepare the statement using sqlsrv_prepare
	$stmt = sqlsrv_prepare($conn, $query, $params);

	// Check if the preparation succeeded
	if ($stmt === false) {
		die("Statement preparation failed: " . print_r(sqlsrv_errors(), true)); // Debugging if preparation fails
	}

	// Execute the stored procedure
	if (!sqlsrv_execute($stmt)) {
		die("Execution failed: " . print_r(sqlsrv_errors(), true)); // Debugging if execution fails
	} 

	// Fetch result
	$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
	if ($row) {
		return $row['status']; // Should return 1 if successful
	}

	// Check for additional SQL errors
	if ($errors = sqlsrv_errors()) {
		die("SQL Server Error: " . print_r($errors, true));
	}

	return 0; // Failure case

	}
	//! DONE
	function delete_user() {
		extract($_POST);
		$conn = $this->conn; // Database connection
	
		// Prepare stored procedure call using sqlsrv_prepare
		$query = "{CALL SP_Delete_User(?)}";
		$params = array($id);
	
		// Prepare the statement using sqlsrv_prepare
		$stmt = sqlsrv_prepare($conn, $query, $params);
	
		// Check if the preparation succeeded
		if ($stmt === false) {
			die(print_r(sqlsrv_errors(), true)); // Debugging if preparation fails
		}
	
		// Execute the stored procedure
		if (sqlsrv_execute($stmt)) {
			return 1; // Should return 1 if successful
		} else {
			// Debugging: Print errors if execution fails
			echo '<pre>';
			echo "Execution failed:\n";
			print_r(sqlsrv_errors());
			echo '</pre>';
		}
	
		return 0; // Failure case
	}

	// TODO:
	function save_employee() {
		extract($_POST);
		$conn = $this->conn; // Database connection
	
		// Define the output parameters
		$status = 0;
		$id = (!empty($id)) ? $id : 0; // Ensure ID is set to 0 for new records
		$employee_no = (!empty($employee_no)) ? $employee_no : null;
	
		// Prepare stored procedure call
		$query = "{CALL sp_save_employee(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)}";
		$params = array(
			array(&$id, SQLSRV_PARAM_INOUT), // Employee ID as input/output
			&$employee_no,  // Employee No (If auto-generated, can be null)
			&$firstname,
			&$middlename,
			&$lastname,
			&$suffix,
			&$department_id,
			&$position_id,
			&$salary,
			array(&$status, SQLSRV_PARAM_INOUT) // Output status
		);
	
		// Execute stored procedure
		$stmt = sqlsrv_query($conn, $query, $params);
	
		if ($stmt === false) {
			die("Error saving employee: " . print_r(sqlsrv_errors(), true)); // Debugging
		}
	
		return $status; // 1 = Insert, 2 = Update, -1 = Not Found, -2 = FK Error
	}
	
	
	
	//! DONE
	function delete_employee() {
		extract($_POST);
		$conn = $this->conn; // Database connection
	
		// Prepare stored procedure call using sqlsrv_prepare
		$query = "EXEC SP_Delete_Employee @p_id= ?";
		$params = array($id);
	
		// Prepare the statement using sqlsrv_prepare
		$stmt = sqlsrv_prepare($conn, $query, $params);
	
		// Check if the preparation succeeded
		if ($stmt === false) {
			die(print_r(sqlsrv_errors(), true)); // Debugging if preparation fails
		}
	
		// Execute the stored procedure
		if (sqlsrv_execute($stmt)) {
			// Fetch result
			if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
				return $row['status']; // Should return 1 if successful
			}
		}
	
		return 0; // Failure case
	}
	
	//! DONE
	function save_department() {
		extract($_POST);
		$conn = $this->conn; // Database connection
	
		// Define the output parameter for status
		$status = 0;
		$id = ($id) ? $id : 0; // Ensure ID is set to 0 for new records
	
		// Prepare stored procedure call using sqlsrv_prepare
		$query = "{CALL sp_save_department(?, ?, ?)}";
		$params = array(
			$id,
			$name,
			array(&$status, SQLSRV_PARAM_INOUT) // Output status
		);
	
		// Prepare the statement using sqlsrv_prepare
		$stmt = sqlsrv_prepare($conn, $query, $params);
	
		// Check if the preparation succeeded
		if ($stmt === false) {
			die(print_r(sqlsrv_errors(), true)); // Debugging if preparation fails
		}
	
		// Execute the stored procedure
		if (sqlsrv_execute($stmt)) {
			return $status; // Return operation status
		}
	
		return 0; // Failure case
	}
	
	//! DONE
	function delete_department() {
		extract($_POST);
		$conn = $this->conn; // Database connection
	
		// Prepare stored procedure call using sqlsrv_prepare
		$query = "EXEC sp_delete_department ?";
		$params = array($id);
	
		// Prepare the statement using sqlsrv_prepare
		$stmt = sqlsrv_prepare($conn, $query, $params);
	
		// Check if the preparation succeeded
		if ($stmt === false) {
			die(print_r(sqlsrv_errors(), true)); // Debugging if preparation fails
		}
	
		// Execute the stored procedure
		if (sqlsrv_execute($stmt)) {
			// Fetch result
			if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
				return $row['status']; // Should return 1 if successful
			}
		}
	
		return 0; // Failure case
	}

	// TODO:
	function save_position() {
		extract($_POST);
		$conn = $this->conn; // Database connection
	
		// Ensure ID is set (for new records)
		$id = isset($id) ? $id : 0;
		$status = 0; // Output status variable
	
		// Prepare stored procedure call with @status as an output parameter
		$query = "EXEC sp_save_position ?, ?, ?, ?, ?";
		$params = array(&$id, $name, $department_id, 0, &$status); // isDeleted is set to 0 by default
	
		// Prepare the statement using sqlsrv_prepare
		$stmt = sqlsrv_prepare($conn, $query, $params);
	
		// Check if preparation succeeded
		if ($stmt === false) {
			die("Statement preparation failed: " . print_r(sqlsrv_errors(), true));
		}
	
		// Execute the stored procedure
		if (!sqlsrv_execute($stmt)) {
			die("Execution failed: " . print_r(sqlsrv_errors(), true));
		}
	
		// Fetch the result
		$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
		if ($row) {
			return $row['status']; // Should return 1 if successful
		}
	
		// Check for additional SQL errors
		if ($errors = sqlsrv_errors()) {
			die("SQL Server Error: " . print_r($errors, true));
		}
	
		return 0; // Failure case
	}
	
	
	//! DONE
	function delete_position() {
		extract($_POST);
		$conn = $this->conn; // Database connection
	
		// Prepare stored procedure call using sqlsrv_prepare
		$query = "EXEC sp_delete_position ?";
		$params = array($id);
	
		// Prepare the statement using sqlsrv_prepare
		$stmt = sqlsrv_prepare($conn, $query, $params);
	
		// Check if the preparation succeeded
		if ($stmt === false) {
			die(print_r(sqlsrv_errors(), true)); // Debugging if preparation fails
		}
	
		// Execute the stored procedure
		if (sqlsrv_execute($stmt)) {
			// Fetch result
			if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
				return $row['status']; // Should return 1 if successful
			}
		}
	
		return 0; // Failure case
	}

	//! DONE
	function save_allowances() {
		extract($_POST);
		$conn = $this->conn; // Database connection
	
		// Ensure ID is set (for new records)
		$id = isset($id) ? $id : 0;
		$isDeleted = 0; // Default to not deleted
		$status = 0; // Output status variable
	
		// Define stored procedure execution
		$query = "{CALL sp_save_allowances(?, ?, ?, ?, ?)}";
		$params = array(
			array(&$id, SQLSRV_PARAM_IN), 
			array($allowance, SQLSRV_PARAM_IN), 
			array($description, SQLSRV_PARAM_IN), 
			array($isDeleted, SQLSRV_PARAM_IN), // Default is 0
			array(&$status, SQLSRV_PARAM_OUT) // Correct output parameter binding
		);
	
		// Prepare statement
		$stmt = sqlsrv_prepare($conn, $query, $params);
		if ($stmt === false) {
			die("Statement preparation failed: " . print_r(sqlsrv_errors(), true));
		}
	
		// Execute stored procedure
		if (!sqlsrv_execute($stmt)) {
			die("Execution failed: " . print_r(sqlsrv_errors(), true));
		}
	
		// Return the output status (1 if successful, 0 if failure)
		return $status;
	}
	
	//! DONE
	function delete_allowances() {
		extract($_POST);
		$conn = $this->conn; // Database connection
	
		// Prepare stored procedure call using sqlsrv_prepare
		$query = "EXEC sp_delete_allowance ?";
		$params = array($id);
	
		// Prepare the statement using sqlsrv_prepare
		$stmt = sqlsrv_prepare($conn, $query, $params);
	
		// Check if the preparation succeeded
		if ($stmt === false) {
			die(print_r(sqlsrv_errors(), true)); // Debugging if preparation fails
		}
	
		// Execute the stored procedure
		if (sqlsrv_execute($stmt)) {
			// Fetch result
			if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
				return $row['status']; // Should return 1 if successful
			}
		}
	
		return 0; // Failure case
	}
	
	// TODO:
	function save_employee_allowance() {
		extract($_POST);
		$conn = $this->conn; // Database connection
	
		foreach ($allowance_id as $k => $v) {
			// Prepare stored procedure call using sqlsrv_prepare
			$query = "EXEC sp_save_employee_allowance ?, ?, ?, ?, ?, @status OUTPUT";
			$params = array($employee_id, $allowance_id[$k], $type[$k], $amount[$k], $effective_date[$k]);
	
			// Prepare the statement using sqlsrv_prepare
			$stmt = sqlsrv_prepare($conn, $query, $params);
	
			// Check if the preparation succeeded
			if ($stmt === false) {
				die(print_r(sqlsrv_errors(), true)); // Debugging if preparation fails
			}
	
			// Execute the stored procedure
			if (!sqlsrv_execute($stmt)) {
				return 0; // Failure case
			}
		}
	
		return 1; // Success case
	}
	
	/// TO BE TESTED
	function delete_employee_allowance() {
		extract($_POST);
		$conn = $this->conn; // Database connection
	
		// Prepare stored procedure call using sqlsrv_prepare
		$query = "EXEC sp_delete_employee_allowance ?";
		$params = array($id);
	
		// Prepare the statement using sqlsrv_prepare
		$stmt = sqlsrv_prepare($conn, $query, $params);
	
		// Check if the preparation succeeded
		if ($stmt === false) {
			die(print_r(sqlsrv_errors(), true)); // Debugging if preparation fails
		}
	
		// Execute the stored procedure
		if (sqlsrv_execute($stmt)) {
			// Fetch result
			if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
				return $row['status']; // Should return 1 if successful
			}
		}
	
		return 0; // Failure case
	}

	//! DONE
	function save_deductions() {
		extract($_POST);
		$conn = $this->conn; // Database connection
	
		// Ensure ID is set (for new records)
		$id = isset($id) ? $id : 0;
		$status = 0; // Output status variable
	
		// Prepare stored procedure call
		$query = "{CALL sp_save_employee_deduction (?, ?, ?, ?, ?)}";
		$params = array(&$id, $deduction, $description, 0, &$status); // isDeleted default to 0
	
		// Prepare the statement using sqlsrv_prepare
		$stmt = sqlsrv_prepare($conn, $query, $params);
	
		// Check if preparation succeeded
		if ($stmt === false) {
			die("Statement preparation failed: " . print_r(sqlsrv_errors(), true));
		}
	
		// Execute the stored procedure
		if (!sqlsrv_execute($stmt)) {
			die("Execution failed: " . print_r(sqlsrv_errors(), true));
		}
	
		// Fetch the output status manually
		$query_status = "SELECT @status AS status";
		$stmt_status = sqlsrv_query($conn, $query_status);
	
		if ($stmt_status === false) {
			die("Status retrieval failed: " . print_r(sqlsrv_errors(), true));
		}
	
		$row = sqlsrv_fetch_array($stmt_status, SQLSRV_FETCH_ASSOC);
		return $row ? $row['status'] : 0; // Return the output status
	}
	
	//! DONE
	function delete_deductions() {
		extract($_POST);
		$conn = $this->conn; // Database connection
	
		// Prepare stored procedure call using sqlsrv_prepare
		$query = "EXEC sp_delete_deduction ?";
		$params = array($id);
	
		// Prepare the statement using sqlsrv_prepare
		$stmt = sqlsrv_prepare($conn, $query, $params);
	
		// Check if the preparation succeeded
		if ($stmt === false) {
			die(print_r(sqlsrv_errors(), true)); // Debugging if preparation fails
		}
	
		// Execute the stored procedure
		if (sqlsrv_execute($stmt)) {
			// Fetch result
			if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
				return $row['status']; // Should return 1 if successful
			}
		}
	
		return 0; // Failure case
	}
	
	// TODO:
	function save_employee_deduction() {
		extract($_POST);
		$conn = $this->conn; // Database connection
	
		foreach ($deduction_id as $k => $v) {
			// Prepare stored procedure call using sqlsrv_prepare
			$query = "EXEC sp_save_employee_deduction ?, ?, ?, ?, ?, @status OUTPUT";
			$params = array($employee_id, $deduction_id[$k], $type[$k], $amount[$k], $effective_date[$k]);
	
			// Prepare the statement using sqlsrv_prepare
			$stmt = sqlsrv_prepare($conn, $query, $params);
	
			// Check if the preparation succeeded
			if ($stmt === false) {
				die(print_r(sqlsrv_errors(), true)); // Debugging if preparation fails
			}
	
			// Execute the stored procedure
			if (!sqlsrv_execute($stmt)) {
				return 0; // Failure case
			}
		}
	
		return 1; // Success case
	}
	
	/// TO BE TESTED
	function delete_employee_deduction() {
		extract($_POST);
		$conn = $this->conn; // Database connection
	
		// Prepare stored procedure call using sqlsrv_prepare
		$query = "EXEC sp_delete_employee_deduction ?";
		$params = array($id);
	
		// Prepare the statement using sqlsrv_prepare
		$stmt = sqlsrv_prepare($conn, $query, $params);
	
		// Check if the preparation succeeded
		if ($stmt === false) {
			die(print_r(sqlsrv_errors(), true)); // Debugging if preparation fails
		}
	
		// Execute the stored procedure
		if (sqlsrv_execute($stmt)) {
			// Fetch result
			if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
				return $row['status']; // Should return 1 if successful
			}
		}
	
		return 0; // Failure case
	}

	// TODO:
	function save_employee_attendance() {
		extract($_POST);
		$conn = $this->conn; // Database connection
	
		foreach ($employee_id as $k => $v) {
			$datetime_log[$k] = date("Y-m-d H:i", strtotime($datetime_log[$k]));
	
			// Define the output parameter
			$status = 0;
			$outputParam = array(&$status); // Pass by reference
	
			$query = "DECLARE @output_status INT; 
					  EXEC sp_save_employee_attendance ?, ?, ?, @output_status OUTPUT;
					  SELECT @output_status;";
	
			$params = array($employee_id[$k], $log_type[$k], $datetime_log[$k]);
	
			// Execute Query
			$stmt = sqlsrv_query($conn, $query, $params, array("Scrollable" => SQLSRV_CURSOR_STATIC));
	
			if ($stmt === false) {
				die("SQL Execution Error: " . print_r(sqlsrv_errors(), true)); // Debugging
			}
	
			// Fetch output parameter value
			sqlsrv_fetch($stmt);
			$status = sqlsrv_get_field($stmt, 0); 
	
			if ($status != 1) {
				return 0; // Failure case
			}
		}
	
		return 1; // Success case
	}
	
	
	//! DONE
	function delete_employee_attendance() {
		extract($_POST);
		$date = explode('_', $id);
		$dt = date("Y-m-d", strtotime($date[1]));
		$employee_id = $date[0];
		
		$conn = $this->conn; // Database connection
	
		// Prepare stored procedure call using sqlsrv_prepare
		$query = "EXEC sp_delete_employee_attendance ?, ?";
		$params = array($employee_id, $dt);
	
		// Prepare the statement using sqlsrv_prepare
		$stmt = sqlsrv_prepare($conn, $query, $params);
	
		// Check if the preparation succeeded
		if ($stmt === false) {
			die(print_r(sqlsrv_errors(), true)); // Debugging if preparation fails
		}
	
		// Execute the stored procedure
		if (sqlsrv_execute($stmt)) {
			// Fetch result
			if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
				return $row['status']; // Should return 1 if successful
			}
		}
	
		return 0; // Failure case
	}

	//! DONE
	function delete_employee_attendance_single() {
		extract($_POST);
		$conn = $this->conn; // Database connection
	
		// Prepare stored procedure call using sqlsrv_prepare
		$query = "EXEC sp_delete_employee_attendance_single ?";
		$params = array($id);
	
		// Prepare the statement using sqlsrv_prepare
		$stmt = sqlsrv_prepare($conn, $query, $params);
	
		// Check if the preparation succeeded
		if ($stmt === false) {
			die(print_r(sqlsrv_errors(), true)); // Debugging if preparation fails
		}
	
		// Execute the stored procedure
		if (sqlsrv_execute($stmt)) {
			// Fetch result
			if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
				return $row['status']; // Should return 1 if successful
			}
		}
	
		return 0; // Failure case
	}
	
	// TODO:
 
    public function save_payroll($id,$date_from,$date_to,$type) {
        $id = isset($_POST['id']) ? $_POST['id'] : null;
        $date_from = isset($_POST['date_from']) ? $_POST['date_from'] : null;
        $date_to = isset($_POST['date_to']) ? $_POST['date_to'] : null;
        $type = isset($_POST['type']) ? $_POST['type'] : null;
        $conn = $this->conn; // Database connection

        // Initialize the output parameter
        $ref_no = '';

        // Prepare stored procedure call using sqlsrv_prepare
        $query = "{CALL sp_save_payroll (?, ?, ?, ?, ?)}";
        $params = array(
            $id,
            $date_from,
            $date_to,
            $type,
            array(&$ref_no, SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_STRING(SQLSRV_ENC_CHAR)) // Output parameter
        );

        // Prepare the statement using sqlsrv_prepare
        $stmt = sqlsrv_prepare($conn, $query, $params);

        // Check if the preparation succeeded
        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true)); // Debugging if preparation fails
        }

        // Execute the stored procedure
        if (sqlsrv_execute($stmt)) {
            // Fetch result
            if (empty($id)) {
                $result = sqlsrv_query($conn, "SELECT @ref_no AS ref_no");
                if ($result) {
                    $row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
                    $ref_no = $row['ref_no'];
                }
            }

            return 1; // Success case
        }

        return 0; // Failure case
    }
	
	//! DONE
	function delete_payroll() {
		extract($_POST);
		$conn = $this->conn; // Database connection
	
		// Prepare stored procedure call using sqlsrv_prepare
		$query = "EXEC sp_delete_payroll ?";
		$params = array($id);
	
		// Prepare the statement using sqlsrv_prepare
		$stmt = sqlsrv_prepare($conn, $query, $params);
	
		// Check if the preparation succeeded
		if ($stmt === false) {
			die(print_r(sqlsrv_errors(), true)); // Debugging if preparation fails
		}
	
		// Execute the stored procedure
		if (sqlsrv_execute($stmt)) {
			// Fetch result
			if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
				return $row['status']; // Should return 1 if successful
			}
		}
	
		return 0; // Failure case
	}
	
	/// TO BE CHECKED
	function calculate_payroll(){
		extract($_POST);
		$am_in = "08:00";
		$am_out = "12:00";
		$pm_in = "13:00";
		$pm_out = "17:00";
		$this->conn->query("DELETE FROM payroll_items WHERE payroll_id=".$id);
		$pay = $this->conn->query("SELECT * FROM payroll WHERE id = ".$id)->fetch_array();
		$employee = $this->conn->query("SELECT * FROM employee");
		if($pay['type'] == 1)
			$dm = 22;
		else
			$dm = 11;
		$calc_days = abs(strtotime($pay['date_to']." 23:59:59")) - strtotime($pay['date_from']." 00:00:00 -1 day"); 
		$calc_days = floor($calc_days / (60*60*24));
		$att = $this->conn->query("SELECT * FROM attendance WHERE CONVERT(date, datetime_log) BETWEEN '".$pay['date_from']."' AND '".$pay['date_from']."' ORDER BY DATEDIFF(SECOND, '1970-01-01', datetime_log) ASC") or die(sqlsrv_errors());
		while($row = $att->fetch_array()){
			$date = date("Y-m-d", strtotime($row['datetime_log']));
			if($row['log_type'] == 1 || $row['log_type'] == 3){
				if(!isset($attendance[$row['employee_id']."_".$date]['log'][$row['log_type']]))
					$attendance[$row['employee_id']."_".$date]['log'][$row['log_type']] = $row['datetime_log'];
			} else {
				$attendance[$row['employee_id']."_".$date]['log'][$row['log_type']] = $row['datetime_log'];
			}
		}
		$deductions = $this->conn->query("SELECT * FROM employee_deductions WHERE (`type` = '".$pay['type']."' OR (CONVERT(date, effective_date) BETWEEN '".$pay['date_from']."' AND '".$pay['date_from']."'))");
		$allowances = $this->conn->query("SELECT * FROM employee_allowances WHERE (`type` = '".$pay['type']."' OR (CONVERT(date, effective_date) BETWEEN '".$pay['date_from']."' AND '".$pay['date_from']."'))");
		while($row = $deductions->fetch_assoc()){
			$ded[$row['employee_id']][] = array('did'=>$row['deduction_id'], "amount"=>$row['amount']);
		}
		while($row = $allowances->fetch_assoc()){
			$allow[$row['employee_id']][] = array('aid'=>$row['allowance_id'], "amount"=>$row['amount']);
		}
		while($row = $employee->fetch_assoc()){
			$salary = $row['salary'];
			$daily = $salary / 22;
			$min = (($salary / 22) / 8) / 60;
			$absent = 0;
			$late = 0;
			$dp = 22 / $pay['type'];
			$present = 0;
			$net = 0;
			$allow_amount = 0;
			$ded_amount = 0;
	
			for($i = 0; $i < $calc_days; $i++){
				$dd = date("Y-m-d", strtotime($pay['date_from']." +".$i." days"));
				$count = 0;
				$p = 0;
				if(isset($attendance[$row['id']."_".$dd]['log']))
					$count = count($attendance[$row['id']."_".$dd]['log']);
					
				if(isset($attendance[$row['id']."_".$dd]['log'][1]) && isset($attendance[$row['id']."_".$dd]['log'][2])){
					$att_mn = abs(strtotime($attendance[$row['id']."_".$dd]['log'][2])) - strtotime($attendance[$row['id']."_".$dd]['log'][1]); 
					$att_mn = floor($att_mn / 60);
					$net += ($att_mn * $min);
					$late += (240 - $att_mn);
					$present += .5;
				}
				if(isset($attendance[$row['id']."_".$dd]['log'][3]) && isset($attendance[$row['id']."_".$dd]['log'][4])){
					$att_mn = abs(strtotime($attendance[$row['id']."_".$dd]['log'][4])) - strtotime($attendance[$row['id']."_".$dd]['log'][3]); 
					$att_mn = floor($att_mn / 60);
					$net += ($att_mn * $min);
					$late += (240 - $att_mn);
					$present += .5;
				}
			}
			$ded_arr = array();
			$all_arr = array();
			if(isset($allow[$row['id']])){
				foreach ($allow[$row['id']] as $arow) {
					$all_arr[] = $arow;
					$net += $arow['amount'];
					$allow_amount += $arow['amount'];
				}
			}
			if(isset($ded[$row['id']])){
				foreach ($ded[$row['id']] as $drow) {
					$ded_arr[] = $drow;
					$net -= $drow['amount'];
					$ded_amount += $drow['amount'];
				}
			}
			$absent = $dp - $present; 
			$data = " payroll_id = '".$pay['id']."' ";
			$data .= ", employee_id = '".$row['id']."' ";
			$data .= ", absent = '$absent' ";
			$data .= ", present = '$present' ";
			$data .= ", late = '$late' ";
			$data .= ", salary = '$salary' ";
			$data .= ", allowance_amount = '$allow_amount' ";
			$data .= ", deduction_amount = '$ded_amount' ";
			$data .= ", allowances = '".json_encode($all_arr)."' ";
			$data .= ", deductions = '".json_encode($ded_arr)."' ";
			$data .= ", net = '$net' ";
			$save[] = $this->conn->query("INSERT INTO payroll_items SET ".$data);
		}
		if(isset($save)){
			$this->conn->query("UPDATE payroll SET status = 1 WHERE id = ".$pay['id']);
			return 1;
		}
	}
}