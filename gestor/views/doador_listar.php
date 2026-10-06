<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../includes/verificar_login.php';

use App\Controllers\DoadorController;
use App\Helpers\Documento;

$currentPage = $_SERVER["PHP_SELF"];

$maxRows = 30;
$pageNum = 0;
if (isset($_GET['pageNum'])) {
    $pageNum = (int) $_GET['pageNum'];
}
$startRow = $pageNum * $maxRows;

$filter = array();

if(isset($_GET['consulta']) && trim($_GET['consulta']) != ''){
    $filter['consulta'] = trim($_GET['consulta']);

    // Permite buscar CPF/CNPJ com ou sem pontuação
    $consultaNumeros = Documento::somenteNumeros($filter['consulta']);
    if($consultaNumeros != ''){
        $filter['consulta_numeros'] = $consultaNumeros;
    }
}

$tipo = '';
if(isset($_GET['tipo']) && in_array($_GET['tipo'], array('empresa', 'pessoa'))){
    $tipo = $_GET['tipo'];
    $filter['tipo'] = $tipo;
}

$controllerDoador = new DoadorController;

$doadores = $controllerDoador->getDonorsList($filter, $startRow, $maxRows);

$totalRows = $controllerDoador->getDonorsTotal($filter);
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
        <h2 class="titulo titulo-acao">
            <span>Doadores</span>
            <a href="doador_cadastrar.php" title="Cadastrar doador" class="but-novo icon-plus">Cadastrar doador</a>
        </h2>

        <?php if(isset($_GET['newDonor'])){echo '<span class="alerta sucesso">Doador cadastrado com sucesso!</span>';}?>
        <?php if(isset($_GET['updateDonor'])){echo '<span class="alerta sucesso">Doador alterado com sucesso!</span>';}?>
        <?php if(isset($_GET['errorInfo'])){echo '<span class="alerta">Nenhum dado de doador encontrado!</span>';}?>

        <div id="filtro">
            <form name="form-busca" method="GET" action="" id="form-busca">
                <input type="text" name="consulta" id="consulta" class="long" placeholder="Nome/CPF/CNPJ/E-mail" value="<?php if(isset($filter['consulta'])){ echo htmlspecialchars($filter['consulta']); } ?>">
                <select name="tipo">
                    <option value="">Tipo</option>
                    <option value="empresa" <?php if($tipo == 'empresa'){ echo 'selected'; } ?>>Empresa</option>
                    <option value="pessoa" <?php if($tipo == 'pessoa'){ echo 'selected'; } ?>>Pessoa</option>
                </select>
                <input type="submit" value="Buscar" class="but-procura">
                <a href="doador_listar.php" title="Limpar Filtro">» Limpar Filtro</a>
            </form>
        </div>

        <?php if ($totalRows > 0) { ?>
            <table cellspacing="0" cellpadding="0" class="tab-listagem">
                <thead>
                <tr>
                    <th width="50">ID</th>
                    <th width="140">Cadastro</th>
                    <th width="90">Tipo</th>
                    <th>Nome</th>
                    <th width="170">CPF/CNPJ</th>
                    <th width="250">E-mail</th>
                    <th width="140">Telefone</th>
                    <th width="90">Ação</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($doadores as $doador) { ?>
                    <tr>
                        <td class="id"><?php echo $doador['id_doador']; ?></td>
                        <td align="center"><?php echo date('d/m/Y H:i', strtotime($doador['data_registro'])); ?></td>
                        <td align="center"><?php echo $doador['tipo'] == 'empresa' ? 'Empresa' : 'Pessoa'; ?></td>
                        <td class="nome"><b><?php echo htmlspecialchars($doador['nome']); ?></b></td>
                        <td align="center">
                            <?php if($doador['tipo'] == 'empresa'){
                                echo '<small>CNPJ</small> '.Documento::formatarCNPJ($doador['documento']);
                            }else{
                                echo '<small>CPF</small> '.Documento::formatarCPF($doador['documento']);
                            } ?>
                        </td>
                        <td><?php echo htmlspecialchars((string) $doador['email']); ?></td>
                        <td align="center"><?php echo Documento::formatarTelefone((string) $doador['telefone']); ?></td>
                        <td class="actions">
                            <a href="doador_detalhar.php?id=<?php echo $doador['id_doador']; ?>" title="Detalhar doador e doações" class="icon-search">Detalhar</a>
                            <a href="doador_alterar.php?id=<?php echo $doador['id_doador']; ?>" title="Alterar dados do doador" class="icon-pencil">Alterar</a>
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

        <?php } elseif (count($filter) > 0) {
            echo '<span class="alerta">Nenhum doador encontrado para o filtro informado.</span>';
        } else {
            echo '<span class="alerta">Nenhum doador cadastrado.</span>';
        } ?>
    </div>
</div>
<?php include '../includes/rodape.html'; ?>
</body>
</html>
