<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../includes/verificar_login.php';

use App\Controllers\ProjetoController;
use App\Controllers\AtividadeController;
use App\Controllers\InscricaoController;
use App\Controllers\LogController;

$controllerProjeto = new ProjetoController;
$controllerAtividade = new AtividadeController;
$controllerInscricao = new InscricaoController;
$controllerLogs = new LogController;

$idInscricao = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Inscrição com os dados do voluntário (nome, CPF, e-mail)
$voluntario = $controllerInscricao->getInscriptionByID($idInscricao);

if($voluntario == NULL){
    header("Location: projeto_listar.php?errorInfo=true");
    exit;
}

$atividadeAtual = $controllerAtividade->getActivityByID($voluntario['id_atividade']);
$projeto = $controllerProjeto->getProjectByID($atividadeAtual['id_projeto']);

// Outras atividades do mesmo projeto, com o total de inscritos de cada uma
$atividades = array();
foreach ($controllerAtividade->getActivitiesByProject($projeto['id_projeto']) as $a) {
    if($a['id_atividade'] != $atividadeAtual['id_atividade']){
        $a['lotada'] = $a['vagas'] != NULL && $a['total_voluntarios'] >= $a['vagas'];
        $atividades[$a['id_atividade']] = $a;
    }
}

$idNova = 0;
$erro = '';

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
    $idNova = isset($_POST['id_atividade']) ? (int) $_POST['id_atividade'] : 0;

    if(!isset($atividades[$idNova])){
        $erro = 'Selecione uma atividade deste projeto.';
    }elseif($atividades[$idNova]['vagas'] != NULL && $controllerInscricao->getInscriptionsTotalByActivity($idNova) >= $atividades[$idNova]['vagas']){
        $erro = 'A atividade escolhida não tem vagas disponíveis.';
    }elseif($controllerInscricao->checkInscription($voluntario['id_voluntario'], $idNova)){
        $erro = 'Este voluntário já está inscrito na atividade escolhida.';
    }else{
        if($controllerInscricao->updateInscriptionActivity($idInscricao, $idNova)){
            $controllerLogs->insertActionUserLog(15, $_SESSION['SS_UserID'], 'Inscrição ID '.$idInscricao.' (voluntário ID '.$voluntario['id_voluntario'].') - Atividade ID '.$atividadeAtual['id_atividade'].' para '.$idNova);

            header("Location: inscricao_listar.php?id_atividade=".$atividadeAtual['id_atividade']."&movedVolunteer=".$idNova);
            exit;
        }else{
            $erro = 'Não foi possível alterar a atividade do voluntário.';
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
        <h2 class="titulo">Alterar Atividade do Voluntário - <b><?php echo htmlspecialchars($voluntario['nome']); ?></b></h2>

        <?php if($erro != ''){ echo '<span class="alerta">'.$erro.'</span>'; } ?>

        <table id="tab-detalhes" cellspacing="0" cellpadding="0">
            <tr>
                <td class="rotulo">Projeto:</td>
                <td><?php echo htmlspecialchars($projeto['titulo']); ?></td>
            </tr>
            <tr>
                <td class="rotulo">Atividade atual:</td>
                <td><?php echo htmlspecialchars($atividadeAtual['titulo']); ?> <small>(<?php echo date('d/m/Y', strtotime($atividadeAtual['data_inicio'])); ?>)</small></td>
            </tr>
            <tr>
                <td class="rotulo">E-mail:</td>
                <td><?php echo htmlspecialchars($voluntario['email']); ?></td>
            </tr>
        </table>

        <?php if(count($atividades) == 0){ ?>
            <span class="alerta">Este projeto não tem outra atividade para onde o voluntário possa ser transferido.</span>

            <div class="acao-buts">
                <a href="inscricao_listar.php?id_atividade=<?php echo $atividadeAtual['id_atividade']; ?>" class="but but-secundario">Voltar</a>
            </div>
        <?php }else{ ?>
            <form method="POST" name="form1" id="form1" action="inscricao_alterar_atividade.php?id=<?php echo $idInscricao; ?>" data-parsley-validate="">
                <div class="formulario">
                    <div>
                        <label>Nova atividade</label>
                        <select name="id_atividade" required>
                            <option value="">Selecione a atividade</option>
                            <?php foreach ($atividades as $a) { ?>
                                <option value="<?php echo $a['id_atividade']; ?>" <?php if($idNova == $a['id_atividade']){ echo 'selected'; } ?> <?php if($a['lotada']){ echo 'disabled'; } ?>>
                                    <?php
                                    echo htmlspecialchars($a['titulo']).' - '.date('d/m/Y', strtotime($a['data_inicio']));
                                    echo $a['vagas'] != NULL ? ' ('.$a['total_voluntarios'].' de '.$a['vagas'].' vagas)' : ' (sem limite de vagas)';
                                    if($a['lotada']){ echo ' - lotada'; }
                                    if($a['status'] == 'off'){ echo ' - desativada'; }
                                    ?>
                                </option>
                            <?php } ?>
                        </select>
                        <span class="dica-campo">Somente atividades deste projeto. Atividades lotadas não podem ser escolhidas.</span>
                    </div>

                    <div class="acao-buts">
                        <input type="hidden" name="MM_update" value="form1">
                        <input type="submit" value="Alterar atividade" class="but">
                        <a href="inscricao_listar.php?id_atividade=<?php echo $atividadeAtual['id_atividade']; ?>" class="but but-secundario">Cancelar</a>
                    </div>
                </div>
            </form>
        <?php } ?>
    </div>
</div>
<?php include '../includes/rodape.html'; ?>
</body>
</html>
