<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../includes/verificar_login.php';

use App\Controllers\AtividadeController;
use App\Controllers\LogController;

$controllerAtividade = new AtividadeController;
$controllerLogs = new LogController;

$idActivity = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$atividade = $controllerAtividade->getActivityByID($idActivity);

if($atividade == NULL){
    header("Location: projeto_listar.php?errorInfo=true");
    exit;
}

// Novo status desejado
$status = (isset($_GET['status']) && $_GET['status'] == 'off') ? 'off' : 'on';

if($controllerAtividade->updateActivityStatus($idActivity, $status)){
    $controllerLogs->insertActionUserLog(10, $_SESSION['SS_UserID'], 'Atividade ID '.$idActivity.' - Status '.$status);
}

header("Location: atividade_listar.php?id_projeto=".$atividade['id_projeto']."&statusActivity=".$status);
exit;
