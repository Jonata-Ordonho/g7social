<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../includes/verificar_login.php';

date_default_timezone_set('America/Fortaleza');

use App\Controllers\ProjetoController;

$currentPage = $_SERVER["PHP_SELF"];

$maxRows = 30;
$pageNum = 0;
if (isset($_GET['pageNum'])) {
    $pageNum = (int) $_GET['pageNum'];
}
$startRow = $pageNum * $maxRows;

$filter = array();
$filter['hoje'] = date('Y-m-d');

$situacoes = array(
    'ativos' => 'Ativos',
    'finalizados' => 'Finalizados'
);

$situacao = '';
if(isset($_GET['situacao']) && isset($situacoes[$_GET['situacao']])){
    $situacao = $_GET['situacao'];
    $filter['situacao'] = $situacao;
}

$captacao = '';
if(isset($_GET['captacao']) && in_array($_GET['captacao'], array('empresas', 'voluntarios'))){
    $captacao = $_GET['captacao'];
    $filter['captacao'] = $captacao;
}

if(isset($_GET['consulta']) && $_GET['consulta'] != ''){
    $filter['consulta'] = $_GET['consulta'];
}

$controllerProjeto = new ProjetoController;

$dadosProjetos = $controllerProjeto->getProjectsList($filter, $startRow, $maxRows);

$totalRows = $controllerProjeto->getProjectsTotal($filter);
$totalPages = ceil($totalRows / $maxRows) - 1;

$queryString = "";
if (!empty($_SERVER['QUERY_STRING'])) {
    $params = explode("&", $_SERVER['QUERY_STRING']);
    $newParams = array();
    foreach ($params as $param) {
        if (stristr($param, "pageNum") == false &&
            stristr($param, "totalRows") == false) {
            array_push($newParams, $param);
        }
    }
    if (count($newParams) != 0) {
        $queryString = "&" . htmlentities(implode("&", $newParams));
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
        <h2 class="titulo">Projetos Sociais <?php if($situacao != ''){ echo $situacoes[$situacao]; } ?></h2>

        <?php if(isset($_GET['updateProject'])){echo '<span class="alerta sucesso">Projeto alterado com sucesso!</span>';}?>
        <?php if(isset($_GET['acessoNegado'])){echo '<span class="alerta">Você não tem permissão para acessar essa página.</span>';}?>
        <?php if(isset($_GET['errorInfo'])){echo '<span class="alerta">Nenhum dado de projeto encontrado!</span>';}?>

        <div id="filtro">
            <form name="form-busca" method="GET" action="" id="form-busca">
                <input type="text" name="consulta" id="consulta" class="long" placeholder="Título" value="<?php if(isset($_GET['consulta'])){ echo htmlspecialchars($_GET['consulta']); } ?>">
                <select name="situacao">
                    <option value="">Situação</option>
                    <?php foreach ($situacoes as $valor => $rotulo) { ?>
                        <option value="<?php echo $valor; ?>" <?php if($situacao == $valor){ echo 'selected'; } ?>><?php echo $rotulo; ?></option>
                    <?php } ?>
                </select>
                <select name="captacao">
                    <option value="">Captação</option>
                    <option value="empresas" <?php if($captacao == 'empresas'){ echo 'selected'; } ?>>Doações de empresas</option>
                    <option value="voluntarios" <?php if($captacao == 'voluntarios'){ echo 'selected'; } ?>>Voluntários</option>
                </select>
                <input type="submit" value="Buscar" class="but-procura">
                <a href="projeto_listar.php" title="Limpar Filtro">» Limpar Filtro</a>
            </form>
        </div>

        <?php if ($totalRows > 0) { ?>
            <table cellspacing="0" cellpadding="0" class="tab-listagem">
                <thead>
                <tr>
                    <th width="50">ID</th>
                    <th>Título</th>
                    <th width="190">Período das Atividades</th>
                    <th width="90">Doações</th>
                    <th width="140">Arrecadado</th>
                    <th width="120">Ação</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($dadosProjetos as $projeto) { ?>
                    <tr>
                        <td class="id"><?php echo $projeto['id_projeto']; ?></td>
                        <td class="nome">
                            <b><?php echo htmlspecialchars($projeto['titulo']); ?></b>
                            <?php if($projeto['situacao'] == 'ativo'){ ?>
                                <span class="tag tag-ativo">Ativo</span>
                            <?php }else{ ?>
                                <span class="tag tag-inativo">Finalizado</span>
                            <?php } ?>
                            <?php if($projeto['descricao'] != ''){ ?>
                                <br><?php echo htmlspecialchars($projeto['descricao']); ?>
                            <?php } ?>
                        </td>
                        <td align="center">
                            <?php if($projeto['total_atividades'] > 0){
                                echo date('d/m/Y',strtotime($projeto['atividade_inicio'])).' - '.date('d/m/Y',strtotime($projeto['atividade_fim']));
                                echo '<br><small>'.$projeto['total_atividades'].($projeto['total_atividades'] == 1 ? ' atividade' : ' atividades').'</small>';
                            }else{
                                echo '<i>Nenhuma atividade</i>';
                            } ?>
                        </td>
                        <td align="center"><?php echo $projeto['total_doacoes']; ?></td>
                        <td align="right"><?php echo 'R$ '.number_format($projeto['total_dinheiro'], 2, ',', '.'); ?></td>
                        <td class="actions">
                            <a href="projeto_alterar.php?id=<?php echo $projeto['id_projeto']; ?>" title="Alterar dados do projeto" class="icon-pencil">Alterar</a>
                            <a href="atividade_listar.php?id_projeto=<?php echo $projeto['id_projeto']; ?>" title="Atividades e voluntários" class="icon-calendar">Atividades</a>
                            <a href="arrecadacao_listar.php?id_projeto=<?php echo $projeto['id_projeto']; ?>" title="Arrecadações (doações das empresas)" class="icon-bank">Arrecadações</a>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        <?php } ?>

        <?php if ($totalRows > 0) {?>
            <div id="paginacao">
                <span id="total"><?php echo ($startRow + 1) ?> até <?php echo min($startRow + $maxRows, $totalRows) ?> de <?php echo $totalRows; ?></span>
                <?php if ($pageNum > 0) {?>
                    <a href="<?php printf("%s?pageNum=%d%s", $currentPage, 0, $queryString);?>">« Primeiro</a>
                <?php }?>

                <?php if ($pageNum > 0) {?>
                    <a href="<?php printf("%s?pageNum=%d%s", $currentPage, max(0, $pageNum - 1), $queryString);?>">« Anterior</a>
                <?php }?>

                <?php if ($pageNum < $totalPages) {?>
                    <a href="<?php printf("%s?pageNum=%d%s", $currentPage, min($totalPages, $pageNum + 1), $queryString);?>">Próximo »</a>
                <?php }?>

                <?php if ($pageNum < $totalPages) {?>
                    <a href="<?php printf("%s?pageNum=%d%s", $currentPage, $totalPages, $queryString);?>">Último »</a>
                <?php }?>
            </div>

        <?php } else {
            echo '<span class="alerta">Nenhum projeto encontrado.</span>';
        } ?>
    </div>
</div>
<?php include '../includes/rodape.html'; ?>
</body>
</html>
