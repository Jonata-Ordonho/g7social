<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../includes/verificar_login.php';

use App\Controllers\ArrecadacaoController;
use App\Controllers\LogController;
use App\Helpers\Comprovante;
use App\Helpers\Permissao;

$controllerArrecadacao = new ArrecadacaoController;
$controllerLogs = new LogController;

// A exclusão só é aceita por formulário (POST), nunca por um link simples
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: projeto_listar.php");
    exit;
}

$idDonation = isset($_POST['id_arrecadacao']) ? (int) $_POST['id_arrecadacao'] : 0;
$arrecadacao = $controllerArrecadacao->getDonationByID($idDonation);

if($arrecadacao == NULL){
    header("Location: projeto_listar.php?errorInfo=true");
    exit;
}

$voltar = "arrecadacao_listar.php?id_projeto=".$arrecadacao['id_projeto'];

if(!Permissao::podeExcluirArrecadacao($_SESSION['SS_UserType'])){
    header("Location: ".$voltar."&semPermissao=true");
    exit;
}

if($controllerArrecadacao->deleteDonation($idDonation)){
    Comprovante::excluir(isset($arrecadacao['comprovante']) ? (string) $arrecadacao['comprovante'] : '');

    $detalhe = $arrecadacao['tipo'] == 'dinheiro' ? 'R$ '.number_format($arrecadacao['valor'], 2, ',', '.') : 'itens: '.$arrecadacao['descricao'];
    $controllerLogs->insertActionUserLog(19, $_SESSION['SS_UserID'], 'Arrecadação ID '.$idDonation.' - Projeto ID '.$arrecadacao['id_projeto'].' - Doador ID '.$arrecadacao['id_doador'].' - '.$detalhe);
    header("Location: ".$voltar."&deletedDonation=true");
}else{
    header("Location: ".$voltar."&errorDelete=true");
}
exit;
