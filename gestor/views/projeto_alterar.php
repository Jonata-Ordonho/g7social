<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../includes/verificar_login.php';

use App\Controllers\ProjetoController;
use App\Controllers\LogController;
use App\Models\Projeto;

$controllerProjeto = new ProjetoController;
$controllerLogs = new LogController;

$idProject = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$projeto = $controllerProjeto->getProjectByID($idProject);

if($projeto == NULL){
    header("Location: projeto_listar.php?errorInfo=true");
    exit;
}

$erro = '';

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
    $projeto['titulo'] = trim($_POST['titulo']);
    $projeto['descricao'] = trim($_POST['descricao']);
    $projeto['captacao_empresas'] = isset($_POST['captacao_empresas']) ? 's' : 'n';
    $projeto['captacao_voluntarios'] = isset($_POST['captacao_voluntarios']) ? 's' : 'n';
    $projeto['data_inicio'] = $_POST['data_inicio'];
    $projeto['data_fim'] = $_POST['data_fim'];

    if($projeto['titulo'] == '' || !strtotime($projeto['data_inicio']) || !strtotime($projeto['data_fim'])){
        $erro = 'Preencha o título e as datas do período de captação.';
    }elseif($projeto['data_fim'] < $projeto['data_inicio']){
        $erro = 'A data final da captação não pode ser anterior à data inicial.';
    }else{
        $objProjeto = new Projeto;
        $objProjeto->setProjetoID($idProject);
        $objProjeto->setTitulo($projeto['titulo']);
        $objProjeto->setDescricao($projeto['descricao']);
        $objProjeto->setCaptacaoEmpresas($projeto['captacao_empresas']);
        $objProjeto->setCaptacaoVoluntarios($projeto['captacao_voluntarios']);
        $objProjeto->setDataInicio($projeto['data_inicio']);
        $objProjeto->setDataFim($projeto['data_fim']);

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
            <div class="formulario">
                <div>
                    <label>Título</label>
                    <input type="text" name="titulo" maxlength="200" required value="<?php echo htmlspecialchars($projeto['titulo']); ?>">
                </div>

                <div>
                    <label>Descrição</label>
                    <textarea name="descricao" rows="5"><?php echo htmlspecialchars($projeto['descricao']); ?></textarea>
                </div>

                <div id="areas">
                    <label>Captação</label>
                    <div>
                        <label><input type="checkbox" name="captacao_empresas" value="s" <?php if($projeto['captacao_empresas'] == 's'){ echo 'checked'; } ?>> Doações de empresas</label><br>
                        <label><input type="checkbox" name="captacao_voluntarios" value="s" <?php if($projeto['captacao_voluntarios'] == 's'){ echo 'checked'; } ?>> Voluntários</label>
                    </div>
                </div>

                <div>
                    <label>Início da captação</label>
                    <input type="date" name="data_inicio" required value="<?php echo htmlspecialchars($projeto['data_inicio']); ?>">
                </div>

                <div>
                    <label>Fim da captação</label>
                    <input type="date" name="data_fim" required value="<?php echo htmlspecialchars($projeto['data_fim']); ?>">
                </div>

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
