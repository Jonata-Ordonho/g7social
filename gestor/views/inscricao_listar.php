<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../includes/verificar_login.php';

use App\Controllers\ProjetoController;
use App\Controllers\AtividadeController;
use App\Controllers\InscricaoController;
use App\Helpers\Documento;
use App\Helpers\Permissao;

$controllerProjeto = new ProjetoController;
$controllerAtividade = new AtividadeController;
$controllerInscricao = new InscricaoController;

$idActivity = isset($_GET['id_atividade']) ? (int) $_GET['id_atividade'] : 0;

$atividade = $controllerAtividade->getActivityByID($idActivity);

if($atividade == NULL){
    header("Location: projeto_listar.php?errorInfo=true");
    exit;
}

$projeto = $controllerProjeto->getProjectByID($atividade['id_projeto']);

$inscricoes = $controllerInscricao->getInscriptionsByActivity($idActivity);

$vagasEsgotadas = $atividade['vagas'] != NULL && count($inscricoes) >= $atividade['vagas'];

$podeExcluir = Permissao::podeExcluirVoluntario($_SESSION['SS_UserType']);

// Atividade para onde a inscrição foi transferida (mensagem de confirmação)
$atividadeDestino = NULL;
if(isset($_GET['movedVolunteer'])){
    $atividadeDestino = $controllerAtividade->getActivityByID((int) $_GET['movedVolunteer']);
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
</head>

<body>
<?php include '../includes/topo.html'; ?>
<?php include '../includes/menu.php'; ?>
<div id="container">
    <div id="conteudo">
        <h2 class="titulo titulo-acao">
            <span>Voluntários - <b><?php echo htmlspecialchars($atividade['titulo']); ?></b></span>
            <?php if(!$vagasEsgotadas && Permissao::podeGerenciarVoluntarios($_SESSION['SS_UserType'])){ ?>
                <a href="inscricao_cadastrar.php?id_atividade=<?php echo $idActivity; ?>" title="Inscrever voluntário" class="but-novo icon-user-plus">Inscrever voluntário</a>
            <?php } ?>
        </h2>

        <?php if(isset($_GET['newVolunteer'])){echo '<span class="alerta sucesso">'.($_GET['newVolunteer'] == 'existente' ? 'Voluntário já cadastrado no banco: inscrição realizada com sucesso!' : 'Voluntário cadastrado e inscrito com sucesso!').'</span>';}?>
        <?php if($atividadeDestino != NULL){echo '<span class="alerta sucesso">Inscrição transferida para a atividade "'.htmlspecialchars($atividadeDestino['titulo']).'".</span>';}?>
        <?php if(isset($_GET['deletedVolunteer'])){echo '<span class="alerta sucesso">Inscrição excluída com sucesso! O voluntário continua no banco de voluntários.</span>';}?>
        <?php if(isset($_GET['semPermissao'])){echo '<span class="alerta">Você não tem permissão para excluir inscrições.</span>';}?>
        <?php if(isset($_GET['semPermissaoInscrever'])){echo '<span class="alerta">Você não tem permissão para inscrever voluntários.</span>';}?>
        <?php if(isset($_GET['errorDelete'])){echo '<span class="alerta">Não foi possível excluir a inscrição.</span>';}?>
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
                <td><?php echo $atividade['vagas'] != NULL ? count($inscricoes).' de '.$atividade['vagas'].' preenchidas' : count($inscricoes).' inscritos (sem limite de vagas)'; ?></td>
            </tr>
        </table>

        <h3>Voluntários inscritos (<?php echo count($inscricoes); ?>)</h3>

        <?php if(count($inscricoes) > 0){ ?>
            <table cellspacing="0" cellpadding="0" class="tab-listagem">
                <thead>
                <tr>
                    <th width="50">ID</th>
                    <th>Nome</th>
                    <th width="140">CPF</th>
                    <th width="150">Telefone</th>
                    <th width="250">E-mail</th>
                    <th width="140">Inscrição</th>
                    <th width="90">Ação</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($inscricoes as $inscricao) { ?>
                    <tr>
                        <td class="id"><?php echo $inscricao['id_voluntario']; ?></td>
                        <td class="nome"><a href="voluntario_detalhar.php?id=<?php echo $inscricao['id_voluntario']; ?>" title="Ver dados do voluntário"><b><?php echo htmlspecialchars($inscricao['nome']); ?></b></a></td>
                        <td align="center"><?php echo $inscricao['cpf'] != NULL ? Documento::formatarCPF($inscricao['cpf']) : '<i>Não informado</i>'; ?></td>
                        <td align="center"><?php echo Documento::formatarTelefone((string) $inscricao['telefone']); ?></td>
                        <td><?php echo htmlspecialchars($inscricao['email']); ?></td>
                        <td align="center"><?php echo date('d/m/Y H:i', strtotime($inscricao['data_inscricao'])); ?></td>
                        <td class="actions">
                            <a href="inscricao_alterar_atividade.php?id=<?php echo $inscricao['id_inscricao']; ?>" title="Alterar a atividade desta inscrição" class="icon-forward">Alterar atividade</a>
                            <?php if($podeExcluir){ ?>
                                <form method="POST" action="inscricao_excluir.php" onsubmit="return confirm('Deseja excluir a inscrição de <?php echo htmlspecialchars(addslashes($inscricao['nome']), ENT_QUOTES); ?> nesta atividade? O voluntário continuará no banco de voluntários.');">
                                    <input type="hidden" name="id_inscricao" value="<?php echo $inscricao['id_inscricao']; ?>">
                                    <button type="submit" title="Excluir inscrição" class="icon-trash acao-excluir">Excluir</button>
                                </form>
                            <?php }else{ ?>
                                <a title="Você não tem permissão para excluir inscrições. class="icon-trash desativar">Excluir</a>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        <?php }else{
            echo '<span class="alerta">Nenhum voluntário inscrito nesta atividade.</span>';
        } ?>

        <div class="acao-buts">
            <a href="atividade_listar.php?id_projeto=<?php echo $atividade['id_projeto']; ?>" class="but but-secundario">Voltar</a>
        </div>
    </div>
</div>
<?php include '../includes/rodape.html'; ?>
</body>
</html>
