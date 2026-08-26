CREATE DATABASE IF NOT EXISTS global_mart_demo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE global_mart_demo;

CREATE TABLE IF NOT EXISTS users (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL, username VARCHAR(50) NOT NULL UNIQUE, email VARCHAR(150) NOT NULL UNIQUE, phone VARCHAR(30) NULL, password_hash VARCHAR(255) NOT NULL, referral_code VARCHAR(20) NOT NULL UNIQUE, referred_by INT UNSIGNED NULL, status ENUM('active','inactive','banned') NOT NULL DEFAULT 'active', is_admin TINYINT(1) NOT NULL DEFAULT 0, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, CONSTRAINT fk_users_parent FOREIGN KEY(referred_by) REFERENCES users(id) ON DELETE SET NULL, INDEX idx_users_referred_by(referred_by)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS wallets (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id INT UNSIGNED NOT NULL UNIQUE, balance DECIMAL(15,2) NOT NULL DEFAULT 0, total_invested DECIMAL(15,2) NOT NULL DEFAULT 0, total_withdrawn DECIMAL(15,2) NOT NULL DEFAULT 0, profit_30_days DECIMAL(15,2) NOT NULL DEFAULT 0, commission DECIMAL(15,2) NOT NULL DEFAULT 0, last_earning_at TIMESTAMP NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, CONSTRAINT fk_wallet_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS packages (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL, description TEXT NULL, price DECIMAL(15,2) NOT NULL, validity_days INT UNSIGNED NOT NULL, daily_profit DECIMAL(15,2) NOT NULL, total_return DECIMAL(15,2) NOT NULL DEFAULT 0, status ENUM('active','inactive') NOT NULL DEFAULT 'active', created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS investments (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id INT UNSIGNED NOT NULL, package_id INT UNSIGNED NOT NULL, amount DECIMAL(15,2) NOT NULL, daily_profit DECIMAL(15,2) NOT NULL, validity_days INT UNSIGNED NOT NULL, status ENUM('active','completed','cancelled') NOT NULL DEFAULT 'active', started_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, ends_at DATETIME NOT NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, CONSTRAINT fk_invest_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE, CONSTRAINT fk_invest_package FOREIGN KEY(package_id) REFERENCES packages(id) ON DELETE RESTRICT
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS deposits (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id INT UNSIGNED NOT NULL, amount DECIMAL(15,2) NOT NULL, payment_method VARCHAR(50) NOT NULL, payment_reference VARCHAR(100) NULL, status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending', created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, reviewed_at TIMESTAMP NULL, CONSTRAINT fk_deposit_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE, INDEX idx_deposit_status(status)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS withdrawals (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id INT UNSIGNED NOT NULL, amount DECIMAL(15,2) NOT NULL, payment_method VARCHAR(50) NOT NULL, account_details VARCHAR(255) NOT NULL, status ENUM('pending','approved','rejected','completed') NOT NULL DEFAULT 'pending', created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, reviewed_at TIMESTAMP NULL, CONSTRAINT fk_withdraw_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE, INDEX idx_withdraw_status(status)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS referrals (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, referrer_id INT UNSIGNED NOT NULL, referee_id INT UNSIGNED NOT NULL, level TINYINT UNSIGNED NOT NULL DEFAULT 1, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY uq_referral(referrer_id,referee_id,level), CONSTRAINT fk_referrer FOREIGN KEY(referrer_id) REFERENCES users(id) ON DELETE CASCADE, CONSTRAINT fk_referee FOREIGN KEY(referee_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS commissions (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id INT UNSIGNED NOT NULL, source_user_id INT UNSIGNED NOT NULL, level TINYINT UNSIGNED NOT NULL, amount DECIMAL(15,2) NOT NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, CONSTRAINT fk_commission_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE, CONSTRAINT fk_commission_source FOREIGN KEY(source_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS transactions (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id INT UNSIGNED NOT NULL, type ENUM('deposit','withdrawal','commission','reward','investment') NOT NULL, amount DECIMAL(15,2) NOT NULL, description VARCHAR(255) NOT NULL, reference_id INT UNSIGNED NULL, status ENUM('pending','completed','failed') NOT NULL DEFAULT 'pending', created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, CONSTRAINT fk_transaction_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE, INDEX idx_transaction_user(user_id,created_at)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS settings (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, setting_key VARCHAR(80) NOT NULL UNIQUE, setting_value TEXT NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS support_links (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, title VARCHAR(50) NOT NULL, url VARCHAR(255) NOT NULL, icon VARCHAR(50) NULL, status ENUM('active','inactive') NOT NULL DEFAULT 'active', created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP);

INSERT INTO settings(setting_key,setting_value) VALUES
('site_name','Global Mart Demo'),('level1_commission','11'),('level2_commission','3'),('level3_commission','2'),('support_admin','#'),('support_channel','#'),('support_telegram','#'),('support_install','#')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
INSERT INTO packages(name,description,price,validity_days,daily_profit,total_return,status) VALUES
('Wireless Earbuds Pack','Demo package for testing account flows.',380,90,12.70,1143,'active'),
('Smart Watch Pack','Feature-rich demo campaign package.',750,90,25,2250,'active'),
('Home Security Pack','Complete demo home security campaign.',1500,120,50,6000,'active'),
('Premium Gadget Pack','Premium demo gadget bundle.',3000,180,100,18000,'active')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Demo credentials are generated by database/seed.php after import.
