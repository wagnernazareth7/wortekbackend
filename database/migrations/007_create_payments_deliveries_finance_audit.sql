CREATE TABLE pagamentos (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 pedido_id INT UNSIGNED NULL,
 cliente_id INT UNSIGNED NULL,
 metodo ENUM('mpesa','emola','transferencia','numerario','outro') NOT NULL,
 valor DECIMAL(15,2) NOT NULL,
 referencia VARCHAR(120) NULL,
 estado ENUM('pendente','confirmado','reembolsado','cancelado') NOT NULL DEFAULT 'pendente',
 data_pagamento DATETIME NULL,
 created_by INT UNSIGNED NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(pedido_id) REFERENCES pedidos(id) ON DELETE SET NULL,
 FOREIGN KEY(cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
 FOREIGN KEY(created_by) REFERENCES utilizadores(id) ON DELETE SET NULL,
 INDEX(estado),INDEX(pedido_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE entregas (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 pedido_id INT UNSIGNED NULL,
 cliente_id INT UNSIGNED NULL,
 tipo ENUM('pedido','transporte_independente') NOT NULL DEFAULT 'pedido',
 origem VARCHAR(255) NULL,
 destino VARCHAR(255) NOT NULL,
 motorista VARCHAR(150) NULL,
 viatura VARCHAR(120) NULL,
 custo DECIMAL(15,2) NOT NULL DEFAULT 0,
 estado ENUM('pendente','agendada','em_transito','entregue','cancelada') NOT NULL DEFAULT 'pendente',
 data_prevista DATETIME NULL,
 data_entrega DATETIME NULL,
 observacoes TEXT NULL,
 created_by INT UNSIGNED NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY(pedido_id) REFERENCES pedidos(id) ON DELETE SET NULL,
 FOREIGN KEY(cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
 FOREIGN KEY(created_by) REFERENCES utilizadores(id) ON DELETE SET NULL,
 INDEX(estado),INDEX(pedido_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE despesas (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 categoria VARCHAR(100) NOT NULL,
 descricao VARCHAR(255) NOT NULL,
 valor DECIMAL(15,2) NOT NULL,
 data_despesa DATE NOT NULL,
 unidade_negocio ENUM('comercio','encomendas','construcao','logistica','geral') NOT NULL DEFAULT 'geral',
 created_by INT UNSIGNED NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(created_by) REFERENCES utilizadores(id) ON DELETE SET NULL,
 INDEX(data_despesa),INDEX(unidade_negocio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE auditoria (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 utilizador_id INT UNSIGNED NULL,
 acao VARCHAR(120) NOT NULL,
 entidade VARCHAR(100) NULL,
 entidade_id INT UNSIGNED NULL,
 dados JSON NULL,
 ip VARCHAR(64) NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(utilizador_id) REFERENCES utilizadores(id) ON DELETE SET NULL,
 INDEX(utilizador_id),INDEX(acao),INDEX(entidade,entidade_id),INDEX(created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
