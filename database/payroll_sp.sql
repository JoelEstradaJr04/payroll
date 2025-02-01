DELIMITER //

CREATE PROCEDURE `sp_save_user` (
    IN p_id INT,
    IN p_name VARCHAR(255),
    IN p_username VARCHAR(255),
    IN p_password VARCHAR(255),
    IN p_type VARCHAR(50)
)
BEGIN
    -- Check if id is NULL or 0 (Insert case)
    IF p_id IS NULL OR p_id = 0 THEN
        INSERT INTO users (name, username, password, type) 
        VALUES (p_name, p_username, p_password, p_type);
    ELSE
        -- Update case
        UPDATE users 
        SET name = p_name, 
            username = p_username, 
            password = p_password, 
            type = p_type
        WHERE id = p_id;
    END IF;

    -- Return success
    SELECT 1 AS status;
END //

DELIMITER ;

DELIMITER //

CREATE PROCEDURE `sp_delete_user` (
    IN p_id INT
)
BEGIN
    DELETE FROM users WHERE id = p_id;
    
    -- Return success
    SELECT 1 AS status;
END //

DELIMITER ;

DELIMITER //

CREATE PROCEDURE `sp_save_employee` (
    IN p_id INT,
    IN p_firstname VARCHAR(255),
    IN p_middlename VARCHAR(255),
    IN p_lastname VARCHAR(255),
    IN p_position_id INT,
    IN p_department_id INT,
    IN p_salary DECIMAL(10,2),
    OUT p_status INT
)
BEGIN
    DECLARE v_employee_no VARCHAR(20);
    
    -- If id is NULL or 0, generate a unique employee number and insert
    IF p_id IS NULL OR p_id = 0 THEN
        SET v_employee_no = CONCAT(YEAR(CURDATE()), '-', FLOOR(RAND() * 9999));
        
        -- Ensure the generated employee number is unique
        WHILE EXISTS (SELECT 1 FROM employee WHERE employee_no = v_employee_no) DO
            SET v_employee_no = CONCAT(YEAR(CURDATE()), '-', FLOOR(RAND() * 9999));
        END WHILE;

        -- Insert new employee
        INSERT INTO employee (firstname, middlename, lastname, position_id, department_id, salary, employee_no)
        VALUES (p_firstname, p_middlename, p_lastname, p_position_id, p_department_id, p_salary, v_employee_no);
    ELSE
        -- Update existing employee
        UPDATE employee
        SET firstname = p_firstname,
            middlename = p_middlename,
            lastname = p_lastname,
            position_id = p_position_id,
            department_id = p_department_id,
            salary = p_salary
        WHERE id = p_id;
    END IF;

    -- Set return status
    SET p_status = 1;
END //

DELIMITER ;


DELIMITER //

CREATE PROCEDURE `sp_delete_employee` (
    IN p_id INT
)
BEGIN
    DELETE FROM employee WHERE id = p_id;
    
    -- Return success
    SELECT 1 AS status;
END //

DELIMITER ;

DELIMITER //

CREATE PROCEDURE sp_save_department
    @p_id INT,
    @p_name NVARCHAR(255),
    @status INT OUTPUT
AS
BEGIN
    SET NOCOUNT ON;

    -- If id is NULL or 0, insert a new department
    IF @p_id IS NULL OR @p_id = 0
    BEGIN
        INSERT INTO department (name) VALUES (@p_name);
        SET @p_id = SCOPE_IDENTITY();
    END
    ELSE
    BEGIN
        -- Update existing department
        UPDATE department 
        SET name = @p_name 
        WHERE id = @p_id;
    END

    -- Set return status
    SET @status = 1;
END;
GO

DELIMITER ;

DELIMITER //

CREATE PROCEDURE `sp_delete_department` (
    IN p_id INT
)
BEGIN
    DELETE FROM department WHERE id = p_id;
    
    -- Return success
    SELECT 1 AS status;
END //

DELIMITER $$

CREATE PROCEDURE sp_save_position(
    IN p_id INT,
    IN p_name VARCHAR(255),
    IN p_department_id INT
)
BEGIN
    IF p_id IS NULL OR p_id = 0 THEN
        -- Insert new position
        INSERT INTO position (name, department_id) 
        VALUES (p_name, p_department_id);
    ELSE
        -- Update existing position
        UPDATE position 
        SET name = p_name, department_id = p_department_id 
        WHERE id = p_id;
    END IF;
END $$

DELIMITER ;

DELIMITER $$

CREATE PROCEDURE sp_delete_position(
    IN p_id INT
)
BEGIN
    DELETE FROM position WHERE id = p_id;
END $$

DELIMITER ;


DELIMITER $$

CREATE PROCEDURE sp_save_allowances(
    IN p_id INT,
    IN p_allowance VARCHAR(100),
    IN p_description VARCHAR(255)
)
BEGIN
    IF p_id IS NULL OR p_id = 0 THEN
        -- Insert new allowance
        INSERT INTO allowances (allowance, description) 
        VALUES (p_allowance, p_description);
    ELSE
        -- Update existing allowance
        UPDATE allowances 
        SET allowance = p_allowance, description = p_description 
        WHERE id = p_id;
    END IF;
END $$

DELIMITER ;

DELIMITER $$

CREATE PROCEDURE sp_delete_allowance(IN p_id INT)
BEGIN
    DELETE FROM allowances WHERE id = p_id;
END $$

DELIMITER ;

DELIMITER $$

CREATE PROCEDURE sp_save_employee_allowance(
    IN p_employee_id INT,
    IN p_allowance_id INT,
    IN p_type VARCHAR(50),
    IN p_amount DECIMAL(10,2),
    IN p_effective_date DATE
)
BEGIN
    INSERT INTO employee_allowances (employee_id, allowance_id, type, amount, effective_date)
    VALUES (p_employee_id, p_allowance_id, p_type, p_amount, p_effective_date);
END

DELIMITER ;

DELIMITER $$

CREATE PROCEDURE sp_delete_employee_allowance(IN p_id INT)
BEGIN
    DELETE FROM employee_allowances WHERE id = p_id;
END $$

DELIMITER ;

DELIMITER $$

CREATE PROCEDURE sp_save_deduction(
    IN p_id INT,
    IN p_deduction VARCHAR(255),
    IN p_description TEXT
)
BEGIN
    IF p_id IS NULL OR p_id = 0 THEN
        INSERT INTO deductions (deduction, description)
        VALUES (p_deduction, p_description);
    ELSE
        UPDATE deductions
        SET deduction = p_deduction, description = p_description
        WHERE id = p_id;
    END IF;
END $$

DELIMITER ;

DELIMITER $$

CREATE PROCEDURE sp_delete_deduction(IN p_id INT)
BEGIN
    DELETE FROM deductions WHERE id = p_id;
END $$

DELIMITER ;

DELIMITER $$

CREATE PROCEDURE sp_save_employee_deduction(
    IN p_employee_id INT,
    IN p_deduction_id INT,
    IN p_type VARCHAR(50),
    IN p_amount DECIMAL(10,2),
    IN p_effective_date DATE
)
BEGIN
    INSERT INTO employee_deductions (employee_id, deduction_id, type, amount, effective_date)
    VALUES (p_employee_id, p_deduction_id, p_type, p_amount, p_effective_date);
END $$

DELIMITER ;

DELIMITER $$

CREATE PROCEDURE sp_delete_employee_deduction(IN p_id INT)
BEGIN
    DELETE FROM employee_deductions WHERE id = p_id;
END $$

DELIMITER ;

DELIMITER $$

CREATE PROCEDURE sp_save_employee_attendance(
    IN p_employee_id INT,
    IN p_log_type VARCHAR(50),
    IN p_datetime_log DATETIME
)
BEGIN
    INSERT INTO attendance (employee_id, log_type, datetime_log)
    VALUES (p_employee_id, p_log_type, p_datetime_log);
END $$

DELIMITER ;


DELIMITER $$

CREATE PROCEDURE sp_delete_employee_attendance(IN p_employee_id INT, IN p_date DATE)
BEGIN
    DELETE FROM attendance WHERE employee_id = p_employee_id AND DATE(datetime_log) = p_date;
END $$

DELIMITER ;

DELIMITER $$

CREATE PROCEDURE sp_delete_employee_attendance_single(IN p_id INT)
BEGIN
    DELETE FROM attendance WHERE id = p_id;
END $$

DELIMITER ;

DELIMITER $$

CREATE PROCEDURE sp_save_payroll(
    IN p_id INT,
    IN p_date_from DATE,
    IN p_date_to DATE,
    IN p_type VARCHAR(50),
    OUT p_ref_no VARCHAR(20)
)
BEGIN
    DECLARE v_ref_no VARCHAR(20);
    DECLARE v_exists INT DEFAULT 1;

    -- Generate a unique reference number if inserting a new record
    IF p_id IS NULL OR p_id = 0 THEN
        WHILE v_exists > 0 DO
            SET v_ref_no = CONCAT(YEAR(NOW()), '-', FLOOR(1 + (RAND() * 9999)));
            SELECT COUNT(*) INTO v_exists FROM payroll WHERE ref_no = v_ref_no;
        END WHILE;

        INSERT INTO payroll (date_from, date_to, type, ref_no)
        VALUES (p_date_from, p_date_to, p_type, v_ref_no);

        SET p_ref_no = v_ref_no;
    ELSE
        -- Update existing payroll entry
        UPDATE payroll
        SET date_from = p_date_from,
            date_to = p_date_to,
            type = p_type
        WHERE id = p_id;
    END IF;
END $$

DELIMITER ;

DELIMITER $$

CREATE PROCEDURE sp_delete_payroll(IN p_id INT)
BEGIN
    DELETE FROM payroll WHERE id = p_id;
END $$

DELIMITER ;


