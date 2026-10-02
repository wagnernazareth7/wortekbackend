<?php
namespace Wortek\Store\Repositories;
use PDO;
final class DashboardRepository {
 public function __construct(private PDO $pdo){}
 private function scalar(string $sql){return $this->pdo->query($sql)->fetchColumn();}
 public function indicators(): array { return [
  'clientes'=>(int)$this->scalar("SELECT COUNT(*) FROM clientes WHERE estado='ativo'"),
  'produtos'=>(int)$this->scalar("SELECT COUNT(*) FROM produtos WHERE estado='ativo'"),
  'pedidos_abertos'=>(int)$this->scalar("SELECT COUNT(*) FROM pedidos WHERE estado NOT IN ('concluido','cancelado')"),
  'entregas_pendentes'=>(int)$this->scalar("SELECT COUNT(*) FROM entregas WHERE estado NOT IN ('entregue','cancelada')"),
  'stock_baixo'=>(int)$this->scalar("SELECT COUNT(*) FROM stock s JOIN produtos p ON p.id=s.produto_id WHERE s.quantidade<=p.stock_minimo"),
  'receita_confirmada'=>(float)$this->scalar("SELECT COALESCE(SUM(valor),0) FROM pagamentos WHERE estado='confirmado'"),
  'despesas'=>(float)$this->scalar("SELECT COALESCE(SUM(valor),0) FROM despesas"),
 ]; }
}
