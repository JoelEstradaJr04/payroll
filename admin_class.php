<?php
class Action {
    private $conn;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        $serverName = "ESTRADAJR\SQLEXPRESS";
        $database = "payroll";
        $username = "sa";
        $password = "password";

        $connectionOptions = array(
            "Database" => $database,
            "Uid" => $username,
            "PWD" => $password,
            "TrustServerCertificate" => true
        );

        try {
            $this->conn = sqlsrv_connect($serverName, $connectionOptions);

            if ($this->conn === false) {
                $errors = sqlsrv_errors();
                throw new Exception("Connection failed: " . ($errors ? $errors[0]['message'] : 'Unknown error'));
            }
            // REMOVE THIS LINE: echo "<div class='alert alert-success' role='alert'>Successfully connected to the database!</div>";
        } catch (Exception $e) {
            // Log the error, but don't echo it directly.  Handle it where you call connect()
            error_log($e->getMessage()); 
            die(json_encode(['success' => false, 'message' => 'Database connection error.'])); // Return JSON error
        }
    }

    public function __destruct() {
        if($this->conn) {
            sqlsrv_close($this->conn);
        }
    }

	//! DONE [UPDATED!!!]
	public function login() {
		if(!isset($_POST['username']) || !isset($_POST['password'])) {
			return json_encode(['status' => 0, 'message' => 'Missing credentials']);
		}
	
		$username = $_POST['username'];
		$password = $_POST['password'];
		
		$status = 0;
		$message = '';
	
		$query = "{CALL sp_login (?, ?, ?, ?)}";
		$params = array(
			array($username, SQLSRV_PARAM_IN),
			array($password, SQLSRV_PARAM_IN),
			array(&$status, SQLSRV_PARAM_OUT),
			array(&$message, SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_STRING(SQLSRV_ENC_CHAR), SQLSRV_SQLTYPE_NVARCHAR(255))
		);
	
		$stmt = sqlsrv_prepare($this->conn, $query, $params);
		
		if($stmt === false) {
			return json_encode(['status' => 0, 'message' => 'Database error: ' . print_r(sqlsrv_errors(), true)]);
		}
	
		if(sqlsrv_execute($stmt) === false) {
			return json_encode(['status' => 0, 'message' => 'Execution error: ' . print_r(sqlsrv_errors(), true)]);
		}
	
		// Get user data for session if login successful
		if($status == 1) {
			$qry = "SELECT u.*, CONCAT(e.firstname,' ',e.lastname) as name 
					FROM users u 
					LEFT JOIN employee e ON u.employee_id = e.id 
					WHERE u.username = ? AND u.isDeleted = 0";
			
			$stmt2 = sqlsrv_prepare($this->conn, $qry, array($username));
			sqlsrv_execute($stmt2);
			$row = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC);
			
			foreach($row as $key => $value) {
				if($key != 'password') {
					$_SESSION['login_'.$key] = $value;
				}
			}
		}
	
		return json_encode(['status' => $status, 'message' => $message]);
	}


//? DONE [SAVE FUNCTION UPDATED!!!]
//? DONE [SAVE FUNCTION UPDATED!!!]
//? DONE [SAVE FUNCTION UPDATED!!!]
//? DONE [SAVE FUNCTION UPDATED!!!]
//? DONE [SAVE FUNCTION UPDATED!!!]
//? DONE [SAVE FUNCTION UPDATED!!!]
//? DONE [SAVE FUNCTION UPDATED!!!]
//? DONE [SAVE FUNCTION UPDATED!!!]
//? DONE [SAVE FUNCTION UPDATED!!!]
//? DONE [SAVE FUNCTION UPDATED!!!]
//? DONE [SAVE FUNCTION UPDATED!!!]
//? DONE [SAVE FUNCTION UPDATED!!!]
//? DONE [SAVE FUNCTION UPDATED!!!]
	
	//! DONE [UPDATED!!!]
	function save_user(){
		extract($_POST);
		$conn = $this->conn;
	
		$hashed_password = password_hash($password, PASSWORD_DEFAULT);
	
		$query = "{CALL sp_save_user(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)}";
	
		$status = 0;
		$message = "";
	
		$params = array(
			$firstname,
			$middlename,
			$lastname,
			$suffix,
			$employee_no,
			$username,
			$hashed_password,
			$type,
			array(&$status, SQLSRV_PARAM_OUT),
			array(&$message, SQLSRV_PARAM_OUT)
		);
	
		$stmt = sqlsrv_prepare($conn, $query, $params);
	
		if ($stmt === false) {
			die(json_encode(['success' => false, 'message' => sqlsrv_errors()]));
		}
	
		if (sqlsrv_execute($stmt)) {
			return json_encode(['success' => ($status == 1), 'message' => $message]);
		} else {
			$errors = sqlsrv_errors();
			$errorMessage = "Database error occurred: ";
			if ($errors) {
				foreach ($errors as $error) {
					$errorMessage .= "SQLSTATE: " . $error['SQLSTATE'] . ", Code: " . $error['code'] . ", Message: " . $error['message'] . "\n";
				}
			}
			error_log($errorMessage);
			return json_encode(['success' => false, 'message' => 'Database error occurred.']);
		}
	}

	//! DONE [UPDATED!!!]
	function save_deductions() {
		extract($_POST);
		$conn = $this->conn; // Database connection
	
		// Ensure ID is set (for new records)
		$id = isset($id) ? $id : 0;
		$status = 0; // Output status variable
	
		// Prepare stored procedure call
		$query = "{CALL sp_save_deduction (?, ?, ?, ?, ?)}";
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

	//! DONE [UPDATED!!!]
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
		
//? DONE [DELETE FUNCTION UPDATED!!!]
//? DONE [DELETE FUNCTION UPDATED!!!]
//? DONE [DELETE FUNCTION UPDATED!!!]
//? DONE [DELETE FUNCTION UPDATED!!!]
//? DONE [DELETE FUNCTION UPDATED!!!]
//? DONE [DELETE FUNCTION UPDATED!!!]
//? DONE [DELETE FUNCTION UPDATED!!!]
//? DONE [DELETE FUNCTION UPDATED!!!]
//? DONE [DELETE FUNCTION UPDATED!!!]
//? DONE [DELETE FUNCTION UPDATED!!!]
//? DONE [DELETE FUNCTION UPDATED!!!]

	//! DONE [UPDATED, LOADS, AND WORKS]
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

	//! DONE [UPDATED!!!]
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

	//! DONE [UPDATED!!!, MIGHT NEED HANDLER ABOUT ITS DEPENDENTS POSITIONS]
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
	
	//! DONE [UPDATED!!!, MIGHT NEED HANDLER ABOUT PARENT DEPARMTENTS]
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
	

//* 	TO DO LIST:
//* 	TO DO LIST:
//* 	TO DO LIST:
//* 	TO DO LIST:
//* 	TO DO LIST:
//* 	TO DO LIST:
//* 	TO DO LIST:
//* 	TO DO LIST:
//* 	TO DO LIST:
//* 	TO DO LIST:
//* 	TO DO LIST:
//* 	TO DO LIST:
//* 	TO DO LIST:
//* 	TO DO LIST:
//* 	TO DO LIST:
//* 	TO DO LIST:


	//! DONE
	function delete_user() {
		extract($_POST);
		$conn = $this->conn;
	
		// Prepare stored procedure call
		$query = "{CALL sp_delete_user(?, ?, ?)}";
	
		// Define output parameters
		$status = 0;
		$message = "";
	
		$params = array(
			$id, // @UserID
			array(&$status, SQLSRV_PARAM_OUT),
			array(&$message, SQLSRV_PARAM_OUT)
		);
	
		// Prepare and execute the statement
		$stmt = sqlsrv_prepare($conn, $query, $params);
	
		if ($stmt === false) {
			die(json_encode(['success' => false, 'message' => sqlsrv_errors()]));
		}
	
		if (sqlsrv_execute($stmt)) {
			return json_encode(['success' => ($status == 1), 'message' => $message]);
		} else {
			return json_encode(['success' => false, 'message' => 'Database error occurred.']);
		}
	}

	//! DONE
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
	
	// TODO:
	function save_employee_allowance() {
		extract($_POST);
		$conn = $this->conn; // Database connection
	
		foreach ($allowance_id as $k => $v) {
			$status = 0; // Initialize status variable
	
			// Define query with OUTPUT parameter
			$query = "EXEC sp_save_employee_allowance ?, ?, ?, ?, ?, ?";
			$params = array(
				$employee_id,
				$allowance_id[$k],
				$type[$k],
				$amount[$k],
				$effective_date[$k],
				array(&$status, SQLSRV_PARAM_OUT) // Bind output parameter
			);
	
			// Prepare and execute
			$stmt = sqlsrv_prepare($conn, $query, $params);
	
			if ($stmt === false) {
				die("Error in SQL Prepare: " . print_r(sqlsrv_errors(), true)); 
			}
	
			if (!sqlsrv_execute($stmt)) {
				die("Error in SQL Execute: " . print_r(sqlsrv_errors(), true)); 
			}
	
			// Check if status is successful
			if ($status != 1) {
				return 0; // Return failure
			}
		}
	
		return 1; // Return success
	}
	
	/// TO BE TESTED
	function delete_employee_allowance() {
		extract($_POST);
		$conn = $this->conn; // Database connection
		
		$status = 0; // Output variable
	
		// Prepare stored procedure call using sqlsrv_prepare
		$query = "{CALL sp_delete_employee_allowance(?, ?)}";
		$params = array(
			array($id, SQLSRV_PARAM_IN),
			array(&$status, SQLSRV_PARAM_OUT) // Output parameter
		);
	
		// Prepare the statement
		$stmt = sqlsrv_prepare($conn, $query, $params);
	
		// Check if the preparation succeeded
		if ($stmt === false) {
			die(print_r(sqlsrv_errors(), true)); // Debugging if preparation fails
		}
	
		// Execute the stored procedure
		if (sqlsrv_execute($stmt)) {
			return $status; // Return the status from the stored procedure
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

	//! DONE
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
 
    public function save_payroll() {
		$conn = $this->conn;
	
		// Fetch data from POST request
		$id = isset($_POST['id']) && !empty($_POST['id']) ? (int)$_POST['id'] : 0;
		$date_from = isset($_POST['date_from']) ? $_POST['date_from'] : null;
		$date_to = isset($_POST['date_to']) ? $_POST['date_to'] : null;
		$type = isset($_POST['type']) ? (int)$_POST['type'] : null;
		
		// Output parameters
		$ref_no = "";
		
		// Define stored procedure call
		$query = "{CALL sp_save_payroll (?, ?, ?, ?, ?)}";
		$params = array(
			array(&$id, SQLSRV_PARAM_INOUT),  // ID is an output parameter too
			array($date_from, SQLSRV_PARAM_IN),
			array($date_to, SQLSRV_PARAM_IN),
			array($type, SQLSRV_PARAM_IN),
			array(&$ref_no, SQLSRV_PARAM_INOUT)  // ref_no as output
		);
	
		// Prepare the statement
		$stmt = sqlsrv_prepare($conn, $query, $params);
	
		if ($stmt === false) {
			die("SQL Prepare Error: " . print_r(sqlsrv_errors(), true));
		}
	
		// Execute the stored procedure
		if (sqlsrv_execute($stmt)) {
			return ["success" => 1, "id" => $id, "ref_no" => $ref_no];
		} else {
			return ["success" => 0, "error" => sqlsrv_errors()];
		}
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
	function calculate_payroll() {
		extract($_POST);
	
		$am_in = "08:00";
		$am_out = "12:00";
		$pm_in = "13:00";
		$pm_out = "17:00";
	
		$this->conn->query("DELETE FROM payroll_items WHERE payroll_id = $id");
	
		$pay = $this->conn->query("SELECT * FROM payroll WHERE id = $id")->fetch_array();
		$employee = $this->conn->query("SELECT * FROM employee");
	
		// Determine number of working days
		$dm = ($pay['type'] == 1) ? 22 : 11;
		$calc_days = $this->conn->query("SELECT DATEDIFF(DAY, '".$pay['date_from']."', '".$pay['date_to']."') AS days")->fetch_array()['days'];
	
		// Fetch attendance records
		$att = $this->conn->query("SELECT * FROM attendance WHERE CONVERT(DATE, datetime_log) BETWEEN '".$pay['date_from']."' AND '".$pay['date_to']."' ORDER BY datetime_log ASC");
	
		while ($row = $att->fetch_array()) {
			$date = date("Y-m-d", strtotime($row['datetime_log']));
			if ($row['log_type'] == 1 || $row['log_type'] == 3) {
				if (!isset($attendance[$row['employee_id']."_".$date]['log'][$row['log_type']])) {
					$attendance[$row['employee_id']."_".$date]['log'][$row['log_type']] = $row['datetime_log'];
				}
			} else {
				$attendance[$row['employee_id']."_".$date]['log'][$row['log_type']] = $row['datetime_log'];
			}
		}
	
		// Fetch deductions and allowances
		$deductions = $this->conn->query("SELECT * FROM employee_deductions WHERE (type = '".$pay['type']."' OR CONVERT(DATE, effective_date) BETWEEN '".$pay['date_from']."' AND '".$pay['date_from']."')");
		$allowances = $this->conn->query("SELECT * FROM employee_allowances WHERE (type = '".$pay['type']."' OR CONVERT(DATE, effective_date) BETWEEN '".$pay['date_from']."' AND '".$pay['date_from']."')");
	
		while ($row = $deductions->fetch_assoc()) {
			$ded[$row['employee_id']][] = ['did' => $row['deduction_id'], "amount" => $row['amount']];
		}
		while ($row = $allowances->fetch_assoc()) {
			$allow[$row['employee_id']][] = ['aid' => $row['allowance_id'], "amount" => $row['amount']];
		}
	
		// Process payroll for each employee
		while ($row = $employee->fetch_assoc()) {
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
	
			for ($i = 0; $i < $calc_days; $i++) {
				$dd = date("Y-m-d", strtotime($pay['date_from']." +".$i." days"));
				$count = 0;
				if (isset($attendance[$row['id']."_".$dd]['log'])) {
					$count = count($attendance[$row['id']."_".$dd]['log']);
				}
	
				if (isset($attendance[$row['id']."_".$dd]['log'][1]) && isset($attendance[$row['id']."_".$dd]['log'][2])) {
					$att_mn = abs(strtotime($attendance[$row['id']."_".$dd]['log'][2])) - strtotime($attendance[$row['id']."_".$dd]['log'][1]); 
					$att_mn = floor($att_mn / 60);
					$net += ($att_mn * $min);
					$late += (240 - $att_mn);
					$present += .5;
				}
				if (isset($attendance[$row['id']."_".$dd]['log'][3]) && isset($attendance[$row['id']."_".$dd]['log'][4])) {
					$att_mn = abs(strtotime($attendance[$row['id']."_".$dd]['log'][4])) - strtotime($attendance[$row['id']."_".$dd]['log'][3]); 
					$att_mn = floor($att_mn / 60);
					$net += ($att_mn * $min);
					$late += (240 - $att_mn);
					$present += .5;
				}
			}
	
			$ded_arr = [];
			$all_arr = [];
	
			if (isset($allow[$row['id']])) {
				foreach ($allow[$row['id']] as $arow) {
					$all_arr[] = $arow;
					$net += $arow['amount'];
					$allow_amount += $arow['amount'];
				}
			}
	
			if (isset($ded[$row['id']])) {
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
			$data .= ", allowances = '".json_encode($all_arr, JSON_UNESCAPED_UNICODE)."' ";
			$data .= ", deductions = '".json_encode($ded_arr, JSON_UNESCAPED_UNICODE)."' ";
			$data .= ", net = '$net' ";
	
			$save[] = $this->conn->query("INSERT INTO payroll_items SET ".$data);
		}
	
		if (isset($save)) {
			$this->conn->query("UPDATE payroll SET status = 1 WHERE id = ".$pay['id']);
			return 1;
		}
	}
	
	// TODO:
	function update_user() {
		extract($_POST);
		$conn = $this->conn;
	
		$hashed_password = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : NULL;
	
		$query = "{CALL sp_update_user(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)}";
	
		$status = 0;
		$message = "";
	
		$params = array(
			$id, // UserID
			$firstname,
			$middlename,
			$lastname,
			$suffix,
			$employee_no,
			$username,
			$hashed_password, // Hashed password or NULL
			$type,
			array(&$status, SQLSRV_PARAM_OUT),
			array(&$message, SQLSRV_PARAM_OUT)
		);
	
		$stmt = sqlsrv_prepare($conn, $query, $params);
	
		if ($stmt === false) {
			die(json_encode(['success' => false, 'message' => sqlsrv_errors()]));
		}
	
		if (sqlsrv_execute($stmt)) {
			return json_encode(['success' => ($status == 1), 'message' => $message]);
		} else {
			$errors = sqlsrv_errors();
			$errorMessage = "Database error occurred: ";
			if ($errors) {
				foreach ($errors as $error) {
					$errorMessage .= "SQLSTATE: " . $error['SQLSTATE'] . ", Code: " . $error['code'] . ", Message: " . $error['message'] . "\n";
				}
			}
			error_log($errorMessage);
			return json_encode(['success' => false, 'message' => 'Database error occurred.']);
		}
	}

	// TODO:
	function logout(){
		session_destroy();
		foreach ($_SESSION as $key => $value) {
			unset($_SESSION[$key]);
		}
		header("location:login.php");
	}

}