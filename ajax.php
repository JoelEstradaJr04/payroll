<?php
session_start();
header('Content-Type: application/json');
include 'admin_class.php';
$crud = new Action();

if(isset($_GET['action'])) {
    $action = $_GET['action'];
    if($action == 'login') {
        echo $crud->login();
        exit;
    } elseif ($action == 'logout') { // Use elseif
        $logout = $crud->logout();
        if ($logout) {
            echo $logout;
        }
    } elseif ($action == 'save_user') { // Use elseif
        $save = $crud->save_user();
        if ($save) {
            echo $save;
        }
    } elseif ($action == 'update_user') { // Add this block for update_user
        $update = $crud->update_user();
        if ($update) {
            echo $update;
        }
    } elseif ($action == 'delete_user') { // Use elseif
        $save = $crud->delete_user();
        if ($save) {
            echo $save;
        }
    } elseif ($action == "save_employee") { // Use elseif
        $save = $crud->save_employee();
        if ($save) {
            echo $save;
        }
    } elseif ($action == "delete_employee") { // Use elseif
        $save = $crud->delete_employee();
        if ($save) {
            echo $save;
        }
    } elseif ($action == "save_department") { // Use elseif
        $save = $crud->save_department();
        if ($save) {
            echo $save;
        }
    } elseif ($action == "delete_department") { // Use elseif
        $save = $crud->delete_department();
        if ($save) {
            echo $save;
        }
    } elseif ($action == "save_position") { // Use elseif
        $save = $crud->save_position();
        if ($save) {
            echo $save;
        }
    } elseif ($action == "delete_position") { // Use elseif
        $save = $crud->delete_position();
        if ($save) {
            echo $save;
        }
    } elseif ($action == "save_allowances") { // Use elseif
        $save = $crud->save_allowances();
        if ($save) {
            echo $save;
        }
    } elseif ($action == "delete_allowances") { // Use elseif
        $save = $crud->delete_allowances();
        if ($save) {
            echo $save;
        }
    } elseif ($action == "save_employee_allowance") { // Use elseif
        $save = $crud->save_employee_allowance();
        if ($save) {
            echo $save;
        }
    } elseif ($action == "delete_employee_allowance") { // Use elseif
        $save = $crud->delete_employee_allowance();
        if ($save) {
            echo $save;
        }
    } elseif ($action == "save_deductions") { // Use elseif
        $save = $crud->save_deductions();
        if ($save) {
            echo $save;
        }
    } elseif ($action == "delete_deductions") { // Use elseif
        $save = $crud->delete_deductions();
        if ($save) {
            echo $save;
        }
    } elseif ($action == "save_employee_deduction") { // Use elseif
        $save = $crud->save_employee_deduction();
        if ($save) {
            echo $save;
        }
    } elseif ($action == "delete_employee_deduction") { // Use elseif
        $save = $crud->delete_employee_deduction();
        if ($save) {
            echo $save;
        }
    } elseif ($action == "save_employee_attendance") { // Use elseif
        $save = $crud->save_employee_attendance();
        if ($save) {
            echo $save;
        }
    } elseif ($action == "delete_employee_attendance") { // Use elseif
        $save = $crud->delete_employee_attendance();
        if ($save) {
            echo $save;
        }
    } elseif ($action == "delete_employee_attendance_single") { // Use elseif
        $save = $crud->delete_employee_attendance_single();
        if ($save) {
            echo $save;
        }
    } elseif ($action == "save_payroll") { // Use elseif
        $save = $crud->save_payroll();
        if ($save) {
            echo $save;
        }
    } elseif ($action == "delete_payroll") { // Use elseif
        $save = $crud->delete_payroll();
        if ($save) {
            echo $save;
        }
    } elseif ($action == "calculate_payroll") { // Use elseif
        $save = $crud->calculate_payroll();
        if ($save) {
            echo $save;
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
}
?>