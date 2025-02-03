-- UPDATED payroll.sql database as of February 3, 2025.
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
  ,[effective_date] DATE NOT NULL
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
  ,[effective_date] DATE NOT NULL
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
('EMP0001', 'Isaac', 'Reyes', 'Newton', 'Sr.', 1, 1, 30000),
('EMP0002', 'Mang', 'Mariz', 'Thomas', 'Jr.', 1, 2, 40000),
('EMP0003', 'Jennifer', 'Lopez', 'Lawrence', 'N/A', 1, 3, 50000),
('EMP0004', 'Antonio', 'Zamora', 'Luna', 'N/A', 2, 1, 35000),
('EMP0005', 'Albert', 'N/A', 'Esteem', 'N/A', 2, 2, 25000),
('EMP0006', 'John', 'Wewers', 'Cena', 'III', 2, 3, 20000),
('EMP0007', 'LeBrand', 'N/A', 'Jamison', 'Sr.', 3, 1, 15000),
('EMP0008', 'Jasmine', 'Omania', 'Henry', 'N/A', 3, 2, 32000),
('EMP0009', 'Maricar', 'Albarez', 'Delos Santos', 'N/A', 3, 3, 24000);

-- Insert data into employee_allowances
INSERT INTO [employee_allowances] ([employee_id], [allowance_id], [type], [amount], [effective_date], [date_created]) VALUES
(1, 1, 1, 1000, DATEADD(day, 3, GETDATE()), GETDATE()),  
(1, 2, 2, 500, DATEADD(day, 7, GETDATE()), GETDATE()), 
(1, 3, 3, 2000, DATEADD(day, 1, GETDATE()), GETDATE()), 
(2, 2, 1, 750, DATEADD(day, 2, GETDATE()), GETDATE()),   
(2, 3, 2, 1500, DATEADD(day, 5, GETDATE()), GETDATE()), 
(2, 4, 3, 3000, DATEADD(day, 9, GETDATE()), GETDATE()), 
(3, 1, 1, 1200, DATEADD(day, 1, GETDATE()), GETDATE()),  
(3, 4, 2, 2500, DATEADD(day, 4, GETDATE()), GETDATE()), 
(3, 2, 3, 600, DATEADD(day, 8, GETDATE()), GETDATE()),  
(4, 3, 1, 800, DATEADD(day, 3, GETDATE()), GETDATE()),   
(4, 1, 2, 400, DATEADD(day, 6, GETDATE()), GETDATE()),   
(4, 4, 3, 1800, DATEADD(day, 10, GETDATE()), GETDATE()), 
(5, 4, 1, 1500, DATEADD(day, 2, GETDATE()), GETDATE()),  
(5, 2, 2, 300, DATEADD(day, 5, GETDATE()), GETDATE()),  
(5, 3, 3, 1200, DATEADD(day, 8, GETDATE()), GETDATE()),  
(6, 1, 1, 900, DATEADD(day, 1, GETDATE()), GETDATE()),   
(6, 3, 2, 700, DATEADD(day, 4, GETDATE()), GETDATE()),   
(6, 2, 3, 500, DATEADD(day, 7, GETDATE()), GETDATE()),   
(7, 2, 1, 600, DATEADD(day, 3, GETDATE()), GETDATE()),   
(7, 4, 2, 1200, DATEADD(day, 6, GETDATE()), GETDATE()),  
(7, 1, 3, 800, DATEADD(day, 9, GETDATE()), GETDATE()),   
(8, 3, 1, 1100, DATEADD(day, 2, GETDATE()), GETDATE()),  
(8, 1, 2, 550, DATEADD(day, 5, GETDATE()), GETDATE()),   
(8, 2, 3, 700, DATEADD(day, 8, GETDATE()), GETDATE()),   
(9, 4, 1, 1800, DATEADD(day, 6, GETDATE()), GETDATE()),
(9, 1, 2, 900, DATEADD(day, 3, GETDATE()), GETDATE()), 
(9, 3, 3, 1200, DATEADD(day, 10, GETDATE()), GETDATE()); 


-- Insert data into employee_deductions
INSERT INTO [employee_deductions] ([employee_id], [deduction_id], [type], [amount], [effective_date], [date_created]) VALUES
(1, 1, 1, 500, DATEADD(day, 4, GETDATE()), GETDATE()),  
(1, 2, 2, 200, DATEADD(day, 7, GETDATE()), GETDATE()),  
(1, 3, 3, 100, DATEADD(day, 1, GETDATE()), GETDATE()),  
(2, 2, 1, 300, DATEADD(day, 2, GETDATE()), GETDATE()),  
(2, 3, 2, 150, DATEADD(day, 5, GETDATE()), GETDATE()),  
(2, 1, 3, 75, DATEADD(day, 9, GETDATE()), GETDATE()),   
(3, 3, 1, 250, DATEADD(day, 1, GETDATE()), GETDATE()),  
(3, 1, 2, 125, DATEADD(day, 4, GETDATE()), GETDATE()),  
(3, 2, 3, 60, DATEADD(day, 8, GETDATE()), GETDATE()),   
(4, 1, 1, 400, DATEADD(day, 3, GETDATE()), GETDATE()),  
(4, 3, 2, 100, DATEADD(day, 6, GETDATE()), GETDATE()), 
(4, 2, 3, 180, DATEADD(day, 10, GETDATE()), GETDATE()), 
(5, 2, 1, 250, DATEADD(day, 2, GETDATE()), GETDATE()),  
(5, 1, 2, 75, DATEADD(day, 5, GETDATE()), GETDATE()),   
(5, 3, 3, 120, DATEADD(day, 8, GETDATE()), GETDATE()),  
(6, 3, 1, 200, DATEADD(day, 1, GETDATE()), GETDATE()),  
(6, 2, 2, 100, DATEADD(day, 4, GETDATE()), GETDATE()),
(6, 1, 3, 50, DATEADD(day, 7, GETDATE()), GETDATE()),   
(7, 1, 1, 300, DATEADD(day, 3, GETDATE()), GETDATE()),  
(7, 2, 2, 150, DATEADD(day, 6, GETDATE()), GETDATE()), 
(7, 3, 3, 80, DATEADD(day, 9, GETDATE()), GETDATE()),   
(8, 2, 1, 350, DATEADD(day, 2, GETDATE()), GETDATE()),  
(8, 3, 2, 175, DATEADD(day, 5, GETDATE()), GETDATE()),  
(8, 1, 3, 90, DATEADD(day, 8, GETDATE()), GETDATE()),   
(9, 1, 1, 350, DATEADD(day, 6, GETDATE()), GETDATE()),
(9, 2, 2, 175, DATEADD(day, 3, GETDATE()), GETDATE()),   
(9, 3, 3, 80, DATEADD(day, 10, GETDATE()), GETDATE());


-- Insert data into payroll
INSERT INTO [payroll] ([ref_no], [date_from], [date_to], [type], [status], [date_created]) VALUES
('PAY-20250201100001', '2025-02-01', '2025-02-15', 2, 0, GETDATE()),  
('PAY-20250201100002', '2025-02-16', '2025-02-28', 2, 0, GETDATE()),  
('PAY-20250201100003', '2025-02-01', '2025-02-28', 1, 0, GETDATE());  

-- insert data into payroll_items

DECLARE @PayrollID1 INT;
DECLARE @PayrollID2 INT;
DECLARE @PayrollID3 INT;

SELECT @PayrollID1 = id FROM payroll WHERE ref_no = 'PAY-20250201100001';
SELECT @PayrollID2 = id FROM payroll WHERE ref_no = 'PAY-20250201100002';
SELECT @PayrollID3 = id FROM payroll WHERE ref_no = 'PAY-20250201100003';


INSERT INTO [payroll_items] ([payroll_id], [employee_id], [present], [absent], [late], [salary], [allowance_amount], [allowances], [deduction_amount], [deductions], [net], [date_created]) VALUES
(@PayrollID1, 1, 10, 1, 30, 15000, 750, '[{"aid":"1","amount":"500"},{"aid":"2","amount":"250"}]', 250, '[{"did":"1","amount":"125"},{"did":"2","amount":"125"}]', 15500, GETDATE()),  -- Isaac
(@PayrollID1, 2, 12, 0, 0, 20000, 1000, '[{"aid":"2","amount":"500"},{"aid":"3","amount":"500"}]', 500, '[{"did":"2","amount":"250"},{"did":"3","amount":"250"}]', 20500, GETDATE()),  -- Mang
(@PayrollID1, 3, 15, 0, 60, 25000, 1250, '[{"aid":"3","amount":"750"},{"aid":"4","amount":"500"}]', 750, '[{"did":"3","amount":"375"},{"did":"1","amount":"375"}]', 25500, GETDATE()),  -- Jennifer
(@PayrollID1, 4, 10, 1, 15, 17500, 875, '[{"aid":"3","amount":"525"},{"aid":"1","amount":"350"}]', 375, '[{"did":"3","amount":"187.5"},{"did":"1","amount":"187.5"}]', 17875, GETDATE()),  -- Antonio
(@PayrollID1, 5, 12, 0, 45, 12500, 625, '[{"aid":"4","amount":"375"},{"aid":"2","amount":"250"}]', 250, '[{"did":"2","amount":"125"},{"did":"1","amount":"125"}]', 12875, GETDATE()),  -- Albert
(@PayrollID1, 6, 15, 0, 0, 10000, 500, '[{"aid":"1","amount":"300"},{"aid":"3","amount":"200"}]', 200, '[{"did":"3","amount":"100"},{"did":"1","amount":"100"}]', 10300, GETDATE()),  -- John
(@PayrollID1, 7, 10, 1, 30, 7500, 375, '[{"aid":"2","amount":"225"},{"aid":"4","amount":"150"}]', 150, '[{"did":"2","amount":"75"},{"did":"3","amount":"75"}]', 7725, GETDATE()),  -- LeBrand
(@PayrollID1, 8, 12, 0, 0, 16000, 800, '[{"aid":"3","amount":"480"},{"aid":"1","amount":"320"}]', 320, '[{"did":"3","amount":"160"},{"did":"1","amount":"160"}]', 16480, GETDATE()),  -- Jasmine
(@PayrollID1, 9, 15, 0, 60, 12000, 600, '[{"aid":"4","amount":"360"},{"aid":"1","amount":"240"}]', 240, '[{"did":"3","amount":"120"},{"did":"1","amount":"120"}]', 12360, GETDATE()),  -- Maricar

(@PayrollID2, 1, 10, 0, 15, 15000, 750, '[{"aid":"1","amount":"500"},{"aid":"2","amount":"250"}]', 250, '[{"did":"1","amount":"125"},{"did":"2","amount":"125"}]', 15500, GETDATE()),  -- Isaac
(@PayrollID2, 2, 10, 0, 0, 20000, 1000, '[{"aid":"2","amount":"500"},{"aid":"3","amount":"500"}]', 500, '[{"did":"2","amount":"250"},{"did":"3","amount":"250"}]', 20500, GETDATE()),  -- Mang
(@PayrollID2, 3, 10, 0, 30, 25000, 1250, '[{"aid":"3","amount":"750"},{"aid":"4","amount":"500"}]', 750, '[{"did":"3","amount":"375"},{"did":"1","amount":"375"}]', 25500, GETDATE()),  -- Jennifer
(@PayrollID2, 4, 10, 0, 0, 17500, 875, '[{"aid":"3","amount":"525"},{"aid":"1","amount":"350"}]', 375, '[{"did":"3","amount":"187.5"},{"did":"1","amount":"187.5"}]', 17875, GETDATE()),  -- Antonio
(@PayrollID2, 5, 10, 0, 60, 12500, 625, '[{"aid":"4","amount":"375"},{"aid":"2","amount":"250"}]', 250, '[{"did":"2","amount":"125"},{"did":"1","amount":"125"}]', 12875, GETDATE()),  -- Albert
(@PayrollID2, 6, 10, 0, 0, 10000, 500, '[{"aid":"1","amount":"300"},{"aid":"3","amount":"200"}]', 200, '[{"did":"3","amount":"100"},{"did":"1","amount":"100"}]', 10300, GETDATE()),  -- John
(@PayrollID2, 7, 10, 0, 15, 7500, 375, '[{"aid":"2","amount":"225"},{"aid":"4","amount":"150"}]', 150, '[{"did":"2","amount":"75"},{"did":"3","amount":"75"}]', 7725, GETDATE()),  -- LeBrand
(@PayrollID2, 8, 10, 0, 0, 16000, 800, '[{"aid":"3","amount":"480"},{"aid":"1","amount":"320"}]', 320, '[{"did":"3","amount":"160"},{"did":"1","amount":"160"}]', 16480, GETDATE()),  -- Jasmine
(@PayrollID2, 9, 10, 0, 30, 12000, 600, '[{"aid":"4","amount":"360"},{"aid":"1","amount":"240"}]', 240, '[{"did":"3","amount":"120"},{"did":"1","amount":"120"}]', 12360, GETDATE()),  -- Maricar  -- This was the missing row

(@PayrollID3, 1, 20, 0, 45, 30000, 1500, '[{"aid":"1","amount":"1000"},{"aid":"2","amount":"500"}]', 500, '[{"did":"1","amount":"250"},{"did":"2","amount":"250"}]', 31000, GETDATE()),  -- Isaac
(@PayrollID3, 2, 22, 0, 0, 40000, 2000, '[{"aid":"2","amount":"1000"},{"aid":"3","amount":"1000"}]', 1000, '[{"did":"2","amount":"500"},{"did":"3","amount":"500"}]', 41000, GETDATE()),  -- Mang
(@PayrollID3, 3, 25, 0, 90, 50000, 2500, '[{"aid":"3","amount":"1500"},{"aid":"4","amount":"1000"}]', 1500, '[{"did":"3","amount":"750"},{"did":"1","amount":"750"}]', 51000, GETDATE()),  -- Jennifer
(@PayrollID3, 4, 20, 1, 30, 35000, 1750, '[{"aid":"3","amount":"1050"},{"aid":"1","amount":"700"}]', 750, '[{"did":"3","amount":"375"},{"did":"1","amount":"375"}]', 36000, GETDATE()),  -- Antonio
(@PayrollID3, 5, 22, 0, 105, 25000, 1250, '[{"aid":"4","amount":"750"},{"aid":"2","amount":"500"}]', 500, '[{"did":"2","amount":"250"},{"did":"1","amount":"250"}]', 26000, GETDATE()),  -- Albert
(@PayrollID3, 6, 25, 0, 0, 20000, 1000, '[{"aid":"1","amount":"600"},{"aid":"3","amount":"400"}]', 400, '[{"did":"3","amount":"200"},{"did":"1","amount":"200"}]', 20600, GETDATE()),  -- John
(@PayrollID3, 7, 20, 1, 45, 15000, 750, '[{"aid":"2","amount":"450"},{"aid":"4","amount":"300"}]', 300, '[{"did":"2","amount":"150"},{"did":"3","amount":"150"}]', 15450, GETDATE()),  -- LeBrand
(@PayrollID3, 8, 22, 0, 0, 32000, 1600, '[{"aid":"3","amount":"960"},{"aid":"1","amount":"640"}]', 640, '[{"did":"3","amount":"320"},{"did":"1","amount":"320"}]', 32640, GETDATE()),  -- Jasmine
(@PayrollID3, 9, 25, 0, 90, 24000, 1200, '[{"aid":"4","amount":"720"},{"aid":"1","amount":"480"}]', 480, '[{"did":"3","amount":"240"},{"did":"1","amount":"240"}]', 24720, GETDATE());  -- Maricar



-- Insert data into attendance
INSERT INTO [attendance] ([employee_id], [log_type], [datetime_log], [date_updated]) VALUES
(1, 1, '2024-07-27 08:00:00', GETDATE()),
(1, 2, '2024-07-27 12:00:00', GETDATE()),
(1, 3, '2024-07-27 13:00:00', GETDATE()),
(1, 4, '2024-07-27 17:00:00', GETDATE()),
(2, 1, '2024-07-27 09:00:00', GETDATE()),
(2, 2, '2024-07-27 12:30:00', GETDATE()),
(2, 3, '2024-07-27 13:30:00', GETDATE()),
(2, 4, '2024-07-27 17:30:00', GETDATE()),
(3, 1, '2024-07-27 08:30:00', GETDATE()),
(3, 2, '2024-07-27 11:30:00', GETDATE()),
(3, 3, '2024-07-27 14:00:00', GETDATE()),
(3, 4, '2024-07-27 17:00:00', GETDATE()),
(4, 1, '2024-07-27 07:50:00', GETDATE()),
(4, 2, '2024-07-27 12:00:00', GETDATE()),
(4, 3, '2024-07-27 13:05:00', GETDATE()),
(4, 4, '2024-07-27 17:00:00', GETDATE()),
(5, 1, '2024-07-27 08:05:00', GETDATE()),
(5, 2, '2024-07-27 12:15:00', GETDATE()),
(5, 3, '2024-07-27 13:10:00', GETDATE()),
(5, 4, '2024-07-27 17:00:00', GETDATE()),
(6, 1, '2024-07-27 08:00:00', GETDATE()),
(6, 2, '2024-07-27 11:55:00', GETDATE()),
(6, 3, '2024-07-27 13:00:00', GETDATE()),
(6, 4, '2024-07-27 17:00:00', GETDATE()),
(7, 1, '2024-07-27 08:10:00', GETDATE()),
(7, 2, '2024-07-27 12:00:00', GETDATE()),
(7, 3, '2024-07-27 13:15:00', GETDATE()),
(7, 4, '2024-07-27 17:00:00', GETDATE()),
(8, 1, '2024-07-27 07:55:00', GETDATE()),
(8, 2, '2024-07-27 12:05:00', GETDATE()),
(8, 3, '2024-07-27 13:00:00', GETDATE()),
(8, 4, '2024-07-27 17:00:00', GETDATE()),
(9, 1, '2024-07-27 08:45:00', GETDATE()),
(9, 2, '2024-07-27 11:45:00', GETDATE()),
(9, 3, '2024-07-27 13:45:00', GETDATE()),
(9, 4, '2024-07-27 17:00:00', GETDATE());

-- Insert data into users
INSERT INTO [users] ([employee_id], [name], [username], [password], [type]) VALUES
(1, 'Isaac Newton', 'isaac.newton', '3l@wsofmotion', 1),
(3, 'Jennifer Lopez Lawrence', 'jennifer.lopez.lawrence', 'hungerg@mes', 0);
GO

-- Create VIEW tables

-- View Payroll_Items
CREATE VIEW PayrollItemsWithRefNo AS
SELECT
    pi.id AS payroll_item_id,
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
FROM
    payroll_items pi
INNER JOIN
    payroll p ON pi.payroll_id = p.id;
GO

-- VIEW Users
CREATE VIEW EmployeeUserView AS 
SELECT
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
FROM employee e
INNER JOIN users u ON e.id = u.employee_id 
WHERE e.isDeleted = 0
  AND u.isDeleted = 0; 