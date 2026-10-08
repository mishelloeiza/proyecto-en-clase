CREATE DATABASE  IF NOT EXISTS nesflis;

USE nesflis;

--seguridad

CREATE TABLE IF NOT EXISTS roles(
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL 
);

CREATE TABLE IF NOT EXISTS planes(
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(10) NOT NULL ,
    price_montly DECIMAL(3,2) NOT NULL,
    price_anual DECIMAL(3,2) NOT NULL,
    deal DECIMAL(3,2) NOT NULL,
    recurrence DECIMAL(8,8) NOT NULL,
    status TINYINT(1) NOT NULL,
);

CREATE TABLE IF NOT EXISTS usuarios(
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(20) NOT NULL UNIQUE,
    pssword VARCHAR(256) NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    roles INT NOT NULL,
    born_date DATE NOT NULL,
    status TINYINT (1) NOT NULL,
    FOREIGN KEY(roles) REFERENCES roles(id)
);

--negocio
CREATE TABLE IF NOT EXISTS categorias(
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    status TINYINT(1) NOT NULL 
);

CREATE TABLE IF NOT EXISTS tipos(
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  status TINYINT (1) NOT NULL
);

CREATE TABLE IF NOT EXISTS multimedia_encabezados(
    id 
    name
    idTipo
    launch_date 
    cover_vertical
    cover_horizontal
    descripcion
    status
);

CREATE TABLE IF NOT EXISTS multimedia_detalle(
    id
    ididMultimediaEncabezado
    restriction
    studio_name
    path_source
    name
    description
    season
    chapter
    duration 
     status
);


