<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../includes/verificar_login.php';

use App\Controllers\UsuarioController;
use App\Controllers\LogController;
use App\Models\Usuario;

$controllerUsuario = new UsuarioController;
$controllerLogs = new LogController;

$idUser = (int) $_SESSION['SS_UserID'];

$usuario = $controllerUsuario->getUserByID($idUser);

if($usuario == NULL){
    header("Location: ../includes/logout.php");
    exit;
}

$dados = array(
    'nome' => $usuario['nome'],
    'email' => $usuario['email']
);

$erro = '';

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
    $dados['nome'] = trim($_POST['nome']);
    $dados['email'] = trim($_POST['email']);
    $senhaAtual = $_POST['senha_atual'];
    $novaSenha = $_POST['nova_senha'];
    $confirmacao = $_POST['confirmar_senha'];

    if($dados['nome'] == ''){
        $erro = 'Informe o seu nome.';
    }elseif(!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)){
        $erro = 'Informe um e-mail válido.';
    }elseif($dados['email'] != $usuario['email'] && $controllerUsuario->checkUserLogin($dados['email'])){
        $erro = 'Este e-mail já está sendo usado por outro usuário.';
    }elseif($novaSenha != '' && sha1($senhaAtual) !== $usuario['senha']){
        $erro = 'A senha atual não confere.';
    }elseif($novaSenha != '' && strlen($novaSenha) < 6){
        $erro = 'A nova senha deve ter pelo menos 6 caracteres.';
    }elseif($novaSenha != $confirmacao){
        $erro = 'A confirmação não confere com a nova senha.';
    }else{
        $objUsuario = new Usuario;
        $objUsuario->setUsuarioID($idUser);
        $objUsuario->setNome($dados['nome']);
        $objUsuario->setEmail($dados['email']);
        $objUsuario->setSenha($novaSenha != '' ? $novaSenha : NULL);

        if($controllerUsuario->updateUserPersonalData($objUsuario)){
            $_SESSION['SS_UserName'] = $dados['nome'];
            $_SESSION['SS_UserEmail'] = $dados['email'];

            $controllerLogs->insertActionUserLog(11, $idUser, $novaSenha != '' ? 'Dados pessoais e senha' : 'Dados pessoais');

            header("Location: usuario_dados.php?updated=true");
            exit;
        }else{
            $erro = 'Não foi possível salvar os seus dados.';
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
        <h2 class="titulo">Meus dados</h2>

        <?php if(isset($_GET['updated'])){echo '<span class="alerta sucesso">Dados atualizados com sucesso!</span>';}?>
        <?php if($erro != ''){ echo '<span class="alerta">'.$erro.'</span>'; } ?>

        <form method="POST" name="form1" id="form1" action="usuario_dados.php" data-parsley-validate="">
            <div class="formulario">
                <div>
                    <label>Nome</label>
                    <input type="text" name="nome" maxlength="150" required value="<?php echo htmlspecialchars($dados['nome']); ?>">
                </div>

                <div>
                    <label>E-mail <span>(usado para entrar no sistema)</span></label>
                    <input type="email" name="email" maxlength="150" required value="<?php echo htmlspecialchars($dados['email']); ?>">
                </div>

                <h3>Alterar senha <small>(preencha apenas se quiser trocar)</small></h3>

                <div>
                    <label>Senha atual</label>
                    <input type="password" name="senha_atual" autocomplete="current-password">
                </div>

                <div>
                    <label>Nova senha <span>(mínimo de 6 caracteres)</span></label>
                    <input type="password" name="nova_senha" id="nova_senha" autocomplete="new-password">
                </div>

                <div>
                    <label>Confirmar nova senha</label>
                    <input type="password" name="confirmar_senha" autocomplete="new-password" data-parsley-equalto="#nova_senha" data-parsley-equalto-message="A confirmação não confere com a nova senha.">
                </div>

                <div class="acao-buts">
                    <input type="hidden" name="MM_update" value="form1">
                    <input type="submit" value="Salvar" class="but">
                    <a href="projeto_listar.php" class="but but-secundario">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>
<?php include '../includes/rodape.html'; ?>
</body>
</html>
