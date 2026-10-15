CREATE DATABASE IF NOT EXISTS nesflis
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

USE nesflis;

-- SEGURIDAD

CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    status TINYINT(1) NOT NULL DEFAULT 1        -- 1 activo, 0 inactivo, -1 eliminado
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS planes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    price_monthly DECIMAL(10, 2) NOT NULL,
    price_annual DECIMAL(10, 2) NOT NULL,
    deal DECIMAL(5, 2) NOT NULL DEFAULT 0.00,   -- descuento en porcentaje
    recurrence INT NOT NULL DEFAULT 30,          -- dias entre cobros
    status TINYINT(1) NOT NULL DEFAULT 1
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    username VARCHAR(20) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    birth_date DATE NOT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles (id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS suscripciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    plan_id INT NOT NULL,
    billing_period ENUM('monthly', 'annual') NOT NULL DEFAULT 'monthly',
    starts_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ends_at DATETIME NOT NULL,
    next_billing_at DATETIME NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,       -- 1 activa, 0 inactiva
    FOREIGN KEY (user_id) REFERENCES usuarios (id),
    FOREIGN KEY (plan_id) REFERENCES planes (id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- NEGOCIO


CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    status TINYINT(1) NOT NULL DEFAULT 1
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tipos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    status TINYINT(1) NOT NULL DEFAULT 1
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS multimedia_encabezados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    tipo_id INT NOT NULL,
    launch_date DATE NOT NULL,
    cover_vertical VARCHAR(255) NULL,
    cover_horizontal VARCHAR(255) NULL,
    description TEXT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (tipo_id) REFERENCES tipos (id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Relacion muchos a muchos: un titulo puede tener varias categorias
CREATE TABLE IF NOT EXISTS multimedia_categorias (
    multimedia_id INT NOT NULL,
    categoria_id INT NOT NULL,
    PRIMARY KEY (multimedia_id, categoria_id),
    FOREIGN KEY (multimedia_id) REFERENCES multimedia_encabezados (id),
    FOREIGN KEY (categoria_id) REFERENCES categorias (id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS multimedia_detalle (
    id INT AUTO_INCREMENT PRIMARY KEY,
    multimedia_id INT NOT NULL,
    restriction VARCHAR(20) NULL,               -- ej. 'PG', '+13', '+18'
    studio_name VARCHAR(100) NULL,
    path_source VARCHAR(255) NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    season SMALLINT NOT NULL DEFAULT 0,
    episode SMALLINT NOT NULL DEFAULT 0,
    duration_seconds INT NOT NULL,
    status TINYINT NOT NULL DEFAULT 1,
    FOREIGN KEY (multimedia_id) REFERENCES multimedia_encabezados (id),
    UNIQUE KEY uq_multimedia_episode (multimedia_id, season, episode)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;



CREATE TABLE IF NOT EXISTS pagos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subscription_id INT NOT NULL,
    reference VARCHAR(64) NOT NULL UNIQUE,
    amount DECIMAL(10, 2) NOT NULL,
    paid_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subscription_id) REFERENCES suscripciones (id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;



INSERT IGNORE INTO roles (name)
VALUES
    ('Admin'),
    ('User');

INSERT IGNORE INTO planes (name, price_monthly, price_annual, deal, recurrence, status)
VALUES
    ('Basico',   4.99,  49.99, 0.00, 30, 1),
    ('Estandar', 9.99,  99.99, 5.00, 30, 1),
    ('Premium', 14.99, 149.99, 10.00, 30, 1);

INSERT IGNORE INTO tipos (name)
VALUES
    ('Pelicula'),
    ('Serie'),
    ('Documental');

INSERT IGNORE INTO categorias (name)
VALUES
    ('Accion'),
    ('Comedia'),
    ('Drama'),
    ('Terror'),
    ('Ciencia ficcion');

INSERT IGNORE INTO usuarios (
    role_id,
    username,
    password_hash,
    name,
    email,
    birth_date
)
VALUES (
    1,
    'admin',
    '$2y$10$JHxpjlYXasPIRAmN9D6MFunFq/aklt08Mr5Bu7dF3dUO3hUt8JZza',
    'Administrador',
    'admin@test.com',
    '2000-01-01'
);

