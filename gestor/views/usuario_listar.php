<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../includes/verificar_admin.php';

use App\Controllers\UsuarioController;
use App\Helpers\Permissao;

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
}

$tipo = '';
if(isset($_GET['tipo']) && isset(Permissao::TIPOS[$_GET['tipo']])){
    $tipo = $_GET['tipo'];
    $filter['tipo'] = $tipo;
}

$status = '';
if(isset($_GET['status']) && in_array($_GET['status'], array('on', 'off'))){
    $status = $_GET['status'];
    $filter['status'] = $status;
}

$controllerUsuario = new UsuarioController;

$usuarios = $controllerUsuario->getUsersList($filter, $startRow, $maxRows);

$totalRows = $controllerUsuario->getUsersTotal($filter);
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
            <span>Usuários</span>
            <a href="usuario_cadastrar.php" title="Cadastrar usuário" class="but-novo icon-plus">Cadastrar usuário</a>
        </h2>

        <?php if(isset($_GET['newUser'])){echo '<span class="alerta sucesso">Usuário cadastrado com sucesso!</span>';}?>
        <?php if(isset($_GET['updateUser'])){echo '<span class="alerta sucesso">Usuário alterado com sucesso!</span>';}?>
        <?php if(isset($_GET['errorInfo'])){echo '<span class="alerta">Nenhum dado de usuário encontrado!</span>';}?>
        <?php if(isset($_GET['semPermissao'])){echo '<span class="alerta">Somente o admin master pode alterar os dados dos usuários.</span>';}?>

        <div id="filtro">
            <form name="form-busca" method="GET" action="" id="form-busca">
                <input type="text" name="consulta" id="consulta" class="long" placeholder="Nome/E-mail" value="<?php if(isset($filter['consulta'])){ echo htmlspecialchars($filter['consulta']); } ?>">
                <select name="tipo">
                    <option value="">Tipo</option>
                    <?php foreach (Permissao::TIPOS as $valor => $rotulo) { ?>
                        <option value="<?php echo $valor; ?>" <?php if($tipo == $valor){ echo 'selected'; } ?>><?php echo $rotulo; ?></option>
                    <?php } ?>
                </select>
                <select name="status">
                    <option value="">Status</option>
                    <option value="on" <?php if($status == 'on'){ echo 'selected'; } ?>>Ativo</option>
                    <option value="off" <?php if($status == 'off'){ echo 'selected'; } ?>>Desativado</option>
                </select>
                <input type="submit" value="Buscar" class="but-procura">
                <a href="usuario_listar.php" title="Limpar Filtro">» Limpar Filtro</a>
            </form>
        </div>

        <?php if ($totalRows > 0) { ?>
            <table cellspacing="0" cellpadding="0" class="tab-listagem">
                <thead>
                <tr>
                    <th width="50">ID</th>
                    <th width="140">Cadastro</th>
                    <th>Nome</th>
                    <th width="260">E-mail</th>
                    <th width="170">Tipo</th>
                    <th width="80">Ação</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($usuarios as $usuario) { ?>
                    <tr>
                        <td class="id"><?php echo $usuario['id_usuario']; ?></td>
                        <td align="center"><?php echo date('d/m/Y H:i', strtotime($usuario['data_registro'])); ?></td>
                        <td class="nome">
                            <b><?php echo htmlspecialchars($usuario['nome']); ?></b>
                            <?php if($usuario['status'] == 'on'){ ?>
                                <span class="tag tag-ativo">Ativo</span>
                            <?php }else{ ?>
                                <span class="tag tag-inativo">Desativado</span>
                            <?php } ?>
                            <?php if($usuario['id_usuario'] == $_SESSION['SS_UserID']){ ?>
                                <small>(você)</small>
                            <?php } ?>
                        </td>
                        <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                        <td align="center"><?php echo Permissao::nomeTipo($usuario['tipo']); ?></td>
                        <td class="actions">
                            <?php if(Permissao::podeAlterarUsuario($_SESSION['SS_UserType'])){ ?>
                                <a href="usuario_alterar.php?id=<?php echo $usuario['id_usuario']; ?>" title="Alterar usuário" class="icon-pencil">Alterar</a>
                            <?php }else{ ?>
                                <a title="Somente o admin master pode alterar os dados dos usuários" class="icon-pencil desativar">Alterar</a>
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
            echo '<span class="alerta">Nenhum usuário encontrado para o filtro informado.</span>';
        } else {
            echo '<span class="alerta">Nenhum usuário cadastrado.</span>';
        } ?>
    </div>
</div>
<?php include '../includes/rodape.html'; ?>
</body>
</html>
