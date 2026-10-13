<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../includes/verificar_login.php';

use App\Controllers\VoluntarioController;
use App\Controllers\LogController;
use App\Helpers\Documento;
use App\Helpers\Permissao;
use App\Models\Voluntario;

$controllerVoluntario = new VoluntarioController;
$controllerLogs = new LogController;

$idVolunteer = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$voluntario = $controllerVoluntario->getVolunteerByID($idVolunteer);

if($voluntario == NULL){
    header("Location: voluntario_listar.php?errorInfo=true");
    exit;
}

// Somente administradores alteram os dados do banco de voluntários
if(!Permissao::podeGerenciarVoluntarios($_SESSION['SS_UserType'])){
    header("Location: voluntario_detalhar.php?id=".$idVolunteer."&semPermissao=true");
    exit;
}

$dados = array(
    'nome' => $voluntario['nome'],
    'cpf' => $voluntario['cpf'] != NULL ? Documento::formatarCPF($voluntario['cpf']) : '',
    'email' => (string) $voluntario['email'],
    'telefone' => Documento::formatarTelefone((string) $voluntario['telefone'])
);

$erro = '';

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
    $dados['nome'] = trim($_POST['nome']);
    $dados['cpf'] = trim($_POST['cpf']);
    $dados['email'] = trim($_POST['email']);
    $dados['telefone'] = trim($_POST['telefone']);

    $cpf = Documento::somenteNumeros($dados['cpf']);
    $telefone = Documento::somenteNumeros($dados['telefone']);

    if($dados['nome'] == ''){
        $erro = 'Informe o nome do voluntário.';
    }elseif(!Documento::validarCPF($cpf)){
        $erro = 'Informe um CPF válido.';
    }elseif(!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)){
        $erro = 'Informe um e-mail válido.';
    }elseif($telefone != '' && strlen($telefone) != 10 && strlen($telefone) != 11){
        $erro = 'Informe o telefone com DDD.';
    }elseif($controllerVoluntario->checkVolunteerCPF($cpf, $idVolunteer)){
        $erro = 'Já existe outro voluntário cadastrado com este CPF.';
    }else{
        $objVoluntario = new Voluntario;
        $objVoluntario->setVoluntarioID($idVolunteer);
        $objVoluntario->setNome($dados['nome']);
        $objVoluntario->setCpf($cpf);
        $objVoluntario->setEmail($dados['email']);
        $objVoluntario->setTelefone($telefone != '' ? $telefone : NULL);

        if($controllerVoluntario->updateVolunteer($objVoluntario)){
            $controllerLogs->insertActionUserLog(17, $_SESSION['SS_UserID'], 'Voluntário ID '.$idVolunteer);

            header("Location: voluntario_detalhar.php?id=".$idVolunteer."&updateVolunteer=true");
            exit;
        }else{
            $erro = 'Não foi possível alterar os dados do voluntário.';
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
            $('#cpf').mask('000.000.000-00');

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
        <h2 class="titulo">Alterar Dados do Voluntário</h2>

        <?php if($erro != ''){ echo '<span class="alerta">'.$erro.'</span>'; } ?>
        <?php if($voluntario['cpf'] == NULL && $erro == ''){ echo '<span class="alerta">Este voluntário ainda não tem CPF cadastrado. Informe o CPF para salvar.</span>'; } ?>

        <form method="POST" name="form1" id="form1" action="voluntario_alterar.php?id=<?php echo $idVolunteer; ?>" data-parsley-validate="">
            <div class="formulario">
                <div>
                    <label>Nome completo</label>
                    <input type="text" name="nome" maxlength="150" required value="<?php echo htmlspecialchars($dados['nome']); ?>">
                </div>

                <div>
                    <label>CPF</label>
                    <input type="text" name="cpf" id="cpf" placeholder="000.000.000-00" required value="<?php echo htmlspecialchars($dados['cpf']); ?>">
                </div>

                <div>
                    <label>E-mail</label>
                    <input type="email" name="email" maxlength="150" required value="<?php echo htmlspecialchars($dados['email']); ?>">
                </div>

                <div>
                    <label>Telefone <span>(opcional)</span></label>
                    <input type="text" name="telefone" id="telefone" placeholder="(00) 00000-0000" value="<?php echo htmlspecialchars($dados['telefone']); ?>">
                </div>

                <div class="acao-buts">
                    <input type="hidden" name="MM_update" value="form1">
                    <input type="submit" value="Atualizar" class="but">
                    <a href="voluntario_listar.php" class="but but-secundario">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>
<?php include '../includes/rodape.html'; ?>
</body>
</html>
