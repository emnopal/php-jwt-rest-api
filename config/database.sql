CREATE DATABASE IF NOT EXISTS `login-management-role-jwt`;
CREATE DATABASE IF NOT EXISTS `login-management-role-test-jwt`;

CREATE TABLE IF NOT EXISTS `login-management-role-jwt`.`users`
(
    `username` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `fullName` VARCHAR(255) NOT NULL,
    `email`    VARCHAR(255) NOT NULL,
    `role`     ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    PRIMARY KEY (`username`),
    UNIQUE KEY `email_key` (`email`)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `login-management-role-test-jwt`.`users`
(
    `username` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `fullName` VARCHAR(255) NOT NULL,
    `email`    VARCHAR(255) NOT NULL,
    `role`     ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    PRIMARY KEY (`username`),
    UNIQUE KEY `email_key` (`email`)
) ENGINE = InnoDB;

-- Create mock admin user
-- Delete this if you want to create a real admin user
-- DON'T USE THIS IN PRODUCTION
INSERT INTO `login-management-role-jwt`.`users` (`username`, `password`, `fullName`, `email`, `role`) VALUES
('admin001', '$2y$10$c4by7hrxbyQoF5icSqMZ7.Wo48GwFfLMwiHSAPR/uElE91nXomOUy', 'Admin', 'admin@admin.com', 'admin')