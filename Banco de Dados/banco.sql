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
    senha VARCHAR(255) NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ultimo_acesso DATETIME NULL
);

ALTER TABLE usuarios
ADD COLUMN IF NOT EXISTS senha VARCHAR(255) NULL;

CREATE TABLE IF NOT EXISTS estacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    nome VARCHAR(100) NOT NULL,
    ordem INT NOT NULL,
    posicao_x DECIMAL(8,2) NOT NULL,
    posicao_y DECIMAL(8,2) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Normal'
);

CREATE TABLE IF NOT EXISTS rotas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    nome VARCHAR(100) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Ativa'
);

CREATE TABLE IF NOT EXISTS trechos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    origem_id INT NOT NULL,
    destino_id INT NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'Normal',
    distancia_km DECIMAL(8,2) NOT NULL DEFAULT 0,
    CONSTRAINT fk_trecho_origem FOREIGN KEY (origem_id) REFERENCES estacoes(id),
    CONSTRAINT fk_trecho_destino FOREIGN KEY (destino_id) REFERENCES estacoes(id)
);

CREATE TABLE IF NOT EXISTS rota_trechos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rota_id INT NOT NULL,
    trecho_id INT NOT NULL,
    ordem INT NOT NULL,
    UNIQUE KEY uk_rota_trecho (rota_id, trecho_id),
    CONSTRAINT fk_rota_trecho_rota FOREIGN KEY (rota_id) REFERENCES rotas(id) ON DELETE CASCADE,
    CONSTRAINT fk_rota_trecho_trecho FOREIGN KEY (trecho_id) REFERENCES trechos(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS trens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    status VARCHAR(30) NOT NULL DEFAULT 'Em operação',
    trecho_id INT NULL,
    rota_id INT NULL,
    posicao_percentual DECIMAL(5,2) NOT NULL DEFAULT 50,
    CONSTRAINT fk_trem_trecho FOREIGN KEY (trecho_id) REFERENCES trechos(id) ON DELETE SET NULL,
    CONSTRAINT fk_trem_rota FOREIGN KEY (rota_id) REFERENCES rotas(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS ocorrencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trem_id INT NULL,
    trecho_id INT NULL,
    tipo VARCHAR(60) NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'Aberta',
    ocorrido_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ocorrencia_trem FOREIGN KEY (trem_id) REFERENCES trens(id) ON DELETE SET NULL,
    CONSTRAINT fk_ocorrencia_trecho FOREIGN KEY (trecho_id) REFERENCES trechos(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS manutencoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trem_id INT NULL,
    trecho_id INT NULL,
    componente VARCHAR(100) NOT NULL,
    ordem_manutencao VARCHAR(30) NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'Pendente',
    inicio DATETIME NOT NULL,
    fim DATETIME NULL,
    descricao VARCHAR(255) NULL,
    CONSTRAINT fk_manutencao_trem FOREIGN KEY (trem_id) REFERENCES trens(id) ON DELETE SET NULL,
    CONSTRAINT fk_manutencao_trecho FOREIGN KEY (trecho_id) REFERENCES trechos(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS sensores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    tipo VARCHAR(60) NOT NULL,
    trecho_id INT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'Normal',
    leitura DECIMAL(10,2) NOT NULL DEFAULT 0,
    limite DECIMAL(10,2) NULL,
    unidade VARCHAR(20) NULL,
    ultima_atualizacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_sensor_trecho FOREIGN KEY (trecho_id) REFERENCES trechos(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS amvs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    trecho_id INT NULL,
    estado VARCHAR(30) NOT NULL DEFAULT 'Normal',
    status VARCHAR(30) NOT NULL DEFAULT 'Operacional',
    ultima_atualizacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_amv_trecho FOREIGN KEY (trecho_id) REFERENCES trechos(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS alertas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sensor_id INT NULL,
    trem_id INT NULL,
    trecho_id INT NULL,
    nivel VARCHAR(20) NOT NULL,
    mensagem VARCHAR(255) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Ativo',
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_alerta_sensor FOREIGN KEY (sensor_id) REFERENCES sensores(id) ON DELETE SET NULL,
    CONSTRAINT fk_alerta_trem FOREIGN KEY (trem_id) REFERENCES trens(id) ON DELETE SET NULL,
    CONSTRAINT fk_alerta_trecho FOREIGN KEY (trecho_id) REFERENCES trechos(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS alteracoes_rota (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trem_id INT NOT NULL,
    rota_anterior_id INT NULL,
    rota_nova_id INT NOT NULL,
    amv_id INT NULL,
    motivo VARCHAR(255) NOT NULL,
    alterado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_alteracao_trem FOREIGN KEY (trem_id) REFERENCES trens(id) ON DELETE CASCADE,
    CONSTRAINT fk_alteracao_rota_anterior FOREIGN KEY (rota_anterior_id) REFERENCES rotas(id) ON DELETE SET NULL,
    CONSTRAINT fk_alteracao_rota_nova FOREIGN KEY (rota_nova_id) REFERENCES rotas(id) ON DELETE CASCADE,
    CONSTRAINT fk_alteracao_amv FOREIGN KEY (amv_id) REFERENCES amvs(id) ON DELETE SET NULL
);

INSERT IGNORE INTO estacoes (codigo, nome, ordem, posicao_x, posicao_y, status) VALUES
('EST-001', 'Central', 1, 90, 180, 'Normal'),
('EST-002', 'Industrial', 2, 300, 120, 'Normal'),
('EST-003', 'Norte', 3, 520, 180, 'Normal'),
('EST-004', 'Oficinas', 4, 740, 120, 'Atenção'),
('EST-005', 'Terminal', 5, 950, 180, 'Normal');

INSERT IGNORE INTO rotas (codigo, nome, status) VALUES
('ROT-001', 'Linha Principal', 'Ativa'),
('ROT-002', 'Linha Alternativa', 'Ativa');

INSERT IGNORE INTO trechos (codigo, origem_id, destino_id, status, distancia_km) VALUES
('TRC-001', 1, 2, 'Normal', 8.50),
('TRC-002', 2, 3, 'Normal', 7.20),
('TRC-003', 3, 4, 'Em manutenção', 6.80),
('TRC-004', 4, 5, 'Atenção', 9.10),
('TRC-005', 2, 4, 'Normal', 10.40);

INSERT IGNORE INTO rota_trechos (rota_id, trecho_id, ordem) VALUES
(1, 1, 1),
(1, 2, 2),
(1, 3, 3),
(1, 4, 4),
(2, 1, 1),
(2, 5, 2),
(2, 4, 3);

INSERT IGNORE INTO trens (codigo, status, trecho_id, rota_id, posicao_percentual) VALUES
('TR-0001', 'Em operação', 2, 1, 55),
('TR-0002', 'Parado', 3, 1, 35),
('TR-0003', 'Em operação', 4, 2, 70);

INSERT IGNORE INTO ocorrencias (trem_id, trecho_id, tipo, descricao, status, ocorrido_em) VALUES
(2, 3, 'Falha de equipamento', 'Falha detectada durante inspeção do trecho.', 'Aberta', '2026-09-15 09:30:00'),
(1, 2, 'Atraso', 'Atraso operacional registrado no trecho.', 'Resolvida', '2026-09-10 14:10:00'),
(3, 4, 'Sinalização', 'Alerta de sinalização no trecho.', 'Em análise', '2026-09-16 08:20:00');

INSERT IGNORE INTO manutencoes (trem_id, trecho_id, componente, ordem_manutencao, status, inicio, fim, descricao) VALUES
(2, 3, 'Sistema de tração', 'OM-0001', 'Em andamento', '2026-09-15 10:00:00', NULL, 'Inspeção e reparo do sistema de tração.'),
(1, 2, 'Freios', 'OM-0002', 'Concluída', '2026-08-12 08:00:00', '2026-08-12 16:00:00', 'Manutenção preventiva.'),
(3, 4, 'Sinalização', 'OM-0003', 'Pendente', '2026-09-17 08:00:00', NULL, 'Avaliação do sistema de sinalização.');

INSERT IGNORE INTO sensores (codigo, tipo, trecho_id, status, leitura, limite, unidade, ultima_atualizacao) VALUES
('SEN-001', 'Manutenção', 3, 'Alerta', 87, 80, '%', '2026-09-17 07:45:00'),
('SEN-002', 'Temperatura', 2, 'Normal', 64, 80, 'C', '2026-09-17 07:50:00'),
('SEN-003', 'Via', 4, 'Atenção', 78, 75, '%', '2026-09-17 07:55:00'),
('SEN-004', 'Manutenção', 1, 'Normal', 42, 80, '%', '2026-09-17 08:00:00');

INSERT IGNORE INTO amvs (codigo, trecho_id, estado, status, ultima_atualizacao) VALUES
('AMV-001', 2, 'Normal', 'Operacional', '2026-09-17 07:40:00'),
('AMV-002', 4, 'Alternado', 'Operacional', '2026-09-17 07:42:00');

INSERT IGNORE INTO alertas (sensor_id, trem_id, trecho_id, nivel, mensagem, status, criado_em) VALUES
(1, 2, 3, 'Alto', 'Sensor de manutenção acima do limite.', 'Ativo', '2026-09-17 07:45:00'),
(3, 3, 4, 'Médio', 'Leitura do sensor acima do limite configurado.', 'Ativo', '2026-09-17 07:55:00');

INSERT IGNORE INTO alteracoes_rota (trem_id, rota_anterior_id, rota_nova_id, amv_id, motivo, alterado_em) VALUES
(2, 1, 2, 2, 'Desvio simulado devido à manutenção do trecho TRC-003.', '2026-09-17 08:10:00');

UPDATE usuarios
SET nome = 'Gabriel Silva',
    email = 'gabriel@gmail.com',
    telefone = '(47) 99999-1111',
    tipo = 'Administrador',
    status = 'Ativo',
    senha = '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'
WHERE LOWER(email) = 'gabriel@gmail.com';

INSERT INTO usuarios (nome, email, telefone, tipo, status, senha)
SELECT 'Gabriel Silva', 'gabriel@gmail.com', '(47) 99999-1111', 'Administrador', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE LOWER(email) = 'gabriel@gmail.com');

UPDATE usuarios
SET nome = 'Ana Souza',
    email = 'ana@gmail.com',
    telefone = '(47) 98888-2222',
    tipo = 'Administrador',
    status = 'Ativo',
    senha = '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'
WHERE LOWER(email) = 'ana@gmail.com';

INSERT INTO usuarios (nome, email, telefone, tipo, status, senha)
SELECT 'Ana Souza', 'ana@gmail.com', '(47) 98888-2222', 'Administrador', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE LOWER(email) = 'ana@gmail.com');

UPDATE usuarios
SET nome = 'Arthur Backes',
    email = 'arthur@gmail.com',
    telefone = '(47) 95555-5555',
    tipo = 'Administrador',
    status = 'Ativo',
    senha = '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'
WHERE LOWER(email) = 'arthur@gmail.com';

INSERT INTO usuarios (nome, email, telefone, tipo, status, senha)
SELECT 'Arthur Backes', 'arthur@gmail.com', '(47) 95555-5555', 'Administrador', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE LOWER(email) = 'arthur@gmail.com');

UPDATE usuarios
SET nome = 'Fernanda Lima',
    email = 'fernanda@gmail.com',
    telefone = '(47) 94444-4444',
    tipo = 'Administrador',
    status = 'Ativo',
    senha = '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'
WHERE LOWER(email) = 'fernanda@gmail.com';

INSERT INTO usuarios (nome, email, telefone, tipo, status, senha)
SELECT 'Fernanda Lima', 'fernanda@gmail.com', '(47) 94444-4444', 'Administrador', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE LOWER(email) = 'fernanda@gmail.com');
