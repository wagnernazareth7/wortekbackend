<?php
namespace Wortek\Store\Repositories;
use PDO;
final class AuditRepository {
 public function __construct(private PDO $pdo){}
 public function log(?int $userId,string $action,?string $entity=null,?int $entityId=null,array $data=[]): void { $s=$this->pdo->prepare('INSERT INTO auditoria(utilizador_id,acao,entidade,entidade_id,dados,ip) VALUES(:u,:a,:e,:i,:d,:ip)');$s->execute(['u'=>$userId,'a'=>$action,'e'=>$entity,'i'=>$entityId,'d'=>$data?json_encode($data,JSON_UNESCAPED_UNICODE):null,'ip'=>$_SERVER['REMOTE_ADDR']??null]); }
 public function all(): array { return $this->pdo->query('SELECT a.*,u.nome AS utilizador_nome FROM auditoria a LEFT JOIN utilizadores u ON u.id=a.utilizador_id ORDER BY a.id DESC LIMIT 500')->fetchAll(); }
}
