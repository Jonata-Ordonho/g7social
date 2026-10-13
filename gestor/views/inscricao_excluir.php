<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../includes/verificar_login.php';

use App\Controllers\InscricaoController;
use App\Controllers\LogController;
use App\Helpers\Permissao;

$controllerInscricao = new InscricaoController;
$controllerLogs = new LogController;

// A exclusão só é aceita por formulário (POST), nunca por um link simples
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: projeto_listar.php");
    exit;
}

$idInscricao = isset($_POST['id_inscricao']) ? (int) $_POST['id_inscricao'] : 0;
$inscricao = $controllerInscricao->getInscriptionByID($idInscricao);

if($inscricao == NULL){
    header("Location: projeto_listar.php?errorInfo=true");
    exit;
}

$voltar = "inscricao_listar.php?id_atividade=".$inscricao['id_atividade'];

if(!Permissao::podeExcluirVoluntario($_SESSION['SS_UserType'])){
    header("Location: ".$voltar."&semPermissao=true");
    exit;
}

// Remove apenas a inscrição: o voluntário continua no banco de voluntários
if($controllerInscricao->deleteInscription($idInscricao)){
    $controllerLogs->insertActionUserLog(16, $_SESSION['SS_UserID'], 'Inscrição ID '.$idInscricao.' - voluntário ID '.$inscricao['id_voluntario'].' ('.$inscricao['nome'].') - Atividade ID '.$inscricao['id_atividade']);
    header("Location: ".$voltar."&deletedVolunteer=true");
}else{
    header("Location: ".$voltar."&errorDelete=true");
}
exit;
