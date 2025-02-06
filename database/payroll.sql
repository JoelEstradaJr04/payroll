-- UPDATED payroll.sql database as of February 5, 2025.
-- All Constraints are NAMED.
-- Proper references are implemented.
-- VIEW is created.

CREATE DATABASE payroll
GO
USE payroll
GO


-- Table structure for table [allowances]
CREATE TABLE [allowances] (
  [id] INT IDENTITY(1,1) CONSTRAINT [PK_allowances] PRIMARY KEY
  ,[allowance] NVARCHAR(250) NOT NULL CONSTRAINT UC_AllowanceName UNIQUE
  ,[description] NVARCHAR(MAX) NOT NULL
  ,[isDeleted] BIT NOT NULL CONSTRAINT DF_allowances_isDeleted DEFAULT 0
);

-- Table structure for table [deductions]
CREATE TABLE [deductions] (
  [id] INT IDENTITY(1,1) CONSTRAINT [PK_deductions] PRIMARY KEY
  ,[deduction] NVARCHAR(250) NOT NULL CONSTRAINT UC_DeductionName UNIQUE
  ,[description] NVARCHAR(MAX) NOT NULL
  ,[isDeleted] BIT NOT NULL CONSTRAINT DF_deductions_isDeleted DEFAULT 0
);

-- Table structure for table [department]
CREATE TABLE [department] (
  [id] INT IDENTITY(1,1) CONSTRAINT [PK_department] PRIMARY KEY
  ,[name] NVARCHAR(250) NOT NULL CONSTRAINT UC_DepartmentName UNIQUE
  ,[isDeleted] BIT NOT NULL CONSTRAINT DF_department_isDeleted DEFAULT 0
);

-- Table structure for table [position]
CREATE TABLE [position] (
  [id] INT IDENTITY(1,1) CONSTRAINT [PK_position] PRIMARY KEY
  ,[department_id] INT NOT NULL
  ,[name] NVARCHAR(250) NOT NULL CONSTRAINT UC_PositionName UNIQUE
  ,[isDeleted] BIT NOT NULL CONSTRAINT DF_position_isDeleted DEFAULT 0
  ,CONSTRAINT [FK_position_department] FOREIGN KEY ([department_id]) REFERENCES [department]([id])
  
);

-- Table structure for table [employee]
CREATE TABLE [employee] (
  [id] INT IDENTITY(1,1) CONSTRAINT [PK_employee] PRIMARY KEY
  ,[employee_no] NVARCHAR(100) NOT NULL CONSTRAINT UC_EmployeeNo UNIQUE
  ,[firstname] NVARCHAR(50) NOT NULL
  ,[middlename] NVARCHAR(20) CONSTRAINT DF_employee_middle_name DEFAULT 'N/A'
  ,[lastname] NVARCHAR(50) NOT NULL
  ,[suffix] NVARCHAR(10) CONSTRAINT DF_employee_suffix DEFAULT 'N/A'
  ,[department_id] INT NOT NULL
  ,[position_id] INT NOT NULL
  ,[salary] FLOAT NOT NULL
  ,[isDeleted] BIT NOT NULL CONSTRAINT DF_employee_isDeleted DEFAULT 0
  ,CONSTRAINT [FK_employees_department] FOREIGN KEY ([department_id]) REFERENCES [department]([id])
  ,CONSTRAINT [FK_employees_position] FOREIGN KEY ([position_id]) REFERENCES [position]([id])
  ,CONSTRAINT UC_EmployeeFullName UNIQUE (firstname, middlename, lastname, suffix)
  ,CONSTRAINT CK_EmployeeSalary CHECK (salary >= 645)

);

-- Table structure for table [attendance]
CREATE TABLE [attendance] (
  [id] INT IDENTITY(1,1) CONSTRAINT [PK_attendance] PRIMARY KEY
  ,[employee_id] INT NOT NULL
  ,[log_type] TINYINT NOT NULL -- 1 = AM IN, 2 = AM out, 3= PM IN, 4= PM out
  ,[datetime_log] DATETIME CONSTRAINT [DF_attendances_datetime_log] DEFAULT GETDATE()
  ,[date_updated] DATETIME CONSTRAINT [DF_attendances_date_updated] DEFAULT GETDATE()
  ,[isDeleted] BIT NOT NULL CONSTRAINT DF_attendance_isDeleted DEFAULT 0
  ,CONSTRAINT [FK_attendance_employees] FOREIGN KEY ([employee_id]) REFERENCES [employee]([id])
);

-- Table structure for table [employee_allowances]
CREATE TABLE [employee_allowances] (
  [id] INT IDENTITY(1,1) CONSTRAINT [PK_employee_allowance] PRIMARY KEY
  ,[employee_id] INT NOT NULL
  ,[allowance_id] INT NOT NULL
  ,[type] TINYINT NOT NULL -- 1 = Monthly, 2= Semi-Monthly, 3 = Once
  ,[amount] FLOAT NOT NULL CONSTRAINT CK_employee_allowances_AmountPositiveOrZero CHECK (amount >= 0)
  ,[effective_date] DATE
  ,[date_created] DATETIME CONSTRAINT [DF_employee_allowances_date_created] DEFAULT GETDATE()
  ,[isDeleted] BIT NOT NULL CONSTRAINT DF_employee_allowances_isDeleted DEFAULT 0
  ,CONSTRAINT [FK_employee_allowances_employee] FOREIGN KEY ([employee_id]) REFERENCES [employee]([id])
  ,CONSTRAINT [FK_employee_allowances_allowance] FOREIGN KEY ([allowance_id]) REFERENCES [allowances]([id])
);

-- Table structure for table [employee_deductions]
CREATE TABLE [employee_deductions] (
  [id] INT IDENTITY(1,1) CONSTRAINT [PK_employee_deduction] PRIMARY KEY
  ,[employee_id] INT NOT NULL
  ,[deduction_id] INT NOT NULL
  ,[type] TINYINT NOT NULL -- 1 = Monthly, 2= Semi-Monthly, 3 = Once
  ,[amount] FLOAT NOT NULL CONSTRAINT CK_Employee_Deductions_AmountPositiveOrZero CHECK (amount >= 0)
  ,[effective_date] DATE
  ,[date_created] DATETIME CONSTRAINT [DF_employee_deductions_date_created] DEFAULT GETDATE()
  ,[isDeleted] BIT NOT NULL CONSTRAINT DF_employee_deductions_isDeleted DEFAULT 0
  ,CONSTRAINT [FK_employee_deductions_employee] FOREIGN KEY ([employee_id]) REFERENCES [employee]([id])
  ,CONSTRAINT [FK_employee_deductions_deduction] FOREIGN KEY ([deduction_id]) REFERENCES [deductions]([id])
);

-- Table structure for table [payroll]
CREATE TABLE [payroll] (
  [id] INT IDENTITY(1,1) CONSTRAINT [PK_payroll] PRIMARY KEY
  ,[ref_no] NVARCHAR(250) NOT NULL  CONSTRAINT UC_RefNo UNIQUE
  ,[date_from] DATE NOT NULL
  ,[date_to] DATE NOT NULL
  ,[type] TINYINT NOT NULL -- 1 = Monthly, 2 = Semi-Monthly
  ,[status] TINYINT CONSTRAINT [DF_payroll_status] DEFAULT 0 -- 0 = New, 1 = Computed
  ,[date_created] DATETIME CONSTRAINT [DF_payroll_date_created] DEFAULT GETDATE()
  ,[isDeleted] BIT NOT NULL CONSTRAINT DF_payroll_isDeleted DEFAULT 0
);

-- Table structure for table [payroll_items]
CREATE TABLE [payroll_items] (
  [id] INT IDENTITY(1,1) CONSTRAINT [PK_payroll_items] PRIMARY KEY
  ,[payroll_id] INT NOT NULL
  ,[employee_id] INT NOT NULL
  ,[present] INT NOT NULL CONSTRAINT CK_Payroll_Items_PresentPositiveOrZero CHECK (present >= 0)
  ,[absent] INT NOT NULL CONSTRAINT CK_Payroll_Items_AbsentPositiveOrZero CHECK ([absent] >= 0)
  ,[late] NVARCHAR(MAX) NOT NULL CONSTRAINT CK_Payroll_Items_LatePositiveOrZero CHECK (late >= 0)
  ,[salary] FLOAT NOT NULL CONSTRAINT CK_Payroll_Items_SalaryPositiveOrZero CHECK (salary >= 0)
  ,[allowance_amount] FLOAT NOT NULL CONSTRAINT CK_Payroll_Items_AllowanceAmountPositiveOrZero CHECK (allowance_amount >= 0)
  ,[allowances] NVARCHAR(MAX) NOT NULL
  ,[deduction_amount] FLOAT NOT NULL CONSTRAINT CK_Payroll_Items_DeductionAmountPositiveOrZero CHECK (deduction_amount >= 0)
  ,[deductions] NVARCHAR(MAX) NOT NULL
  ,[net] INT NOT NULL
  ,[date_created] DATETIME CONSTRAINT [DF_payroll_items_date_created] DEFAULT GETDATE()
  ,[isDeleted] BIT NOT NULL CONSTRAINT DF_payroll_items_isDeleted DEFAULT 0
  ,CONSTRAINT [FK_payroll_items_payrolls] FOREIGN KEY ([payroll_id]) REFERENCES [payroll]([id])
  ,CONSTRAINT [FK_payroll_items_employees] FOREIGN KEY ([employee_id]) REFERENCES [employee]([id])
);

-- Table structure for table [users]
CREATE TABLE [users] (
  [id] INT IDENTITY(1,1) CONSTRAINT [PK_users] PRIMARY KEY
  ,[employee_id] INT NOT NULL CONSTRAINT UC_Users_Employee UNIQUE
  ,[username] NVARCHAR(100) NOT NULL CONSTRAINT UC_Username UNIQUE
  ,[password] NVARCHAR(200) NOT NULL
  ,[type] BIT CONSTRAINT [DF_users_isAdmin] DEFAULT 0 -- 0 = Staff, 1 = Admin
  ,[isDeleted] BIT NOT NULL CONSTRAINT DF_users_isDeleted DEFAULT 0
  ,CONSTRAINT [FK_users_employee] FOREIGN KEY ([employee_id]) REFERENCES [employee]([id])
);

-- Insert data into allowances
INSERT INTO [allowances] ([allowance], [description]) VALUES
('Scholarship', 'Iskolar ng Bayan Allowance'),
('Phone', 'Phone Allowance'),
('Rice', 'Rice Allowance'),
('House', 'House Allowance');

-- Insert data into deductions
INSERT INTO [deductions] ([deduction], [description]) VALUES
('Destruction of Office Property', 'Damaging, misplacing, and other bad actions towards office properties.'),
('Cash Advanced', 'Early claiming of a part of salary.'),
('Sleeping Penalty', 'Sleeping during office hours.');

-- Insert data into department
INSERT INTO [department] ([name]) VALUES
('IT Department'),
('HR Department'),
('Accounting and Finance Department');

-- Insert data into position
INSERT INTO [position] ([department_id], [name])
VALUES 
((SELECT id FROM [department] WHERE [name] = 'IT Department'), 'Junior Programmer'),
((SELECT id FROM [department] WHERE [name] = 'IT Department'), 'Senior Programmer'),
((SELECT id FROM [department] WHERE [name] = 'IT Department'), 'Web App Developer'),
((SELECT id FROM [department] WHERE [name] = 'HR Department'), 'HR Supervisor'),
((SELECT id FROM [department] WHERE [name] = 'HR Department'), 'Junior HR Employee'),
((SELECT id FROM [department] WHERE [name] = 'HR Department'), 'Senior HR Employee'),
((SELECT id FROM [department] WHERE [name] = 'Accounting and Finance Department'), 'Accounting Clerk'),
((SELECT id FROM [department] WHERE [name] = 'Accounting and Finance Department'), 'Accounting Senior'),
((SELECT id FROM [department] WHERE [name] = 'Accounting and Finance Department'), 'Manager of Finance');

-- Insert data into employee
INSERT INTO [employee] ([employee_no], [firstname], [middlename], [lastname], [suffix], [department_id], [position_id], [salary]) VALUES
('EMP0001', 'Isaac', 'Reyes', 'Newton', 'Sr.', 1, 1, 100000),
('EMP0002', 'Mang', 'Mariz', 'Thomas', 'Jr.', 1, 2, 90000),
('EMP0003', 'Jennifer', 'Lopez', 'Lawrence', 'N/A', 1, 3, 80000),
('EMP0004', 'Antonio', 'Zamora', 'Luna', 'N/A', 2, 1, 70000),
('EMP0005', 'Albert', 'N/A', 'Esteem', 'N/A', 2, 2, 60000),
('EMP0006', 'John', 'Wewers', 'Cena', 'III', 2, 3, 50000),
('EMP0007', 'LeBrand', 'N/A', 'Jamison', 'Sr.', 3, 1, 40000),
('EMP0008', 'Jasmine', 'Omania', 'Henry', 'N/A', 3, 2, 30000),
('EMP0009', 'Maricar', 'Albarez', 'Delos Santos', 'N/A', 3, 3, 20000);

-- Insert data into employee_allowances 1 = MONTH 2 = SEMI 3 = ONCE
INSERT INTO [employee_allowances] ([employee_id], [allowance_id], [type], [amount], [effective_date], [date_created]) VALUES
(1, 1, 1, 1000, DATEADD(day, 3, GETDATE()), GETDATE()),  
(1, 2, 2, 2000, DATEADD(day, 7, GETDATE()), GETDATE()), 
(1, 3, 3, 3000, DATEADD(day, 1, GETDATE()), GETDATE()), 
(2, 2, 1, 2000, DATEADD(day, 2, GETDATE()), GETDATE()),   
(2, 3, 2, 3000, DATEADD(day, 5, GETDATE()), GETDATE()), 
(2, 4, 3, 4000, DATEADD(day, 8, GETDATE()), GETDATE()), 
(3, 1, 1, 1000, DATEADD(day, 1, GETDATE()), GETDATE()),  
(3, 4, 2, 4000, DATEADD(day, 4, GETDATE()), GETDATE()), 
(3, 2, 3, 2000, DATEADD(day, 8, GETDATE()), GETDATE()),  
(4, 3, 1, 3000, DATEADD(day, 3, GETDATE()), GETDATE()),   
(4, 1, 2, 1000, DATEADD(day, 6, GETDATE()), GETDATE()),   
(4, 4, 3, 4000, DATEADD(day, 10, GETDATE()), GETDATE()), 
(5, 4, 1, 4000, DATEADD(day, 2, GETDATE()), GETDATE()),  
(5, 2, 2, 2000, DATEADD(day, 5, GETDATE()), GETDATE()),  
(5, 3, 3, 3000, DATEADD(day, 8, GETDATE()), GETDATE()),  
(6, 1, 1, 1000, DATEADD(day, 1, GETDATE()), GETDATE()),   
(6, 3, 2, 3000, DATEADD(day, 4, GETDATE()), GETDATE()),   
(6, 2, 3, 2000, DATEADD(day, 7, GETDATE()), GETDATE()),   
(7, 2, 1, 2000, DATEADD(day, 3, GETDATE()), GETDATE()),   
(7, 4, 2, 4000, DATEADD(day, 6, GETDATE()), GETDATE()),  
(7, 1, 3, 1000, DATEADD(day, 8, GETDATE()), GETDATE()),   
(8, 3, 1, 3000, DATEADD(day, 2, GETDATE()), GETDATE()),  
(8, 1, 2, 1000, DATEADD(day, 5, GETDATE()), GETDATE()),   
(8, 2, 3, 2000, DATEADD(day, 7, GETDATE()), GETDATE()),   
(9, 4, 1, 4000, DATEADD(day, 6, GETDATE()), GETDATE()),
(9, 1, 2, 1000, DATEADD(day, 3, GETDATE()), GETDATE()), 
(9, 3, 3, 3000, DATEADD(day, 10, GETDATE()), GETDATE()); 

-- Insert data into employee_deductions
INSERT INTO [employee_deductions] ([employee_id], [deduction_id], [type], [amount], [effective_date], [date_created]) VALUES
(1, 1, 1, 100, DATEADD(day, 4, GETDATE()), GETDATE()),  
(1, 2, 2, 200, DATEADD(day, 7, GETDATE()), GETDATE()),  
(1, 3, 3, 300, DATEADD(day, 1, GETDATE()), GETDATE()),  
(2, 2, 1, 300, DATEADD(day, 2, GETDATE()), GETDATE()),  
(2, 3, 2, 300, DATEADD(day, 5, GETDATE()), GETDATE()),  
(2, 1, 3, 100, DATEADD(day, 9, GETDATE()), GETDATE()),   
(3, 3, 1, 300, DATEADD(day, 1, GETDATE()), GETDATE()),  
(3, 1, 2, 100, DATEADD(day, 4, GETDATE()), GETDATE()),  
(3, 2, 3, 200, DATEADD(day, 8, GETDATE()), GETDATE()),   
(4, 1, 1, 100, DATEADD(day, 3, GETDATE()), GETDATE()),  
(4, 3, 2, 300, DATEADD(day, 6, GETDATE()), GETDATE()), 
(4, 2, 3, 200, DATEADD(day, 10, GETDATE()), GETDATE()), 
(5, 2, 1, 200, DATEADD(day, 2, GETDATE()), GETDATE()),  
(5, 1, 2, 100, DATEADD(day, 5, GETDATE()), GETDATE()),   
(5, 3, 3, 300, DATEADD(day, 8, GETDATE()), GETDATE()),  
(6, 3, 1, 300, DATEADD(day, 1, GETDATE()), GETDATE()),  
(6, 2, 2, 200, DATEADD(day, 4, GETDATE()), GETDATE()),
(6, 1, 3, 100, DATEADD(day, 7, GETDATE()), GETDATE()),   
(7, 1, 1, 100, DATEADD(day, 3, GETDATE()), GETDATE()),  
(7, 2, 2, 200, DATEADD(day, 6, GETDATE()), GETDATE()), 
(7, 3, 3, 300, DATEADD(day, 9, GETDATE()), GETDATE()),   
(8, 2, 1, 200, DATEADD(day, 2, GETDATE()), GETDATE()),  
(8, 3, 2, 300, DATEADD(day, 5, GETDATE()), GETDATE()),  
(8, 1, 3, 100, DATEADD(day, 8, GETDATE()), GETDATE()),   
(9, 1, 1, 100, DATEADD(day, 6, GETDATE()), GETDATE()),
(9, 2, 2, 200, DATEADD(day, 3, GETDATE()), GETDATE()),   
(9, 3, 3, 300, DATEADD(day, 10, GETDATE()), GETDATE());

-- Insert data into payroll
INSERT INTO [payroll] ([ref_no], [date_from], [date_to], [type], [status], [date_created]) VALUES
('PAY-20250201100001', '2025-02-01', '2025-02-15', 2, 0, GETDATE()),  
('PAY-20250201100002', '2025-02-16', '2025-02-28', 2, 0, GETDATE());

GO
-- insert data into payroll_items

DECLARE @PayrollID1 INT;
DECLARE @PayrollID2 INT;
DECLARE @PayrollID3 INT;

SELECT @PayrollID1 = id FROM payroll WHERE ref_no = 'PAY-20250201100001';
SELECT @PayrollID2 = id FROM payroll WHERE ref_no = 'PAY-20250201100002';

-- Insert data into payroll_items NEEDS NET
INSERT INTO [payroll_items] ([payroll_id], [employee_id], [present], [absent], [late], [salary], [allowance_amount], [allowances], [deduction_amount], [deductions], [net], [date_created]) VALUES
-- SEMI MONTHLY (FEB 1 TO 15)
(@PayrollID1, 1, 10, 5, 800, 50000, 6000, '[{"aid":"2","amount":"2000"},{"aid":"3","amount":"3000"}]', 500, '[{"did":"2","amount":"200"},{"did":"3","amount":"300"}]', 46924.24, GETDATE()),  -- Isaac
(@PayrollID1, 2, 12, 3, 1000, 45000, 9000, '[{"aid":"3","amount":"3000"},{"aid":"4","amount":"4000"}]', 400, '[{"did":"1","amount":"100"},{"did":"3","amount":"300"}]', 43077.27, GETDATE()),  -- Mang
(@PayrollID1, 3, 15, 0, 0, 40000, 7000, '[{"aid":"2","amount":"2000"},{"aid":"4","amount":"4000"}]', 300, '[{"did":"1","amount":"100"},{"did":"2","amount":"200"}]', 45700.00, GETDATE()),  -- Jennifer
(@PayrollID1, 4, 10, 5, 500, 35000, 4000, '[{"aid":"1","amount":"1000"}]', 300, '[{"did":"3","amount":"300"}]', 32385.61, GETDATE()),  -- Antonio
(@PayrollID1, 5, 12, 3, 400, 30000, 9000, '[{"aid":"2","amount":"2000"},{"aid":"3","amount":"3000"}]', 400, '[{"did":"1","amount":"100"},{"did":"3","amount":"300"}]', 32327.27, GETDATE()),  -- Albert
(@PayrollID1, 6, 15, 0, 0, 25000, 6000, '[{"aid":"2","amount":"2000"},{"aid":"3","amount":"3000"}]', 300, '[{"did":"1","amount":"100"},{"did":"2","amount":"200"}]', 29700.00, GETDATE()),  -- John
(@PayrollID1, 7, 10, 5, 300, 20000, 7000, '[{"aid":"1","amount":"1000"},{"aid":"4","amount":"4000"}]', 500, '[{"did":"2","amount":"200"},{"did":"3","amount":"300"}]', 23363.64, GETDATE()),  -- LeBrand
(@PayrollID1, 8, 12, 3, 100, 15000, 6000, '[{"aid":"2","amount":"2000"},{"aid":"1","amount":"1000"}]', 400, '[{"did":"1","amount":"100"},{"did":"3","amount":"300"}]', 17315.91, GETDATE()),  -- Jasmine
(@PayrollID1, 9, 11, 4, 700, 10000, 5000, '[{"aid":"1","amount":"1000"}]', 500, '[{"did":"2","amount":"200"},{"did":"3","amount":"300"}]', 9174.24, GETDATE()),  -- Maricar

-- SEMI MONTHLY (FEB 16 TO 28)
(@PayrollID2, 1, 9, 4, 800, 50000, 3000, '[{"aid":"1","amount":"1000"},{"aid":"2","amount":"2000"}]', 300, '[{"did":"1","amount":"100"},{"did":"2","amount":"200"}]', 45124.24, GETDATE()),  -- Isaac
(@PayrollID2, 2, 11, 2, 1000, 45000, 5000, '[{"aid":"2","amount":"2000"},{"aid":"3","amount":"3000"}]', 500, '[{"did":"3","amount":"300"},{"did":"2","amount":"200"}]', 40977.27, GETDATE()),  -- Mang
(@PayrollID2, 3, 10, 3, 0, 40000, 5000, '[{"aid":"1","amount":"1000"},{"aid":"4","amount":"4000"}]', 400, '[{"did":"1","amount":"100"},{"did":"3","amount":"300"}]', 44600.00, GETDATE()),  -- Jennifer
(@PayrollID2, 4, 9, 5, 500, 35000, 4000, '[{"aid":"1","amount":"1000"},{"aid":"3","amount":"3000"}]', 600, '[{"did":"1","amount":"100"},{"did":"2","amount":"200"},{"did":"3","amount":"300"}]', 35085.61, GETDATE()),  -- Antonio
(@PayrollID2, 5, 11, 2, 400, 30000, 6000, '[{"aid":"2","amount":"2000"},{"aid":"4","amount":"4000"}]', 300, '[{"did":"1","amount":"100"},{"did":"2","amount":"200"}]', 33427.27, GETDATE()),  -- Albert
(@PayrollID2, 6, 12, 1, 0, 25000, 5000, '[{"aid":"2","amount":"2000"},{"aid":"3","amount":"3000"},{"aid":"1","amount":"1000"}]', 500, '[{"did":"300","amount":"300"},{"did":"2","amount":"200"}]', 30500.00, GETDATE()),  -- John
(@PayrollID2, 7, 9, 4, 300, 20000, 6000, '[{"aid":"2","amount":"2000"},{"aid":"4","amount":"4000"}]', 300, '[{"did":"1","amount":"100"},{"did":"2","amount":"200"}]', 24563.64, GETDATE()),  -- LeBrand
(@PayrollID2, 8, 11, 2, 100, 15000, 4000, '[{"aid":"3","amount":"3000"},{"aid":"1","amount":"1000"}]', 500, '[{"did":"300","amount":"300"},{"did":"2","amount":"200"}]', 18215.91, GETDATE()),  -- Jasmine
(@PayrollID2, 9, 9, 4, 700, 10000, 5000, '[{"aid":"1","amount":"1000"},{"aid":"4","amount":"4000"}]', 300, '[{"did":"1","amount":"100"},{"did":"2","amount":"200"}]', 13374.24, GETDATE());  -- Maricar

-- Insert data into attendance
INSERT INTO [attendance] ([employee_id], [log_type], [datetime_log], [date_updated]) VALUES
(1, 1, '2025-02-01 08:00:00', GETDATE()),
(1, 2, '2025-02-01 12:00:00', GETDATE()),
(1, 3, '2025-02-01 13:00:00', GETDATE()),
(1, 4, '2025-02-01 17:00:00', GETDATE()),
(2, 1, '2025-02-01 09:00:00', GETDATE()),
(2, 2, '2025-02-01 12:30:00', GETDATE()),
(2, 3, '2025-02-01 13:30:00', GETDATE()),
(2, 4, '2025-02-01 17:30:00', GETDATE()),
(3, 1, '2025-02-01 08:30:00', GETDATE()),
(3, 2, '2025-02-01 11:30:00', GETDATE()),
(3, 3, '2025-02-01 14:00:00', GETDATE()),
(3, 4, '2025-02-01 17:00:00', GETDATE()),
(4, 1, '2025-02-01 07:50:00', GETDATE()),
(4, 2, '2025-02-01 12:00:00', GETDATE()),
(4, 3, '2025-02-01 13:05:00', GETDATE()),
(4, 4, '2025-02-01 17:00:00', GETDATE()),
(5, 1, '2025-02-01 08:05:00', GETDATE()),
(5, 2, '2025-02-01 12:15:00', GETDATE()),
(5, 3, '2025-02-01 13:10:00', GETDATE()),
(5, 4, '2025-02-01 17:00:00', GETDATE()),
(6, 1, '2025-02-01 08:00:00', GETDATE()),
(6, 2, '2025-02-01 11:55:00', GETDATE()),
(6, 3, '2025-02-01 13:00:00', GETDATE()),
(6, 4, '2025-02-01 17:00:00', GETDATE()),
(7, 1, '2025-02-01 08:10:00', GETDATE()),
(7, 2, '2025-02-01 12:00:00', GETDATE()),
(7, 3, '2025-02-01 13:15:00', GETDATE()),
(7, 4, '2025-02-01 17:00:00', GETDATE()),
(8, 1, '2025-02-01 07:55:00', GETDATE()),
(8, 2, '2025-02-01 12:05:00', GETDATE()),
(8, 3, '2025-02-01 13:00:00', GETDATE()),
(8, 4, '2025-02-01 17:00:00', GETDATE()),
(9, 1, '2025-02-01 08:45:00', GETDATE()),
(9, 2, '2025-02-01 11:45:00', GETDATE()),
(9, 3, '2025-02-01 13:45:00', GETDATE()),
(9, 4, '2025-02-01 17:00:00', GETDATE()),

-- February 2, 2025
(1, 1, '2025-02-02 08:00:00', GETDATE()),
(1, 2, '2025-02-02 12:00:00', GETDATE()),
(1, 3, '2025-02-02 13:00:00', GETDATE()),
(1, 4, '2025-02-02 17:00:00', GETDATE()),
(2, 1, '2025-02-02 09:00:00', GETDATE()),
(2, 2, '2025-02-02 12:30:00', GETDATE()),
(2, 3, '2025-02-02 13:30:00', GETDATE()),
(2, 4, '2025-02-02 17:30:00', GETDATE()),
(3, 1, '2025-02-02 08:30:00', GETDATE()),
(3, 2, '2025-02-02 11:30:00', GETDATE()),
(3, 3, '2025-02-02 14:00:00', GETDATE()),
(3, 4, '2025-02-02 17:00:00', GETDATE()),
(4, 1, '2025-02-02 07:50:00', GETDATE()),
(4, 2, '2025-02-02 12:00:00', GETDATE()),
(4, 3, '2025-02-02 13:05:00', GETDATE()),
(4, 4, '2025-02-02 17:00:00', GETDATE()),
(5, 1, '2025-02-02 08:05:00', GETDATE()),
(5, 2, '2025-02-02 12:15:00', GETDATE()),
(5, 3, '2025-02-02 13:10:00', GETDATE()),
(5, 4, '2025-02-02 17:00:00', GETDATE()),
(6, 1, '2025-02-02 08:00:00', GETDATE()),
(6, 2, '2025-02-02 11:55:00', GETDATE()),
(6, 3, '2025-02-02 13:00:00', GETDATE()),
(6, 4, '2025-02-02 17:00:00', GETDATE()),
(7, 1, '2025-02-02 08:10:00', GETDATE()),
(7, 2, '2025-02-02 12:00:00', GETDATE()),
(7, 3, '2025-02-02 13:15:00', GETDATE()),
(7, 4, '2025-02-02 17:00:00', GETDATE()),
(8, 1, '2025-02-02 07:55:00', GETDATE()),
(8, 2, '2025-02-02 12:05:00', GETDATE()),
(8, 3, '2025-02-02 13:00:00', GETDATE()),
(8, 4, '2025-02-02 17:00:00', GETDATE()),
(9, 1, '2025-02-02 08:45:00', GETDATE()),
(9, 2, '2025-02-02 11:45:00', GETDATE()),
(9, 3, '2025-02-02 13:45:00', GETDATE()),
(9, 4, '2025-02-02 17:00:00', GETDATE()),

-- February 3, 2025
(1, 1, '2025-02-03 08:00:00', GETDATE()),
(1, 2, '2025-02-03 12:00:00', GETDATE()),
(1, 3, '2025-02-03 13:00:00', GETDATE()),
(1, 4, '2025-02-03 17:00:00', GETDATE()),
(2, 1, '2025-02-03 09:00:00', GETDATE()),
(2, 2, '2025-02-03 12:30:00', GETDATE()),
(2, 3, '2025-02-03 13:30:00', GETDATE()),
(2, 4, '2025-02-03 17:30:00', GETDATE()),
(3, 1, '2025-02-03 08:30:00', GETDATE()),
(3, 2, '2025-02-03 11:30:00', GETDATE()),
(3, 3, '2025-02-03 14:00:00', GETDATE()),
(3, 4, '2025-02-03 17:00:00', GETDATE()),
(4, 1, '2025-02-03 07:50:00', GETDATE()),
(4, 2, '2025-02-03 12:00:00', GETDATE()),
(4, 3, '2025-02-03 13:05:00', GETDATE()),
(4, 4, '2025-02-03 17:00:00', GETDATE()),
(5, 1, '2025-02-03 08:05:00', GETDATE()),
(5, 2, '2025-02-03 12:15:00', GETDATE()),
(5, 3, '2025-02-03 13:10:00', GETDATE()),
(5, 4, '2025-02-03 17:00:00', GETDATE()),
(6, 1, '2025-02-03 08:00:00', GETDATE()),
(6, 2, '2025-02-03 11:55:00', GETDATE()),
(6, 3, '2025-02-03 13:00:00', GETDATE()),
(6, 4, '2025-02-03 17:00:00', GETDATE()),
(7, 1, '2025-02-03 08:10:00', GETDATE()),
(7, 2, '2025-02-03 12:00:00', GETDATE()),
(7, 3, '2025-02-03 13:15:00', GETDATE()),
(7, 4, '2025-02-03 17:00:00', GETDATE()),
(8, 1, '2025-02-03 07:55:00', GETDATE()),
(8, 2, '2025-02-03 12:05:00', GETDATE()),
(8, 3, '2025-02-03 13:00:00', GETDATE()),
(8, 4, '2025-02-03 17:00:00', GETDATE()),
(9, 1, '2025-02-03 08:45:00', GETDATE()),
(9, 2, '2025-02-03 11:45:00', GETDATE()),
(9, 3, '2025-02-03 13:45:00', GETDATE()),
(9, 4, '2025-02-03 17:00:00', GETDATE()),

-- February 4, 2025
(1, 1, '2025-02-04 08:00:00', GETDATE()),
(1, 2, '2025-02-04 12:00:00', GETDATE()),
(1, 3, '2025-02-04 13:00:00', GETDATE()),
(1, 4, '2025-02-04 17:00:00', GETDATE()),
(2, 1, '2025-02-04 09:00:00', GETDATE()),
(2, 2, '2025-02-04 12:30:00', GETDATE()),
(2, 3, '2025-02-04 13:30:00', GETDATE()),
(2, 4, '2025-02-04 17:30:00', GETDATE()),
(3, 1, '2025-02-04 08:30:00', GETDATE()),
(3, 2, '2025-02-04 11:30:00', GETDATE()),
(3, 3, '2025-02-04 14:00:00', GETDATE()),
(3, 4, '2025-02-04 17:00:00', GETDATE()),
(4, 1, '2025-02-04 07:50:00', GETDATE()),
(4, 2, '2025-02-04 12:00:00', GETDATE()),
(4, 3, '2025-02-04 13:05:00', GETDATE()),
(4, 4, '2025-02-04 17:00:00', GETDATE()),
(5, 1, '2025-02-04 08:05:00', GETDATE()),
(5, 2, '2025-02-04 12:15:00', GETDATE()),
(5, 3, '2025-02-04 13:10:00', GETDATE()),
(5, 4, '2025-02-04 17:00:00', GETDATE()),
(6, 1, '2025-02-04 08:00:00', GETDATE()),
(6, 2, '2025-02-04 11:55:00', GETDATE()),
(6, 3, '2025-02-04 13:00:00', GETDATE()),
(6, 4, '2025-02-04 17:00:00', GETDATE()),
(7, 1, '2025-02-04 08:10:00', GETDATE()),
(7, 2, '2025-02-04 12:00:00', GETDATE()),
(7, 3, '2025-02-04 13:15:00', GETDATE()),
(7, 4, '2025-02-04 17:00:00', GETDATE()),
(8, 1, '2025-02-04 07:55:00', GETDATE()),
(8, 2, '2025-02-04 12:05:00', GETDATE()),
(8, 3, '2025-02-04 13:00:00', GETDATE()),
(8, 4, '2025-02-04 17:00:00', GETDATE()),
(9, 1, '2025-02-04 08:45:00', GETDATE()),
(9, 2, '2025-02-04 11:45:00', GETDATE()),
(9, 3, '2025-02-04 13:45:00', GETDATE()),
(9, 4, '2025-02-04 17:00:00', GETDATE()),

-- February 5, 2025
(1, 1, '2025-02-05 08:00:00', GETDATE()),
(1, 2, '2025-02-05 12:00:00', GETDATE()),
(1, 3, '2025-02-05 13:00:00', GETDATE()),
(1, 4, '2025-02-05 17:00:00', GETDATE()),
(2, 1, '2025-02-05 09:00:00', GETDATE()),
(2, 2, '2025-02-05 12:30:00', GETDATE()),
(2, 3, '2025-02-05 13:30:00', GETDATE()),
(2, 4, '2025-02-05 17:30:00', GETDATE()),
(3, 1, '2025-02-05 08:30:00', GETDATE()),
(3, 2, '2025-02-05 11:30:00', GETDATE()),
(3, 3, '2025-02-05 14:00:00', GETDATE()),
(3, 4, '2025-02-05 17:00:00', GETDATE()),
(4, 1, '2025-02-05 07:50:00', GETDATE()),
(4, 2, '2025-02-05 12:00:00', GETDATE()),
(4, 3, '2025-02-05 13:05:00', GETDATE()),
(4, 4, '2025-02-05 17:00:00', GETDATE()),
(5, 1, '2025-02-05 08:05:00', GETDATE()),
(5, 2, '2025-02-05 12:15:00', GETDATE()),
(5, 3, '2025-02-05 13:10:00', GETDATE()),
(5, 4, '2025-02-05 17:00:00', GETDATE()),
(6, 1, '2025-02-05 08:00:00', GETDATE()),
(6, 2, '2025-02-05 11:55:00', GETDATE()),
(6, 3, '2025-02-05 13:00:00', GETDATE()),
(6, 4, '2025-02-05 17:00:00', GETDATE()),
(7, 1, '2025-02-05 08:10:00', GETDATE()),
(7, 2, '2025-02-05 12:00:00', GETDATE()),
(7, 3, '2025-02-05 13:15:00', GETDATE()),
(7, 4, '2025-02-05 17:00:00', GETDATE()),
(8, 1, '2025-02-05 07:55:00', GETDATE()),
(8, 2, '2025-02-05 12:05:00', GETDATE()),
(8, 3, '2025-02-05 13:00:00', GETDATE()),
(8, 4, '2025-02-05 17:00:00', GETDATE()),
(9, 1, '2025-02-05 08:45:00', GETDATE()),
(9, 2, '2025-02-05 11:45:00', GETDATE()),
(9, 3, '2025-02-05 13:45:00', GETDATE()),
(9, 4, '2025-02-05 17:00:00', GETDATE()),

-- February 6, 2025
(1, 1, '2025-02-06 08:00:00', GETDATE()),
(1, 2, '2025-02-06 12:00:00', GETDATE()),
(1, 3, '2025-02-06 13:00:00', GETDATE()),
(1, 4, '2025-02-06 17:00:00', GETDATE()),
(2, 1, '2025-02-06 09:00:00', GETDATE()),
(2, 2, '2025-02-06 12:30:00', GETDATE()),
(2, 3, '2025-02-06 13:30:00', GETDATE()),
(2, 4, '2025-02-06 17:30:00', GETDATE()),
(3, 1, '2025-02-06 08:30:00', GETDATE()),
(3, 2, '2025-02-06 11:30:00', GETDATE()),
(3, 3, '2025-02-06 14:00:00', GETDATE()),
(3, 4, '2025-02-06 17:00:00', GETDATE()),
(4, 1, '2025-02-06 07:50:00', GETDATE()),
(4, 2, '2025-02-06 12:00:00', GETDATE()),
(4, 3, '2025-02-06 13:05:00', GETDATE()),
(4, 4, '2025-02-06 17:00:00', GETDATE()),
(5, 1, '2025-02-06 08:05:00', GETDATE()),
(5, 2, '2025-02-06 12:15:00', GETDATE()),
(5, 3, '2025-02-06 13:10:00', GETDATE()),
(5, 4, '2025-02-06 17:00:00', GETDATE()),
(6, 1, '2025-02-06 08:00:00', GETDATE()),
(6, 2, '2025-02-06 11:55:00', GETDATE()),
(6, 3, '2025-02-06 13:00:00', GETDATE()),
(6, 4, '2025-02-06 17:00:00', GETDATE()),
(7, 1, '2025-02-06 08:10:00', GETDATE()),
(7, 2, '2025-02-06 12:00:00', GETDATE()),
(7, 3, '2025-02-06 13:15:00', GETDATE()),
(7, 4, '2025-02-06 17:00:00', GETDATE()),
(8, 1, '2025-02-06 07:55:00', GETDATE()),
(8, 2, '2025-02-06 12:05:00', GETDATE()),
(8, 3, '2025-02-06 13:00:00', GETDATE()),
(8, 4, '2025-02-06 17:00:00', GETDATE()),
(9, 1, '2025-02-06 08:45:00', GETDATE()),
(9, 2, '2025-02-06 11:45:00', GETDATE()),
(9, 3, '2025-02-06 13:45:00', GETDATE()),
(9, 4, '2025-02-06 17:00:00', GETDATE()),

-- February 7, 2025
(1, 1, '2025-02-07 08:00:00', GETDATE()),
(1, 2, '2025-02-07 12:00:00', GETDATE()),
(1, 3, '2025-02-07 13:00:00', GETDATE()),
(1, 4, '2025-02-07 17:00:00', GETDATE()),
(2, 1, '2025-02-07 09:00:00', GETDATE()),
(2, 2, '2025-02-07 12:30:00', GETDATE()),
(2, 3, '2025-02-07 13:30:00', GETDATE()),
(2, 4, '2025-02-07 17:30:00', GETDATE()),
(3, 1, '2025-02-07 08:30:00', GETDATE()),
(3, 2, '2025-02-07 11:30:00', GETDATE()),
(3, 3, '2025-02-07 14:00:00', GETDATE()),
(3, 4, '2025-02-07 17:00:00', GETDATE()),
(4, 1, '2025-02-07 07:50:00', GETDATE()),
(4, 2, '2025-02-07 12:00:00', GETDATE()),
(4, 3, '2025-02-07 13:05:00', GETDATE()),
(4, 4, '2025-02-07 17:00:00', GETDATE()),
(5, 1, '2025-02-07 08:05:00', GETDATE()),
(5, 2, '2025-02-07 12:15:00', GETDATE()),
(5, 3, '2025-02-07 13:10:00', GETDATE()),
(5, 4, '2025-02-07 17:00:00', GETDATE()),
(6, 1, '2025-02-07 08:00:00', GETDATE()),
(6, 2, '2025-02-07 11:55:00', GETDATE()),
(6, 3, '2025-02-07 13:00:00', GETDATE()),
(6, 4, '2025-02-07 17:00:00', GETDATE()),
(7, 1, '2025-02-07 08:10:00', GETDATE()),
(7, 2, '2025-02-07 12:00:00', GETDATE()),
(7, 3, '2025-02-07 13:15:00', GETDATE()),
(7, 4, '2025-02-07 17:00:00', GETDATE()),
(8, 1, '2025-02-07 07:55:00', GETDATE()),
(8, 2, '2025-02-07 12:05:00', GETDATE()),
(8, 3, '2025-02-07 13:00:00', GETDATE()),
(8, 4, '2025-02-07 17:00:00', GETDATE()),
(9, 1, '2025-02-07 08:45:00', GETDATE()),
(9, 2, '2025-02-07 11:45:00', GETDATE()),
(9, 3, '2025-02-07 13:45:00', GETDATE()),
(9, 4, '2025-02-07 17:00:00', GETDATE()),

-- February 8, 2025
(1, 1, '2025-02-08 08:00:00', GETDATE()),
(1, 2, '2025-02-08 12:00:00', GETDATE()),
(1, 3, '2025-02-08 13:00:00', GETDATE()),
(1, 4, '2025-02-08 17:00:00', GETDATE()),
(2, 1, '2025-02-08 09:00:00', GETDATE()),
(2, 2, '2025-02-08 12:30:00', GETDATE()),
(2, 3, '2025-02-08 13:30:00', GETDATE()),
(2, 4, '2025-02-08 17:30:00', GETDATE()),
(3, 1, '2025-02-08 08:30:00', GETDATE()),
(3, 2, '2025-02-08 11:30:00', GETDATE()),
(3, 3, '2025-02-08 14:00:00', GETDATE()),
(3, 4, '2025-02-08 17:00:00', GETDATE()),
(4, 1, '2025-02-08 07:50:00', GETDATE()),
(4, 2, '2025-02-08 12:00:00', GETDATE()),
(4, 3, '2025-02-08 13:05:00', GETDATE()),
(4, 4, '2025-02-08 17:00:00', GETDATE()),
(5, 1, '2025-02-08 08:05:00', GETDATE()),
(5, 2, '2025-02-08 12:15:00', GETDATE()),
(5, 3, '2025-02-08 13:10:00', GETDATE()),
(5, 4, '2025-02-08 17:00:00', GETDATE()),
(6, 1, '2025-02-08 08:00:00', GETDATE()),
(6, 2, '2025-02-08 11:55:00', GETDATE()),
(6, 3, '2025-02-08 13:00:00', GETDATE()),
(6, 4, '2025-02-08 17:00:00', GETDATE()),
(7, 1, '2025-02-08 08:10:00', GETDATE()),
(7, 2, '2025-02-08 12:00:00', GETDATE()),
(7, 3, '2025-02-08 13:15:00', GETDATE()),
(7, 4, '2025-02-08 17:00:00', GETDATE()),
(8, 1, '2025-02-08 07:55:00', GETDATE()),
(8, 2, '2025-02-08 12:05:00', GETDATE()),
(8, 3, '2025-02-08 13:00:00', GETDATE()),
(8, 4, '2025-02-08 17:00:00', GETDATE()),
(9, 1, '2025-02-08 08:45:00', GETDATE()),
(9, 2, '2025-02-08 11:45:00', GETDATE()),
(9, 3, '2025-02-08 13:45:00', GETDATE()),
(9, 4, '2025-02-08 17:00:00', GETDATE()),

-- February 9, 2025
(1, 1, '2025-02-09 08:00:00', GETDATE()),
(1, 2, '2025-02-09 12:00:00', GETDATE()),
(1, 3, '2025-02-09 13:00:00', GETDATE()),
(1, 4, '2025-02-09 17:00:00', GETDATE()),
(2, 1, '2025-02-09 09:00:00', GETDATE()),
(2, 2, '2025-02-09 12:30:00', GETDATE()),
(2, 3, '2025-02-09 13:30:00', GETDATE()),
(2, 4, '2025-02-09 17:30:00', GETDATE()),
(3, 1, '2025-02-09 08:30:00', GETDATE()),
(3, 2, '2025-02-09 11:30:00', GETDATE()),
(3, 3, '2025-02-09 14:00:00', GETDATE()),
(3, 4, '2025-02-09 17:00:00', GETDATE()),
(4, 1, '2025-02-09 07:50:00', GETDATE()),
(4, 2, '2025-02-09 12:00:00', GETDATE()),
(4, 3, '2025-02-09 13:05:00', GETDATE()),
(4, 4, '2025-02-09 17:00:00', GETDATE()),
(5, 1, '2025-02-09 08:05:00', GETDATE()),
(5, 2, '2025-02-09 12:15:00', GETDATE()),
(5, 3, '2025-02-09 13:10:00', GETDATE()),
(5, 4, '2025-02-09 17:00:00', GETDATE()),
(6, 1, '2025-02-09 08:00:00', GETDATE()),
(6, 2, '2025-02-09 11:55:00', GETDATE()),
(6, 3, '2025-02-09 13:00:00', GETDATE()),
(6, 4, '2025-02-09 17:00:00', GETDATE()),
(7, 1, '2025-02-09 08:10:00', GETDATE()),
(7, 2, '2025-02-09 12:00:00', GETDATE()),
(7, 3, '2025-02-09 13:15:00', GETDATE()),
(7, 4, '2025-02-09 17:00:00', GETDATE()),
(8, 1, '2025-02-09 07:55:00', GETDATE()),
(8, 2, '2025-02-09 12:05:00', GETDATE()),
(8, 3, '2025-02-09 13:00:00', GETDATE()),
(8, 4, '2025-02-09 17:00:00', GETDATE()),
(9, 1, '2025-02-09 08:45:00', GETDATE()),
(9, 2, '2025-02-09 11:45:00', GETDATE()),
(9, 3, '2025-02-09 13:45:00', GETDATE()),
(9, 4, '2025-02-09 17:00:00', GETDATE()),

-- February 10, 2025
(1, 1, '2025-02-10 08:00:00', GETDATE()),
(1, 2, '2025-02-10 12:00:00', GETDATE()),
(1, 3, '2025-02-10 13:00:00', GETDATE()),
(1, 4, '2025-02-10 17:00:00', GETDATE()),
(2, 1, '2025-02-10 09:00:00', GETDATE()),
(2, 2, '2025-02-10 12:30:00', GETDATE()),
(2, 3, '2025-02-10 13:30:00', GETDATE()),
(2, 4, '2025-02-10 17:30:00', GETDATE()),
(3, 1, '2025-02-10 08:30:00', GETDATE()),
(3, 2, '2025-02-10 11:30:00', GETDATE()),
(3, 3, '2025-02-10 14:00:00', GETDATE()),
(3, 4, '2025-02-10 17:00:00', GETDATE()),
(4, 1, '2025-02-10 07:50:00', GETDATE()),
(4, 2, '2025-02-10 12:00:00', GETDATE()),
(4, 3, '2025-02-10 13:05:00', GETDATE()),
(4, 4, '2025-02-10 17:00:00', GETDATE()),
(5, 1, '2025-02-10 08:05:00', GETDATE()),
(5, 2, '2025-02-10 12:15:00', GETDATE()),
(5, 3, '2025-02-10 13:10:00', GETDATE()),
(5, 4, '2025-02-10 17:00:00', GETDATE()),
(6, 1, '2025-02-10 08:00:00', GETDATE()),
(6, 2, '2025-02-10 11:55:00', GETDATE()),
(6, 3, '2025-02-10 13:00:00', GETDATE()),
(6, 4, '2025-02-10 17:00:00', GETDATE()),
(7, 1, '2025-02-10 08:10:00', GETDATE()),
(7, 2, '2025-02-10 12:00:00', GETDATE()),
(7, 3, '2025-02-10 13:15:00', GETDATE()),
(7, 4, '2025-02-10 17:00:00', GETDATE()),
(8, 1, '2025-02-10 07:55:00', GETDATE()),
(8, 2, '2025-02-10 12:05:00', GETDATE()),
(8, 3, '2025-02-10 13:00:00', GETDATE()),
(8, 4, '2025-02-10 17:00:00', GETDATE()),
(9, 1, '2025-02-10 08:45:00', GETDATE()),
(9, 2, '2025-02-10 11:45:00', GETDATE()),
(9, 3, '2025-02-10 13:45:00', GETDATE()),
(9, 4, '2025-02-10 17:00:00', GETDATE()),

-- February 11, 2025
(1, 1, '2025-02-11 08:00:00', GETDATE()),
(1, 2, '2025-02-11 12:00:00', GETDATE()),
(1, 3, '2025-02-11 13:00:00', GETDATE()),
(1, 4, '2025-02-11 17:00:00', GETDATE()),
(2, 1, '2025-02-11 09:00:00', GETDATE()),
(2, 2, '2025-02-11 12:30:00', GETDATE()),
(2, 3, '2025-02-11 13:30:00', GETDATE()),
(2, 4, '2025-02-11 17:30:00', GETDATE()),
(3, 1, '2025-02-11 08:30:00', GETDATE()),
(3, 2, '2025-02-11 11:30:00', GETDATE()),
(3, 3, '2025-02-11 14:00:00', GETDATE()),
(3, 4, '2025-02-11 17:00:00', GETDATE()),
(4, 1, '2025-02-11 07:50:00', GETDATE()),
(4, 2, '2025-02-11 12:00:00', GETDATE()),
(4, 3, '2025-02-11 13:05:00', GETDATE()),
(4, 4, '2025-02-11 17:00:00', GETDATE()),
(5, 1, '2025-02-11 08:05:00', GETDATE()),
(5, 2, '2025-02-11 12:15:00', GETDATE()),
(5, 3, '2025-02-11 13:10:00', GETDATE()),
(5, 4, '2025-02-11 17:00:00', GETDATE()),
(6, 1, '2025-02-11 08:00:00', GETDATE()),
(6, 2, '2025-02-11 11:55:00', GETDATE()),
(6, 3, '2025-02-11 13:00:00', GETDATE()),
(6, 4, '2025-02-11 17:00:00', GETDATE()),
(7, 1, '2025-02-11 08:10:00', GETDATE()),
(7, 2, '2025-02-11 12:00:00', GETDATE()),
(7, 3, '2025-02-11 13:15:00', GETDATE()),
(7, 4, '2025-02-11 17:00:00', GETDATE()),
(8, 1, '2025-02-11 07:55:00', GETDATE()),
(8, 2, '2025-02-11 12:05:00', GETDATE()),
(8, 3, '2025-02-11 13:00:00', GETDATE()),
(8, 4, '2025-02-11 17:00:00', GETDATE()),
(9, 1, '2025-02-11 08:45:00', GETDATE()),
(9, 2, '2025-02-11 11:45:00', GETDATE()),
(9, 3, '2025-02-11 13:45:00', GETDATE()),
(9, 4, '2025-02-11 17:00:00', GETDATE()),

-- February 24, 2025
(1, 1, '2025-02-24 08:00:00', GETDATE()),
(1, 2, '2025-02-24 12:00:00', GETDATE()),
(1, 3, '2025-02-24 13:00:00', GETDATE()),
(1, 4, '2025-02-24 17:00:00', GETDATE()),
(2, 1, '2025-02-24 09:00:00', GETDATE()),
(2, 2, '2025-02-24 12:30:00', GETDATE()),
(2, 3, '2025-02-24 13:30:00', GETDATE()),
(2, 4, '2025-02-24 17:30:00', GETDATE()),
(3, 1, '2025-02-24 08:30:00', GETDATE()),
(3, 2, '2025-02-24 11:30:00', GETDATE()),
(3, 3, '2025-02-24 14:00:00', GETDATE()),
(3, 4, '2025-02-24 17:00:00', GETDATE()),
(4, 1, '2025-02-24 07:50:00', GETDATE()),
(4, 2, '2025-02-24 12:00:00', GETDATE()),
(4, 3, '2025-02-24 13:05:00', GETDATE()),
(4, 4, '2025-02-24 17:00:00', GETDATE()),
(5, 1, '2025-02-24 08:05:00', GETDATE()),
(5, 2, '2025-02-24 12:15:00', GETDATE()),
(5, 3, '2025-02-24 13:10:00', GETDATE()),
(5, 4, '2025-02-24 17:00:00', GETDATE()),
(6, 1, '2025-02-24 08:00:00', GETDATE()),
(6, 2, '2025-02-24 11:55:00', GETDATE()),
(6, 3, '2025-02-24 13:00:00', GETDATE()),
(6, 4, '2025-02-24 17:00:00', GETDATE()),
(7, 1, '2025-02-24 08:10:00', GETDATE()),
(7, 2, '2025-02-24 12:00:00', GETDATE()),
(7, 3, '2025-02-24 13:15:00', GETDATE()),
(7, 4, '2025-02-24 17:00:00', GETDATE()),
(8, 1, '2025-02-24 07:55:00', GETDATE()),
(8, 2, '2025-02-24 12:05:00', GETDATE()),
(8, 3, '2025-02-24 13:00:00', GETDATE()),
(8, 4, '2025-02-24 17:00:00', GETDATE()),
(9, 1, '2025-02-24 08:45:00', GETDATE()),
(9, 2, '2025-02-24 11:45:00', GETDATE()),
(9, 3, '2025-02-24 13:45:00', GETDATE()),
(9, 4, '2025-02-24 17:00:00', GETDATE()),

-- February 25, 2025
(1, 1, '2025-02-25 08:00:00', GETDATE()),
(1, 2, '2025-02-25 12:00:00', GETDATE()),
(1, 3, '2025-02-25 13:00:00', GETDATE()),
(1, 4, '2025-02-25 17:00:00', GETDATE()),
(2, 1, '2025-02-25 09:00:00', GETDATE()),
(2, 2, '2025-02-25 12:30:00', GETDATE()),
(2, 3, '2025-02-25 13:30:00', GETDATE()),
(2, 4, '2025-02-25 17:30:00', GETDATE()),
(3, 1, '2025-02-25 08:30:00', GETDATE()),
(3, 2, '2025-02-25 11:30:00', GETDATE()),
(3, 3, '2025-02-25 14:00:00', GETDATE()),
(3, 4, '2025-02-25 17:00:00', GETDATE()),
(4, 1, '2025-02-25 07:50:00', GETDATE()),
(4, 2, '2025-02-25 12:00:00', GETDATE()),
(4, 3, '2025-02-25 13:05:00', GETDATE()),
(4, 4, '2025-02-25 17:00:00', GETDATE()),
(5, 1, '2025-02-25 08:05:00', GETDATE()),
(5, 2, '2025-02-25 12:15:00', GETDATE()),
(5, 3, '2025-02-25 13:10:00', GETDATE()),
(5, 4, '2025-02-25 17:00:00', GETDATE()),
(6, 1, '2025-02-25 08:00:00', GETDATE()),
(6, 2, '2025-02-25 11:55:00', GETDATE()),
(6, 3, '2025-02-25 13:00:00', GETDATE()),
(6, 4, '2025-02-25 17:00:00', GETDATE()),
(7, 1, '2025-02-25 08:10:00', GETDATE()),
(7, 2, '2025-02-25 12:00:00', GETDATE()),
(7, 3, '2025-02-25 13:15:00', GETDATE()),
(7, 4, '2025-02-25 17:00:00', GETDATE()),
(8, 1, '2025-02-25 07:55:00', GETDATE()),
(8, 2, '2025-02-25 12:05:00', GETDATE()),
(8, 3, '2025-02-25 13:00:00', GETDATE()),
(8, 4, '2025-02-25 17:00:00', GETDATE()),
(9, 1, '2025-02-25 08:45:00', GETDATE()),
(9, 2, '2025-02-25 11:45:00', GETDATE()),
(9, 3, '2025-02-25 13:45:00', GETDATE()),
(9, 4, '2025-02-25 17:00:00', GETDATE()),

-- February 26, 2025
(1, 1, '2025-02-26 08:00:00', GETDATE()),
(1, 2, '2025-02-26 12:00:00', GETDATE()),
(1, 3, '2025-02-26 13:00:00', GETDATE()),
(1, 4, '2025-02-26 17:00:00', GETDATE()),
(2, 1, '2025-02-26 09:00:00', GETDATE()),
(2, 2, '2025-02-26 12:30:00', GETDATE()),
(2, 3, '2025-02-26 13:30:00', GETDATE()),
(2, 4, '2025-02-26 17:30:00', GETDATE()),
(3, 1, '2025-02-26 08:30:00', GETDATE()),
(3, 2, '2025-02-26 11:30:00', GETDATE()),
(3, 3, '2025-02-26 14:00:00', GETDATE()),
(3, 4, '2025-02-26 17:00:00', GETDATE()),
(4, 1, '2025-02-26 07:50:00', GETDATE()),
(4, 2, '2025-02-26 12:00:00', GETDATE()),
(4, 3, '2025-02-26 13:05:00', GETDATE()),
(4, 4, '2025-02-26 17:00:00', GETDATE()),
(5, 1, '2025-02-26 08:05:00', GETDATE()),
(5, 2, '2025-02-26 12:15:00', GETDATE()),
(5, 3, '2025-02-26 13:10:00', GETDATE()),
(5, 4, '2025-02-26 17:00:00', GETDATE()),
(6, 1, '2025-02-26 08:00:00', GETDATE()),
(6, 2, '2025-02-26 11:55:00', GETDATE()),
(6, 3, '2025-02-26 13:00:00', GETDATE()),
(6, 4, '2025-02-26 17:00:00', GETDATE()),
(7, 1, '2025-02-26 08:10:00', GETDATE()),
(7, 2, '2025-02-26 12:00:00', GETDATE()),
(7, 3, '2025-02-26 13:15:00', GETDATE()),
(7, 4, '2025-02-26 17:00:00', GETDATE()),
(8, 1, '2025-02-26 07:55:00', GETDATE()),
(8, 2, '2025-02-26 12:05:00', GETDATE()),
(8, 3, '2025-02-26 13:00:00', GETDATE()),
(8, 4, '2025-02-26 17:00:00', GETDATE()),
(9, 1, '2025-02-26 08:45:00', GETDATE()),
(9, 2, '2025-02-26 11:45:00', GETDATE()),
(9, 3, '2025-02-26 13:45:00', GETDATE()),
(9, 4, '2025-02-26 17:00:00', GETDATE()),

-- February 28, 2025
(1, 1, '2025-02-28 08:00:00', GETDATE()),
(1, 2, '2025-02-28 12:00:00', GETDATE()),
(1, 3, '2025-02-28 13:00:00', GETDATE()),
(1, 4, '2025-02-28 17:00:00', GETDATE()),
(2, 1, '2025-02-28 09:00:00', GETDATE()),
(2, 2, '2025-02-28 12:30:00', GETDATE()),
(2, 3, '2025-02-28 13:30:00', GETDATE()),
(2, 4, '2025-02-28 17:30:00', GETDATE()),
(3, 1, '2025-02-28 08:30:00', GETDATE()),
(3, 2, '2025-02-28 11:30:00', GETDATE()),
(3, 3, '2025-02-28 14:00:00', GETDATE()),
(3, 4, '2025-02-28 17:00:00', GETDATE()),
(4, 1, '2025-02-28 07:50:00', GETDATE()),
(4, 2, '2025-02-28 12:00:00', GETDATE()),
(4, 3, '2025-02-28 13:05:00', GETDATE()),
(4, 4, '2025-02-28 17:00:00', GETDATE()),
(5, 1, '2025-02-28 08:05:00', GETDATE()),
(5, 2, '2025-02-28 12:15:00', GETDATE()),
(5, 3, '2025-02-28 13:10:00', GETDATE()),
(5, 4, '2025-02-28 17:00:00', GETDATE()),
(6, 1, '2025-02-28 08:00:00', GETDATE()),
(6, 2, '2025-02-28 11:55:00', GETDATE()),
(6, 3, '2025-02-28 13:00:00', GETDATE()),
(6, 4, '2025-02-28 17:00:00', GETDATE()),
(7, 1, '2025-02-28 08:10:00', GETDATE()),
(7, 2, '2025-02-28 12:00:00', GETDATE()),
(7, 3, '2025-02-28 13:15:00', GETDATE()),
(7, 4, '2025-02-28 17:00:00', GETDATE()),
(8, 1, '2025-02-28 07:55:00', GETDATE()),
(8, 2, '2025-02-28 12:05:00', GETDATE()),
(8, 3, '2025-02-28 13:00:00', GETDATE()),
(8, 4, '2025-02-28 17:00:00', GETDATE()),
(9, 1, '2025-02-28 08:45:00', GETDATE()),
(9, 2, '2025-02-28 11:45:00', GETDATE()),
(9, 3, '2025-02-28 13:45:00', GETDATE()),
(9, 4, '2025-02-28 17:00:00', GETDATE());

-- Insert data into users
INSERT INTO [users] ([employee_id], [username], [password], [type]) VALUES
(1, 'isaac.newton', '3l@wsofmotion', 1),
(3, 'jennifer.lopez.lawrence', 'hungerg@mes', 0);

/* 
** Create VIEW tables
*/

GO
-- View Payroll_Items [UPDATED, NEW!]
CREATE VIEW PayrollItemsWithRefNo AS
SELECT
    pi.id AS payroll_item_id,  -- Explicitly name the payroll_items ID
    p.ref_no,
    pi.payroll_id,
    pi.employee_id,
    pi.present,
    pi.absent,
    pi.late,
    pi.salary,
    pi.allowance_amount,
    pi.allowances,
    pi.deduction_amount,
    pi.deductions,
    pi.net,
    pi.date_created,
    pi.isDeleted
FROM payroll_items pi
INNER JOIN payroll p ON pi.payroll_id = p.id;
GO  -- Ensure separation between statements

-- VIEW Users [UPDATED, NEW!]
CREATE VIEW EmployeeUserView AS
SELECT  
    u.id AS id, -- Include the user's ID from the users table
    e.id AS employee_id, 
    e.employee_no, 
    e.firstname, 
    e.middlename, 
    e.lastname, 
    e.suffix,
    CASE 
        WHEN e.middlename = 'N/A' AND e.suffix = 'N/A' THEN e.firstname + ' ' + e.lastname 
        WHEN e.middlename = 'N/A' THEN e.firstname + ' ' + e.lastname + ' ' + e.suffix 
        WHEN e.suffix = 'N/A' THEN e.firstname + ' ' + e.middlename + ' ' + e.lastname 
        ELSE e.firstname + ' ' + e.middlename + ' ' + e.lastname + ' ' + e.suffix 
    END AS full_name,
    u.username, 
    u.type AS user_type, 
    u.isDeleted AS user_isDeleted, 
    e.isDeleted AS employee_isDeleted
FROM dbo.employee AS e 
INNER JOIN dbo.users AS u ON e.id = u.employee_id
WHERE e.isDeleted = 0 AND u.isDeleted = 0;
GO  -- Ensure separation

-- VIEW Employee [UPDATED, NEW!]
CREATE VIEW EmployeeDetailsView AS
SELECT
    e.employee_no,
    e.firstname,
    e.middlename,
    e.lastname,
    e.suffix,
    d.name AS department_name,
    p.name AS position_name,
    e.id  -- Include employee ID for actions
FROM employee e
INNER JOIN department d ON e.department_id = d.id
INNER JOIN position p ON e.position_id = p.id
WHERE e.isDeleted = 0;  -- Filter deleted employees
GO
