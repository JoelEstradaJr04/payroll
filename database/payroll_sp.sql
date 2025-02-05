USE [payroll]
GO

/*
**
** SAVE SPs
**
*/


-- sp_save_user [UPDATED!!!]

CREATE PROCEDURE [dbo].[sp_save_user]
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
			SET @Message = 'User successfully created!';
		END TRY
		BEGIN CATCH
			SET @Status = 0;
			SET @Message = ERROR_MESSAGE();
		END CATCH
	END
	
GO

-- sp_save_allowances [UPDATED!!!]

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

-- sp_save_deduction [UPDATED!!!]

CREATE PROCEDURE [dbo].[sp_save_deduction]
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

-- sp_save_employee [UPDATED!!!]

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
    @status INT OUTPUT,
    @message NVARCHAR(200) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    BEGIN TRY
        -- Validate department and position
        IF NOT EXISTS (SELECT 1 FROM department WHERE id = @p_department_id)
        BEGIN
            SET @status = -2;
            SET @message = 'Invalid department';
            RETURN;
        END

        IF NOT EXISTS (SELECT 1 FROM position WHERE id = @p_position_id)
        BEGIN
            SET @status = -2;
            SET @message = 'Invalid position';
            RETURN;
        END

        -- Handle NULL values
        SET @p_suffix = ISNULL(@p_suffix, 'N/A');
        SET @p_middlename = ISNULL(@p_middlename, 'N/A');

        -- New Employee
        IF @p_id IS NULL OR @p_id = 0
        BEGIN
            -- Generate employee number if not provided
            IF @p_employee_no IS NULL
            BEGIN
                SELECT @p_employee_no = 'EMP' + RIGHT('000' + CAST(ISNULL(MAX(id), 0) + 1 AS NVARCHAR(10)), 4) 
                FROM employee;
            END

            -- Check duplicate employee number
            IF EXISTS (SELECT 1 FROM employee WHERE employee_no = @p_employee_no AND isDeleted = 0)
            BEGIN
                SET @status = -4;
                SET @message = 'Employee number already exists';
                RETURN;
            END

            -- Insert new employee
            INSERT INTO employee (
                employee_no, firstname, middlename, lastname, 
                suffix, department_id, position_id, salary, isDeleted
            )
            VALUES (
                @p_employee_no, @p_firstname, @p_middlename, @p_lastname,
                @p_suffix, @p_department_id, @p_position_id, @p_salary, 0
            );

            SET @p_id = SCOPE_IDENTITY();
            SET @status = 1;
            SET @message = 'New employee successfully added';
        END
        ELSE -- Update Employee
        BEGIN
            IF EXISTS (SELECT 1 FROM employee WHERE id = @p_id AND isDeleted = 0)
            BEGIN
                UPDATE employee
                SET firstname = @p_firstname,
                    middlename = @p_middlename,
                    lastname = @p_lastname,
                    suffix = @p_suffix,
                    department_id = @p_department_id,
                    position_id = @p_position_id,
                    salary = @p_salary
                WHERE id = @p_id;
                
                SET @status = 2;
                SET @message = 'Employee successfully updated';
            END
            ELSE
            BEGIN
                SET @status = -1;
                SET @message = 'Employee not found or already deleted';
            END
        END
    END TRY
    BEGIN CATCH
        SET @status = -99;
        SET @message = ERROR_MESSAGE();
    END CATCH
END;
GO

-- sp_save_department [UPDATED!!!]

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

-- sp_save_position [UPDATED!!!]

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
        INSERT INTO [position] (name, department_id, isDeleted) 
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

-- sp_save_payroll [UPDATED!!!]

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

-- sp_save_employee_attendance [UPDATED!!!] could add UPDATE

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

/*
**
** DELETE SPs
**
*/


-- sp_delete_department [UPDATED!!!]
CREATE PROCEDURE [dbo].[sp_delete_department]
    @p_id INT
AS
BEGIN
    SET NOCOUNT ON;

    IF EXISTS (SELECT 1 FROM department WHERE id = @p_id)
    BEGIN
        UPDATE department SET isDeleted = 1 WHERE id = @p_id;
        UPDATE position SET isDeleted = 1 WHERE department_id = @p_id;
        UPDATE employee SET isDeleted = 1 WHERE department_id = @p_id;
        SELECT 1 AS status;
    END
    ELSE
    BEGIN
        SELECT 0 AS status;
    END
END;
GO

-- sp_delete_position [UPDATED!!!]
CREATE PROCEDURE [dbo].[sp_delete_position]
    @p_id INT
AS
BEGIN
    SET NOCOUNT ON;

    IF EXISTS (SELECT 1 FROM position WHERE id = @p_id)
    BEGIN
        UPDATE position SET isDeleted = 1 WHERE id = @p_id;
        UPDATE employee SET isDeleted = 1 WHERE position_id = @p_id;
        SELECT 1 AS status;
    END
    ELSE
    BEGIN
        SELECT 0 AS status;
    END
END;
GO

-- sp_delete_employee [UPDATED!!!]
CREATE PROCEDURE [dbo].[sp_delete_employee]
    @p_id INT,
    @status INT OUTPUT,
    @message NVARCHAR(200) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    BEGIN TRY
        -- Check if employee exists and is not already deleted
        IF EXISTS (SELECT 1 FROM employee WHERE id = @p_id AND isDeleted = 0)
        BEGIN
            BEGIN TRANSACTION;
                -- Soft delete associated user account if exists
                IF EXISTS (SELECT 1 FROM users WHERE employee_id = @p_id AND isDeleted = 0)
                BEGIN
                    UPDATE users SET isDeleted = 1 WHERE employee_id = @p_id;
                END

                -- Soft delete the employee
                UPDATE employee SET isDeleted = 1 WHERE id = @p_id;

            COMMIT TRANSACTION;
            
            SET @status = 3; -- Delete successful
            SET @message = 'Employee successfully deleted';
        END
        ELSE
        BEGIN
            SET @status = -1;
            SET @message = 'Employee not found or already deleted';
        END
    END TRY
    BEGIN CATCH
        IF @@TRANCOUNT > 0
            ROLLBACK TRANSACTION;
        
        SET @status = -99;
        SET @message = ERROR_MESSAGE();
    END CATCH
END;
GO

-- sp_delete_allowance [UPDATED!!!] 
CREATE PROCEDURE [dbo].[sp_delete_allowance]
    @p_id INT
AS
BEGIN
    SET NOCOUNT ON;

    IF EXISTS (SELECT 1 FROM allowances WHERE id = @p_id)
    BEGIN
        UPDATE allowances SET isDeleted = 1 WHERE id = @p_id;
        UPDATE employee_allowances SET isDeleted = 1 WHERE allowance_id = @p_id;
        SELECT 1 AS status;
    END
    ELSE
    BEGIN
        SELECT 0 AS status;
    END
END;
GO

-- sp_delete_deduction [UPDATED!!!]
CREATE PROCEDURE [dbo].[sp_delete_deduction]
    @p_id INT
AS
BEGIN
    SET NOCOUNT ON;

    IF EXISTS (SELECT 1 FROM deductions WHERE id = @p_id)
    BEGIN
        UPDATE deductions SET isDeleted = 1 WHERE id = @p_id;
        UPDATE employee_deductions SET isDeleted = 1 WHERE deduction_id = @p_id;
        SELECT 1 AS status;
    END
    ELSE
    BEGIN
        SELECT 0 AS status;
    END
END;
GO

-- sp_delete_payroll [UPDATED!!!]
CREATE PROCEDURE [dbo].[sp_delete_payroll]
    @p_id INT
AS
BEGIN
    SET NOCOUNT ON;

    IF EXISTS (SELECT 1 FROM payroll WHERE id = @p_id)
    BEGIN
        UPDATE payroll SET isDeleted = 1 WHERE id = @p_id;
        UPDATE payroll_items SET isDeleted = 1 WHERE payroll_id = @p_id;
        SELECT 1 AS status;
    END
    ELSE
    BEGIN
        SELECT 0 AS status;
    END
END;
GO

-- sp_delete_employee_attendance_single [UPDATED!!!]
CREATE PROCEDURE sp_delete_employee_attendance_single
    @attendance_id INT,
    @output_status INT OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    
    BEGIN TRY
        UPDATE attendance
        SET isDeleted = 1
        WHERE id = @attendance_id
        AND isDeleted = 0;  -- Only update if not already deleted
        
        SET @output_status = CASE WHEN @@ROWCOUNT > 0 THEN 1 ELSE 0 END;
    END TRY
    BEGIN CATCH
        SET @output_status = 0;
    END CATCH
END
GO

-- sp_delete_employee_attendance [UPDATED!!!]
CREATE PROCEDURE sp_delete_employee_attendance
    @id VARCHAR(50),  -- Changed parameter name to match PHP
    @output_status INT OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    
    BEGIN TRY
        DECLARE @employee_id INT,
                @date DATE;
        
        -- Split the compound key
        SET @employee_id = CAST(SUBSTRING(@id, 1, CHARINDEX('_', @id) - 1) AS INT);
        SET @date = CAST(SUBSTRING(@id, CHARINDEX('_', @id) + 1, LEN(@id)) AS DATE);
        
        UPDATE attendance
        SET isDeleted = 1
        WHERE employee_id = @employee_id
        AND CAST(datetime_log AS DATE) = @date
        AND isDeleted = 0;  -- Only update if not already deleted
        
        SET @output_status = CASE WHEN @@ROWCOUNT > 0 THEN 1 ELSE 0 END;
    END TRY
    BEGIN CATCH
        SET @output_status = 0;
    END CATCH
END
GO	

-- sp_delete_payroll_item *** NOT YET TESTED
CREATE PROCEDURE [dbo].[sp_delete_payroll_item]
    @p_id INT
AS
BEGIN
    SET NOCOUNT ON;

    IF EXISTS (SELECT 1 FROM payroll_items WHERE id = @p_id)
    BEGIN
        UPDATE payroll_items SET isDeleted = 1 WHERE id = @p_id;
        SELECT 1 AS status;
    END
    ELSE
    BEGIN
        SELECT 0 AS status;
    END
END;
GO

-- sp_delete_employee_allowance *** NOT YET TESTED
CREATE PROCEDURE [dbo].[sp_delete_employee_allowance]
    @p_id INT
AS
BEGIN
    SET NOCOUNT ON;

    IF EXISTS (SELECT 1 FROM employee_allowances WHERE id = @p_id)
    BEGIN
        UPDATE employee_allowances SET isDeleted = 1 WHERE id = @p_id;
        SELECT 1 AS status;
    END
    ELSE
    BEGIN
        SELECT 0 AS status;
    END
END;
GO

-- sp_delete_employee_deduction *** NOT YET TESTED
CREATE PROCEDURE [dbo].[sp_delete_employee_deduction]
    @p_id INT
AS
BEGIN
    SET NOCOUNT ON;

    IF EXISTS (SELECT 1 FROM employee_deductions WHERE id = @p_id)
    BEGIN
        UPDATE employee_deductions SET isDeleted = 1 WHERE id = @p_id;
        SELECT 1 AS status;
    END
    ELSE
    BEGIN
        SELECT 0 AS status;
    END
END;
GO

-- sp_delete_user ** NOT WORKING
CREATE PROCEDURE [dbo].[sp_delete_user]
    @UserID INT,
    @Status INT OUTPUT,
    @Message NVARCHAR(255) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    BEGIN TRY
        -- Check if user exists and not already deleted
        IF NOT EXISTS (SELECT 1 FROM users WHERE id = @UserID)
        BEGIN
            SET @Status = 0
            SET @Message = 'User not found'
            RETURN
        END

        IF EXISTS (SELECT 1 FROM users WHERE id = @UserID AND isDeleted = 1)
        BEGIN
            SET @Status = 0 
            SET @Message = 'User already deleted'
            RETURN
        END

        -- Get employee_id for this user
        DECLARE @EmployeeID INT
        SELECT @EmployeeID = employee_id FROM users WHERE id = @UserID

        -- Soft delete the user record
        UPDATE users 
        SET isDeleted = 1
        WHERE id = @UserID

        SET @Status = 1
        SET @Message = 'User successfully deleted'
    END TRY
    BEGIN CATCH
        SET @Status = 0
        SET @Message = ERROR_MESSAGE()
    END CATCH
END
GO


/*
**
** READ/SHOW SPs
**
*/

-- sp_show_deduction [UPDATED!!!]

CREATE PROCEDURE [dbo].[sp_show_deduction]
AS
BEGIN
	SELECT * 
		FROM deductions 
		WHERE isDeleted = 0
		ORDER BY id
END
GO

-- sp_show_department [UPDATED!!!]

CREATE PROCEDURE sp_show_department
AS
BEGIN
    SELECT id, name
    FROM department
    WHERE isDeleted = 0
    ORDER BY name ASC;
END;
GO

-- sp_show_allowances [UPDATED!!!]
CREATE PROCEDURE sp_show_allowances
AS
BEGIN
    SELECT * 
    FROM allowances 
    WHERE isDeleted = 0 
    ORDER BY id ASC;
END;
GO

-- sp_show_positions [UPDATED!!!]

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


/*
**
** MISC SPs
**
*/


-- sp_login [UPDATED!!!]

CREATE PROCEDURE sp_login
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
GO


-- sp_update_user ** NOT WORKING
CREATE PROCEDURE [dbo].[sp_update_user] (
    @UserID INT,
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
)
AS
BEGIN
    SET NOCOUNT ON;
    BEGIN TRY
        -- Start a transaction
        BEGIN TRANSACTION;

        DECLARE @EmployeeId INT;

        -- Get the user's current employee_id
        SELECT @EmployeeId = employee_id 
        FROM users 
        WHERE id = @UserID AND isDeleted = 0;

        IF @EmployeeId IS NULL
        BEGIN
            SET @Status = 0;
            SET @Message = 'User not found.';
            ROLLBACK TRANSACTION;
            RETURN;
        END

        -- Update the employee details
        UPDATE employee
        SET firstname = @FirstName,
            middlename = @MiddleName,
            lastname = @LastName,
            suffix = @Suffix,
            employee_no = @EmployeeNo
        WHERE id = @EmployeeId;

        -- Check for duplicate username
        IF EXISTS (SELECT 1 FROM users WHERE username = @Username AND id <> @UserID AND isDeleted = 0)
        BEGIN
            SET @Status = 0;
            SET @Message = 'Username already exists.';
            ROLLBACK TRANSACTION;
            RETURN;
        END

        -- Update user details
        UPDATE users
        SET username = @Username,
            type = @Type
        WHERE id = @UserID;

        -- Update password if provided
        IF @Password IS NOT NULL
        BEGIN
            UPDATE users
            SET password = @Password
            WHERE id = @UserID;
        END

        -- Commit the transaction
        COMMIT TRANSACTION;

        SET @Status = 1;
        SET @Message = 'User updated successfully.';
    END TRY
    BEGIN CATCH
        -- Rollback the transaction on error
        IF @@TRANCOUNT > 0
            ROLLBACK TRANSACTION;

        SET @Status = 0;
        SET @Message = ERROR_MESSAGE();
    END CATCH
END
GO

/*
	TO DO
	TO DO
	TO DO
	TO DO
	TO DO
	TO DO
*/

-- sp_save_employee_allowance *** NOT YET TESTED

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


CREATE PROCEDURE [dbo].[sp_calculate_payroll]
    @PayrollID INT
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @DateFrom DATE, @DateTo DATE, @Type TINYINT;
    
    -- Get payroll details
    SELECT @DateFrom = date_from, @DateTo = date_to, @Type = type 
    FROM payroll 
    WHERE id = @PayrollID;

    IF @DateFrom IS NULL OR @DateTo IS NULL OR @Type NOT IN (1, 2) BEGIN
        PRINT 'Error: Invalid Payroll ID or Type';
        RETURN;
    END

    -- Delete existing payroll items
    DELETE FROM payroll_items WHERE payroll_id = @PayrollID;
    
    -- Define working days
    DECLARE @WorkingDays INT = CASE WHEN @Type = 1 THEN 22 ELSE 11 END;
    IF @WorkingDays = 0 BEGIN
        PRINT 'Error: Working days cannot be zero';
        RETURN;
    END

    DECLARE @CalcDays INT = DATEDIFF(DAY, @DateFrom, @DateTo) + 1;
    
    -- Process each employee
    DECLARE @EmployeeID INT, @Salary FLOAT, @DailyRate FLOAT, @MinuteRate FLOAT;
    DECLARE emp_cursor CURSOR FOR
    SELECT id, salary FROM employee WHERE isDeleted = 0;
    
    OPEN emp_cursor;
    FETCH NEXT FROM emp_cursor INTO @EmployeeID, @Salary;
    
    WHILE @@FETCH_STATUS = 0
    BEGIN
        SET @DailyRate = @Salary / 22;
        SET @MinuteRate = (@DailyRate / 8) / 60;
        
        DECLARE @Present INT = 0, @Absent INT = 0, @Late INT = 0, @Net FLOAT = 0;
        DECLARE @AllowAmount FLOAT = 0, @DedAmount FLOAT = 0;
        
        -- Calculate attendance
        DECLARE @CurrentDate DATE, @MinutesWorked INT, @LateMinutes INT;
        DECLARE @LogIn DATETIME, @LogOut DATETIME;
        
        DECLARE date_cursor CURSOR FOR
        SELECT DISTINCT CONVERT(DATE, datetime_log) FROM attendance
        WHERE employee_id = @EmployeeID AND CONVERT(DATE, datetime_log) BETWEEN @DateFrom AND @DateTo;
        
        OPEN date_cursor;
        FETCH NEXT FROM date_cursor INTO @CurrentDate;
        
        WHILE @@FETCH_STATUS = 0
        BEGIN
            -- Fetch morning logs
            SELECT @LogIn = MIN(datetime_log), @LogOut = MAX(datetime_log) 
            FROM attendance
            WHERE employee_id = @EmployeeID AND log_type IN (1, 2) AND CONVERT(DATE, datetime_log) = @CurrentDate;
            
            IF @LogIn IS NOT NULL AND @LogOut IS NOT NULL
            BEGIN
                SET @MinutesWorked = DATEDIFF(MINUTE, @LogIn, @LogOut);
                SET @LateMinutes = 240 - @MinutesWorked;
                SET @Net = @Net + (@MinutesWorked * @MinuteRate);
                SET @Late = @Late + @LateMinutes;
                SET @Present = @Present + 0.5;
            END
            
            -- Fetch afternoon logs
            SELECT @LogIn = MIN(datetime_log), @LogOut = MAX(datetime_log) 
            FROM attendance
            WHERE employee_id = @EmployeeID AND log_type IN (3, 4) AND CONVERT(DATE, datetime_log) = @CurrentDate;
            
            IF @LogIn IS NOT NULL AND @LogOut IS NOT NULL
            BEGIN
                SET @MinutesWorked = DATEDIFF(MINUTE, @LogIn, @LogOut);
                SET @LateMinutes = 240 - @MinutesWorked;
                SET @Net = @Net + (@MinutesWorked * @MinuteRate);
                SET @Late = @Late + @LateMinutes;
                SET @Present = @Present + 0.5;
            END
            
            FETCH NEXT FROM date_cursor INTO @CurrentDate;
        END
        
        CLOSE date_cursor;
        DEALLOCATE date_cursor;
        
        -- Ensure Absent is not negative
        SET @Absent = CASE WHEN (@WorkingDays - @Present) < 0 THEN 0 ELSE (@WorkingDays - @Present) END;

        -- Fetch Allowances
        SELECT @AllowAmount = SUM(amount)
        FROM employee_allowances
        WHERE employee_id = @EmployeeID AND (type = @Type OR effective_date BETWEEN @DateFrom AND @DateTo);
        
        -- Fetch Deductions
        SELECT @DedAmount = SUM(amount)
        FROM employee_deductions
        WHERE employee_id = @EmployeeID AND (type = @Type OR effective_date BETWEEN @DateFrom AND @DateTo);
        
        -- Compute net pay
        SET @Net = @Net + ISNULL(@AllowAmount, 0) - ISNULL(@DedAmount, 0);
        
        -- Insert payroll item
        INSERT INTO payroll_items (payroll_id, employee_id, absent, present, late, salary, allowance_amount, deduction_amount, net)
        VALUES (@PayrollID, @EmployeeID, @Absent, @Present, @Late, @Salary, ISNULL(@AllowAmount, 0), ISNULL(@DedAmount, 0), @Net);
        
        FETCH NEXT FROM emp_cursor INTO @EmployeeID, @Salary;
    END
    
    CLOSE emp_cursor;
    DEALLOCATE emp_cursor;

    -- Update payroll status
    UPDATE payroll SET status = 1 WHERE id = @PayrollID;
END
