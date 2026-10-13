<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../includes/verificar_login.php';

use App\Controllers\ProjetoController;
use App\Controllers\AtividadeController;
use App\Controllers\LogController;
use App\Helpers\Permissao;
use App\Models\Atividade;

$controllerProjeto = new ProjetoController;
$controllerAtividade = new AtividadeController;
$controllerLogs = new LogController;

$idProject = isset($_GET['id_projeto']) ? (int) $_GET['id_projeto'] : 0;

$projeto = $controllerProjeto->getProjectByID($idProject);

if($projeto == NULL){
    header("Location: projeto_listar.php?errorInfo=true");
    exit;
}

// Somente administradores cadastram atividades
if(!Permissao::podeGerenciarAtividades($_SESSION['SS_UserType'])){
    header("Location: atividade_listar.php?id_projeto=".$idProject."&semPermissao=true");
    exit;
}

$dados = array(
    'titulo' => '',
    'descricao' => '',
    'local' => '',
    'data_inicio' => '',
    'data_fim' => '',
    'horario' => '',
    'vagas' => '',
    'status' => 'on'
);

$erro = '';

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
    $dados['titulo'] = trim($_POST['titulo']);
    $dados['descricao'] = trim($_POST['descricao']);
    $dados['local'] = trim($_POST['local']);
    $dados['data_inicio'] = $_POST['data_inicio'];
    $dados['data_fim'] = $_POST['data_fim'];
    $dados['horario'] = trim($_POST['horario']);
    $dados['vagas'] = trim($_POST['vagas']);
    $dados['status'] = (isset($_POST['status']) && $_POST['status'] == 'off') ? 'off' : 'on';

    if($dados['titulo'] == '' || $dados['local'] == '' || !strtotime($dados['data_inicio']) || !strtotime($dados['data_fim'])){
        $erro = 'Preencha o título, o local e as datas da atividade.';
    }elseif($dados['data_fim'] < $dados['data_inicio']){
        $erro = 'A data final da atividade não pode ser anterior à data inicial.';
    }elseif($dados['data_inicio'] < $projeto['data_inicio'] || $dados['data_fim'] > $projeto['data_fim']){
        $erro = 'As datas da atividade devem ficar dentro do período do projeto ('.date('d/m/Y', strtotime($projeto['data_inicio'])).' a '.date('d/m/Y', strtotime($projeto['data_fim'])).').';
    }elseif($dados['vagas'] != '' && !ctype_digit($dados['vagas'])){
        $erro = 'O número de vagas deve ser um número inteiro.';
    }else{
        $objAtividade = new Atividade;
        $objAtividade->setProjetoID($idProject);
        $objAtividade->setTitulo($dados['titulo']);
        $objAtividade->setDescricao($dados['descricao']);
        $objAtividade->setLocal($dados['local']);
        $objAtividade->setDataInicio($dados['data_inicio']);
        $objAtividade->setDataFim($dados['data_fim']);
        $objAtividade->setHorario($dados['horario'] != '' ? $dados['horario'] : NULL);
        $objAtividade->setVagas($dados['vagas'] != '' ? (int) $dados['vagas'] : NULL);
        $objAtividade->setStatus($dados['status']);
        $objAtividade->setUsuarioID($_SESSION['SS_UserID']);

        $idAtividade = $controllerAtividade->insertActivity($objAtividade);

        if($idAtividade){
            $controllerLogs->insertActionUserLog(3, $_SESSION['SS_UserID'], 'Atividade ID '.$idAtividade.' - Projeto ID '.$idProject);

            header("Location: atividade_listar.php?id_projeto=".$idProject."&newActivity=true");
            exit;
        }else{
            $erro = 'Não foi possível cadastrar a atividade.';
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
        <h2 class="titulo">Cadastrar Atividade - <b><?php echo htmlspecialchars($projeto['titulo']); ?></b></h2>

        <?php if($erro != ''){ echo '<span class="alerta">'.$erro.'</span>'; } ?>

        <form method="POST" name="form1" id="form1" action="atividade_cadastrar.php?id_projeto=<?php echo $idProject; ?>" data-parsley-validate="">
            <?php include '../includes/form_atividade.php'; ?>

            <div class="formulario">
                <div class="acao-buts">
                    <input type="hidden" name="MM_insert" value="form1">
                    <input type="submit" value="Cadastrar" class="but">
                    <a href="atividade_listar.php?id_projeto=<?php echo $idProject; ?>" class="but but-secundario">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>
<?php include '../includes/rodape.html'; ?>
</body>
</html>
