DROP DATABASE IF EXISTS ferrorama_db;

CREATE DATABASE ferrorama_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE ferrorama_db;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    telefone VARCHAR(20),
    tipo VARCHAR(50),
    status VARCHAR(20) NOT NULL DEFAULT 'Ativo',
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ultimo_acesso DATETIME NULL
);

CREATE TABLE sensores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    tipo VARCHAR(50) NOT NULL,
    leitura DECIMAL(10,2) NOT NULL DEFAULT 0,
    unidade VARCHAR(20) NOT NULL,
    limite DECIMAL(10,2) NOT NULL DEFAULT 0,
    status VARCHAR(30) NOT NULL DEFAULT 'Normal',
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE trechos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    origem VARCHAR(100) NOT NULL,
    destino VARCHAR(100) NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'Normal',
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE estacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    nome VARCHAR(100) NOT NULL,
    posicao DECIMAL(5,2) NOT NULL DEFAULT 50,
    status VARCHAR(30) NOT NULL DEFAULT 'Operacional'
);

CREATE TABLE trens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    trecho_id INT NULL,
    posicao DECIMAL(5,2) NOT NULL DEFAULT 50,
    status VARCHAR(30) NOT NULL DEFAULT 'Normal',
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trecho_id) REFERENCES trechos(id)
);

CREATE TABLE amvs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    trecho_id INT NULL,
    estado VARCHAR(30) NOT NULL DEFAULT 'Normal',
    status VARCHAR(30) NOT NULL DEFAULT 'Operacional',
    FOREIGN KEY (trecho_id) REFERENCES trechos(id)
);

INSERT INTO usuarios
(nome, email, telefone, tipo, status)
VALUES
('Gabriel Silva', 'gabriel@gmail.com', '(47) 99999-1111', 'Administrador', 'Ativo'),
('Ana Souza', 'ana@gmail.com', '(47) 98888-2222', 'Administrador', 'Ativo'),
('Arthur Backes', 'Arthur@gmail.com', '(47) 95555-5555', 'Administrador', 'Inativo');

INSERT INTO sensores
(codigo, tipo, leitura, unidade, limite, status)
VALUES
('SEN-001', 'Manutenção', 87, '%', 80, 'Alerta'),
('SEN-002', 'Temperatura', 64, 'C', 70, 'Normal'),
('SEN-003', 'Via', 78, '%', 85, 'Atenção'),
('SEN-004', 'Manutenção', 42, '%', 80, 'Normal');

INSERT INTO trechos
(codigo, origem, destino, status)
VALUES
('TRC-001', 'EST-001', 'EST-002', 'Normal'),
('TRC-002', 'EST-002', 'EST-003', 'Normal'),
('TRC-003', 'EST-003', 'EST-004', 'Normal'),
('TRC-004', 'EST-004', 'EST-005', 'Normal');

INSERT INTO estacoes
(codigo, nome, posicao, status)
VALUES
('EST-001', 'Estação 001', 10, 'Operacional'),
('EST-002', 'Estação 002', 30, 'Operacional'),
('EST-003', 'Estação 003', 50, 'Operacional'),
('EST-004', 'Estação 004', 70, 'Operacional'),
('EST-005', 'Estação 005', 90, 'Operacional');

INSERT INTO trens
(codigo, trecho_id, posicao, status)
VALUES
('TR-0001', 1, 20, 'Normal'),
('TR-0002', 2, 48, 'Atenção'),
('TR-0003', 3, 78, 'Normal');

INSERT INTO amvs
(codigo, trecho_id, estado, status)
VALUES
('AMV-001', 2, 'Normal', 'Operacional'),
('AMV-002', 4, 'Alternado', 'Operacional');