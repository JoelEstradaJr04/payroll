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
        $password = "password"; // Add your password here

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
	
		$stmt = sqlsrv_prepare($this->conn, $query, array($username, $password));
	
		if ($stmt === false) {
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
				
				$emp_stmt = sqlsrv_prepare($this->conn, $emp_query, array($row['employee_id']));
				
				if ($emp_stmt && sqlsrv_execute($emp_stmt)) {
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
				
				// Return only the number, no HTML or scripts
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

	//! DONE
	function save_user(){
		extract($_POST);
		$conn = $this->db; // Assuming $this->db is the database connection
	
		// Prepare stored procedure call
		$stmt = $conn->prepare("CALL sp_save_user(?, ?, ?, ?, ?)");
		
		// Bind parameters (id can be null)
		$stmt->bind_param("issss", $id, $name, $username, $password, $type);
		
		// Execute the stored procedure
		$stmt->execute();
	
		// Fetch result
		$result = $stmt->get_result();
		if ($result) {
			$row = $result->fetch_assoc();
			return $row['status']; // Should return 1 if successful
		}
	
		return 0; // Failure case
	}
	
	//! DONE
	function delete_user() {
		extract($_POST);
		$conn = $this->db; // Database connection

		// Prepare stored procedure call
		$stmt = $conn->prepare("CALL sp_delete_user(?)");
		
		// Bind the id parameter
		$stmt->bind_param("i", $id);
		
		// Execute the procedure
		$stmt->execute();
		
		// Fetch result
		$result = $stmt->get_result();
		if ($result) {
			$row = $result->fetch_assoc();
			return $row['status']; // Should return 1 if successful
		}

		return 0; // Failure case
	}

	//! DONE
	function save_employee() {
		extract($_POST);
		$conn = $this->db; // Database connection
	
		// Prepare stored procedure call
		$stmt = $conn->prepare("CALL sp_save_employee(?, ?, ?, ?, ?, ?, ?, @status)");
		
		// Bind parameters (id can be null)
		$stmt->bind_param("isssiii", $id, $firstname, $middlename, $lastname, $position_id, $department_id, $salary);
		
		// Execute the procedure
		$stmt->execute();
	
		// Retrieve the output status
		$result = $conn->query("SELECT @status AS status");
		if ($result) {
			$row = $result->fetch_assoc();
			return $row['status']; // Should return 1 if successful
		}
	
		return 0; // Failure case
	}
	
	//! DONE
	function delete_employee() {
		extract($_POST);
		$conn = $this->db; // Database connection
	
		// Prepare stored procedure call
		$stmt = $conn->prepare("CALL sp_delete_employee(?)");
		
		// Bind the id parameter
		$stmt->bind_param("i", $id);
		
		// Execute the procedure
		$stmt->execute();
		
		// Fetch result
		$result = $stmt->get_result();
		if ($result) {
			$row = $result->fetch_assoc();
			return $row['status']; // Should return 1 if successful
		}
	
		return 0; // Failure case
	}
	
	//!DONE
	function save_department() {
		extract($_POST);
		$conn = $this->db; // Database connection
	
		// Prepare stored procedure call
		$stmt = $conn->prepare("CALL sp_save_department(?, ?, @status)");
		
		// Bind parameters (id can be null)
		$stmt->bind_param("is", $id, $name);
		
		// Execute the procedure
		$stmt->execute();
	
		// Retrieve the output status
		$result = $conn->query("SELECT @status AS status");
		if ($result) {
			$row = $result->fetch_assoc();
			return $row['status']; // Should return 1 if successful
		}
	
		return 0; // Failure case
	}
	
	//! DONE
	function delete_department() {
		extract($_POST);
		$conn = $this->db; // Database connection
	
		// Prepare stored procedure call
		$stmt = $conn->prepare("CALL sp_delete_department(?)");
		
		// Bind the id parameter
		$stmt->bind_param("i", $id);
		
		// Execute the procedure
		$stmt->execute();
		
		// Fetch result
		$result = $stmt->get_result();
		if ($result) {
			$row = $result->fetch_assoc();
			return $row['status']; // Should return 1 if successful
		}
	
		return 0; // Failure case
	}

	//! DONE
	function save_position() {
		extract($_POST);
		
		// Connect to the database
		$conn = $this->db;
	
		// Prepare the stored procedure call
		$stmt = $conn->prepare("CALL sp_save_position(?, ?, ?)");
	
		// Bind parameters
		$stmt->bind_param("isi", $id, $name, $department_id);
	
		// Execute the statement
		$save = $stmt->execute();
	
		// Close the statement
		$stmt->close();
	
		// Return success status
		if ($save) {
			return 1;
		} else {
			return 0;
		}
	}
	
	//! DONE
	function delete_position() {
		extract($_POST);
		
		// Connect to the database
		$conn = $this->db;
	
		// Prepare the stored procedure call
		$stmt = $conn->prepare("CALL sp_delete_position(?)");
	
		// Bind parameters
		$stmt->bind_param("i", $id);
	
		// Execute the statement
		$delete = $stmt->execute();
	
		// Close the statement
		$stmt->close();
	
		// Return success status
		if ($delete) {
			return 1;
		} else {
			return 0;
		}
	}

	//!DONE
	function save_allowances() {
		extract($_POST);
		
		// Connect to the database
		$conn = $this->db;
	
		// Prepare the stored procedure call
		$stmt = $conn->prepare("CALL sp_save_allowances(?, ?, ?)");
	
		// Bind parameters: 'i' for integer (id), 's' for string (allowance), 's' for string (description)
		$stmt->bind_param("iss", $id, $allowance, $description);
	
		// Execute the statement
		$save = $stmt->execute();
	
		// Close the statement
		$stmt->close();
	
		// Return success status
		if ($save) {
			return 1;
		} else {
			return 0;
		}
	}
	
	//! DONE
	function delete_allowances() {
		extract($_POST);
		$stmt = $this->db->prepare("CALL sp_delete_allowance(?)");
		$stmt->bind_param("i", $id);
		$stmt->execute();
		if ($stmt) {
			return 1;
		}
	}
	
	//! DONE
	function save_employee_allowance() {
		extract($_POST);
		foreach ($allowance_id as $k => $v) {
			$stmt = $this->db->prepare("CALL sp_save_employee_allowance(?, ?, ?, ?, ?)");
			$stmt->bind_param("iisss", $employee_id, $allowance_id[$k], $type[$k], $amount[$k], $effective_date[$k]);
			$stmt->execute();
		}
		return 1;
	}
	
	//! DONE
	function delete_employee_allowance() {
		extract($_POST);
		$stmt = $this->db->prepare("CALL sp_delete_employee_allowance(?)");
		$stmt->bind_param("i", $id);
		$stmt->execute();
		if ($stmt) {
			return 1;
		}
	}

	//! DONE
	function save_deductions() {
		extract($_POST);
		$stmt = $this->db->prepare("CALL sp_save_deduction(?, ?, ?)");
		$stmt->bind_param("iss", $id, $deduction, $description);
		$stmt->execute();
		if ($stmt) {
			return 1;
		}
	}
	
	//! DONE
	function delete_deductions() {
		extract($_POST);
		$stmt = $this->db->prepare("CALL sp_delete_deduction(?)");
		$stmt->bind_param("i", $id);
		$stmt->execute();
		if ($stmt) {
			return 1;
		}
	}
	
	//! DONE
	function save_employee_deduction() {
		extract($_POST);
		foreach ($deduction_id as $k => $v) {
			$stmt = $this->db->prepare("CALL sp_save_employee_deduction(?, ?, ?, ?, ?)");
			$stmt->bind_param("iisss", $employee_id, $deduction_id[$k], $type[$k], $amount[$k], $effective_date[$k]);
			$stmt->execute();
		}
		return 1;
	}
	
	//! DONE
	function delete_employee_deduction() {
		extract($_POST);
		$stmt = $this->db->prepare("CALL sp_delete_employee_deduction(?)");
		$stmt->bind_param("i", $id);
		$stmt->execute();
		if ($stmt) {
			return 1;
		}
	}

	//! DONE
	function save_employee_attendance() {
		extract($_POST);
		foreach ($employee_id as $k => $v) {
			$datetime_log[$k] = date("Y-m-d H:i", strtotime($datetime_log[$k]));
			$stmt = $this->db->prepare("CALL sp_save_employee_attendance(?, ?, ?)");
			$stmt->bind_param("iss", $employee_id[$k], $log_type[$k], $datetime_log[$k]);
			$stmt->execute();
		}
		return 1;
	}
	
	//! DONE
	function delete_employee_attendance(){
		extract($_POST);
		$date = explode('_',$id);
		$dt = date("Y-m-d",strtotime($date[1]));
 
		$delete = $this->db->query("DELETE FROM attendance where employee_id = '".$date[0]."' and date(datetime_log) ='$dt' ");
		if($delete)
			return 1;
	}

	//! DONE
	function delete_employee_attendance_single() {
		extract($_POST);
		$stmt = $this->db->prepare("CALL sp_delete_employee_attendance_single(?)");
		$stmt->bind_param("i", $id);
		$stmt->execute();
		if ($stmt) {
			return 1;
		}
	}
	
	//! DONE
	function save_payroll() {
		extract($_POST);
		$ref_no = "";
	
		$stmt = $this->db->prepare("CALL sp_save_payroll(?, ?, ?, ?, @ref_no)");
		$stmt->bind_param("isss", $id, $date_from, $date_to, $type);
		$stmt->execute();
	
		if (empty($id)) {
			$result = $this->db->query("SELECT @ref_no AS ref_no");
			$row = $result->fetch_assoc();
			$ref_no = $row['ref_no'];
		}
	
		return 1;
	}
	
	//! DONE
	function delete_payroll() {
		extract($_POST);
		$stmt = $this->db->prepare("CALL sp_delete_payroll(?)");
		$stmt->bind_param("i", $id);
		$stmt->execute();
		if ($stmt) {
			return 1;
		}
	}
	
	
	function calculate_payroll(){
		extract($_POST);
		$am_in = "08:00";
		$am_out = "12:00";
		$pm_in = "13:00";
		$pm_out = "17:00";
		$this->db->query("DELETE FROM payroll_items where payroll_id=".$id);
		$pay = $this->db->query("SELECT * FROM payroll where id = ".$id)->fetch_array();
		$employee = $this->db->query("SELECT * FROM employee");
		if($pay['type'] == 1)
		$dm = 22;
		else
		$dm = 11;
		$calc_days = abs(strtotime($pay['date_to']." 23:59:59")) - strtotime($pay['date_from']." 00:00:00 -1 day") ; 
        $calc_days =floor($calc_days / (60*60*24)  );
		$att=$this->db->query("SELECT * FROM attendance where date(datetime_log) between '".$pay['date_from']."' and '".$pay['date_from']."' order by UNIX_TIMESTAMP(datetime_log) asc  ") or die(mysqli_error($conn));
		while($row=$att->fetch_array()){
			$date = date("Y-m-d",strtotime($row['datetime_log']));
			if($row['log_type'] == 1 || $row['log_type'] == 3){
				if(!isset($attendance[$row['employee_id']."_".$date]['log'][$row['log_type']]))
				$attendance[$row['employee_id']."_".$date]['log'][$row['log_type']] = $row['datetime_log'];
			}else{
				$attendance[$row['employee_id']."_".$date]['log'][$row['log_type']] = $row['datetime_log'];
			}
			}
		$deductions = $this->db->query("SELECT * FROM employee_deductions where (`type` = '".$pay['type']."' or (date(effective_date) between '".$pay['date_from']."' and '".$pay['date_from']."' ) ) ");
		$allowances = $this->db->query("SELECT * FROM employee_allowances where (`type` = '".$pay['type']."' or (date(effective_date) between '".$pay['date_from']."' and '".$pay['date_from']."' ) ) ");
		while($row = $deductions->fetch_assoc()){
			$ded[$row['employee_id']][] = array('did'=>$row['deduction_id'],"amount"=>$row['amount']);
		}
		while($row = $allowances->fetch_assoc()){
			$allow[$row['employee_id']][] = array('aid'=>$row['allowance_id'],"amount"=>$row['amount']);
		}
		while($row =$employee->fetch_assoc()){
			$salary = $row['salary'];
			$daily = $salary / 22;
			$min = (($salary / 22) / 8) /60;
			$absent = 0;
			$late = 0;
			$dp = 22 / $pay['type'];
			$present=0;
			$net=0;
			$allow_amount=0;
			$ded_amount=0;


			for($i = 0; $i < $calc_days;$i++){
				$dd = date("Y-m-d",strtotime($pay['date_from']." +".$i." days"));
				$count = 0;
				$p = 0;
				if(isset($attendance[$row['id']."_".$dd]['log']))
				$count = count($attendance[$row['id']."_".$dd]['log']);
					
					if(isset($attendance[$row['id']."_".$dd]['log'][1]) && isset($attendance[$row['id']."_".$dd]['log'][2])){
						$att_mn = abs(strtotime($attendance[$row['id']."_".$dd]['log'][2])) - strtotime($attendance[$row['id']."_".$dd]['log'][1]) ; 
        				$att_mn =floor($att_mn  /60 );
        				$net += ($att_mn * $min);
        				$late += (240 - $att_mn);
        				$present += .5;
        				
					}
					if(isset($attendance[$row['id']."_".$dd]['log'][3]) && isset($attendance[$row['id']."_".$dd]['log'][4])){
						$att_mn = abs(strtotime($attendance[$row['id']."_".$dd]['log'][4])) - strtotime($attendance[$row['id']."_".$dd]['log'][3]) ; 
        				$att_mn =floor($att_mn  /60 );
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
					$ded_amount +=$drow['amount'];
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
			$save[] = $this->db->query("INSERT INTO payroll_items set ".$data);

		}
		if(isset($save)){
			$this->db->query("UPDATE payroll set status = 1 where id = ".$pay['id']);
			return 1;
		}
	}
}