<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../includes/verificar_login.php';

use App\Controllers\ProjetoController;
use App\Controllers\AtividadeController;
use App\Controllers\VoluntarioController;
use App\Helpers\Documento;

$controllerProjeto = new ProjetoController;
$controllerAtividade = new AtividadeController;
$controllerVoluntario = new VoluntarioController;

$idActivity = isset($_GET['id_atividade']) ? (int) $_GET['id_atividade'] : 0;

$atividade = $controllerAtividade->getActivityByID($idActivity);

if($atividade == NULL){
    header("Location: projeto_listar.php?errorInfo=true");
    exit;
}

$projeto = $controllerProjeto->getProjectByID($atividade['id_projeto']);

$voluntarios = $controllerVoluntario->getVolunteersByActivity($idActivity);

$vagasEsgotadas = $atividade['vagas'] != NULL && count($voluntarios) >= $atividade['vagas'];

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
</head>

<body>
<?php include '../includes/topo.html'; ?>
<?php include '../includes/menu.php'; ?>
<div id="container">
    <div id="conteudo">
        <h2 class="titulo titulo-acao">
            <span>Voluntários - <b><?php echo htmlspecialchars($atividade['titulo']); ?></b></span>
            <?php if(!$vagasEsgotadas){ ?>
                <a href="voluntario_cadastrar.php?id_atividade=<?php echo $idActivity; ?>" title="Cadastrar voluntário" class="but-novo icon-user-plus">Cadastrar voluntário</a>
            <?php } ?>
        </h2>

        <?php if(isset($_GET['newVolunteer'])){echo '<span class="alerta sucesso">Voluntário cadastrado com sucesso!</span>';}?>
        <?php if($vagasEsgotadas){echo '<span class="alerta">Todas as vagas desta atividade foram preenchidas.</span>';}?>

        <table id="tab-detalhes" cellspacing="0" cellpadding="0">
            <tr>
                <td class="rotulo">Projeto:</td>
                <td><?php echo htmlspecialchars($projeto['titulo']); ?></td>
            </tr>
            <tr>
                <td class="rotulo">Período:</td>
                <td><?php echo date('d/m/Y', strtotime($atividade['data_inicio'])).' - '.date('d/m/Y', strtotime($atividade['data_fim'])); if($atividade['horario'] != NULL){ echo ' ('.htmlspecialchars($atividade['horario']).')'; } ?></td>
            </tr>
            <tr>
                <td class="rotulo">Local:</td>
                <td><?php echo htmlspecialchars($atividade['local']); ?></td>
            </tr>
            <tr>
                <td class="rotulo">Vagas:</td>
                <td><?php echo $atividade['vagas'] != NULL ? count($voluntarios).' de '.$atividade['vagas'].' preenchidas' : count($voluntarios).' inscritos (sem limite de vagas)'; ?></td>
            </tr>
        </table>

        <h3>Voluntários inscritos (<?php echo count($voluntarios); ?>)</h3>

        <?php if(count($voluntarios) > 0){ ?>
            <table cellspacing="0" cellpadding="0" class="tab-listagem">
                <thead>
                <tr>
                    <th width="50">ID</th>
                    <th>Nome</th>
                    <th width="160">Telefone</th>
                    <th width="280">E-mail</th>
                    <th width="140">Cadastro</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($voluntarios as $voluntario) { ?>
                    <tr>
                        <td class="id"><?php echo $voluntario['id_voluntario']; ?></td>
                        <td class="nome"><b><?php echo htmlspecialchars($voluntario['nome']); ?></b></td>
                        <td align="center"><?php echo Documento::formatarTelefone((string) $voluntario['telefone']); ?></td>
                        <td><?php echo htmlspecialchars($voluntario['email']); ?></td>
                        <td align="center"><?php echo date('d/m/Y H:i', strtotime($voluntario['data_registro'])); ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        <?php }else{
            echo '<span class="alerta">Nenhum voluntário cadastrado nesta atividade.</span>';
        } ?>

        <div class="acao-buts">
            <a href="atividade_listar.php?id_projeto=<?php echo $atividade['id_projeto']; ?>" class="but but-secundario">Voltar</a>
        </div>
    </div>
</div>
<?php include '../includes/rodape.html'; ?>
</body>
</html>
