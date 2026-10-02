INSERT INTO categorias (
    nome,
    descricao,
    estado
)
VALUES
(
    'Computadores',
    'Computadores portáteis e de secretária',
    'ativo'
),
(
    'Componentes',
    'Componentes e peças informáticas',
    'ativo'
),
(
    'Periféricos',
    'Teclados, ratos, monitores e outros periféricos',
    'ativo'
),
(
    'Redes',
    'Equipamentos e acessórios de rede',
    'ativo'
),
(
    'Armazenamento',
    'Discos, SSD, pen drives e outros dispositivos de armazenamento',
    'ativo'
),
(
    'Acessórios',
    'Acessórios diversos de informática',
    'ativo'
)
ON DUPLICATE KEY UPDATE
    descricao = VALUES(descricao),
    estado = VALUES(estado);