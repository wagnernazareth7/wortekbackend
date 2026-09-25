-- ADMINISTRADOR
-- Acesso a todas as permissões existentes.
INSERT IGNORE INTO perfil_permissoes (perfil_id, permissao_id)
SELECT p.id, pm.id
FROM perfis p
CROSS JOIN permissoes pm
WHERE p.nome = 'Administrador';


-- GESTOR
-- Gestão transversal das operações.
INSERT IGNORE INTO perfil_permissoes (perfil_id, permissao_id)
SELECT p.id, pm.id
FROM perfis p
JOIN permissoes pm
WHERE p.nome = 'Gestor'
AND pm.codigo IN (
    'dashboard.visualizar',

    'cliente.visualizar',
    'cliente.criar',
    'cliente.editar',

    'produto.visualizar',
    'produto.criar',
    'produto.editar',

    'stock.visualizar',
    'stock.movimentar',
    'stock.ajustar',

    'pedido.visualizar',
    'pedido.criar',
    'pedido.editar',
    'pedido.cancelar',

    'solicitacao.visualizar',
    'solicitacao.criar',
    'solicitacao.gerir',

    'cotacao.visualizar',
    'cotacao.criar',
    'cotacao.gerir',

    'fornecedor.visualizar',
    'fornecedor.gerir',

    'compra.visualizar',
    'compra.criar',
    'compra.gerir',

    'pagamento.visualizar',
    'pagamento.registar',
    'pagamento.confirmar',
    'pagamento.reembolsar',

    'entrega.visualizar',
    'entrega.criar',
    'entrega.gerir',

    'financeiro.visualizar',
    'despesa.registar',

    'utilizador.visualizar',
    'auditoria.visualizar'
);


-- COMERCIAL
INSERT IGNORE INTO perfil_permissoes (perfil_id, permissao_id)
SELECT p.id, pm.id
FROM perfis p
JOIN permissoes pm
WHERE p.nome = 'Comercial'
AND pm.codigo IN (
    'dashboard.visualizar',
    'cliente.visualizar',
    'cliente.criar',
    'cliente.editar',
    'produto.visualizar',
    'stock.visualizar',
    'pedido.visualizar',
    'pedido.criar',
    'pedido.editar',
    'pedido.cancelar',
    'solicitacao.visualizar',
    'solicitacao.criar',
    'solicitacao.gerir',
    'cotacao.visualizar',
    'cotacao.criar',
    'cotacao.gerir',
    'pagamento.visualizar',
    'entrega.visualizar'
);


-- STOCK
INSERT IGNORE INTO perfil_permissoes (perfil_id, permissao_id)
SELECT p.id, pm.id
FROM perfis p
JOIN permissoes pm
WHERE p.nome = 'Stock'
AND pm.codigo IN (
    'dashboard.visualizar',
    'produto.visualizar',
    'produto.criar',
    'produto.editar',
    'stock.visualizar',
    'stock.movimentar',
    'stock.ajustar',
    'pedido.visualizar',
    'compra.visualizar'
);


-- COMPRAS
INSERT IGNORE INTO perfil_permissoes (perfil_id, permissao_id)
SELECT p.id, pm.id
FROM perfis p
JOIN permissoes pm
WHERE p.nome = 'Compras'
AND pm.codigo IN (
    'dashboard.visualizar',
    'produto.visualizar',
    'stock.visualizar',
    'solicitacao.visualizar',
    'fornecedor.visualizar',
    'fornecedor.gerir',
    'compra.visualizar',
    'compra.criar',
    'compra.gerir'
);


-- LOGÍSTICA
INSERT IGNORE INTO perfil_permissoes (perfil_id, permissao_id)
SELECT p.id, pm.id
FROM perfis p
JOIN permissoes pm
WHERE p.nome = 'Logística'
AND pm.codigo IN (
    'dashboard.visualizar',
    'cliente.visualizar',
    'pedido.visualizar',
    'produto.visualizar',
    'entrega.visualizar',
    'entrega.criar',
    'entrega.gerir'
);


-- FINANCEIRO
INSERT IGNORE INTO perfil_permissoes (perfil_id, permissao_id)
SELECT p.id, pm.id
FROM perfis p
JOIN permissoes pm
WHERE p.nome = 'Financeiro'
AND pm.codigo IN (
    'dashboard.visualizar',
    'cliente.visualizar',
    'pedido.visualizar',
    'cotacao.visualizar',
    'compra.visualizar',
    'pagamento.visualizar',
    'pagamento.registar',
    'pagamento.confirmar',
    'pagamento.reembolsar',
    'financeiro.visualizar',
    'despesa.registar'
);