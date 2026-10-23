<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../includes/verificar_login.php';

use App\Controllers\DoadorController;
use App\Helpers\Documento;

// Busca de doadores para o autocomplete do formulário de arrecadação
header('Content-Type: application/json; charset=utf-8');

$consulta = isset($_GET['consulta']) ? trim($_GET['consulta']) : '';

if(mb_strlen($consulta) < 2){
    echo json_encode(array());
    exit;
}

$filter = array('consulta' => $consulta);

// Permite buscar CPF/CNPJ com ou sem pontuação
$consultaNumeros = Documento::somenteNumeros($consulta);
if($consultaNumeros != ''){
    $filter['consulta_numeros'] = $consultaNumeros;
}

$controllerDoador = new DoadorController;

$resultado = array();
foreach ($controllerDoador->getDonorsList($filter, 0, 10) as $doador) {
    $resultado[] = array(
        'id' => (int) $doador['id_doador'],
        'nome' => $doador['nome'],
        'detalhe' => $doador['tipo'] == 'empresa' ? 'CNPJ '.Documento::formatarCNPJ($doador['documento']) : 'CPF '.Documento::formatarCPF($doador['documento'])
    );
}

echo json_encode($resultado);
