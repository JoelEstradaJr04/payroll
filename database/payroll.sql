CREATE DATABASE payroll
GO
USE payroll
GO


-- Table structure for table [allowances]
CREATE TABLE [allowances] (
  [id] INT CONSTRAINT [PK_allowances] PRIMARY KEY,
  [allowance] NVARCHAR(250) NOT NULL CONSTRAINT UC_AllowanceName UNIQUE,
  [description] NVARCHAR(MAX) NOT NULL
  ,[isDeleted] BIT NOT NULL CONSTRAINT DF_allowances_isDeleted DEFAULT 0
);

-- Table structure for table [deductions]
CREATE TABLE [deductions] (
  [id] INT CONSTRAINT [PK_deductions] PRIMARY KEY,
  [deduction] NVARCHAR(250) NOT NULL CONSTRAINT UC_DeductionName UNIQUE,
  [description] NVARCHAR(MAX) NOT NULL
  ,[isDeleted] BIT NOT NULL CONSTRAINT DF_deductions_isDeleted DEFAULT 0
);

-- Table structure for table [department]
CREATE TABLE [department] (
  [id] INT CONSTRAINT [PK_department] PRIMARY KEY,
  [name] NVARCHAR(250) NOT NULL CONSTRAINT UC_DepartmentName UNIQUE,
  [isDeleted] BIT NOT NULL CONSTRAINT DF_department_isDeleted DEFAULT 0
);

-- Table structure for table [position]
CREATE TABLE [position] (
  [id] INT CONSTRAINT [PK_position] PRIMARY KEY,
  [department_id] INT NOT NULL,
  [name] NVARCHAR(250) NOT NULL CONSTRAINT UC_PositionName UNIQUE,
  [isDeleted] BIT NOT NULL CONSTRAINT DF_position_isDeleted DEFAULT 0,
  CONSTRAINT [FK_position_department] FOREIGN KEY ([department_id]) REFERENCES [department]([id])
  
);

-- Table structure for table [employee]
CREATE TABLE [employee] (
  [id] INT CONSTRAINT [PK_employee] PRIMARY KEY,
  [employee_no] NVARCHAR(100) CONSTRAINT UC_employee_EmployeeNo UNIQUE,
  [firstname] NVARCHAR(50) NOT NULL,
  [middlename] NVARCHAR(20),
  [lastname] NVARCHAR(50) NOT NULL,
  [suffix] NVARCHAR(10),
  [department_id] INT NOT NULL,
  [position_id] INT NOT NULL,
  [salary] FLOAT NOT NULL,
  [isDeleted] BIT NOT NULL CONSTRAINT DF_employee_isDeleted DEFAULT 0,
  CONSTRAINT [FK_employees_department] FOREIGN KEY ([department_id]) REFERENCES [department]([id]),
  CONSTRAINT [FK_employees_position] FOREIGN KEY ([position_id]) REFERENCES [position]([id])

);

-- Table structure for table [attendance]
CREATE TABLE [attendance] (
  [id] INT CONSTRAINT [PK_attendance] PRIMARY KEY,
  [employee_id] INT NOT NULL,
  [log_type] TINYINT NOT NULL, -- 1 = AM IN, 2 = AM out, 3= PM IN, 4= PM out
  [datetime_log] DATETIME CONSTRAINT [DF_attendances_datetime_log] DEFAULT GETDATE(),
  [date_updated] DATETIME CONSTRAINT [DF_attendances_date_updated] DEFAULT GETDATE(),
  [isDeleted] BIT NOT NULL CONSTRAINT DF_attendance_isDeleted DEFAULT 0,
  CONSTRAINT [FK_attendance_employees] FOREIGN KEY ([employee_id]) REFERENCES [employee]([id])
);

-- Table structure for table [employee_allowances]
CREATE TABLE [employee_allowances] (
  [id] INT CONSTRAINT [PK_employee_allowance] PRIMARY KEY,
  [employee_id] INT NOT NULL,
  [allowance_id] INT NOT NULL,
  [type] TINYINT NOT NULL, -- 1 = Monthly, 2= Semi-Monthly, 3 = Once
  [amount] FLOAT NOT NULL CONSTRAINT CK_employee_allowances_AmountPositiveOrZero CHECK (amount >= 0),
  [effective_date] DATE NOT NULL,
  [date_created] DATETIME CONSTRAINT [DF_employee_allowances_date_created] DEFAULT GETDATE(),
  [isDeleted] BIT NOT NULL CONSTRAINT DF_employee_allowances_isDeleted DEFAULT 0,
  CONSTRAINT [FK_employee_allowances_employee] FOREIGN KEY ([employee_id]) REFERENCES [employee]([id]),
  CONSTRAINT [FK_employee_allowances_allowance] FOREIGN KEY ([allowance_id]) REFERENCES [allowances]([id])
);

-- Table structure for table [employee_deductions]
CREATE TABLE [employee_deductions] (
  [id] INT CONSTRAINT [PK_employee_deduction] PRIMARY KEY,
  [employee_id] INT NOT NULL,
  [deduction_id] INT NOT NULL,
  [type] TINYINT NOT NULL, -- 1 = Monthly, 2= Semi-Monthly, 3 = Once
  [amount] FLOAT NOT NULL CONSTRAINT CK_Employee_Deductions_AmountPositiveOrZero CHECK (amount >= 0),
  [effective_date] DATE NOT NULL,
  [date_created] DATETIME CONSTRAINT [DF_employee_deductions_date_created] DEFAULT GETDATE(),
  [isDeleted] BIT NOT NULL CONSTRAINT DF_employee_deductions_isDeleted DEFAULT 0,
  CONSTRAINT [FK_employee_deductions_employee] FOREIGN KEY ([employee_id]) REFERENCES [employee]([id]),
  CONSTRAINT [FK_employee_deductions_deduction] FOREIGN KEY ([deduction_id]) REFERENCES [deductions]([id])
);

-- Table structure for table [payroll]
CREATE TABLE [payroll] (
  [id] INT CONSTRAINT [PK_payroll] PRIMARY KEY,
  [ref_no] NVARCHAR(MAX) NOT NULL,
  [date_from] DATE NOT NULL,
  [date_to] DATE NOT NULL,
  [type] TINYINT NOT NULL, -- 1 = Monthly, 2 = Semi-Monthly
  [status] TINYINT CONSTRAINT [DF_payroll_status] DEFAULT 0, -- 0 = New, 1 = Computed
  [date_created] DATETIME CONSTRAINT [DF_payroll_date_created] DEFAULT GETDATE()
  ,[isDeleted] BIT NOT NULL CONSTRAINT DF_payroll_isDeleted DEFAULT 0
);

-- Table structure for table [payroll_items]
CREATE TABLE [payroll_items] (
  [id] INT CONSTRAINT [PK_payroll_items] PRIMARY KEY,
  [payroll_id] INT NOT NULL,
  [employee_id] INT NOT NULL,
  [present] INT NOT NULL CONSTRAINT CK_Payroll_Items_PresentPositiveOrZero CHECK (present >= 0),
  [absent] INT NOT NULL CONSTRAINT CK_Payroll_Items_AbsentPositiveOrZero CHECK ([absent] >= 0),
  [late] NVARCHAR(MAX) NOT NULL CONSTRAINT CK_Payroll_Items_LatePositiveOrZero CHECK (late >= 0),
  [salary] FLOAT NOT NULL CONSTRAINT CK_Payroll_Items_SalaryPositiveOrZero CHECK (salary >= 0),
  [allowance_amount] FLOAT NOT NULL CONSTRAINT CK_Payroll_Items_AllowanceAmountPositiveOrZero CHECK (allowance_amount >= 0),
  [allowances] NVARCHAR(MAX) NOT NULL,
  [deduction_amount] FLOAT NOT NULL CONSTRAINT CK_Payroll_Items_DeductionAmountPositiveOrZero CHECK (deduction_amount >= 0),
  [deductions] NVARCHAR(MAX) NOT NULL,
  [net] INT NOT NULL,
  [date_created] DATETIME CONSTRAINT [DF_payroll_items_date_created] DEFAULT GETDATE(),
  [isDeleted] BIT NOT NULL CONSTRAINT DF_payroll_items_isDeleted DEFAULT 0,
  CONSTRAINT [FK_payroll_items_payrolls] FOREIGN KEY ([payroll_id]) REFERENCES [payroll]([id]),
  CONSTRAINT [FK_payroll_items_employees] FOREIGN KEY ([employee_id]) REFERENCES [employee]([id])
);

-- Table structure for table [users]
CREATE TABLE [users] (
  [id] INT CONSTRAINT [PK_users] PRIMARY KEY,
  [employee_id] INT NOT NULL,
  [name] VARCHAR(200),
  [username] NVARCHAR(100) NOT NULL CONSTRAINT UC_Username UNIQUE,
  [password] NVARCHAR(200) NOT NULL,
  [type] BIT CONSTRAINT [DF_users_isAdmin] DEFAULT 0, -- 0 = Staff, 1 = Admin
  [isDeleted] BIT NOT NULL CONSTRAINT DF_users_isDeleted DEFAULT 0,
  CONSTRAINT [FK_users_employee] FOREIGN KEY ([employee_id]) REFERENCES [employee]([id])
);

-- Insert data into allowances
INSERT INTO [allowances] ([id], [allowance], [description]) VALUES
(1, 'Sample', 'Sample Allowance'),
(2, 'Phone', 'Phone Allowance'),
(3, 'Rice', 'Rice Allowance'),
(4, 'House', 'House Allowance');

-- Insert data into deductions
INSERT INTO [deductions] ([id], [deduction], [description]) VALUES
(1, 'Cash Advance', 'Cash Advance'),
(3, 'Sample', 'Sample Deduction');

-- Insert data into department
INSERT INTO [department] ([id], [name]) VALUES
(1, 'IT Department'),
(2, 'HR Department'),
(3, 'Accounting and Finance Department');

-- Insert data into position
INSERT INTO [position] ([id], [department_id], [name]) VALUES
(1, 1, 'Programmer'),
(2, 2, 'HR Supervisor'),
(4, 3, 'Accounting Clerk');

-- Insert data into employee
INSERT INTO [employee] ([id], [employee_no], [firstname], [middlename], [lastname], [suffix], [department_id], [position_id], [salary]) VALUES
(9, '2025-0001', 'Isaac', 'Reyes', 'Newton', 'Sr.', 1, 1, 30000);

-- Insert data into employee_allowances
INSERT INTO [employee_allowances] ([id], [employee_id], [allowance_id], [type], [amount], [effective_date], [date_created]) VALUES
(1, 9, 4, 1, 1000, '1900-01-01', '2020-09-29 11:20:04'),
(3, 9, 3, 2, 300, '1900-01-01', '2020-09-29 11:37:31'),
(5, 9, 1, 3, 1000, '2020-09-16', '2020-09-29 11:38:31');

-- Insert data into employee_deductions
INSERT INTO [employee_deductions] ([id], [employee_id], [deduction_id], [type], [amount], [effective_date], [date_created]) VALUES
(1, 9, 1, 1, 500, '1900-01-01', '2020-09-29 11:20:04'),
(2, 9, 1, 2, 200, '2020-09-16', '2020-09-29 11:21:00'),
(3, 9, 3, 1, 500, '2020-09-16', '2020-09-29 11:22:00');

-- Insert data into payroll
INSERT INTO [payroll] ([id], [ref_no], [date_from], [date_to], [type], [status], [date_created]) VALUES
(1, '2020-3543', '2020-09-16', '2020-09-30', 2, 1, '2020-09-29 15:04:13');

-- Insert data into payroll_items
INSERT INTO [payroll_items] ([id], [payroll_id], [employee_id], [present], [absent], [late], [salary], [allowance_amount], [allowances], [deduction_amount], [deductions], [net], [date_created]) VALUES
(10, 1, 9, 1, 10, 0, 30000, 1300, '[{"aid":"3","amount":"300"},{"aid":"1","amount":"1000"}]', 2000, '[{"did":"3","amount":"500"},{"did":"1","amount":"1500"}]', 664, '2020-09-29 18:46:59');

-- Insert data into attendance
INSERT INTO [attendance] ([id], [employee_id], [log_type], [datetime_log], [date_updated]) VALUES
(10, 9, 1, '2020-09-16 08:00:00', '2020-09-29 16:16:57'),
(11, 9, 2, '2020-09-16 12:00:00', '2020-09-29 16:16:57'),
(12, 9, 3, '2020-09-16 13:00:00', '2020-09-29 16:16:57'),
(16, 9, 4, '2020-09-16 17:00:00', '2020-09-29 16:16:57'),
(17, 9, 1, '2021-03-07 09:00:00', '2021-03-07 15:10:07'),
(18, 9, 2, '2021-03-07 11:00:00', '2021-03-07 15:11:06');

-- Insert data into users
INSERT INTO [users] ([id], [employee_id], [name], [username], [password], [type]) VALUES
(1, 9, 'Isaac Newton', 'admin', 'admin123', 1);




-- Backup existing tables
SELECT * INTO allowances_backup FROM allowances;
SELECT * INTO deductions_backup FROM deductions;
SELECT * INTO department_backup FROM department;
SELECT * INTO position_backup FROM position;
SELECT * INTO employee_backup FROM employee;
SELECT * INTO attendance_backup FROM attendance;
SELECT * INTO employee_allowances_backup FROM employee_allowances;
SELECT * INTO employee_deductions_backup FROM employee_deductions;
SELECT * INTO payroll_backup FROM payroll;
SELECT * INTO payroll_items_backup FROM payroll_items;
SELECT * INTO users_backup FROM users;

-- Drop Foreign Key Constraints
ALTER TABLE [users] DROP CONSTRAINT IF EXISTS [FK_users_employee];
ALTER TABLE [payroll_items] DROP CONSTRAINT IF EXISTS [FK_payroll_items_employees];
ALTER TABLE [payroll_items] DROP CONSTRAINT IF EXISTS [FK_payroll_items_payrolls];
ALTER TABLE [employee_deductions] DROP CONSTRAINT IF EXISTS [FK_employee_deductions_deduction];
ALTER TABLE [employee_deductions] DROP CONSTRAINT IF EXISTS [FK_employee_deductions_employee];
ALTER TABLE [employee_allowances] DROP CONSTRAINT IF EXISTS [FK_employee_allowances_allowance];
ALTER TABLE [employee_allowances] DROP CONSTRAINT IF EXISTS [FK_employee_allowances_employee];
ALTER TABLE [attendance] DROP CONSTRAINT IF EXISTS [FK_attendance_employees];
ALTER TABLE [employee] DROP CONSTRAINT IF EXISTS [FK_employees_department];
ALTER TABLE [position] DROP CONSTRAINT IF EXISTS [FK_position_department];

-- Drop Original Tables
DROP TABLE users;
DROP TABLE payroll_items;
DROP TABLE payroll;
DROP TABLE employee_deductions;
DROP TABLE employee_allowances;
DROP TABLE attendance;
DROP TABLE employee;
DROP TABLE position;
DROP TABLE department;
DROP TABLE deductions;
DROP TABLE allowances;


-- Recreate Tables with IDENTITY(1,1)
CREATE TABLE allowances (
    id INT IDENTITY(1,1) PRIMARY KEY,
    allowance NVARCHAR(250) NOT NULL CONSTRAINT UC_AllowanceName UNIQUE,
    description NVARCHAR(MAX) NOT NULL,
    isDeleted BIT NOT NULL CONSTRAINT DF_allowances_isDeleted DEFAULT 0
);

CREATE TABLE deductions (
    id INT IDENTITY(1,1) PRIMARY KEY,
    deduction NVARCHAR(250) NOT NULL CONSTRAINT UC_DeductionName UNIQUE,
    description NVARCHAR(MAX) NOT NULL,
    isDeleted BIT NOT NULL CONSTRAINT DF_deductions_isDeleted DEFAULT 0
);

CREATE TABLE department (
    id INT IDENTITY(1,1) PRIMARY KEY,
    name NVARCHAR(250) NOT NULL CONSTRAINT UC_DepartmentName UNIQUE,
    isDeleted BIT NOT NULL CONSTRAINT DF_department_isDeleted DEFAULT 0
);

CREATE TABLE position (
    id INT IDENTITY(1,1) PRIMARY KEY,
    department_id INT NOT NULL,
    name NVARCHAR(250) NOT NULL CONSTRAINT UC_PositionName UNIQUE,
    isDeleted BIT NOT NULL CONSTRAINT DF_position_isDeleted DEFAULT 0
);

CREATE TABLE employee (
    id INT IDENTITY(1,1) PRIMARY KEY,
    employee_no NVARCHAR(100),
    firstname NVARCHAR(50) NOT NULL,
    middlename NVARCHAR(20),
    lastname NVARCHAR(50) NOT NULL,
    suffix NVARCHAR(10),
    department_id INT NOT NULL,
    position_id INT NOT NULL,
    salary FLOAT NOT NULL,
    isDeleted BIT NOT NULL CONSTRAINT DF_employee_isDeleted DEFAULT 0
);

CREATE TABLE attendance (
    id INT IDENTITY(1,1) PRIMARY KEY,
    employee_id INT NOT NULL,
    log_type TINYINT NOT NULL, -- 1 = AM IN, 2 = AM out, 3= PM IN, 4= PM out
    datetime_log DATETIME CONSTRAINT DF_attendance_datetime_log DEFAULT GETDATE(),
    date_updated DATETIME CONSTRAINT DF_attendance_date_updated DEFAULT GETDATE(),
    isDeleted BIT NOT NULL CONSTRAINT DF_attendance_isDeleted DEFAULT 0
);

CREATE TABLE employee_allowances (
    id INT IDENTITY(1,1) PRIMARY KEY,
    employee_id INT NOT NULL,
    allowance_id INT NOT NULL,
    type TINYINT NOT NULL, -- 1 = Monthly, 2= Semi-Monthly, 3 = Once
    amount FLOAT NOT NULL CONSTRAINT CK_employee_allowances_AmountPositiveOrZero CHECK (amount >= 0),
    effective_date DATE NOT NULL,
    date_created DATETIME CONSTRAINT DF_employee_allowances_date_created DEFAULT GETDATE(),
    isDeleted BIT NOT NULL CONSTRAINT DF_employee_allowances_isDeleted DEFAULT 0
);

CREATE TABLE employee_deductions (
    id INT IDENTITY(1,1) PRIMARY KEY,
    employee_id INT NOT NULL,
    deduction_id INT NOT NULL,
    type TINYINT NOT NULL, -- 1 = Monthly, 2= Semi-Monthly, 3 = Once
    amount FLOAT NOT NULL CONSTRAINT CK_Employee_Deductions_AmountPositiveOrZero CHECK (amount >= 0),
    effective_date DATE NOT NULL,
    date_created DATETIME CONSTRAINT DF_employee_deductions_date_created DEFAULT GETDATE(),
    isDeleted BIT NOT NULL CONSTRAINT DF_employee_deductions_isDeleted DEFAULT 0
);

CREATE TABLE payroll (
    id INT IDENTITY(1,1) PRIMARY KEY,
    ref_no NVARCHAR(MAX) NOT NULL,
    date_from DATE NOT NULL,
    date_to DATE NOT NULL,
    type TINYINT NOT NULL, -- 1 = Monthly, 2 = Semi-Monthly
    status TINYINT CONSTRAINT DF_payroll_status DEFAULT 0, -- 0 = New, 1 = Computed
    date_created DATETIME CONSTRAINT DF_payroll_date_created DEFAULT GETDATE(),
    isDeleted BIT NOT NULL CONSTRAINT DF_payroll_isDeleted DEFAULT 0
);

CREATE TABLE payroll_items (
    id INT IDENTITY(1,1) PRIMARY KEY,
    payroll_id INT NOT NULL,
    employee_id INT NOT NULL,
    present INT NOT NULL CHECK (present >= 0),
    absent INT NOT NULL CHECK (absent >= 0),
    late NVARCHAR(MAX) NOT NULL CHECK (late >= 0),
    salary FLOAT NOT NULL CHECK (salary >= 0),
    allowance_amount FLOAT NOT NULL CHECK (allowance_amount >= 0),
    allowances NVARCHAR(MAX) NOT NULL,
    deduction_amount FLOAT NOT NULL CHECK (deduction_amount >= 0),
    deductions NVARCHAR(MAX) NOT NULL,
    net INT NOT NULL,
    date_created DATETIME CONSTRAINT DF_payroll_items_date_created DEFAULT GETDATE(),
    isDeleted BIT NOT NULL CONSTRAINT DF_payroll_items_isDeleted DEFAULT 0
);

CREATE TABLE users (
    id INT IDENTITY(1,1) PRIMARY KEY,
    employee_id INT NOT NULL CONSTRAINT UC_Users_Employee UNIQUE,
    name VARCHAR(200),
    username NVARCHAR(100) NOT NULL CONSTRAINT UC_Username UNIQUE,
    password NVARCHAR(200) NOT NULL,
    type BIT CONSTRAINT DF_users_isAdmin DEFAULT 0, -- 0 = Staff, 1 = Admin
    isDeleted BIT NOT NULL CONSTRAINT DF_users_isDeleted DEFAULT 0
);


-- Restore Data with IDENTITY_INSERT
SET IDENTITY_INSERT allowances ON;
INSERT INTO allowances (id, allowance, description, isDeleted) SELECT id, allowance, description, isDeleted FROM allowances_backup;
SET IDENTITY_INSERT allowances OFF;

SET IDENTITY_INSERT deductions ON;
INSERT INTO deductions (id, deduction, description, isDeleted) SELECT id, deduction, description, isDeleted FROM deductions_backup;
SET IDENTITY_INSERT deductions OFF;

SET IDENTITY_INSERT department ON;
INSERT INTO department (id, name, isDeleted) SELECT id, name, isDeleted FROM department_backup;
SET IDENTITY_INSERT department OFF;

SET IDENTITY_INSERT position ON;
INSERT INTO position (id, department_id, name, isDeleted) SELECT id, department_id, name, isDeleted FROM position_backup;
SET IDENTITY_INSERT position OFF;

SET IDENTITY_INSERT employee ON;
INSERT INTO employee (id, employee_no, firstname, middlename, lastname, suffix, department_id, position_id, salary, isDeleted) SELECT id, employee_no, firstname, middlename, lastname, suffix, department_id, position_id, salary, isDeleted FROM employee_backup;
SET IDENTITY_INSERT employee OFF;

SET IDENTITY_INSERT attendance ON;
INSERT INTO attendance (id, employee_id, log_type, datetime_log, date_updated, isDeleted) SELECT id, employee_id, log_type, datetime_log, date_updated, isDeleted FROM attendance_backup;
SET IDENTITY_INSERT attendance OFF;

SET IDENTITY_INSERT employee_allowances ON;
INSERT INTO employee_allowances (id, employee_id, allowance_id, type, amount, effective_date, date_created, isDeleted) SELECT id, employee_id, allowance_id, type, amount, effective_date, date_created, isDeleted FROM employee_allowances_backup;
SET IDENTITY_INSERT employee_allowances OFF;

SET IDENTITY_INSERT employee_deductions ON;
INSERT INTO employee_deductions (id, employee_id, deduction_id, type, amount, effective_date, date_created, isDeleted) SELECT id, employee_id, deduction_id, type, amount, effective_date, date_created, isDeleted FROM employee_deductions_backup;
SET IDENTITY_INSERT employee_deductions OFF;

SET IDENTITY_INSERT payroll ON;
INSERT INTO payroll (id, ref_no, date_from, date_to, type, status, date_created, isDeleted) SELECT id, ref_no, date_from, date_to, type, status, date_created, isDeleted FROM payroll_backup;
SET IDENTITY_INSERT payroll OFF;

SET IDENTITY_INSERT payroll_items ON;
INSERT INTO payroll_items (id, payroll_id, employee_id, present, absent, late, salary, allowance_amount, allowances, deduction_amount, deductions, net, date_created, isDeleted) SELECT id, payroll_id, employee_id, present, absent, late, salary, allowance_amount, allowances, deduction_amount, deductions, net, date_created, isDeleted FROM payroll_items_backup;
SET IDENTITY_INSERT payroll_items OFF;

SET IDENTITY_INSERT users ON;
INSERT INTO users (id, employee_id, name, username, password, type, isDeleted) SELECT id, employee_id, name, username, password, type, isDeleted FROM users_backup;
SET IDENTITY_INSERT users OFF;

-- Don't forget to reseed the identity after the restore!
DBCC CHECKIDENT ('allowances', RESEED);
DBCC CHECKIDENT ('deductions', RESEED);
DBCC CHECKIDENT ('department', RESEED);
DBCC CHECKIDENT ('position', RESEED);
DBCC CHECKIDENT ('employee', RESEED);
DBCC CHECKIDENT ('attendance', RESEED);
DBCC CHECKIDENT ('employee_allowances', RESEED);
DBCC CHECKIDENT ('employee_deductions', RESEED);
DBCC CHECKIDENT ('payroll', RESEED);
DBCC CHECKIDENT ('payroll_items', RESEED);
DBCC CHECKIDENT ('users', RESEED);

-- Re-add Foreign Key Constraints
ALTER TABLE [position] ADD CONSTRAINT [FK_position_department] FOREIGN KEY ([department_id]) REFERENCES [department]([id]);

ALTER TABLE [employee] ADD CONSTRAINT [FK_employees_department] FOREIGN KEY ([department_id]) REFERENCES [department]([id]);
ALTER TABLE [employee] ADD CONSTRAINT [FK_employees_position] FOREIGN KEY ([position_id]) REFERENCES [position]([id]);

ALTER TABLE [attendance] ADD CONSTRAINT [FK_attendance_employees] FOREIGN KEY ([employee_id]) REFERENCES [employee]([id]);

ALTER TABLE [employee_allowances] ADD CONSTRAINT [FK_employee_allowances_employee] FOREIGN KEY ([employee_id]) REFERENCES [employee]([id]);
ALTER TABLE [employee_allowances] ADD CONSTRAINT [FK_employee_allowances_allowance] FOREIGN KEY ([allowance_id]) REFERENCES [allowances]([id]);

ALTER TABLE [employee_deductions] ADD CONSTRAINT [FK_employee_deductions_employee] FOREIGN KEY ([employee_id]) REFERENCES [employee]([id]);
ALTER TABLE [employee_deductions] ADD CONSTRAINT [FK_employee_deductions_deduction] FOREIGN KEY ([deduction_id]) REFERENCES [deductions]([id]);

ALTER TABLE [payroll_items] ADD CONSTRAINT [FK_payroll_items_payrolls] FOREIGN KEY ([payroll_id]) REFERENCES [payroll]([id]);
ALTER TABLE [payroll_items] ADD CONSTRAINT [FK_payroll_items_employees] FOREIGN KEY ([employee_id]) REFERENCES [employee]([id]);

ALTER TABLE [users] ADD CONSTRAINT [FK_users_employee] FOREIGN KEY ([employee_id]) REFERENCES [employee]([id]);

-- Drop existing backup tables
DROP TABLE allowances_backup; 
DROP TABLE deductions_backup;
DROP TABLE department_backup;
DROP TABLE position_backup;
DROP TABLE employee_backup;
DROP TABLE attendance_backup;
DROP TABLE employee_allowances_backup;
DROP TABLE employee_deductions_backup;
DROP TABLE payroll_backup;
DROP TABLE payroll_items_backup;
DROP TABLE users_backup;