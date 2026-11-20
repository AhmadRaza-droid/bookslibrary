-- Bookslibrary schema compatibility migration
-- Run once in the target database using phpMyAdmin or the MySQL client.
-- Adds columns used by Admin Dashboard and creates the download history table.

SET @db_name = DATABASE();

SET @sql = (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE books ADD COLUMN category VARCHAR(120) NULL',
    'SELECT 1')
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'books' AND COLUMN_NAME = 'category'
);
PREPARE migration_stmt FROM @sql;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

SET @sql = (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE books ADD COLUMN download_count INT NOT NULL DEFAULT 0',
    'SELECT 1')
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'books' AND COLUMN_NAME = 'download_count'
);
PREPARE migration_stmt FROM @sql;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

CREATE TABLE IF NOT EXISTS downloads (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  book_id INT NOT NULL,
  book_title VARCHAR(255) NOT NULL,
  download_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_downloads_user_id (user_id),
  INDEX idx_downloads_book_id (book_id),
  INDEX idx_downloads_date (download_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
