<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../includes/verificar_login.php';

use App\Controllers\ProjetoController;
use App\Controllers\ArrecadacaoController;

$controllerProjeto = new ProjetoController;
$controllerArrecadacao = new ArrecadacaoController;

$idProject = isset($_GET['id_projeto']) ? (int) $_GET['id_projeto'] : 0;

$projeto = $controllerProjeto->getProjectByID($idProject);

if($projeto == NULL){
    header("Location: projeto_listar.php?errorInfo=true");
    exit;
}

$filter = array();

if(isset($_GET['consulta']) && trim($_GET['consulta']) != ''){
    $filter['consulta'] = trim($_GET['consulta']);
}

$tipo = '';
if(isset($_GET['tipo']) && in_array($_GET['tipo'], array('dinheiro', 'itens'))){
    $tipo = $_GET['tipo'];
    $filter['tipo'] = $tipo;
}

$arrecadacoes = $controllerArrecadacao->getDonationsByProject($idProject, $filter);

$totalValor = 0;
foreach ($arrecadacoes as $arrecadacao) {
    if($arrecadacao['valor'] != NULL){
        $totalValor += $arrecadacao['valor'];
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
            <span>Arrecadações - <b><?php echo htmlspecialchars($projeto['titulo']); ?></b></span>
            <a href="arrecadacao_cadastrar.php?id_projeto=<?php echo $idProject; ?>" title="Cadastrar arrecadação" class="but-novo icon-plus">Cadastrar arrecadação</a>
        </h2>

        <?php if(isset($_GET['newDonation'])){echo '<span class="alerta sucesso">Arrecadação cadastrada com sucesso!</span>';}?>
        <?php if(isset($_GET['updateDonation'])){echo '<span class="alerta sucesso">Arrecadação alterada com sucesso!</span>';}?>

        <div id="filtro">
            <form name="form-busca" method="GET" action="" id="form-busca">
                <input type="hidden" name="id_projeto" value="<?php echo $idProject; ?>">
                <input type="text" name="consulta" id="consulta" class="long" placeholder="Doador/Descrição" value="<?php if(isset($filter['consulta'])){ echo htmlspecialchars($filter['consulta']); } ?>">
                <select name="tipo">
                    <option value="">Tipo</option>
                    <option value="dinheiro" <?php if($tipo == 'dinheiro'){ echo 'selected'; } ?>>Dinheiro</option>
                    <option value="itens" <?php if($tipo == 'itens'){ echo 'selected'; } ?>>Itens</option>
                </select>
                <input type="submit" value="Buscar" class="but-procura">
                <a href="arrecadacao_listar.php?id_projeto=<?php echo $idProject; ?>" title="Limpar Filtro">» Limpar Filtro</a>
            </form>
        </div>

        <?php if(count($arrecadacoes) > 0){ ?>
            <table cellspacing="0" cellpadding="0" class="tab-listagem">
                <thead>
                <tr>
                    <th width="50">ID</th>
                    <th width="100">Data</th>
                    <th>Doador</th>
                    <th width="90">Tipo</th>
                    <th>Descrição</th>
                    <th width="120">Valor</th>
                    <th width="80">Ação</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($arrecadacoes as $arrecadacao) { ?>
                    <tr>
                        <td class="id"><?php echo $arrecadacao['id_arrecadacao']; ?></td>
                        <td align="center"><?php echo date('d/m/Y', strtotime($arrecadacao['data_arrecadacao'])); ?></td>
                        <td class="nome"><b><?php echo htmlspecialchars($arrecadacao['doador_nome']); ?></b></td>
                        <td align="center"><?php echo $arrecadacao['tipo'] == 'itens' ? 'Itens' : 'Dinheiro'; ?></td>
                        <td><?php echo htmlspecialchars((string) $arrecadacao['descricao']); ?></td>
                        <td align="right"><?php if($arrecadacao['valor'] != NULL){ echo 'R$ '.number_format($arrecadacao['valor'], 2, ',', '.'); } ?></td>
                        <td class="actions">
                            <a href="arrecadacao_alterar.php?id=<?php echo $arrecadacao['id_arrecadacao']; ?>" title="Alterar arrecadação" class="icon-pencil">Alterar</a>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
                <?php if($tipo != 'itens'){ ?>
                    <tfoot>
                    <tr>
                        <td colspan="5" align="right"><b>Total em dinheiro</b></td>
                        <td align="right"><b><?php echo 'R$ '.number_format($totalValor, 2, ',', '.'); ?></b></td>
                        <td></td>
                    </tr>
                    </tfoot>
                <?php } ?>
            </table>
        <?php }else{
            if(count($filter) > 0){
                echo '<span class="alerta">Nenhuma arrecadação encontrada para o filtro informado.</span>';
            }else{
                echo '<span class="alerta">Nenhuma arrecadação registrada para este projeto.</span>';
            }
        } ?>

        <div class="acao-buts">
            <a href="projeto_listar.php" class="but but-secundario">Voltar</a>
        </div>
    </div>
</div>
<?php include '../includes/rodape.html'; ?>
</body>
</html>
