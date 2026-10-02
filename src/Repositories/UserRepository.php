<?php
namespace Wortek\Store\Repositories;
use PDO;
final class UserRepository {
    public function __construct(private PDO $pdo) {}
    public function findByEmail(string $email): ?array {
        $s=$this->pdo->prepare('SELECT u.*,p.nome AS perfil_nome FROM utilizadores u JOIN perfis p ON p.id=u.perfil_id WHERE u.email=:email LIMIT 1'); $s->execute(['email'=>$email]); $u=$s->fetch(); return $u?:null;
    }
    public function findById(int $id): ?array {
        $s=$this->pdo->prepare('SELECT u.id,u.nome,u.email,u.telefone,u.estado,u.ultimo_login,u.perfil_id,p.nome AS perfil_nome FROM utilizadores u JOIN perfis p ON p.id=u.perfil_id WHERE u.id=:id LIMIT 1');
        $s->execute(['id'=>$id]); $u=$s->fetch(); if(!$u) return null; $u['permissoes']=$this->getPermissions((int)$u['perfil_id']); return $u;
    }
    public function getPermissions(int $perfilId): array {
        $s=$this->pdo->prepare('SELECT pm.codigo FROM permissoes pm JOIN perfil_permissoes pp ON pp.permissao_id=pm.id WHERE pp.perfil_id=:id ORDER BY pm.codigo'); $s->execute(['id'=>$perfilId]); return $s->fetchAll(PDO::FETCH_COLUMN);
    }
    public function updateLastLogin(int $id): void { $s=$this->pdo->prepare('UPDATE utilizadores SET ultimo_login=NOW() WHERE id=:id'); $s->execute(['id'=>$id]); }
    public function all(): array { return $this->pdo->query('SELECT u.id,u.nome,u.email,u.telefone,u.estado,u.perfil_id,p.nome AS perfil_nome,u.created_at FROM utilizadores u JOIN perfis p ON p.id=u.perfil_id ORDER BY u.nome')->fetchAll(); }
    public function profiles(): array { return $this->pdo->query("SELECT id,nome,descricao FROM perfis WHERE estado='ativo' ORDER BY nome")->fetchAll(); }
    public function create(array $d): int { $s=$this->pdo->prepare('INSERT INTO utilizadores(nome,email,telefone,senha,perfil_id,estado) VALUES(:nome,:email,:telefone,:senha,:perfil_id,:estado)'); $s->execute($d); return (int)$this->pdo->lastInsertId(); }
    public function update(int $id,array $d): void { $s=$this->pdo->prepare('UPDATE utilizadores SET nome=:nome,email=:email,telefone=:telefone,perfil_id=:perfil_id,estado=:estado WHERE id=:id'); $s->execute([...$d,'id'=>$id]); }
}
