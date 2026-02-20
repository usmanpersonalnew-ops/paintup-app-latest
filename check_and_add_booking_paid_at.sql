-- ============================================
-- SQL Queries to Check and Add booking_paid_at Column
-- ============================================

-- 1. CHECK if column exists
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'paintup_db'
AND TABLE_NAME = 'projects'
AND COLUMN_NAME = 'booking_paid_at';

-- 2. CHECK all payment-related columns in projects table
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'paintup_db'
AND TABLE_NAME = 'projects'
AND COLUMN_NAME LIKE '%paid_at%'
ORDER BY COLUMN_NAME;

-- 3. ADD column if it doesn't exist (MySQL 5.7+)
-- First, find the position - check what column comes before it
SELECT COLUMN_NAME, ORDINAL_POSITION
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'paintup_db'
AND TABLE_NAME = 'projects'
AND COLUMN_NAME IN ('final_reference', 'final_status', 'payment_method')
ORDER BY ORDINAL_POSITION;

-- 4. ADD booking_paid_at column (after final_reference if it exists, otherwise after payment_method)
ALTER TABLE `projects`
ADD COLUMN IF NOT EXISTS `booking_paid_at` TIMESTAMP NULL DEFAULT NULL
AFTER `final_reference`;

-- If the above doesn't work (MySQL < 5.7), use this version:
-- First check if column exists, then add it manually
SET @dbname = DATABASE();
SET @tablename = 'projects';
SET @columnname = 'booking_paid_at';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1', -- Column exists, do nothing
  CONCAT('ALTER TABLE `', @tablename, '` ADD COLUMN `', @columnname, '` TIMESTAMP NULL DEFAULT NULL AFTER `final_reference`')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 5. SIMPLE VERSION - Just add the column (will fail if exists, but you can ignore the error)
ALTER TABLE `projects`
ADD COLUMN `booking_paid_at` TIMESTAMP NULL DEFAULT NULL
AFTER `final_reference`;

-- 6. If final_reference doesn't exist, add after payment_method instead
ALTER TABLE `projects`
ADD COLUMN `booking_paid_at` TIMESTAMP NULL DEFAULT NULL
AFTER `payment_method`;

-- 7. VERIFY the column was added
DESCRIBE `projects` `booking_paid_at`;

-- Or check all columns
SHOW COLUMNS FROM `projects` LIKE '%paid_at%';

