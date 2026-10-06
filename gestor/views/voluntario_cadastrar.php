<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../includes/verificar_login.php';

use App\Controllers\ProjetoController;
use App\Controllers\AtividadeController;
use App\Controllers\VoluntarioController;
use App\Controllers\LogController;
use App\Helpers\Documento;
use App\Models\Voluntario;

$controllerProjeto = new ProjetoController;
$controllerAtividade = new AtividadeController;
$controllerVoluntario = new VoluntarioController;
$controllerLogs = new LogController;

$idActivity = isset($_GET['id_atividade']) ? (int) $_GET['id_atividade'] : 0;

$atividade = $controllerAtividade->getActivityByID($idActivity);

if($atividade == NULL){
    header("Location: projeto_listar.php?errorInfo=true");
    exit;
}

$projeto = $controllerProjeto->getProjectByID($atividade['id_projeto']);

$dados = array(
    'nome' => '',
    'telefone' => '',
    'email' => ''
);

$erro = '';

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
    $dados['nome'] = trim($_POST['nome']);
    $dados['telefone'] = trim($_POST['telefone']);
    $dados['email'] = trim($_POST['email']);

    $telefone = Documento::somenteNumeros($dados['telefone']);

    if($dados['nome'] == ''){
        $erro = 'Informe o nome do voluntário.';
    }elseif(strlen($telefone) != 10 && strlen($telefone) != 11){
        $erro = 'Informe o telefone com DDD.';
    }elseif(!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)){
        $erro = 'Informe um e-mail válido.';
    }elseif($controllerVoluntario->checkVolunteerEmail($idActivity, $dados['email'])){
        $erro = 'Já existe um voluntário cadastrado com este e-mail nesta atividade.';
    }elseif($atividade['vagas'] != NULL && $controllerVoluntario->getVolunteersTotalByActivity($idActivity) >= $atividade['vagas']){
        $erro = 'Todas as vagas desta atividade já foram preenchidas.';
    }else{
        $objVoluntario = new Voluntario;
        $objVoluntario->setAtividadeID($idActivity);
        $objVoluntario->setNome($dados['nome']);
        $objVoluntario->setTelefone($telefone);
        $objVoluntario->setEmail($dados['email']);

        $idVoluntario = $controllerVoluntario->insertVolunteer($objVoluntario);

        if($idVoluntario){
            $controllerLogs->insertActionUserLog(9, $_SESSION['SS_UserID'], 'Voluntário ID '.$idVoluntario.' - Atividade ID '.$idActivity);

            header("Location: voluntario_listar.php?id_atividade=".$idActivity."&newVolunteer=true");
            exit;
        }else{
            $erro = 'Não foi possível cadastrar o voluntário.';
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
    <script type="text/javascript" src="../../js/jquery.mask.js"></script>
    <script type="text/javascript" src="../../js/parsley.min.js"></script>

    <script>
        $(document).ready(function(){
            // Telefone fixo (10 dígitos) ou celular (11 dígitos)
            var mascaraTelefone = function (val) {
                return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009';
            };
            $('#telefone').mask(mascaraTelefone, {
                onKeyPress: function(val, e, field, options) {
                    field.mask(mascaraTelefone.apply({}, arguments), options);
                }
            });
        });
    </script>
</head>

<body>
<?php include '../includes/topo.html'; ?>
<?php include '../includes/menu.php'; ?>
<div id="container">
    <div id="conteudo">
        <h2 class="titulo">Cadastrar Voluntário - <b><?php echo htmlspecialchars($atividade['titulo']); ?></b> <small>(<?php echo htmlspecialchars($projeto['titulo']); ?>)</small></h2>

        <?php if($erro != ''){ echo '<span class="alerta">'.$erro.'</span>'; } ?>

        <form method="POST" name="form1" id="form1" action="voluntario_cadastrar.php?id_atividade=<?php echo $idActivity; ?>" data-parsley-validate="">
            <div class="formulario">
                <div>
                    <label>Nome</label>
                    <input type="text" name="nome" maxlength="150" required value="<?php echo htmlspecialchars($dados['nome']); ?>">
                </div>

                <div>
                    <label>Telefone</label>
                    <input type="text" name="telefone" id="telefone" placeholder="(00) 00000-0000" required value="<?php echo htmlspecialchars($dados['telefone']); ?>">
                </div>

                <div>
                    <label>E-mail</label>
                    <input type="email" name="email" maxlength="150" required value="<?php echo htmlspecialchars($dados['email']); ?>">
                </div>

                <div class="acao-buts">
                    <input type="hidden" name="MM_insert" value="form1">
                    <input type="submit" value="Cadastrar" class="but">
                    <a href="voluntario_listar.php?id_atividade=<?php echo $idActivity; ?>" class="but but-secundario">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>
<?php include '../includes/rodape.html'; ?>
</body>
</html>
