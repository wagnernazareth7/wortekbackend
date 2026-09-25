CREATE TABLE perfis (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao VARCHAR(255) NULL,
    estado ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT uq_perfis_nome UNIQUE (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE permissoes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(150) NOT NULL,
    descricao VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT uq_permissoes_codigo UNIQUE (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE perfil_permissoes (
    perfil_id INT UNSIGNED NOT NULL,
    permissao_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (perfil_id, permissao_id),

    CONSTRAINT fk_perfil_permissoes_perfil
        FOREIGN KEY (perfil_id)
        REFERENCES perfis(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_perfil_permissoes_permissao
        FOREIGN KEY (permissao_id)
        REFERENCES permissoes(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE utilizadores (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    telefone VARCHAR(30) NULL,
    senha VARCHAR(255) NOT NULL,
    perfil_id INT UNSIGNED NOT NULL,
    estado ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    ultimo_login DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT uq_utilizadores_email UNIQUE (email),

    CONSTRAINT fk_utilizadores_perfil
        FOREIGN KEY (perfil_id)
        REFERENCES perfis(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    INDEX idx_utilizadores_perfil (perfil_id),
    INDEX idx_utilizadores_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
