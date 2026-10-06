<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../includes/verificar_login.php';

use App\Controllers\DoadorController;
use App\Controllers\ArrecadacaoController;
use App\Helpers\Documento;

$controllerDoador = new DoadorController;
$controllerArrecadacao = new ArrecadacaoController;

$idDonor = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$doador = $controllerDoador->getDonorByID($idDonor);

if($doador == NULL){
    header("Location: doador_listar.php?errorInfo=true");
    exit;
}

$arrecadacoes = $controllerArrecadacao->getDonationsByDonor($idDonor);

$totalValor = 0;
$temDinheiro = false;
foreach ($arrecadacoes as $arrecadacao) {
    if($arrecadacao['valor'] != NULL){
        $totalValor += $arrecadacao['valor'];
        $temDinheiro = true;
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>G7 Social - Sistema de Atividades Sociais do Grupo Sete</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="GTEC - Gerência de Tecnologia">
    <meta name="reply-to" content="web@unirios.edu.br">
    <meta name="robots" content="noindex,nofollow">
    <link href="../css/css.css" rel="stylesheet" type="text/css" media="screen">
    <link rel="icon" href="../images/favicon.png">
</head>

<body>
<?php include '../includes/topo.html'; ?>
<?php include '../includes/menu.php'; ?>
<div id="container">
    <div id="conteudo">
        <h2 class="titulo titulo-acao">
            <span>Doador - <b><?php echo htmlspecialchars($doador['nome']); ?></b></span>
            <a href="doador_alterar.php?id=<?php echo $idDonor; ?>" title="Alterar dados do doador" class="but-novo icon-pencil">Alterar</a>
        </h2>

        <table id="tab-detalhes" cellspacing="0" cellpadding="0">
            <tr>
                <td class="rotulo">ID:</td>
                <td><?php echo $doador['id_doador']; ?></td>
            </tr>
            <tr>
                <td class="rotulo">Tipo:</td>
                <td><?php echo $doador['tipo'] == 'empresa' ? 'Empresa' : 'Pessoa'; ?></td>
            </tr>
            <tr>
                <td class="rotulo">Nome:</td>
                <td><?php echo htmlspecialchars($doador['nome']); ?></td>
            </tr>
            <tr>
                <td class="rotulo"><?php echo $doador['tipo'] == 'empresa' ? 'CNPJ:' : 'CPF:'; ?></td>
                <td><?php echo $doador['tipo'] == 'empresa' ? Documento::formatarCNPJ($doador['documento']) : Documento::formatarCPF($doador['documento']); ?></td>
            </tr>
            <tr>
                <td class="rotulo">E-mail:</td>
                <td><?php echo $doador['email'] != NULL ? htmlspecialchars($doador['email']) : '<i>Não informado</i>'; ?></td>
            </tr>
            <tr>
                <td class="rotulo">Telefone:</td>
                <td><?php echo $doador['telefone'] != NULL ? Documento::formatarTelefone($doador['telefone']) : '<i>Não informado</i>'; ?></td>
            </tr>
            <tr>
                <td class="rotulo">Cadastrado em:</td>
                <td><?php echo date('d/m/Y \à\s H:i', strtotime($doador['data_registro'])); ?></td>
            </tr>
        </table>

        <h3>Doações realizadas (<?php echo count($arrecadacoes); ?>)</h3>

        <?php if(count($arrecadacoes) > 0){ ?>
            <table cellspacing="0" cellpadding="0" class="tab-listagem">
                <thead>
                <tr>
                    <th width="50">ID</th>
                    <th width="100">Data</th>
                    <th>Projeto</th>
                    <th width="90">Tipo</th>
                    <th>Descrição/Valor</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($arrecadacoes as $arrecadacao) { ?>
                    <tr>
                        <td class="id"><?php echo $arrecadacao['id_arrecadacao']; ?></td>
                        <td align="center"><?php echo date('d/m/Y', strtotime($arrecadacao['data_arrecadacao'])); ?></td>
                        <td class="nome"><a href="arrecadacao_listar.php?id_projeto=<?php echo $arrecadacao['id_projeto']; ?>" title="Ver arrecadações do projeto"><b><?php echo htmlspecialchars($arrecadacao['projeto_titulo']); ?></b></a></td>
                        <td align="center"><?php echo $arrecadacao['tipo'] == 'itens' ? 'Itens' : 'Dinheiro'; ?></td>
                        <td>
                            <?php if($arrecadacao['tipo'] == 'dinheiro'){
                                echo 'R$ '.number_format($arrecadacao['valor'], 2, ',', '.');
                            }else{
                                echo htmlspecialchars((string) $arrecadacao['descricao']);
                            } ?>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
                <?php if($temDinheiro){ ?>
                    <tfoot>
                    <tr>
                        <td colspan="4" align="right"><b>Total em dinheiro</b></td>
                        <td><b><?php echo 'R$ '.number_format($totalValor, 2, ',', '.'); ?></b></td>
                    </tr>
                    </tfoot>
                <?php } ?>
            </table>
        <?php }else{
            echo '<span class="alerta">Nenhuma doação registrada para este doador.</span>';
        } ?>

        <div class="acao-buts">
            <a href="doador_listar.php" class="but but-secundario">Voltar</a>
        </div>
    </div>
</div>
<?php include '../includes/rodape.html'; ?>
</body>
</html>
