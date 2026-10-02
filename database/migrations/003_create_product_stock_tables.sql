CREATE TABLE categorias (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    descricao VARCHAR(255) NULL,
    estado ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_categorias_nome (nome),
    KEY idx_categorias_estado (estado)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


CREATE TABLE produtos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    categoria_id INT UNSIGNED NULL,

    sku VARCHAR(60) NOT NULL,
    nome VARCHAR(180) NOT NULL,
    descricao TEXT NULL,

    marca VARCHAR(120) NULL,
    modelo VARCHAR(120) NULL,

    unidade VARCHAR(30) NOT NULL DEFAULT 'unidade',

    preco_compra DECIMAL(15,2) NULL,
    preco_venda DECIMAL(15,2) NOT NULL DEFAULT 0.00,

    stock_minimo DECIMAL(15,3) NOT NULL DEFAULT 0.000,

    estado ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',

    created_by INT UNSIGNED NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_produtos_sku (sku),

    KEY idx_produtos_nome (nome),
    KEY idx_produtos_categoria (categoria_id),
    KEY idx_produtos_estado (estado),
    KEY idx_produtos_marca (marca),

    CONSTRAINT fk_produtos_categoria
        FOREIGN KEY (categoria_id)
        REFERENCES categorias(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    CONSTRAINT fk_produtos_created_by
        FOREIGN KEY (created_by)
        REFERENCES utilizadores(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


CREATE TABLE stock (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    produto_id INT UNSIGNED NOT NULL,

    quantidade DECIMAL(15,3) NOT NULL DEFAULT 0.000,
    quantidade_reservada DECIMAL(15,3) NOT NULL DEFAULT 0.000,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_stock_produto (produto_id),

    CONSTRAINT fk_stock_produto
        FOREIGN KEY (produto_id)
        REFERENCES produtos(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


CREATE TABLE movimentacoes_stock (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    produto_id INT UNSIGNED NOT NULL,

    tipo ENUM(
        'entrada',
        'saida',
        'ajuste_entrada',
        'ajuste_saida'
    ) NOT NULL,

    quantidade DECIMAL(15,3) NOT NULL,

    stock_anterior DECIMAL(15,3) NOT NULL,
    stock_posterior DECIMAL(15,3) NOT NULL,

    origem_tipo VARCHAR(50) NULL,
    origem_id INT UNSIGNED NULL,

    observacoes VARCHAR(500) NULL,

    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    KEY idx_mov_stock_produto (produto_id),
    KEY idx_mov_stock_tipo (tipo),
    KEY idx_mov_stock_data (created_at),
    KEY idx_mov_stock_origem (origem_tipo, origem_id),

    CONSTRAINT fk_mov_stock_produto
        FOREIGN KEY (produto_id)
        REFERENCES produtos(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_mov_stock_created_by
        FOREIGN KEY (created_by)
        REFERENCES utilizadores(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;