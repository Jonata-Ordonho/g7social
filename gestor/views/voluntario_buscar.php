<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../includes/verificar_login.php';

use App\Controllers\VoluntarioController;
use App\Helpers\Documento;
use App\Helpers\Permissao;

// Busca de voluntários para o autocomplete da inscrição em atividade
header('Content-Type: application/json; charset=utf-8');

$consulta = isset($_GET['consulta']) ? trim($_GET['consulta']) : '';

if(!Permissao::podeGerenciarVoluntarios($_SESSION['SS_UserType']) || mb_strlen($consulta) < 2){
    echo json_encode(array());
    exit;
}

$filter = array('consulta' => $consulta);

// Permite buscar CPF com ou sem pontuação
$consultaNumeros = Documento::somenteNumeros($consulta);
if($consultaNumeros != ''){
    $filter['consulta_numeros'] = $consultaNumeros;
}

$controllerVoluntario = new VoluntarioController;

$resultado = array();
foreach ($controllerVoluntario->getVolunteersList($filter, 0, 10) as $voluntario) {
    $resultado[] = array(
        'id' => (int) $voluntario['id_voluntario'],
        'nome' => $voluntario['nome'],
        'detalhe' => 'CPF '.Documento::formatarCPF($voluntario['cpf']).' - '.$voluntario['email']
    );
}

echo json_encode($resultado);
