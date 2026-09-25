CREATE TABLE clientes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    tipo ENUM('particular', 'empresa')
        NOT NULL DEFAULT 'particular',

    nome VARCHAR(180) NOT NULL,

    nuit VARCHAR(30) NULL,

    email VARCHAR(150) NULL,

    telefone VARCHAR(30) NOT NULL,

    telefone_alternativo VARCHAR(30) NULL,

    endereco VARCHAR(255) NULL,

    cidade VARCHAR(100) NULL,

    observacoes TEXT NULL,

    estado ENUM('ativo', 'inativo')
        NOT NULL DEFAULT 'ativo',

    created_by INT UNSIGNED NULL,

    created_at TIMESTAMP
        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP
        NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_clientes_created_by
        FOREIGN KEY (created_by)
        REFERENCES utilizadores(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    INDEX idx_clientes_nome (nome),
    INDEX idx_clientes_telefone (telefone),
    INDEX idx_clientes_email (email),
    INDEX idx_clientes_nuit (nuit),
    INDEX idx_clientes_estado (estado)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;