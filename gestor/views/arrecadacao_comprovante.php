<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../includes/verificar_login.php';

use App\Controllers\ArrecadacaoController;
use App\Helpers\Comprovante;

$controllerArrecadacao = new ArrecadacaoController;

$idDonation = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$arrecadacao = $controllerArrecadacao->getDonationByID($idDonation);

if($arrecadacao == NULL || !isset($arrecadacao['comprovante']) || !Comprovante::existe($arrecadacao['comprovante'])){
    header("Location: projeto_listar.php?errorInfo=true");
    exit;
}

$arquivo = Comprovante::caminho($arrecadacao['comprovante']);
$extensao = pathinfo($arquivo, PATHINFO_EXTENSION);

// Abre no navegador (imagem/PDF), com nome amigável caso seja baixado
header('Content-Type: '.Comprovante::tipo($arquivo));
header('Content-Length: '.filesize($arquivo));
header('Content-Disposition: inline; filename="comprovante-arrecadacao-'.$idDonation.'.'.$extensao.'"');
header('X-Content-Type-Options: nosniff');
readfile($arquivo);
exit;
