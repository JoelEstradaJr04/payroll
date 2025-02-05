<?php
ob_start();
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
include 'db_connect.php';
include 'admin_class.php';
$admin = new Action();
extract($_POST);
$action = $_GET['action'] ?? '';

if ($action == 'login') {
    echo $admin->login();
}
if ($action == 'logout') {
    echo $admin->logout();
}
if ($action == 'save_user') {
    echo $admin->save_user();
}
if ($action == 'delete_user') {
    echo $admin->delete_user();
}
if ($action == 'load_employee_table') {
    echo $admin->load_employee_table();
}
if ($action == 'save_employee') {
    echo $admin->save_employee();
}
if ($action == 'delete_employee') {
    echo $admin->delete_employee();
}
if ($action == 'save_department') {
    echo $admin->save_department();
}
if ($action == 'delete_department') {
    echo $admin->delete_department();
}
if ($action == 'save_position') {
    echo $admin->save_position();
}
if ($action == 'delete_position') {
    echo $admin->delete_position();
}
if ($action == 'save_allowances') {
    echo $admin->save_allowances();
}
if ($action == 'delete_allowances') {
    echo $admin->delete_allowances();
}
if ($action == 'save_deductions') {
    echo $admin->save_deductions();
}
if ($action == 'delete_deductions') {
    echo $admin->delete_deductions();
}
if ($action == 'save_employee_attendance') {
    echo $admin->save_employee_attendance();
}
if ($action == 'delete_employee_attendance') {
    echo $admin->delete_employee_attendance();
}
if ($action == 'delete_employee_attendance_single') {
    echo $admin->delete_employee_attendance_single();
}
if ($action == 'save_payroll') {
    echo $admin->save_payroll();
}
if ($action == 'delete_payroll') {
    echo $admin->delete_payroll();
}
if ($action == 'save_payroll_items') {
    echo $admin->save_payroll_items();
}
if ($action == 'delete_payroll_items') {
    echo $admin->delete_payroll_items();
}
if ($action == 'update_attendance') {
    echo $admin->update_attendance();
}

ob_end_flush();
?>