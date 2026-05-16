CREATE DATABASE IF NOT EXISTS cad_collab CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cad_collab;

CREATE TABLE IF NOT EXISTS users (
    id VARCHAR(36) PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    avatar_url VARCHAR(255),
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS drawings (
    id VARCHAR(36) PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    thumbnail_url VARCHAR(255),
    wasm_data LONGBLOB,
    json_data LONGTEXT,
    created_by VARCHAR(36) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS collaborations (
    id VARCHAR(36) PRIMARY KEY,
    drawing_id VARCHAR(36) NOT NULL,
    user_id VARCHAR(36) NOT NULL,
    session_id VARCHAR(64) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (drawing_id) REFERENCES drawings(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS drawing_versions (
    id VARCHAR(36) PRIMARY KEY,
    drawing_id VARCHAR(36) NOT NULL,
    version_number INT NOT NULL,
    wasm_data LONGBLOB,
    json_data LONGTEXT,
    changed_by VARCHAR(36) NOT NULL,
    change_description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (drawing_id) REFERENCES drawings(id),
    FOREIGN KEY (changed_by) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS shared_access (
    id VARCHAR(36) PRIMARY KEY,
    drawing_id VARCHAR(36) NOT NULL,
    user_id VARCHAR(36) NOT NULL,
    permission ENUM('view', 'edit', 'admin') DEFAULT 'view',
    granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (drawing_id) REFERENCES drawings(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

INSERT INTO users (id, username, email, password, full_name, role) VALUES
('admin-uuid-0000', 'admin', 'admin@cadcollab.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', 'admin');
