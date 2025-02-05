<?php
class Action {
    private $conn;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        $serverName = "DESKTOP-2A9KFDV\SQLEXPRESS";
        $database = "payroll9";
        $username = "sa";
        $password = "abc123";

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
			array(&$message, SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_STRING(SQLSRV_ENC_CHAR))
		);

		// Prepare and execute the statement
		$stmt = sqlsrv_prepare($conn, $query, $params);

		if ($stmt === false) {
			return json_encode(['success' => false, 'message' => sqlsrv_errors()]);
		}

		if (sqlsrv_execute($stmt)) {
			return json_encode(['success' => ($status == 1), 'message' => $message]);
		}
		
		return json_encode(['success' => false, 'message' => 'Database error occurred.']);
	}


	//! DONE

    function save_employee() {
        extract($_POST);
        $conn = $this->conn;

        // Define output parameters
        $status = 0;
        $message = '';
        $id = (!empty($id)) ? $id : 0;
        $employee_no = (!empty($employee_no)) ? $employee_no : null;

        // Prepare stored procedure call
        $query = "{CALL sp_save_employee(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)}";
        $params = array(
            array(&$id, SQLSRV_PARAM_INOUT),
            &$employee_no,
            &$firstname,
            &$middlename,
            &$lastname,
            &$suffix,
            &$department_id,
            &$position_id,
            &$salary,
            array(&$status, SQLSRV_PARAM_INOUT),
            array(&$message, SQLSRV_PARAM_INOUT, SQLSRV_PHPTYPE_STRING(SQLSRV_ENC_CHAR), SQLSRV_SQLTYPE_NVARCHAR(200))
        );

        $stmt = sqlsrv_query($conn, $query, $params);

        if ($stmt === false) {
            error_log("Error in save_employee: " . print_r(sqlsrv_errors(), true));
            return json_encode(array(
                'status' => -99,
                'message' => 'Database error occurred'
            ));
        }

        return json_encode(array(
            'status' => $status,
            'message' => $message
        ));
    }


	//! DONE
    function delete_employee() {
        extract($_POST);
        $conn = $this->conn;

        // Define output parameters
        $status = 0;
        $message = '';

        // Prepare stored procedure call
        $query = "{CALL sp_delete_employee(?, ?, ?)}";
        $params = array(
            &$id,
            array(&$status, SQLSRV_PARAM_INOUT),
            array(&$message, SQLSRV_PARAM_INOUT, SQLSRV_PHPTYPE_STRING(SQLSRV_ENC_CHAR), SQLSRV_SQLTYPE_NVARCHAR(200))
        );

        $stmt = sqlsrv_query($conn, $query, $params);

        if ($stmt === false) {
            error_log("Error in delete_employee: " . print_r(sqlsrv_errors(), true));
            return json_encode(array(
                'status' => -99,
                'message' => 'Database error occurred'
            ));
        }

        return json_encode(array(
            'status' => $status,
            'message' => $message
        ));
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
		if (!isset($_POST['id'])) {
			die("Error: Missing payroll ID.");
		}
	
		$id = $_POST['id']; // Get Payroll ID
	
		// Establish database connection
		$conn = $this->conn;
	
		// Delete existing payroll items
		$sql = "DELETE FROM payroll_items WHERE payroll_id = ?";
		$stmt = sqlsrv_query($conn, $sql, [$id]);
	
		if ($stmt === false) {
			die("Error deleting payroll items: " . print_r(sqlsrv_errors(), true));
		}
	
		// Fetch payroll details
		$sql = "SELECT * FROM payroll WHERE id = ?";
		$stmt = sqlsrv_query($conn, $sql, [$id]);
	
		if ($stmt === false) {
			die("Error fetching payroll data: " . print_r(sqlsrv_errors(), true));
		}
	
		$pay = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
	
		if (!$pay) {
			die("Error: Payroll record not found.");
		}
	
		if (!isset($pay['type']) || $pay['type'] == 0) {
			die("Error: Invalid payroll type.");
		}
	
		// Determine number of working days
		$dm = ($pay['type'] == 1) ? 22 : 11;
	
		if ($dm == 0) {
			die("Error: Division by zero detected in working days calculation.");
		}
	
		// Call the stored procedure to process payroll
		$sql = "{CALL sp_calculate_payroll(?)}";
		$stmt = sqlsrv_query($conn, $sql, [$id]);
	
		if ($stmt === false) {
			die("Error executing stored procedure: " . print_r(sqlsrv_errors(), true));
		}
	
		// Update payroll status
		$sql = "UPDATE payroll SET status = 1 WHERE id = ?";
		$stmt = sqlsrv_query($conn, $sql, [$id]);
	
		if ($stmt === false) {
			die("Error updating payroll status: " . print_r(sqlsrv_errors(), true));
		}
	
		return 1; // Success
	}	
	
	// TODO:
	function update_user() {
		// Start output buffering to prevent accidental output
		ob_start();
	
		// Extract POST data
		extract($_POST);
		$conn = $this->conn;
	
		// Hash the password if provided
		$hashed_password = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : NULL;
	
		// Define the stored procedure call
		$query = "{CALL sp_update_user(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)}";
	
		// Initialize output parameters
		$status = 0;
		$message = "";
	
		// Prepare parameters for the stored procedure
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
	
		// Prepare the SQL statement
		$stmt = sqlsrv_prepare($conn, $query, $params);
	
		if ($stmt === false) {
			// Clean the buffer and return a JSON error
			ob_end_clean();
			return json_encode(['success' => false, 'message' => 'Failed to prepare statement: ' . print_r(sqlsrv_errors(), true)]);
		}
	
		// Execute the statement
		if (sqlsrv_execute($stmt)) {
			// Clean the buffer and return success
			ob_end_clean();
			return json_encode(['success' => ($status == 1), 'message' => $message]);
		} else {
			// Handle execution errors
			$errors = sqlsrv_errors();
			$errorMessage = "Database error occurred: ";
			if ($errors) {
				foreach ($errors as $error) {
					$errorMessage .= "SQLSTATE: " . $error['SQLSTATE'] . ", Code: " . $error['code'] . ", Message: " . $error['message'] . "\n";
				}
			}
			// Log the error
			error_log($errorMessage);
	
			// Clean the buffer and return a JSON error
			ob_end_clean();
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

	function load_employee_table() {
		$conn = $this->conn;
		
		$sql = "SELECT e.*, d.name as department_name, p.name as position_name 
				FROM employee e 
				LEFT JOIN department d ON e.department_id = d.id 
				LEFT JOIN position p ON e.position_id = p.id 
				WHERE e.isDeleted = 0 
				ORDER BY e.id DESC";
				
		$stmt = sqlsrv_query($conn, $sql);
		
		if($stmt === false) {
			die(print_r(sqlsrv_errors(), true));
		}
		
		ob_start(); // Start output buffering
		?>
		<table class="table table-bordered table-hover">
			<thead>
				<tr>
					<th>Employee No</th>
					<th>Name</th>
					<th>Department</th>
					<th>Position</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)): ?>
				<tr>
					<td><?php echo $row['employee_no'] ?></td>
					<td><?php echo $row['lastname'].', '.$row['firstname'].' '.$row['middlename'].' '.$row['suffix'] ?></td>
					<td><?php echo $row['department_name'] ?></td>
					<td><?php echo $row['position_name'] ?></td>
					<td>
						<button type="button" class="btn btn-sm btn-primary edit_employee" data-id="<?php echo $row['id'] ?>">Edit</button>
						<button type="button" class="btn btn-sm btn-danger delete_employee" data-id="<?php echo $row['id'] ?>">Delete</button>
					</td>
				</tr>
				<?php endwhile; ?>
			</tbody>
		</table>
		<?php
		return ob_get_clean(); // Return the buffered content
	}




















	//? DONE [UPDATED WORKING!!!]
	function save_employee_attendance() {
		extract($_POST);
		$conn = $this->conn; // Database connection
	
		foreach ($employee_id as $k => $v) {
			$datetime_log[$k] = date("Y-m-d H:i", strtotime($datetime_log[$k]));
	
			// Define the output parameter
			$status = 0;
			$outputParam = array(&$status); // Pass by reference
	
			$query = "EXEC sp_save_employee_attendance ?, ?, ?, ? OUTPUT";
	
			// Parameters array, including the output parameter
			$params = array($employee_id[$k], $log_type[$k], $datetime_log[$k], &$status);
	
			// Execute Query
			$stmt = sqlsrv_query($conn, $query, $params);
	
			if ($stmt === false) {
				die("SQL Execution Error: " . print_r(sqlsrv_errors(), true)); // Debugging
			}
	
			// Check if output parameter was set correctly
			if ($status != 1) {
				return 0; // Failure case
			}
		}
	
		return 1; // Success case
	}
	

	//! DONE
	function delete_employee_attendance() {
		extract($_POST);
		$conn = $this->conn;
		
		// Debug logging
		error_log("Attempting to delete attendance with ID: " . $id);
		
		// Define output parameter
		$params = array(
			array($id, SQLSRV_PARAM_IN),
			array(&$status, SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT)
		);
	
		$query = "DECLARE @output_status INT;
				  EXEC sp_delete_employee_attendance @id = ?, @output_status = @output_status OUTPUT;
				  SELECT @output_status AS status;";
	
		$stmt = sqlsrv_query($conn, $query, $params);
	
		if ($stmt === false) {
			error_log("SQL Error: " . print_r(sqlsrv_errors(), true));
			die("SQL Execution Error: " . print_r(sqlsrv_errors(), true));
		}
	
		sqlsrv_next_result($stmt); // Move to the next result set
		$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
		
		// Check if row exists before accessing array offset
		$status = ($row !== false && isset($row['status'])) ? $row['status'] : 0;
	
		error_log("Delete attendance status: " . $status);
		return $status;
	}
	
	function delete_employee_attendance_single() {
		extract($_POST);
		$conn = $this->conn;
		
		// Debug logging
		error_log("Attempting to delete single attendance with ID: " . $id);
		
		// Define output parameter
		$params = array(
			array($id, SQLSRV_PARAM_IN),
			array(&$status, SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT)
		);
	
		$query = "DECLARE @output_status INT;
				  EXEC sp_delete_employee_attendance_single @attendance_id = ?, @output_status = @output_status OUTPUT;
				  SELECT @output_status AS status;";
	
		$stmt = sqlsrv_query($conn, $query, $params);
	
		if ($stmt === false) {
			error_log("SQL Error: " . print_r(sqlsrv_errors(), true));
			die("SQL Execution Error: " . print_r(sqlsrv_errors(), true));
		}
	
		sqlsrv_next_result($stmt); // Move to the next result set
		$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
		
		// Check if row exists before accessing array offset
		$status = ($row !== false && isset($row['status'])) ? $row['status'] : 0;
	
		error_log("Delete single attendance status: " . $status);
		return $status;
	}
	
}