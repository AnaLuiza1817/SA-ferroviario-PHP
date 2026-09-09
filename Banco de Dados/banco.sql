CREATE DATABASE IF NOT EXISTS ferrorama_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE ferrorama_db;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    telefone VARCHAR(20) NULL,
    tipo VARCHAR(50) NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Ativo',
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ultimo_acesso DATETIME NULL
);

INSERT INTO usuarios
(nome, email, telefone, tipo, status)
VALUES
('Gabriel Silva', 'gabriel@gmail.com', '(47) 99999-1111', 'Administrador', 'Ativo'),
('Ana Souza', 'ana@gmail.com', '(47) 98888-2222', 'Administrador', 'Ativo'),
('Arthur Backes', 'Arthur@gmail.com', '(47) 95555-5555', 'Administrador', 'Inativo');