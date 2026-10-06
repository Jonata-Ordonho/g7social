<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../includes/verificar_admin.php';

use App\Controllers\UsuarioController;
use App\Controllers\LogController;
use App\Helpers\Permissao;
use App\Models\Usuario;

$controllerUsuario = new UsuarioController;
$controllerLogs = new LogController;

$idUser = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$usuario = $controllerUsuario->getUserByID($idUser);

if($usuario == NULL){
    header("Location: usuario_listar.php?errorInfo=true");
    exit;
}

// Somente o admin master altera dados de usuários
if(!Permissao::podeAlterarUsuario($_SESSION['SS_UserType'])){
    header("Location: usuario_listar.php?semPermissao=true");
    exit;
}

$tiposPermitidos = Permissao::tiposPermitidos($_SESSION['SS_UserType']);
$edicaoPropria = $idUser == (int) $_SESSION['SS_UserID'];
$senhaObrigatoria = false;
$exibirStatus = true;

$dados = array(
    'nome' => $usuario['nome'],
    'email' => $usuario['email'],
    'tipo' => $usuario['tipo'],
    'status' => $usuario['status']
);

$erro = '';

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
    $dados['nome'] = trim($_POST['nome']);
    $dados['email'] = trim($_POST['email']);
    // O próprio usuário não altera o seu tipo nem o seu status
    if(!$edicaoPropria){
        $dados['tipo'] = isset($_POST['tipo']) ? $_POST['tipo'] : '';
        $dados['status'] = (isset($_POST['status']) && $_POST['status'] == 'off') ? 'off' : 'on';
    }
    $senha = $_POST['senha'];
    $confirmacao = $_POST['confirmar_senha'];

    if($dados['nome'] == ''){
        $erro = 'Informe o nome do usuário.';
    }elseif(!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)){
        $erro = 'Informe um e-mail válido.';
    }elseif(!$edicaoPropria && !isset($tiposPermitidos[$dados['tipo']])){
        $erro = 'Selecione um tipo de usuário válido.';
    }elseif($senha != '' && strlen($senha) < 6){
        $erro = 'A senha deve ter pelo menos 6 caracteres.';
    }elseif($senha != $confirmacao){
        $erro = 'A confirmação não confere com a senha.';
    }elseif($dados['email'] != $usuario['email'] && $controllerUsuario->checkUserLogin($dados['email'])){
        $erro = 'Já existe outro usuário cadastrado com este e-mail.';
    }else{
        $objUsuario = new Usuario;
        $objUsuario->setUsuarioID($idUser);
        $objUsuario->setNome($dados['nome']);
        $objUsuario->setEmail($dados['email']);
        $objUsuario->setSenha($senha != '' ? $senha : NULL);
        $objUsuario->setTipo($dados['tipo']);
        $objUsuario->setStatus($dados['status']);

        if($controllerUsuario->updateUser($objUsuario)){
            $controllerLogs->insertActionUserLog(13, $_SESSION['SS_UserID'], 'Usuário ID '.$idUser);

            if($edicaoPropria){
                $_SESSION['SS_UserName'] = $dados['nome'];
                $_SESSION['SS_UserEmail'] = $dados['email'];
            }

            header("Location: usuario_listar.php?updateUser=true");
            exit;
        }else{
            $erro = 'Não foi possível alterar o usuário.';
        }
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
    <script type="text/javascript" src="../../js/jquery.js"></script>
    <script type="text/javascript" src="../../js/parsley.min.js"></script>
</head>

<body>
<?php include '../includes/topo.html'; ?>
<?php include '../includes/menu.php'; ?>
<div id="container">
    <div id="conteudo">
        <h2 class="titulo">Alterar Usuário - <b><?php echo htmlspecialchars($usuario['nome']); ?></b></h2>

        <?php if($erro != ''){ echo '<span class="alerta">'.$erro.'</span>'; } ?>

        <form method="POST" name="form1" id="form1" action="usuario_alterar.php?id=<?php echo $idUser; ?>" data-parsley-validate="">
            <?php include '../includes/form_usuario.php'; ?>

            <div class="formulario">
                <div class="acao-buts">
                    <input type="hidden" name="MM_update" value="form1">
                    <input type="submit" value="Atualizar" class="but">
                    <a href="usuario_listar.php" class="but but-secundario">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>
<?php include '../includes/rodape.html'; ?>
</body>
</html>
