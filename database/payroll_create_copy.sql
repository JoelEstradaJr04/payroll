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
