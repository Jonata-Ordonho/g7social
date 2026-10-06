<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\UsuarioController;
use App\Controllers\LogController;
use DataBase\Connections;

if (!isset($_SESSION)) {
    session_start();
}

$controllerUsers = new UsuarioController;
$controllerLogs = new LogController;
$connections = new Connections;

$turnstile = $connections->connectTurnstile();

if (isset($_POST['usu_email'])) {
    // TODO: CAPTCHA desativado temporariamente - reativar antes de publicar
    // $turnstile_response   = $_POST['cf-turnstile-response'];
    // $post_fields          = http_build_query(array('secret' => $turnstile['secret'], 'response' => $turnstile_response));
    //
    // $ch = curl_init($turnstile['url']);
    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // curl_setopt($ch, CURLOPT_POST, true);
    // curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    // $response = curl_exec($ch);
    // curl_close($ch);
    //
    // $response_data = json_decode($response);
    $response_data = (object) array('success' => true);

    if ($response_data && $response_data->success) {
        $email = $_POST['usu_email'];
        $password = $_POST['usu_senha'];

        $MM_redirectLoginSuccess = "views/projeto_listar.php";

        $dadosLogin = $controllerUsers->authenticationUserLogin($email, $password);

        if ($dadosLogin != NULL) {

            if ($dadosLogin['status'] == 'on') {
                session_regenerate_id(true);
                $_SESSION['SS_UserID'] = $dadosLogin['id_usuario'];
                $_SESSION['SS_UserEmail'] = $dadosLogin['email'];
                $_SESSION['SS_UserName'] = $dadosLogin['nome'];
                $_SESSION['SS_UserType'] = $dadosLogin['tipo'];
                $_SESSION['SS_Access'] = 'gestor';

                $controllerLogs->insertActionUserLog(1, $dadosLogin['id_usuario'], null);

                header("Location: " . $MM_redirectLoginSuccess);
            } else {
                $MM_redirectLoginFailed = "?login_desativado=true";
                header("Location: " . $MM_redirectLoginFailed);
            }
        } else {
            $MM_redirectLoginFailed = "?login_errado=true";
            header("Location: " . $MM_redirectLoginFailed);
        }

    } else {
        $MM_redirectLoginFailed = "?erro_captcha=true";
        header("Location: " . $MM_redirectLoginFailed);
    }
    exit;
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
    <link href="css/css.css" rel="stylesheet" type="text/css" media="screen">
    <link rel="icon" href="images/favicon.png">
    <script type="text/javascript" src="../js/jquery.js"></script>
    <script type="text/javascript" src="../js/parsley.min.js"></script>

    <!-- TODO: CAPTCHA desativado temporariamente -->
    <!-- <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script> -->
</head>

<body id="pag-login">
    <div id="container-login">
        <header>
            <img src="images/logo_white.png" alt="Grupo Sete" draggable="false">
        </header>

        <div id="login">
            <h1>Acesso ao gestor</h1>
            <span class="subtitulo">G7 Social - Atividades Sociais do Grupo Sete</span>

            <?php if (isset($_GET["erro_captcha"])) { ?>
                <span class="alerta">Falha na verificação do CAPTCHA.</span>
            <?php } ?>
            <?php if (isset($_GET["login_errado"])) { ?>
                <span class="alerta">O usuário e/ou senha incorreto(s).</span>
            <?php } ?>
            <?php if (isset($_GET["acesso_restrito"])) { ?>
                <span class="alerta">Área restrita, informe seu nome de usuario e senha.</span>
            <?php } ?>
            <?php if (isset($_GET["login_desativado"])) { ?>
                <span class="alerta">Usuário desativado.</span>
            <?php } ?>
            <form name="form1" id="form1" method="POST" action="" data-parsley-validate="">
                <fieldset>
                    <legend>Login</legend>
                    <label for="usu_email">E-mail</label>
                    <input type="text" name="usu_email" id="usu_email" required>

                    <label for="usu_senha">Senha</label>
                    <input type="password" name="usu_senha" id="usu_senha" required>

                    <input type="submit" name="entrar" id="entrar" class="but" value="Entrar">
                </fieldset>
                <!-- <div class="cf-turnstile" data-sitekey="<?php echo $turnstile['site_key']; ?>" style="margin-top: 10px; display: flex; justify-content: center;"></div> -->
            </form>
        </div>

        <span id="desenvolvedor">
            GRUPO SETE DE SETEMBRO<br>
            GTEC - Departamento de Desenvolvimento de Software<br>
            E-mail: web@unirios.edu.br | Fone: (75) 3501-0773
        </span>
    </div>

</body>

</html>
