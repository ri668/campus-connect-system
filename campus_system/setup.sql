

CREATE DATABASE IF NOT EXISTS finals_lab1;
USE finals_lab1;

CREATE TABLE IF NOT EXISTS records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    week_no VARCHAR(20),
    number_no VARCHAR(20),
    week_timeline VARCHAR(100),
    invited_during VARCHAR(100),
    last_name VARCHAR(100),
    first_name VARCHAR(100),
    mi VARCHAR(10),
    campus VARCHAR(100),
    added_status VARCHAR(20),
    cell_leader VARCHAR(100),
    consolidation_process VARCHAR(100),
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_campus VARCHAR(100),
    sender_role VARCHAR(20) DEFAULT 'user',
    message TEXT,
    note TEXT,
    target VARCHAR(500) DEFAULT 'all',
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
