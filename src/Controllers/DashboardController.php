<?php
namespace Wortek\Store\Controllers;
use Wortek\Store\Http\Response; use Wortek\Store\Repositories\DashboardRepository;
final class DashboardController { public function __construct(private DashboardRepository $r){} public function index(): never { Response::success(['indicadores'=>$this->r->indicators()]); } }
