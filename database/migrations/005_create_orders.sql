CREATE TABLE pedidos (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 numero VARCHAR(40) NOT NULL UNIQUE,
 cliente_id INT UNSIGNED NOT NULL,
 tipo ENUM('stock','encomenda','transporte','construcao') NOT NULL DEFAULT 'stock',
 estado ENUM('rascunho','confirmado','em_preparacao','em_entrega','concluido','cancelado') NOT NULL DEFAULT 'rascunho',
 subtotal DECIMAL(15,2) NOT NULL DEFAULT 0,
 entrega DECIMAL(15,2) NOT NULL DEFAULT 0,
 desconto DECIMAL(15,2) NOT NULL DEFAULT 0,
 total DECIMAL(15,2) NOT NULL DEFAULT 0,
 observacoes TEXT NULL,
 created_by INT UNSIGNED NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY(cliente_id) REFERENCES clientes(id) ON DELETE RESTRICT,
 FOREIGN KEY(created_by) REFERENCES utilizadores(id) ON DELETE SET NULL,
 INDEX(cliente_id),INDEX(estado),INDEX(tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE pedido_itens (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 pedido_id INT UNSIGNED NOT NULL,
 produto_id INT UNSIGNED NULL,
 descricao VARCHAR(255) NOT NULL,
 quantidade DECIMAL(15,3) NOT NULL,
 preco_unitario DECIMAL(15,2) NOT NULL,
 total DECIMAL(15,2) NOT NULL,
 FOREIGN KEY(pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
 FOREIGN KEY(produto_id) REFERENCES produtos(id) ON DELETE SET NULL,
 INDEX(pedido_id),INDEX(produto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
