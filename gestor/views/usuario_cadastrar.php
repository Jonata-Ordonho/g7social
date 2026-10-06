<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../includes/verificar_admin.php';

use App\Controllers\UsuarioController;
use App\Controllers\LogController;
use App\Helpers\Permissao;
use App\Models\Usuario;

$controllerUsuario = new UsuarioController;
$controllerLogs = new LogController;

$tiposPermitidos = Permissao::tiposPermitidos($_SESSION['SS_UserType']);
$edicaoPropria = false;
$senhaObrigatoria = true;
$exibirStatus = false; // todo usuário novo entra como ativo

$dados = array(
    'nome' => '',
    'email' => '',
    'tipo' => 'gestor'
);

$erro = '';

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
    $dados['nome'] = trim($_POST['nome']);
    $dados['email'] = trim($_POST['email']);
    $dados['tipo'] = isset($_POST['tipo']) ? $_POST['tipo'] : '';
    $senha = $_POST['senha'];
    $confirmacao = $_POST['confirmar_senha'];

    if($dados['nome'] == ''){
        $erro = 'Informe o nome do usuário.';
    }elseif(!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)){
        $erro = 'Informe um e-mail válido.';
    }elseif(!isset($tiposPermitidos[$dados['tipo']])){
        $erro = 'Selecione um tipo de usuário válido.';
    }elseif(strlen($senha) < 6){
        $erro = 'A senha deve ter pelo menos 6 caracteres.';
    }elseif($senha != $confirmacao){
        $erro = 'A confirmação não confere com a senha.';
    }elseif($controllerUsuario->checkUserLogin($dados['email'])){
        $erro = 'Já existe um usuário cadastrado com este e-mail.';
    }else{
        $objUsuario = new Usuario;
        $objUsuario->setNome($dados['nome']);
        $objUsuario->setEmail($dados['email']);
        $objUsuario->setSenha($senha);
        $objUsuario->setTipo($dados['tipo']);
        $objUsuario->setStatus('on');

        $idUsuario = $controllerUsuario->insertNewUser($objUsuario);

        if($idUsuario){
            $controllerLogs->insertActionUserLog(12, $_SESSION['SS_UserID'], 'Usuário ID '.$idUsuario);

            header("Location: usuario_listar.php?newUser=true");
            exit;
        }else{
            $erro = 'Não foi possível cadastrar o usuário.';
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
        <h2 class="titulo">Cadastrar Usuário</h2>

        <?php if($erro != ''){ echo '<span class="alerta">'.$erro.'</span>'; } ?>

        <form method="POST" name="form1" id="form1" action="usuario_cadastrar.php" data-parsley-validate="">
            <?php include '../includes/form_usuario.php'; ?>

            <div class="formulario">
                <div class="acao-buts">
                    <input type="hidden" name="MM_insert" value="form1">
                    <input type="submit" value="Cadastrar" class="but">
                    <a href="usuario_listar.php" class="but but-secundario">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>
<?php include '../includes/rodape.html'; ?>
</body>
</html>
