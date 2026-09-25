INSERT INTO perfis (nome, descricao)
VALUES
('Administrador', 'Administração técnica da plataforma'),
('Gestor', 'Supervisão geral das operações da Wortek Store'),
('Comercial', 'Gestão de clientes, pedidos, solicitações e cotações'),
('Stock', 'Gestão de produtos, inventário e movimentações de stock'),
('Compras', 'Gestão de fornecedores e aquisições'),
('Logística', 'Gestão de entregas e operações logísticas'),
('Financeiro', 'Gestão de pagamentos, despesas e resultados')
ON DUPLICATE KEY UPDATE
    descricao = VALUES(descricao);