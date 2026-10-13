<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../includes/verificar_gestao_projetos.php';

use App\Controllers\ProjetoController;
use App\Controllers\AtividadeController;
use App\Controllers\LogController;
use App\Helpers\Moeda;
use App\Models\Projeto;

$controllerProjeto = new ProjetoController;
$controllerLogs = new LogController;

$idProject = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$projeto = $controllerProjeto->getProjectByID($idProject);

if($projeto == NULL){
    header("Location: projeto_listar.php?errorInfo=true");
    exit;
}

$projeto['arrecadacao_estimada_campo'] = Moeda::paraCampo($projeto['arrecadacao_estimada']);

// Período ocupado pelas atividades: o novo período do projeto precisa continuar contendo todas elas
$atividadeInicio = NULL;
$atividadeFim = NULL;
foreach ((new AtividadeController)->getActivitiesByProject($idProject) as $a) {
    $atividadeInicio = $atividadeInicio === NULL ? $a['data_inicio'] : min($atividadeInicio, $a['data_inicio']);
    $atividadeFim = $atividadeFim === NULL ? $a['data_fim'] : max($atividadeFim, $a['data_fim']);
}

$erro = '';

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
    $projeto['titulo'] = trim($_POST['titulo']);
    $projeto['descricao'] = trim($_POST['descricao']);
    $projeto['data_inicio'] = $_POST['data_inicio'];
    $projeto['data_fim'] = $_POST['data_fim'];
    $projeto['arrecadacao_estimada_campo'] = isset($_POST['arrecadacao_estimada']) ? trim($_POST['arrecadacao_estimada']) : '';
    $estimada = Moeda::paraDecimal($projeto['arrecadacao_estimada_campo']);

    if($projeto['titulo'] == '' || !strtotime($projeto['data_inicio']) || !strtotime($projeto['data_fim'])){
        $erro = 'Preencha o título e as datas do período de captação.';
    }elseif($projeto['data_fim'] < $projeto['data_inicio']){
        $erro = 'A data final da captação não pode ser anterior à data inicial.';
    }elseif($atividadeInicio !== NULL && ($projeto['data_inicio'] > $atividadeInicio || $projeto['data_fim'] < $atividadeFim)){
        $erro = 'O período do projeto precisa incluir todas as suas atividades, que vão de '.date('d/m/Y', strtotime($atividadeInicio)).' a '.date('d/m/Y', strtotime($atividadeFim)).'. Altere as atividades antes ou escolha um período maior.';
    }elseif($estimada === FALSE || ($estimada !== NULL && $estimada <= 0)){
        $erro = 'Informe uma arrecadação estimada válida ou deixe o campo em branco.';
    }else{
        $objProjeto = new Projeto;
        $objProjeto->setProjetoID($idProject);
        $objProjeto->setTitulo($projeto['titulo']);
        $objProjeto->setDescricao($projeto['descricao']);
        $objProjeto->setDataInicio($projeto['data_inicio']);
        $objProjeto->setDataFim($projeto['data_fim']);
        $objProjeto->setArrecadacaoEstimada($estimada);

        if($controllerProjeto->updateProject($objProjeto)){
            $controllerLogs->insertActionUserLog(2, $_SESSION['SS_UserID'], 'Projeto ID '.$idProject);

            header("Location: projeto_listar.php?updateProject=true");
            exit;
        }else{
            $erro = 'Não foi possível alterar o projeto.';
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
</head>

<body>
<?php include '../includes/topo.html'; ?>
<?php include '../includes/menu.php'; ?>
<div id="container">
    <div id="conteudo">
        <h2 class="titulo">Alterar Projeto</h2>

        <?php if($erro != ''){ echo '<span class="alerta">'.$erro.'</span>'; } ?>

        <form method="POST" name="form1" id="form1" action="projeto_alterar.php?id=<?php echo $idProject; ?>" data-parsley-validate="">
            <?php include '../includes/form_projeto.php'; ?>

            <div class="formulario">
                <div class="acao-buts">
                    <input type="hidden" name="MM_update" value="form1">
                    <input type="submit" value="Atualizar" class="but">
                    <a href="projeto_listar.php" class="but but-secundario">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>
<?php include '../includes/rodape.html'; ?>
</body>
</html>
