DROP DATABASE IF EXISTS ferrorama_db;

CREATE DATABASE ferrorama_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE ferrorama_db;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    telefone VARCHAR(20) NULL,
    tipo VARCHAR(50) NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Ativo',
    senha VARCHAR(255) NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ultimo_acesso DATETIME NULL,
    deleted_at DATETIME NULL DEFAULT NULL,
    UNIQUE KEY uk_usuarios_email (email)
);

CREATE TABLE estacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    nome VARCHAR(100) NOT NULL,
    ordem INT NOT NULL,
    posicao_x DECIMAL(8,2) NOT NULL,
    posicao_y DECIMAL(8,2) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Normal'
);

CREATE TABLE rotas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    nome VARCHAR(100) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Ativa',
    descricao TEXT NULL,
    distancia_km DECIMAL(8,2) NULL,
    tempo_estimado VARCHAR(50) NULL,
    data_criacao DATE NULL
);

CREATE TABLE trechos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    origem_id INT NOT NULL,
    destino_id INT NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'Normal',
    distancia_km DECIMAL(8,2) NOT NULL DEFAULT 0,
    CONSTRAINT fk_trecho_origem FOREIGN KEY (origem_id) REFERENCES estacoes(id),
    CONSTRAINT fk_trecho_destino FOREIGN KEY (destino_id) REFERENCES estacoes(id)
);

CREATE TABLE rota_trechos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rota_id INT NOT NULL,
    trecho_id INT NOT NULL,
    ordem INT NOT NULL,
    UNIQUE KEY uk_rota_trecho (rota_id, trecho_id),
    CONSTRAINT fk_rota_trecho_rota FOREIGN KEY (rota_id) REFERENCES rotas(id) ON DELETE CASCADE,
    CONSTRAINT fk_rota_trecho_trecho FOREIGN KEY (trecho_id) REFERENCES trechos(id) ON DELETE CASCADE
);

CREATE TABLE trens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    status VARCHAR(30) NOT NULL DEFAULT 'Em operação',
    trecho_id INT NULL,
    rota_id INT NULL,
    posicao_percentual DECIMAL(5,2) NOT NULL DEFAULT 50,
    usuario_responsavel_id INT NULL,
    CONSTRAINT fk_trem_trecho FOREIGN KEY (trecho_id) REFERENCES trechos(id) ON DELETE SET NULL,
    CONSTRAINT fk_trem_rota FOREIGN KEY (rota_id) REFERENCES rotas(id) ON DELETE SET NULL,
    CONSTRAINT fk_trem_usuario FOREIGN KEY (usuario_responsavel_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

CREATE TABLE ocorrencias (
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

CREATE TABLE manutencoes (
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

CREATE TABLE sensores (
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

CREATE TABLE amvs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    trecho_id INT NULL,
    estado VARCHAR(30) NOT NULL DEFAULT 'Normal',
    status VARCHAR(30) NOT NULL DEFAULT 'Operacional',
    ultima_atualizacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_amv_trecho FOREIGN KEY (trecho_id) REFERENCES trechos(id) ON DELETE SET NULL
);

CREATE TABLE alertas (
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

CREATE TABLE alteracoes_rota (
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

CREATE OR REPLACE VIEW vw_usuarios_publicos AS
SELECT
    id,
    nome,
    email,
    telefone,
    tipo,
    status,
    criado_em,
    ultimo_acesso
FROM usuarios;

INSERT INTO estacoes (codigo, nome, ordem, posicao_x, posicao_y, status) VALUES
('EST-001', 'Central', 1, 90, 180, 'Normal'),
('EST-002', 'Industrial', 2, 300, 120, 'Normal'),
('EST-003', 'Norte', 3, 520, 180, 'Normal'),
('EST-004', 'Oficinas', 4, 740, 120, 'Atenção'),
('EST-005', 'Terminal', 5, 950, 180, 'Normal');

INSERT INTO rotas (codigo, nome, status, descricao, distancia_km, tempo_estimado, data_criacao) VALUES
('ROT-001', 'Linha Principal', 'Ativa', 'Rota principal que liga o centro ao terminal.', 31.60, '45 min', '2026-01-10'),
('ROT-002', 'Linha Alternativa', 'Ativa', 'Rota de desvio passando pelas oficinas.', 28.30, '50 min', '2026-02-15');

INSERT INTO trechos (codigo, origem_id, destino_id, status, distancia_km) VALUES
('TRC-001', 1, 2, 'Normal', 8.50),
('TRC-002', 2, 3, 'Normal', 7.20),
('TRC-003', 3, 4, 'Em manutenção', 6.80),
('TRC-004', 4, 5, 'Atenção', 9.10),
('TRC-005', 2, 4, 'Normal', 10.40);

INSERT INTO rota_trechos (rota_id, trecho_id, ordem) VALUES
(1, 1, 1),
(1, 2, 2),
(1, 3, 3),
(1, 4, 4),
(2, 1, 1),
(2, 5, 2),
(2, 4, 3);

INSERT INTO trens (codigo, status, trecho_id, rota_id, posicao_percentual, usuario_responsavel_id) VALUES
('TR-0001', 'Em operação', 2, 1, 55, 2),
('TR-0002', 'Parado', 3, 1, 35, 3),
('TR-0003', 'Em operação', 4, 2, 70, 4);

INSERT INTO ocorrencias (trem_id, trecho_id, tipo, descricao, status, ocorrido_em) VALUES
(2, 3, 'Falha de equipamento', 'Falha detectada durante inspeção do trecho.', 'Aberta', '2026-09-15 09:30:00'),
(1, 2, 'Atraso', 'Atraso operacional registrado no trecho.', 'Resolvida', '2026-09-10 14:10:00'),
(3, 4, 'Sinalização', 'Alerta de sinalização no trecho.', 'Em análise', '2026-09-16 08:20:00');

INSERT INTO manutencoes (trem_id, trecho_id, componente, ordem_manutencao, status, inicio, fim, descricao) VALUES
(2, 3, 'Sistema de tração', 'OM-0001', 'Em andamento', '2026-09-15 10:00:00', NULL, 'Inspeção e reparo do sistema de tração.'),
(1, 2, 'Freios', 'OM-0002', 'Concluída', '2026-08-12 08:00:00', '2026-08-12 16:00:00', 'Manutenção preventiva.'),
(3, 4, 'Sinalização', 'OM-0003', 'Pendente', '2026-09-17 08:00:00', NULL, 'Avaliação do sistema de sinalização.');

INSERT INTO sensores (codigo, tipo, trecho_id, status, leitura, limite, unidade, ultima_atualizacao) VALUES
('SEN-001', 'Manutenção', 3, 'Alerta', 87, 80, '%', '2026-09-17 07:45:00'),
('SEN-002', 'Temperatura', 2, 'Normal', 64, 80, 'C', '2026-09-17 07:50:00'),
('SEN-003', 'Via', 4, 'Atenção', 78, 75, '%', '2026-09-17 07:55:00'),
('SEN-004', 'Manutenção', 1, 'Normal', 42, 80, '%', '2026-09-17 08:00:00');

INSERT INTO amvs (codigo, trecho_id, estado, status, ultima_atualizacao) VALUES
('AMV-001', 2, 'Normal', 'Operacional', '2026-09-17 07:40:00'),
('AMV-002', 4, 'Alternado', 'Operacional', '2026-09-17 07:42:00');

INSERT INTO alertas (sensor_id, trem_id, trecho_id, nivel, mensagem, status, criado_em) VALUES
(1, 2, 3, 'Alto', 'Sensor de manutenção acima do limite.', 'Ativo', '2026-09-17 07:45:00'),
(3, 3, 4, 'Médio', 'Leitura do sensor acima do limite configurado.', 'Ativo', '2026-09-17 07:55:00');

INSERT INTO alteracoes_rota (trem_id, rota_anterior_id, rota_nova_id, amv_id, motivo, alterado_em) VALUES
(2, 1, 2, 2, 'Desvio simulado devido à manutenção do trecho TRC-003.', '2026-09-17 08:10:00');

INSERT INTO usuarios (nome, email, telefone, tipo, status, senha) VALUES
('Gabriel Silva', 'gabriel@gmail.com', '(47) 99999-1111', 'Administrador', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Ana Souza', 'ana@gmail.com', '(47) 98888-2222', 'Administrador', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Arthur Backes', 'arthur@gmail.com', '(47) 95555-5555', 'Administrador', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Fernanda Lima', 'fernanda@gmail.com', '(47) 94444-4444', 'Administrador', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Lucas Pereira', 'lucas.pereira@gmail.com', '(47) 99101-0001', 'Usuário', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Mariana Alves', 'mariana.alves@gmail.com', '(47) 99102-0002', 'Usuário', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Rafael Costa', 'rafael.costa@gmail.com', '(47) 99103-0003', 'Usuário', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Juliana Rocha', 'juliana.rocha@gmail.com', '(47) 99104-0004', 'Usuário', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Bruno Martins', 'bruno.martins@gmail.com', '(47) 99105-0005', 'Usuário', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Camila Ferreira', 'camila.ferreira@gmail.com', '(47) 99106-0006', 'Usuário', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Diego Santos', 'diego.santos@gmail.com', '(47) 99107-0007', 'Usuário', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Patrícia Oliveira', 'patricia.oliveira@gmail.com', '(47) 99108-0008', 'Usuário', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Thiago Ribeiro', 'thiago.ribeiro@gmail.com', '(47) 99109-0009', 'Usuário', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Larissa Mendes', 'larissa.mendes@gmail.com', '(47) 99110-0010', 'Usuário', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Felipe Barbosa', 'felipe.barbosa@gmail.com', '(47) 99111-0011', 'Usuário', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Amanda Cardoso', 'amanda.cardoso@gmail.com', '(47) 99112-0012', 'Usuário', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Gustavo Nunes', 'gustavo.nunes@gmail.com', '(47) 99113-0013', 'Usuário', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Beatriz Carvalho', 'beatriz.carvalho@gmail.com', '(47) 99114-0014', 'Usuário', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Rodrigo Teixeira', 'rodrigo.teixeira@gmail.com', '(47) 99115-0015', 'Usuário', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Vanessa Araújo', 'vanessa.araujo@gmail.com', '(47) 99116-0016', 'Usuário', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Leonardo Pinto', 'leonardo.pinto@gmail.com', '(47) 99117-0017', 'Usuário', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Carolina Moreira', 'carolina.moreira@gmail.com', '(47) 99118-0018', 'Usuário', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Vinícius Castro', 'vinicius.castro@gmail.com', '(47) 99119-0019', 'Usuário', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Isabela Freitas', 'isabela.freitas@gmail.com', '(47) 99120-0020', 'Usuário', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Matheus Gomes', 'matheus.gomes@gmail.com', '(47) 99121-0021', 'Usuário', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Natália Dias', 'natalia.dias@gmail.com', '(47) 99122-0022', 'Usuário', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Pedro Henrique', 'pedro.henrique@gmail.com', '(47) 99123-0023', 'Usuário', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Renata Lopes', 'renata.lopes@gmail.com', '(47) 99124-0024', 'Usuário', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Samuel Correia', 'samuel.correia@gmail.com', '(47) 99125-0025', 'Usuário', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Tatiane Moura', 'tatiane.moura@gmail.com', '(47) 99126-0026', 'Usuário', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Ulysses Ramos', 'ulysses.ramos@gmail.com', '(47) 99127-0027', 'Usuário', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Vitória Campos', 'vitoria.campos@gmail.com', '(47) 99128-0028', 'Usuário', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Wesley Farias', 'wesley.farias@gmail.com', '(47) 99129-0029', 'Usuário', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Yasmin Duarte', 'yasmin.duarte@gmail.com', '(47) 99130-0030', 'Usuário', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Carlos Andrade', 'carlos.andrade@gmail.com', '(47) 99201-0101', 'Supervisor', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Débora Pires', 'debora.pires@gmail.com', '(47) 99202-0102', 'Supervisor', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Eduardo Tavares', 'eduardo.tavares@gmail.com', '(47) 99203-0103', 'Supervisor', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Flávia Monteiro', 'flavia.monteiro@gmail.com', '(47) 99204-0104', 'Supervisor', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Gilberto Azevedo', 'gilberto.azevedo@gmail.com', '(47) 99205-0105', 'Supervisor', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Helena Vasconcelos', 'helena.vasconcelos@gmail.com', '(47) 99206-0106', 'Supervisor', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Igor Sampaio', 'igor.sampaio@gmail.com', '(47) 99207-0107', 'Supervisor', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Joana Rezende', 'joana.rezende@gmail.com', '(47) 99208-0108', 'Supervisor', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Kleber Machado', 'kleber.machado@gmail.com', '(47) 99209-0109', 'Supervisor', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Luciana Peixoto', 'luciana.peixoto@gmail.com', '(47) 99210-0110', 'Supervisor', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Marcelo Fonseca', 'marcelo.fonseca@gmail.com', '(47) 99211-0111', 'Supervisor', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Nádia Bezerra', 'nadia.bezerra@gmail.com', '(47) 99212-0112', 'Supervisor', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Otávio Siqueira', 'otavio.siqueira@gmail.com', '(47) 99213-0113', 'Supervisor', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Priscila Amaral', 'priscila.amaral@gmail.com', '(47) 99214-0114', 'Supervisor', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Quintino Braga', 'quintino.braga@gmail.com', '(47) 99215-0115', 'Supervisor', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Roberta Salles', 'roberta.salles@gmail.com', '(47) 99216-0116', 'Supervisor', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Sérgio Vilela', 'sergio.vilela@gmail.com', '(47) 99217-0117', 'Supervisor', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Talita Novaes', 'talita.novaes@gmail.com', '(47) 99218-0118', 'Supervisor', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Ubirajara Prado', 'ubirajara.prado@gmail.com', '(47) 99219-0119', 'Supervisor', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W'),
('Verônica Sales', 'veronica.sales@gmail.com', '(47) 99220-0120', 'Supervisor', 'Ativo', '$2y$12$QLY5V3Es2CxsdAWTt1/2NeIXVDF6Vz5GnMiT5DGR795qtOevbaJ2W');