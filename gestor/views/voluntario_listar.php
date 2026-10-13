<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../includes/verificar_login.php';

use App\Controllers\VoluntarioController;
use App\Helpers\Documento;
use App\Helpers\Permissao;

$currentPage = $_SERVER["PHP_SELF"];
$gerenciaVoluntarios = Permissao::podeGerenciarVoluntarios($_SESSION['SS_UserType']);

$maxRows = 30;
$pageNum = 0;
if (isset($_GET['pageNum'])) {
    $pageNum = (int) $_GET['pageNum'];
}
$startRow = $pageNum * $maxRows;

$filter = array();

if(isset($_GET['consulta']) && trim($_GET['consulta']) != ''){
    $filter['consulta'] = trim($_GET['consulta']);

    // Permite buscar o CPF com ou sem pontuação
    $consultaNumeros = Documento::somenteNumeros($filter['consulta']);
    if($consultaNumeros != ''){
        $filter['consulta_numeros'] = $consultaNumeros;
    }
}

$controllerVoluntario = new VoluntarioController;

$voluntarios = $controllerVoluntario->getVolunteersList($filter, $startRow, $maxRows);

$totalRows = $controllerVoluntario->getVolunteersTotal($filter);
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
        <h2 class="titulo">Voluntários</h2>

        <?php if(isset($_GET['updateVolunteer'])){echo '<span class="alerta sucesso">Dados do voluntário alterados com sucesso!</span>';}?>
        <?php if(isset($_GET['errorInfo'])){echo '<span class="alerta">Nenhum dado de voluntário encontrado!</span>';}?>

        <div id="filtro">
            <form name="form-busca" method="GET" action="" id="form-busca">
                <input type="text" name="consulta" id="consulta" class="long" placeholder="Nome/CPF/E-mail" value="<?php if(isset($filter['consulta'])){ echo htmlspecialchars($filter['consulta']); } ?>">
                <input type="submit" value="Buscar" class="but-procura">
                <a href="voluntario_listar.php" title="Limpar Filtro">» Limpar Filtro</a>
            </form>
        </div>

        <?php if ($totalRows > 0) { ?>
            <table cellspacing="0" cellpadding="0" class="tab-listagem">
                <thead>
                <tr>
                    <th width="50">ID</th>
                    <th width="140">Cadastro</th>
                    <th>Nome</th>
                    <th width="150">CPF</th>
                    <th width="250">E-mail</th>
                    <th width="140">Telefone</th>
                    <th width="110">Participações</th>
                    <th width="90">Ação</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($voluntarios as $voluntario) { ?>
                    <tr>
                        <td class="id"><?php echo $voluntario['id_voluntario']; ?></td>
                        <td align="center"><?php echo date('d/m/Y H:i', strtotime($voluntario['data_registro'])); ?></td>
                        <td class="nome"><b><?php echo htmlspecialchars($voluntario['nome']); ?></b></td>
                        <td align="center"><?php echo $voluntario['cpf'] != NULL ? Documento::formatarCPF($voluntario['cpf']) : '<i>Não informado</i>'; ?></td>
                        <td><?php echo htmlspecialchars((string) $voluntario['email']); ?></td>
                        <td align="center"><?php echo Documento::formatarTelefone((string) $voluntario['telefone']); ?></td>
                        <td align="center"><?php echo $voluntario['total_inscricoes']; ?></td>
                        <td class="actions">
                            <a href="voluntario_detalhar.php?id=<?php echo $voluntario['id_voluntario']; ?>" title="Detalhar voluntário e participações" class="icon-search">Detalhar</a>
                            <?php if($gerenciaVoluntarios){ ?>
                                <a href="voluntario_alterar.php?id=<?php echo $voluntario['id_voluntario']; ?>" title="Alterar dados do voluntário" class="icon-pencil">Alterar</a>
                            <?php }else{ ?>
                                <a title="Somente administradores podem alterar os dados dos voluntários" class="icon-pencil desativar">Alterar</a>
                            <?php } ?>
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
            echo '<span class="alerta">Nenhum voluntário encontrado para o filtro informado.</span>';
        } else {
            echo '<span class="alerta">Nenhum voluntário cadastrado. Os voluntários entram no banco ao se inscreverem em uma atividade.</span>';
        } ?>
    </div>
</div>
<?php include '../includes/rodape.html'; ?>
</body>
</html>
