<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../includes/verificar_login.php';

use App\Controllers\AtividadeController;
use App\Controllers\LogController;
use App\Helpers\Permissao;

$controllerAtividade = new AtividadeController;
$controllerLogs = new LogController;

// A troca de status só é aceita por formulário (POST), nunca por um link simples
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: projeto_listar.php");
    exit;
}

$idActivity = isset($_POST['id_atividade']) ? (int) $_POST['id_atividade'] : 0;

$atividade = $controllerAtividade->getActivityByID($idActivity);

if($atividade == NULL){
    header("Location: projeto_listar.php?errorInfo=true");
    exit;
}

// Somente administradores ativam/desativam atividades
if(!Permissao::podeGerenciarAtividades($_SESSION['SS_UserType'])){
    header("Location: atividade_listar.php?id_projeto=".$atividade['id_projeto']."&semPermissao=true");
    exit;
}

// Novo status desejado
$status = (isset($_POST['status']) && $_POST['status'] == 'off') ? 'off' : 'on';

if($controllerAtividade->updateActivityStatus($idActivity, $status)){
    $controllerLogs->insertActionUserLog(10, $_SESSION['SS_UserID'], 'Atividade ID '.$idActivity.' - Status '.$status);
}

header("Location: atividade_listar.php?id_projeto=".$atividade['id_projeto']."&statusActivity=".$status);
exit;
