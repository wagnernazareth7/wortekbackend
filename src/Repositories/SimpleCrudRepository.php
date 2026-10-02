<?php
namespace Wortek\Store\Repositories;
use PDO;
use RuntimeException;
final class SimpleCrudRepository {
    public function __construct(private PDO $pdo,private string $table,private array $fields,private array $searchFields=[]) { if(!preg_match('/^[a-z_]+$/',$table)) throw new RuntimeException('Tabela inválida.'); }
    public function all(?string $search=null): array {
        $sql='SELECT * FROM '.$this->table; $params=[];
        if($search!==null&&trim($search)!==''&&$this->searchFields){ $parts=[]; foreach($this->searchFields as $i=>$f){ $k='q'.$i; $parts[]="$f LIKE :$k"; $params[$k]='%'.trim($search).'%'; } $sql.=' WHERE '.implode(' OR ',$parts); }
        $sql.=' ORDER BY id DESC'; $s=$this->pdo->prepare($sql); $s->execute($params); return $s->fetchAll();
    }
    public function find(int $id): ?array { $s=$this->pdo->prepare('SELECT * FROM '.$this->table.' WHERE id=:id LIMIT 1'); $s->execute(['id'=>$id]); $r=$s->fetch(); return $r?:null; }
    public function create(array $data): int { $d=array_intersect_key($data,array_flip($this->fields)); $cols=array_keys($d); $sql='INSERT INTO '.$this->table.'('.implode(',',$cols).') VALUES(:'.implode(',:',$cols).')'; $s=$this->pdo->prepare($sql); $s->execute($d); return (int)$this->pdo->lastInsertId(); }
    public function update(int $id,array $data): void { $d=array_intersect_key($data,array_flip($this->fields)); $sets=[]; foreach(array_keys($d) as $c)$sets[]="$c=:$c"; $d['id']=$id; $s=$this->pdo->prepare('UPDATE '.$this->table.' SET '.implode(',',$sets).' WHERE id=:id'); $s->execute($d); }
}
