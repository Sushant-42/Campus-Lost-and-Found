CREATE DATABASE IF NOT EXISTS campusfind CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE campusfind;

CREATE TABLE IF NOT EXISTS items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    report_type ENUM('lost','found') NOT NULL,
    item_name VARCHAR(150) NOT NULL,
    category VARCHAR(80) NOT NULL,
    description TEXT NULL,
    location VARCHAR(150) NOT NULL,
    item_date DATE NOT NULL,
    contact_name VARCHAR(120) NOT NULL,
    contact_email VARCHAR(190) NOT NULL,
    status ENUM('searching','available','matched','claimed','closed') NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_items_type_status (report_type, status),
    INDEX idx_items_category (category),
    INDEX idx_items_created (created_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(80) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Local demonstration account. Change the password or delete this account before deploying publicly.
INSERT INTO admins (username, password_hash)
VALUES ('admin', '$2y$12$zLBOZc3Ro1c2wGuYWq/3BOKdAonatX5pWOqiPCdR32LOMwTKcCrru')
ON DUPLICATE KEY UPDATE username = VALUES(username);

-- Demo records corresponding to the original dashboard mock-up.
-- Delete these rows from phpMyAdmin if you prefer to start with an empty database.
INSERT INTO items (report_type, item_name, category, description, location, item_date, contact_name, contact_email, status, created_at) VALUES
('lost','Black Leather Wallet','Accessories','Black leather wallet reported missing.','Engineering Block',CURDATE(),'Demo Student','student1@example.edu','searching',DATE_SUB(NOW(), INTERVAL 2 HOUR)),
('lost','Silver Keychain','Keys','Small silver keychain.','Main Cafeteria',CURDATE(),'Demo Student','student2@example.edu','searching',DATE_SUB(NOW(), INTERVAL 5 HOUR)),
('lost','Blue Water Bottle','Personal','Blue reusable water bottle.','Sports Complex',DATE_SUB(CURDATE(), INTERVAL 1 DAY),'Demo Student','student3@example.edu','matched',DATE_SUB(NOW(), INTERVAL 1 DAY)),
('found','iPhone 14 - Black','Electronics','Black smartphone found on campus.','Student Center',CURDATE(),'Campus Admin','campus@example.edu','available',DATE_SUB(NOW(), INTERVAL 30 MINUTE)),
('found','Grey Backpack','Bags','Grey backpack found near the library.','Library',CURDATE(),'Campus Admin','campus@example.edu','available',DATE_SUB(NOW(), INTERVAL 1 HOUR)),
('found','Black Smartwatch','Electronics','Black smartwatch found in the auditorium.','Auditorium',DATE_SUB(CURDATE(), INTERVAL 1 DAY),'Campus Admin','campus@example.edu','claimed',DATE_SUB(NOW(), INTERVAL 1 DAY)),
('found','Black Leather Wallet','Accessories','Black leather wallet found near the library help desk.','Central Library',CURDATE(),'Campus Admin','campus@example.edu','available',DATE_SUB(NOW(), INTERVAL 10 MINUTE));
