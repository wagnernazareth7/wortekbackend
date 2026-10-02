<?php

declare(strict_types=1);
require dirname(__DIR__) . '/vendor/autoload.php';

use Dotenv\Dotenv;
use Wortek\Store\Config\Database;
use Wortek\Store\Controllers\AuthController;
use Wortek\Store\Controllers\ClientController;
use Wortek\Store\Controllers\DashboardController;
use Wortek\Store\Controllers\OrderController;
use Wortek\Store\Controllers\ProductController;
use Wortek\Store\Controllers\PurchaseController;
use Wortek\Store\Controllers\SimpleCrudController;
use Wortek\Store\Controllers\StockController;
use Wortek\Store\Controllers\UserController;
use Wortek\Store\Http\Authorization;
use Wortek\Store\Http\Response;
use Wortek\Store\Repositories\AuditRepository;
use Wortek\Store\Repositories\ClientRepository;
use Wortek\Store\Repositories\DashboardRepository;
use Wortek\Store\Repositories\OrderRepository;
use Wortek\Store\Repositories\ProductRepository;
use Wortek\Store\Repositories\PurchaseRepository;
use Wortek\Store\Repositories\SimpleCrudRepository;
use Wortek\Store\Repositories\StockRepository;
use Wortek\Store\Repositories\UserRepository;

$root=dirname(__DIR__); Dotenv::createImmutable($root)->load();
$front=$_ENV['FRONTEND_URL']??'http://localhost:5173';
header("Access-Control-Allow-Origin: {$front}");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: Content-Type, Accept');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
if(($_SERVER['REQUEST_METHOD']??'GET')==='OPTIONS'){http_response_code(204);exit;}
session_name('wortek_session'); session_set_cookie_params(['lifetime'=>0,'path'=>'/','domain'=>'','secure'=>false,'httponly'=>true,'samesite'=>'Lax']); session_start();

$pdo=Database::getConnection();
$users=new UserRepository($pdo); $clients=new ClientRepository($pdo); $products=new ProductRepository($pdo); $stock=new StockRepository($pdo);
$authz=new Authorization($users);
$auth=new AuthController($users); $clientCtrl=new ClientController($clients); $productCtrl=new ProductController($products); $stockCtrl=new StockController($stock);
$dashboardCtrl=new DashboardController(new DashboardRepository($pdo)); $orderCtrl=new OrderController(new OrderRepository($pdo)); $purchaseCtrl=new PurchaseController(new PurchaseRepository($pdo),$stock); $userCtrl=new UserController($users); $auditRepo=new AuditRepository($pdo);

$suppliers=new SimpleCrudController(new SimpleCrudRepository($pdo,'fornecedores',['nome','nuit','email','telefone','pais','endereco','observacoes','estado','created_by'],['nome','nuit','email','telefone']),'fornecedor','fornecedores',['nome'],true);
$requests=new SimpleCrudController(new SimpleCrudRepository($pdo,'solicitacoes',['cliente_id','titulo','descricao','referencia','quantidade','origem','estado','created_by'],['titulo','descricao','referencia']),'solicitacao','solicitacoes',['cliente_id','titulo','descricao'],true);
$quotes=new SimpleCrudController(new SimpleCrudRepository($pdo,'cotacoes',['cliente_id','solicitacao_id','descricao','subtotal','custo_logistica','outros_custos','margem','entrega','total','validade','estado','observacoes','created_by'],['descricao']),'cotacao','cotacoes',['cliente_id','descricao'],true);
$payments=new SimpleCrudController(new SimpleCrudRepository($pdo,'pagamentos',['pedido_id','cliente_id','metodo','valor','referencia','estado','data_pagamento','created_by'],['referencia']),'pagamento','pagamentos',['metodo','valor'],true);
$deliveries=new SimpleCrudController(new SimpleCrudRepository($pdo,'entregas',['pedido_id','cliente_id','tipo','origem','destino','motorista','viatura','custo','estado','data_prevista','data_entrega','observacoes','created_by'],['destino','motorista','viatura']),'entrega','entregas',['destino'],true);
$expenses=new SimpleCrudController(new SimpleCrudRepository($pdo,'despesas',['categoria','descricao','valor','data_despesa','unidade_negocio','created_by'],['categoria','descricao']),'despesa','despesas',['categoria','descricao','valor','data_despesa'],true);

$method=$_SERVER['REQUEST_METHOD']; $path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH); $path=rtrim((string)$path,'/'); if($path==='')$path='/';

if($path==='/health'&&$method==='GET') Response::success(['message'=>'Wortek Store API funcionando.','database'=>'connected']);
if($path==='/login'&&$method==='POST') $auth->login();
if($path==='/me'&&$method==='GET') $auth->me();
if($path==='/logout'&&$method==='POST') $auth->logout();

if($path==='/dashboard'&&$method==='GET'){ $authz->requirePermission('dashboard.visualizar'); $dashboardCtrl->index(); }

if($path==='/clientes'&&$method==='GET'){ $authz->requirePermission('cliente.visualizar'); $clientCtrl->index(); }
if(preg_match('#^/clientes/(\d+)$#',$path,$m)&&$method==='GET'){ $authz->requirePermission('cliente.visualizar'); $clientCtrl->show((int)$m[1]); }
if($path==='/clientes'&&$method==='POST'){ $u=$authz->requirePermission('cliente.criar'); $clientCtrl->store((int)$u['id']); }
if(preg_match('#^/clientes/(\d+)$#',$path,$m)&&$method==='PUT'){ $authz->requirePermission('cliente.editar'); $clientCtrl->update((int)$m[1]); }

if($path==='/categorias'&&$method==='GET'){ $authz->requirePermission('produto.visualizar'); $productCtrl->categories(); }
if($path==='/produtos'&&$method==='GET'){ $authz->requirePermission('produto.visualizar'); $productCtrl->index(); }
if(preg_match('#^/produtos/(\d+)$#',$path,$m)&&$method==='GET'){ $authz->requirePermission('produto.visualizar'); $productCtrl->show((int)$m[1]); }
if($path==='/produtos'&&$method==='POST'){ $u=$authz->requirePermission('produto.criar'); $productCtrl->store((int)$u['id']); }
if(preg_match('#^/produtos/(\d+)$#',$path,$m)&&$method==='PUT'){ $authz->requirePermission('produto.editar'); $productCtrl->update((int)$m[1]); }

if($path==='/stock'&&$method==='GET'){ $authz->requirePermission('stock.visualizar'); $stockCtrl->index(); }
if($path==='/stock/movimentacoes'&&$method==='GET'){ $authz->requirePermission('stock.visualizar'); $stockCtrl->movements(); }
if($path==='/stock/movimentacoes'&&$method==='POST'){ $u=$authz->requireAuthentication(); $input=json_decode(file_get_contents('php://input'),true); if(!is_array($input))Response::error('Corpo da requisição inválido.',400); $type=(string)($input['tipo']??''); $perm=in_array($type,['ajuste_entrada','ajuste_saida'],true)?'stock.ajustar':'stock.movimentar'; $u=$authz->requirePermission($perm); $stockCtrl->move((int)$u['id'],$input); }
if(preg_match('#^/stock/(\d+)$#',$path,$m)&&$method==='GET'){ $authz->requirePermission('stock.visualizar'); $stockCtrl->show((int)$m[1]); }

function simpleRoutes(string $base, string $viewPerm, string $writePerm, SimpleCrudController $ctrl, Authorization $authz, string $method, string $path): void {
    if($path===$base&&$method==='GET'){ $authz->requirePermission($viewPerm); $ctrl->index(); }
    if(preg_match('#^'.preg_quote($base,'#').'/(\d+)$#',$path,$m)&&$method==='GET'){ $authz->requirePermission($viewPerm); $ctrl->show((int)$m[1]); }
    if($path===$base&&$method==='POST'){ $u=$authz->requirePermission($writePerm); $ctrl->store((int)$u['id']); }
    if(preg_match('#^'.preg_quote($base,'#').'/(\d+)$#',$path,$m)&&$method==='PUT'){ $authz->requirePermission($writePerm); $ctrl->update((int)$m[1]); }
}
simpleRoutes('/fornecedores','fornecedor.visualizar','fornecedor.gerir',$suppliers,$authz,$method,$path);
simpleRoutes('/solicitacoes','solicitacao.visualizar','solicitacao.gerir',$requests,$authz,$method,$path);
simpleRoutes('/cotacoes','cotacao.visualizar','cotacao.gerir',$quotes,$authz,$method,$path);
simpleRoutes('/pagamentos','pagamento.visualizar','pagamento.registar',$payments,$authz,$method,$path);
simpleRoutes('/entregas','entrega.visualizar','entrega.gerir',$deliveries,$authz,$method,$path);
simpleRoutes('/despesas','financeiro.visualizar','despesa.registar',$expenses,$authz,$method,$path);

if($path==='/pedidos'&&$method==='GET'){ $authz->requirePermission('pedido.visualizar'); $orderCtrl->index(); }
if(preg_match('#^/pedidos/(\d+)$#',$path,$m)&&$method==='GET'){ $authz->requirePermission('pedido.visualizar'); $orderCtrl->show((int)$m[1]); }
if($path==='/pedidos'&&$method==='POST'){ $u=$authz->requirePermission('pedido.criar'); $orderCtrl->store((int)$u['id']); }
if(preg_match('#^/pedidos/(\d+)/estado$#',$path,$m)&&$method==='PUT'){ $authz->requirePermission('pedido.editar'); $orderCtrl->status((int)$m[1]); }

if($path==='/compras'&&$method==='GET'){ $authz->requirePermission('compra.visualizar'); $purchaseCtrl->index(); }
if(preg_match('#^/compras/(\d+)$#',$path,$m)&&$method==='GET'){ $authz->requirePermission('compra.visualizar'); $purchaseCtrl->show((int)$m[1]); }
if($path==='/compras'&&$method==='POST'){ $u=$authz->requirePermission('compra.criar'); $purchaseCtrl->store((int)$u['id']); }
if(preg_match('#^/compras/(\d+)/receber$#',$path,$m)&&$method==='POST'){ $u=$authz->requirePermission('compra.gerir'); $purchaseCtrl->receive((int)$m[1],(int)$u['id']); }

if($path==='/utilizadores'&&$method==='GET'){ $authz->requirePermission('utilizador.visualizar'); $userCtrl->index(); }
if($path==='/utilizadores'&&$method==='POST'){ $authz->requirePermission('utilizador.gerir'); $userCtrl->store(); }
if(preg_match('#^/utilizadores/(\d+)$#',$path,$m)&&$method==='PUT'){ $authz->requirePermission('utilizador.gerir'); $userCtrl->update((int)$m[1]); }
if($path==='/auditoria'&&$method==='GET'){ $authz->requirePermission('auditoria.visualizar'); Response::success(['auditoria'=>$auditRepo->all()]); }

Response::error('Rota não encontrada.',404);
