USE [payroll]
GO

-- sp_save_user [UPDATED!!!]

CREATE PROCEDURE sp_save_user
		@FirstName NVARCHAR(100),
		@MiddleName NVARCHAR(100),
		@LastName NVARCHAR(100),
		@Suffix NVARCHAR(10),
		@EmployeeNo NVARCHAR(50),
		@Username NVARCHAR(50),
		@Password NVARCHAR(255),
		@Type INT,
		@Status INT OUTPUT,
		@Message NVARCHAR(255) OUTPUT
	AS
	BEGIN
		SET NOCOUNT ON;

		BEGIN TRY
			DECLARE @EmployeeId INT;

			-- Check if employee exists
			SELECT @EmployeeId = id 
			FROM employee 
			WHERE firstname = @FirstName
				AND middlename = @MiddleName
				AND lastname = @LastName
				AND suffix = @Suffix
				AND employee_no = @EmployeeNo
				AND isDeleted = 0;

			IF @EmployeeId IS NULL
			BEGIN
				SET @Status = 0;
				SET @Message = 'Employee not found';
				RETURN;
			END

			-- Check if employee already has a user account
			IF EXISTS (SELECT 1 FROM users WHERE employee_id = @EmployeeId AND isDeleted = 0)
			BEGIN
				SET @Status = 0;
				SET @Message = 'Employee already has a user account';
				RETURN;
			END

			-- Check if username exists
			IF EXISTS (SELECT 1 FROM users WHERE username = @Username AND isDeleted = 0)
			BEGIN
				SET @Status = 0;
				SET @Message = 'Username already exists';
				RETURN;
			END

			-- Insert user record
			INSERT INTO users (employee_id, username, password, type, isDeleted)
			VALUES (@EmployeeId, @Username, @Password, @Type, 0);

			-- Get the newly inserted user ID
			DECLARE @NewUserId INT = SCOPE_IDENTITY();

			-- Output success message
			SET @Status = 1;
			SET @Message = 'User successfully created with ID: ' + CAST(@NewUserId AS NVARCHAR(10));
		END TRY
		BEGIN CATCH
			SET @Status = 0;
			SET @Message = ERROR_MESSAGE();
		END CATCH
	END
	
GO

-- sp_update_user
ALTER PROCEDURE sp_update_user (
    @UserID INT,
    @FirstName NVARCHAR(100),
    @MiddleName NVARCHAR(100),
    @LastName NVARCHAR(100),
    @Suffix NVARCHAR(10),
    @EmployeeNo NVARCHAR(50),
    @Username NVARCHAR(50),
    @Password NVARCHAR(255), -- Can be NULL if not changing
    @Type INT,
    @Status INT OUTPUT,
    @Message NVARCHAR(255) OUTPUT
)
AS
BEGIN
    SET NOCOUNT ON;

    BEGIN TRY
        DECLARE @EmployeeId INT;

        -- Validate the user exists
        IF NOT EXISTS (SELECT 1 FROM users WHERE id = @UserID AND isDeleted = 0)
        BEGIN
            SET @Status = 0;
            SET @Message = 'User not found';
            RETURN;
        END

        -- Validate associated employee (using provided name parts and employee_no)
        SELECT @EmployeeId = id
        FROM employee
        WHERE firstname = @FirstName
            AND middlename = @MiddleName
            AND lastname = @LastName
            AND suffix = @Suffix
            AND employee_no = @EmployeeNo
            AND isDeleted = 0;

        IF @EmployeeId IS NULL
        BEGIN
            SET @Status = 0;
            SET @Message = 'Employee not found';
            RETURN;
        END

        -- Ensure the username is unique (excluding current user)
        IF EXISTS (SELECT 1 FROM users WHERE username = @Username AND id <> @UserID AND isDeleted = 0)
        BEGIN
            SET @Status = 0;
            SET @Message = 'Username already exists';
            RETURN;
        END

        -- Update user record (conditionally update password)
        UPDATE users
        SET employee_id = @EmployeeId,
            username = @Username,
            type = @Type
        WHERE id = @UserID;

        -- Conditionally update the password *after* the main update
        IF @Password IS NOT NULL
        BEGIN
            UPDATE users
            SET password = @Password  -- Now this is correct
            WHERE id = @UserID;
        END

        SET @Status = 1;
        SET @Message = 'User successfully updated';
    END TRY
    BEGIN CATCH
        SET @Status = 0;
        SET @Message = ERROR_MESSAGE();
    END CATCH
END
GO

-- sp_delete_deduction

CREATE PROCEDURE [dbo].[sp_delete_deduction]
    @p_id INT
AS
BEGIN
    SET NOCOUNT ON;
    UPDATE deductions SET isDeleted = 1 WHERE id = @p_id;
    SELECT 1 AS status;
END;
GO

-- sp_delete_department

CREATE PROCEDURE [dbo].[sp_delete_department] 
    @p_id INT
AS
BEGIN
    SET NOCOUNT ON;
    UPDATE department SET isDeleted = 1 WHERE id = @p_id;
    SELECT 1 AS status;
END;
GO

-- sp_delete_employee

CREATE PROCEDURE [dbo].[sp_delete_employee] 
    @p_id INT  -- Employee ID to delete
AS
BEGIN
    SET NOCOUNT ON;

    UPDATE [employee]
    SET [isDeleted] = 1
    WHERE [id] = @p_id;
END;
GO

-- sp_delete_employee_allowance

CREATE PROCEDURE [dbo].[sp_delete_employee_allowance]
    @p_id INT
AS
BEGIN
    DELETE FROM employee_allowances WHERE id = @p_id;
END;
GO

-- sp_delete_employee_attendance

CREATE PROCEDURE [dbo].[sp_delete_employee_attendance]
    @p_employee_id INT,
    @p_date DATE
AS
BEGIN
    DELETE FROM attendance WHERE employee_id = @p_employee_id AND CAST(datetime_log AS DATE) = @p_date;
END;
GO

-- sp_delete_employee_attendance_single

CREATE PROCEDURE [dbo].[sp_delete_employee_attendance_single]
    @p_id INT
AS
BEGIN
    DELETE FROM attendance WHERE id = @p_id;
END;
GO

-- sp_delete_employee_deduction

CREATE PROCEDURE [dbo].[sp_delete_employee_deduction]
    @p_id INT
AS
BEGIN
    DELETE FROM employee_deductions WHERE id = @p_id;
END;
GO

-- sp_delete_payroll

CREATE PROCEDURE [dbo].[sp_delete_payroll]
    @p_id INT
AS
BEGIN
    DELETE FROM payroll WHERE id = @p_id;
END;
GO

-- sp_delete position

ALTER PROCEDURE [dbo].[sp_delete_position]
    @p_id INT
AS
BEGIN
    SET NOCOUNT ON;

    -- Instead of deleting, update isDeleted to 1 (soft delete)
    UPDATE position 
    SET isDeleted = 1
    WHERE id = @p_id;
END;
GO

-- sp_delete_user
ALTER PROCEDURE sp_delete_user
    @UserID INT,
    @Status INT OUTPUT,
    @Message NVARCHAR(255) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;

    BEGIN TRY
        -- Check if user exists and is not already deleted
        IF NOT EXISTS (SELECT 1 FROM users WHERE id = @UserID AND isDeleted = 0)
        BEGIN
            SET @Status = 0;
            SET @Message = 'User not found or already deleted';
            RETURN;
        END

        -- Soft delete the user
        UPDATE users
        SET isDeleted = 1
        WHERE id = @UserID;

        SET @Status = 1;
        SET @Message = 'User successfully deleted';
    END TRY
    BEGIN CATCH
        SET @Status = 0;
        SET @Message = ERROR_MESSAGE();
    END CATCH
END
GO



-- sp_save_allowances

CREATE PROCEDURE [dbo].[sp_save_allowances]
    @p_id INT,
    @p_allowance NVARCHAR(250),
    @p_description NVARCHAR(MAX),
    @p_isDeleted BIT = 0, -- Default to not deleted
    @status INT OUTPUT
AS
BEGIN
    SET NOCOUNT ON;

    IF @p_id IS NULL OR @p_id = 0
    BEGIN
        -- Insert new allowance with the isDeleted flag
        INSERT INTO allowances (allowance, description, isDeleted) 
        VALUES (@p_allowance, @p_description, @p_isDeleted);
        
        -- Get the newly inserted ID
        SET @p_id = SCOPE_IDENTITY();
    END
    ELSE
    BEGIN
        -- Update existing allowance
        UPDATE allowances 
        SET allowance = @p_allowance, 
            description = @p_description,
            isDeleted = @p_isDeleted
        WHERE id = @p_id;
    END

    -- Ensure status output is set
    IF @@ROWCOUNT > 0
        SET @status = 1;
    ELSE
        SET @status = 0;
END;
GO

-- sp_save_department

CREATE PROCEDURE [dbo].[sp_save_department] 
    @p_id INT,
    @p_name NVARCHAR(255),
    @p_status INT OUTPUT
AS
BEGIN
    -- If id is NULL or 0, insert a new department
    IF @p_id IS NULL OR @p_id = 0
    BEGIN
        INSERT INTO department (name) VALUES (@p_name);
    END
    ELSE
    BEGIN
        -- Update existing department
        UPDATE department 
        SET name = @p_name 
        WHERE id = @p_id;
    END

    -- Set return status
    SET @p_status = 1;
END;
GO

-- sp_save_employee

CREATE PROCEDURE [dbo].[sp_save_employee]
    @p_id INT OUTPUT,
    @p_employee_no NVARCHAR(50) = NULL,
    @p_firstname NVARCHAR(50),
    @p_middlename NVARCHAR(20),
    @p_lastname NVARCHAR(50),
    @p_suffix NVARCHAR(10) = NULL,
    @p_department_id INT,
    @p_position_id INT,
    @p_salary FLOAT,
    @status INT OUTPUT
AS
BEGIN
    SET NOCOUNT ON;

    -- Ensure Department and Position IDs exist
    IF NOT EXISTS (SELECT 1 FROM department WHERE id = @p_department_id)
    BEGIN
        SET @status = -2; -- Invalid department
        RETURN;
    END

    IF NOT EXISTS (SELECT 1 FROM position WHERE id = @p_position_id)
    BEGIN
        SET @status = -2; -- Invalid position
        RETURN;
    END

    -- If ID is NULL or 0, Insert new employee
    IF @p_id IS NULL OR @p_id = 0
    BEGIN
        -- Generate a unique Employee No if not provided
        IF @p_employee_no IS NULL
        BEGIN
            DECLARE @next_emp_no NVARCHAR(50);
            SELECT @next_emp_no = 'EMP' + RIGHT('000' + CAST(ISNULL(MAX(id), 0) + 1 AS NVARCHAR(10)), 4) FROM employee;
            SET @p_employee_no = @next_emp_no;
        END

        -- Insert new employee
        INSERT INTO employee (employee_no, firstname, middlename, lastname, suffix, department_id, position_id, salary, isDeleted)
        VALUES (@p_employee_no, @p_firstname, @p_middlename, @p_lastname, @p_suffix, @p_department_id, @p_position_id, @p_salary, 0);

        -- Get the new employee ID
        SET @p_id = SCOPE_IDENTITY();
        SET @status = 1; -- Insert successful
    END
    ELSE
    BEGIN
        -- Check if Employee exists
        IF EXISTS (SELECT 1 FROM employee WHERE id = @p_id AND isDeleted = 0)
        BEGIN
            -- Update existing employee
            UPDATE employee
            SET firstname = @p_firstname,
                middlename = @p_middlename,
                lastname = @p_lastname,
                suffix = @p_suffix,
                department_id = @p_department_id,
                position_id = @p_position_id,
                salary = @p_salary
            WHERE id = @p_id;
            
            SET @status = 2; -- Update successful
        END
        ELSE
        BEGIN
            SET @status = -1; -- Employee not found
        END
    END
END;
GO

-- sp_save_employee_allowance

CREATE PROCEDURE [dbo].[sp_save_employee_allowance]
    @p_employee_id INT,
    @p_allowance_id INT,
    @p_type NVARCHAR(50),
    @p_amount DECIMAL(10,2),
    @p_effective_date DATE
AS
BEGIN
    INSERT INTO employee_allowances (employee_id, allowance_id, type, amount, effective_date)
    VALUES (@p_employee_id, @p_allowance_id, @p_type, @p_amount, @p_effective_date);
END;
GO

-- sp_save_employee_attendance

CREATE PROCEDURE [dbo].[sp_save_employee_attendance]
    @p_employee_id INT,
    @p_log_type NVARCHAR(50),
    @p_datetime_log DATETIME,
    @status INT OUTPUT -- Add output parameter
AS
BEGIN
    INSERT INTO attendance (employee_id, log_type, datetime_log)
    VALUES (@p_employee_id, @p_log_type, @p_datetime_log);

    -- Set status to indicate success
    SET @status = 1;
END;
GO

-- sp_save_employee_deduction

CREATE PROCEDURE [dbo].[sp_save_employee_deduction]
    @p_id INT,
    @p_deduction NVARCHAR(250),
    @p_description NVARCHAR(MAX),
    @p_isDeleted BIT = 0, -- Default to not deleted
    @status INT OUTPUT
AS
BEGIN
    SET NOCOUNT ON;

    IF @p_id IS NULL OR @p_id = 0
    BEGIN
        -- Insert new deduction
        INSERT INTO deductions (deduction, description, isDeleted) 
        VALUES (@p_deduction, @p_description, @p_isDeleted);
    END
    ELSE
    BEGIN
        -- Update existing deduction
        UPDATE deductions 
        SET deduction = @p_deduction, 
            description = @p_description,
            isDeleted = @p_isDeleted
        WHERE id = @p_id;
    END

    SET @status = 1;
END;
GO

-- sp_save_payroll

CREATE PROCEDURE [dbo].[sp_save_payroll]
    @id INT OUTPUT,  -- Make @id an OUTPUT parameter
    @date_from DATE,
    @date_to DATE,
    @type INT,
    @ref_no NVARCHAR(50) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;

    IF @id IS NULL OR @id = 0
    BEGIN
        -- Generate a reference number (You can customize this)
        DECLARE @new_ref_no NVARCHAR(50);
        SET @new_ref_no = 'PAY-' + FORMAT(GETDATE(), 'yyyyMMddHHmmss');

        -- Insert new payroll record
        INSERT INTO payroll (date_from, date_to, type, ref_no)
        VALUES (@date_from, @date_to, @type, @new_ref_no);

        -- Get the newly inserted payroll ID and Reference Number
        SET @id = SCOPE_IDENTITY();
        SET @ref_no = @new_ref_no;
    END
    ELSE
    BEGIN
        -- Update existing payroll
        UPDATE payroll
        SET date_from = @date_from,
            date_to = @date_to,
            type = @type
        WHERE id = @id;

        -- Get the reference number
        SELECT @ref_no = ref_no FROM payroll WHERE id = @id;
    END
END;
GO

-- sp_save_position

CREATE PROCEDURE [dbo].[sp_save_position]
    @p_id INT OUTPUT,
    @p_name NVARCHAR(250),
    @p_department_id INT,
    @p_isDeleted BIT,
    @status INT OUTPUT
AS
BEGIN
    SET NOCOUNT ON;

    IF @p_id IS NULL OR @p_id = 0
    BEGIN
        INSERT INTO position (name, department_id, isDeleted) 
        VALUES (@p_name, @p_department_id, @p_isDeleted);

        SET @p_id = SCOPE_IDENTITY(); -- Get the new ID
    END
    ELSE
    BEGIN
        UPDATE position 
        SET name = @p_name, 
            department_id = @p_department_id,
            isDeleted = @p_isDeleted
        WHERE id = @p_id;
    END

    SET @status = 1;
END;
GO

-- sp_save_user

CREATE PROCEDURE [dbo].[sp_save_user]
    @p_id INT,
    @emp_no INT,
    @p_name VARCHAR(255),
    @p_username VARCHAR(255),
    @p_password VARCHAR(255),
    @p_type BIT
AS
BEGIN
    SET NOCOUNT ON;

    -- Check if id is NULL or 0 (Insert case)
    IF @p_id IS NULL OR @p_id = 0
    BEGIN
        INSERT INTO users (employee_id, name, username, password, type) 
        VALUES (@emp_no, @p_name, @p_username, @p_password, @p_type);
    END
    ELSE
    BEGIN
        -- Update case
        UPDATE users 
        SET name = @p_name, 
            username = @p_username, 
            password = @p_password, 
            type = @p_type
        WHERE id = @p_id;
    END

    -- Return success
    SELECT 1 AS status;
END
GO

-- sp_show_deduction [UPDATED]

CREATE PROCEDURE [dbo].[sp_show_deduction]
AS
BEGIN
	SELECT * 
		FROM deductions 
		WHERE isDeleted = 0
		ORDER BY id
END
GO

-- sp_show_department [UPDATED]

CREATE PROCEDURE sp_show_department
AS
BEGIN
    SELECT id, name
    FROM department
    WHERE isDeleted = 0
    ORDER BY name ASC;
END;
GO

-- sp_show_employee [UNNECESSARY]

CREATE PROCEDURE [dbo].[sp_show_employee] 
AS 
BEGIN
	SELECT * 
		FROM employee
		WHERE isDeleted=0
END
GO

-- sp_show_user [UNNECESSARY]

CREATE PROCEDURE [dbo].[sp_show_user]
AS
BEGIN
    SET NOCOUNT ON;

    SELECT u.id, 
           u.employee_id,
           u.name, 
           u.username,
           u.type AS isAdmin
    FROM users u
    LEFT JOIN employee e ON u.employee_id = e.id
    WHERE u.isDeleted = 0
    ORDER BY u.name ASC;
END;
GO

-- sp_show_allowances [UPDATED, NEW!]
CREATE PROCEDURE sp_show_allowances
AS
BEGIN
    SELECT * 
    FROM allowances 
    WHERE isDeleted = 0 
    ORDER BY id ASC;
END;
GO

-- sp_show_positions [UPDATED, NEW!]

CREATE PROCEDURE sp_show_positions
AS
BEGIN
    SELECT
        d.id AS department_id,
        d.name AS department_name,
        p.id AS position_id,
        p.name AS position_name
    FROM department d
    INNER JOIN position p ON d.id = p.department_id
    WHERE d.isDeleted = 0
      AND p.isDeleted = 0
    ORDER BY d.name, p.name ASC;  -- Order by department then position
END;
GO

-- sp_login

CREATE OR ALTER PROCEDURE sp_login
    @Username NVARCHAR(50),
    @Password NVARCHAR(255),
    @Status INT OUTPUT,
    @Message NVARCHAR(255) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @UserID INT;
    DECLARE @EmployeeName NVARCHAR(100);
    
    SELECT @UserID = u.id,
           @EmployeeName = CONCAT(e.firstname, ' ', e.lastname)
    FROM users u
    LEFT JOIN employee e ON u.employee_id = e.id
    WHERE u.username = @Username 
    AND u.isDeleted = 0;

    IF @UserID IS NULL
    BEGIN
        SET @Status = 0;
        SET @Message = 'Invalid username or password';
        RETURN;
    END

    IF EXISTS (SELECT 1 FROM users WHERE id = @UserID AND password = @Password)
    BEGIN
        SET @Status = 1;
        SET @Message = 'Login successful';
        RETURN;
    END

    SET @Status = 0;
    SET @Message = 'Invalid username or password';
END