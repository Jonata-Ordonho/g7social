<?php
require_once __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('America/Fortaleza');

if (!isset($_SESSION)) {
    session_start();
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <title>Grupo Sete - Atividades Sociais</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="GTEC - Gerência de Tecnologia">
    <meta name="reply-to" content="web@unirios.edu.br">
    <meta name="robots" content="noindex,nofollow">
    <link rel="icon" href="images/favicon.png">
    <link href="css/main.css" rel="stylesheet" type="text/css" media="screen">
    <link href="css/login.css" rel="stylesheet" type="text/css" media="screen">
    <script type="text/javascript" src="../js/js.js"></script>
    <script type="text/javascript" src="../js/jquery.js"></script>
    <script type="text/javascript" src="../js/jquery.mask.js"></script>
    <script type="text/javascript" src="../js/parsley.min.js"></script>
</head>

<body>
    <div id="container-login">
        <div>
            <img src="images/logo_grupo_sete_branco.png" id="logo" alt="Logo Grupo Sete">
        </div>

        <div>
            <div id="box-login">
                <h1 class="titulo">Atividades Sociais</h1>

                <?php if(isset($_GET["acesso_restrito"])) { ?>
                    <span class="alerta falha">Área restrita, faça login para continuar.</span>
                <?php } ?>

                <!-- TODO: definir forma de acesso do público -->
            </div>
            <?php include 'includes/rodape.html'; ?>
        </div>
    </div>
</body>
</html>
