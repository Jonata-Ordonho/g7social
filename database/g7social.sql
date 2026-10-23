-- G7 Social - Estrutura inicial do banco de dados

CREATE TABLE usuarios (
    id_usuario INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    senha CHAR(40) NOT NULL,
    tipo ENUM('admin', 'gestor') NOT NULL DEFAULT 'gestor',
    status ENUM('on', 'off') NOT NULL DEFAULT 'on',
    data_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_usuario),
    UNIQUE KEY uk_usuarios_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE acoes (
    id_acao INT UNSIGNED NOT NULL AUTO_INCREMENT,
    descricao VARCHAR(100) NOT NULL,
    PRIMARY KEY (id_acao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE logs (
    id_log INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_acao INT UNSIGNED NOT NULL,
    id_usuario INT UNSIGNED NOT NULL,
    descricao TEXT NULL,
    data_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_log),
    CONSTRAINT fk_logs_acao FOREIGN KEY (id_acao) REFERENCES acoes (id_acao),
    CONSTRAINT fk_logs_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO acoes (id_acao, descricao) VALUES (1, 'Login no gestor');

-- Usuário inicial: admin master (conta DEV, senha: admin) - alterar após o primeiro acesso
INSERT INTO usuarios (id_usuario, nome, email, senha, tipo) VALUES (1, 'Administrador', 'admin@g7social.local', SHA1('admin'), 'admin');

CREATE TABLE projetos (
    id_projeto INT UNSIGNED NOT NULL AUTO_INCREMENT,
    titulo VARCHAR(200) NOT NULL,
    descricao TEXT NULL,
    data_inicio DATE NOT NULL,
    data_fim DATE NOT NULL,
    arrecadacao_estimada DECIMAL(12,2) NULL COMMENT 'Valor em dinheiro previsto em doações (opcional)',
    codigo CHAR(32) NULL COMMENT 'Código do link público de inscrição (gerado pelo sistema)',
    id_usuario INT UNSIGNED NULL,
    data_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_projeto),
    UNIQUE KEY uk_projetos_codigo (codigo),
    KEY idx_projetos_periodo (data_inicio, data_fim),
    CONSTRAINT fk_projetos_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE atividades (
    id_atividade INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_projeto INT UNSIGNED NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    descricao TEXT NULL,
    local VARCHAR(200) NOT NULL,
    data_inicio DATE NOT NULL,
    data_fim DATE NOT NULL,
    horario VARCHAR(100) NULL,
    vagas INT UNSIGNED NULL,
    codigo CHAR(32) NOT NULL COMMENT 'Código do link público de inscrição',
    status ENUM('on', 'off') NOT NULL DEFAULT 'on',
    id_usuario INT UNSIGNED NULL,
    data_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_atividade),
    UNIQUE KEY uk_atividades_codigo (codigo),
    KEY idx_atividades_projeto (id_projeto),
    CONSTRAINT fk_atividades_projeto FOREIGN KEY (id_projeto) REFERENCES projetos (id_projeto),
    CONSTRAINT fk_atividades_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE voluntarios (
    id_voluntario INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome VARCHAR(150) NOT NULL,
    cpf CHAR(11) NULL COMMENT 'Somente números',
    email VARCHAR(150) NOT NULL,
    telefone VARCHAR(11) NULL COMMENT 'Somente números',
    grupo_sete TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = voluntário interno (faz parte do Grupo Sete)',
    data_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_voluntario),
    UNIQUE KEY uk_voluntarios_cpf (cpf)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE inscricoes (
    id_inscricao INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_voluntario INT UNSIGNED NOT NULL,
    id_atividade INT UNSIGNED NOT NULL,
    data_inscricao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_inscricao),
    UNIQUE KEY uk_inscricoes_voluntario_atividade (id_voluntario, id_atividade),
    KEY idx_inscricoes_atividade (id_atividade),
    CONSTRAINT fk_inscricoes_voluntario FOREIGN KEY (id_voluntario) REFERENCES voluntarios (id_voluntario),
    CONSTRAINT fk_inscricoes_atividade FOREIGN KEY (id_atividade) REFERENCES atividades (id_atividade)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE doadores (
    id_doador INT UNSIGNED NOT NULL AUTO_INCREMENT,
    tipo ENUM('empresa', 'pessoa') NOT NULL,
    nome VARCHAR(200) NOT NULL,
    documento VARCHAR(14) NOT NULL COMMENT 'CNPJ (empresa) ou CPF (pessoa), somente números',
    email VARCHAR(150) NULL,
    telefone VARCHAR(11) NULL COMMENT 'Somente números',
    id_usuario INT UNSIGNED NULL,
    data_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_doador),
    UNIQUE KEY uk_doadores_documento (tipo, documento),
    CONSTRAINT fk_doadores_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE arrecadacoes (
    id_arrecadacao INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_projeto INT UNSIGNED NOT NULL,
    id_doador INT UNSIGNED NOT NULL,
    tipo ENUM('dinheiro', 'itens') NOT NULL DEFAULT 'dinheiro',
    valor DECIMAL(12,2) NULL,
    descricao TEXT NULL,
    data_arrecadacao DATE NOT NULL,
    comprovante VARCHAR(50) NULL,
    data_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_arrecadacao),
    KEY idx_arrecadacoes_projeto (id_projeto),
    CONSTRAINT fk_arrecadacoes_projeto FOREIGN KEY (id_projeto) REFERENCES projetos (id_projeto),
    CONSTRAINT fk_arrecadacoes_doador FOREIGN KEY (id_doador) REFERENCES doadores (id_doador)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO acoes (id_acao, descricao) VALUES
(2, 'Alteração de projeto'),
(3, 'Cadastro de atividade'),
(4, 'Alteração de atividade'),
(5, 'Cadastro de arrecadação'),
(6, 'Cadastro de doador'),
(7, 'Alteração de doador'),
(8, 'Alteração de arrecadação'),
(9, 'Cadastro de inscrição de voluntário'),
(10, 'Alteração de status de atividade'),
(11, 'Alteração de dados pessoais'),
(12, 'Cadastro de usuário'),
(13, 'Alteração de usuário'),
(14, 'Cadastro de projeto'),
(15, 'Alteração de atividade da inscrição'),
(16, 'Exclusão de inscrição de voluntário'),
(17, 'Alteração de dados do voluntário'),
(18, 'Exportação de relatório'),
(19, 'Exclusão de arrecadação');
