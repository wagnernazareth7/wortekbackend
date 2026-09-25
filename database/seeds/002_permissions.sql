INSERT INTO permissoes (codigo, descricao)
VALUES
('dashboard.visualizar', 'Visualizar dashboard'),

('cliente.visualizar', 'Visualizar clientes'),
('cliente.criar', 'Registar clientes'),
('cliente.editar', 'Editar clientes'),

('produto.visualizar', 'Visualizar produtos'),
('produto.criar', 'Registar produtos'),
('produto.editar', 'Editar produtos'),

('stock.visualizar', 'Visualizar stock'),
('stock.movimentar', 'Registar movimentações de stock'),
('stock.ajustar', 'Realizar ajustes de stock'),

('pedido.visualizar', 'Visualizar pedidos'),
('pedido.criar', 'Criar pedidos'),
('pedido.editar', 'Editar pedidos'),
('pedido.cancelar', 'Cancelar pedidos'),

('solicitacao.visualizar', 'Visualizar solicitações de produtos'),
('solicitacao.criar', 'Criar solicitações de produtos'),
('solicitacao.gerir', 'Gerir solicitações de produtos'),

('cotacao.visualizar', 'Visualizar cotações'),
('cotacao.criar', 'Criar cotações'),
('cotacao.gerir', 'Gerir cotações'),

('fornecedor.visualizar', 'Visualizar fornecedores'),
('fornecedor.gerir', 'Gerir fornecedores'),

('compra.visualizar', 'Visualizar compras'),
('compra.criar', 'Criar compras'),
('compra.gerir', 'Gerir compras'),

('pagamento.visualizar', 'Visualizar pagamentos'),
('pagamento.registar', 'Registar pagamentos'),
('pagamento.confirmar', 'Confirmar pagamentos'),
('pagamento.reembolsar', 'Registar reembolsos'),

('entrega.visualizar', 'Visualizar entregas'),
('entrega.criar', 'Criar entregas'),
('entrega.gerir', 'Gerir entregas'),

('financeiro.visualizar', 'Visualizar informação financeira'),
('despesa.registar', 'Registar despesas'),

('utilizador.visualizar', 'Visualizar utilizadores'),
('utilizador.gerir', 'Gerir utilizadores'),

('perfil.gerir', 'Gerir perfis e permissões'),
('auditoria.visualizar', 'Visualizar registos de auditoria')
ON DUPLICATE KEY UPDATE
    descricao = VALUES(descricao);