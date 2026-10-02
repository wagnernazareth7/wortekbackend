<?php
namespace Wortek\Store\Controllers;
use Wortek\Store\Http\Response;
use Wortek\Store\Repositories\SimpleCrudRepository;
final class SimpleCrudController {
    public function __construct(private SimpleCrudRepository $repo,private string $singular,private string $plural,private array $required=[],private bool $withCreatedBy=false) {}
    public function index(): never { $q=isset($_GET['search'])?trim((string)$_GET['search']):null; Response::success([$this->plural=>$this->repo->all($q)]); }
    public function show(int $id): never { $r=$this->repo->find($id); if(!$r) Response::error(ucfirst($this->singular).' não encontrado.',404); Response::success([$this->singular=>$r]); }
    public function store(int $userId): never { $d=$this->body(); foreach($this->required as $f) if(!isset($d[$f])||trim((string)$d[$f])==='') Response::error("Campo {$f} é obrigatório.",422); if($this->withCreatedBy)$d['created_by']=$userId; $id=$this->repo->create($d); Response::success(['message'=>ucfirst($this->singular).' registado com sucesso.',$this->singular=>$this->repo->find($id)],201); }
    public function update(int $id): never { if(!$this->repo->find($id)) Response::error(ucfirst($this->singular).' não encontrado.',404); $d=$this->body(); foreach($this->required as $f) if(array_key_exists($f,$d)&&trim((string)$d[$f])==='') Response::error("Campo {$f} é obrigatório.",422); $this->repo->update($id,$d); Response::success(['message'=>ucfirst($this->singular).' actualizado com sucesso.',$this->singular=>$this->repo->find($id)]); }
    private function body(): array { $d=json_decode(file_get_contents('php://input'),true); if(!is_array($d)) Response::error('Corpo da requisição inválido.',400); return $d; }
}
