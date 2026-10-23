<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../includes/verificar_login.php';

use App\Controllers\ProjetoController;
use App\Controllers\AtividadeController;
use App\Helpers\Permissao;

$gerenciaAtividades = Permissao::podeGerenciarAtividades($_SESSION['SS_UserType']);

$controllerProjeto = new ProjetoController;
$controllerAtividade = new AtividadeController;

$idProject = isset($_GET['id_projeto']) ? (int) $_GET['id_projeto'] : 0;

$projeto = $controllerProjeto->getProjectByID($idProject);

if($projeto == NULL){
    header("Location: projeto_listar.php?errorInfo=true");
    exit;
}

$atividades = $controllerAtividade->getActivitiesByProject($idProject);

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
            <span>Atividades - <b><?php echo htmlspecialchars($projeto['titulo']); ?></b></span>
            <?php if($gerenciaAtividades){ ?>
                <a href="atividade_cadastrar.php?id_projeto=<?php echo $idProject; ?>" title="Cadastrar atividade" class="but-novo icon-plus">Cadastrar atividade</a>
            <?php } ?>
        </h2>

        <?php if(isset($_GET['newActivity'])){echo '<span class="alerta sucesso">Atividade cadastrada com sucesso!</span>';}?>
        <?php if(isset($_GET['updateActivity'])){echo '<span class="alerta sucesso">Atividade alterada com sucesso!</span>';}?>
        <?php if(isset($_GET['errorInfo'])){echo '<span class="alerta">Nenhum dado de atividade encontrado!</span>';}?>
        <?php if(isset($_GET['semPermissao'])){echo '<span class="alerta">Você não tem permissão para cadastrar, alterar, ativar ou desativar atividades.</span>';}?>
        <?php if(isset($_GET['statusActivity'])){echo '<span class="alerta sucesso">Atividade '.($_GET['statusActivity'] == 'off' ? 'desativada. Ela não aparece mais na página de inscrição do projeto.' : 'ativada. Ela passou a aparecer na página de inscrição do projeto.').'</span>';}?>

        <?php if(count($atividades) > 0){ ?>
            <table cellspacing="0" cellpadding="0" class="tab-listagem">
                <thead>
                <tr>
                    <th width="50">ID</th>
                    <th>Atividade</th>
                    <th width="190">Período</th>
                    <th width="200">Local</th>
                    <th width="140">Voluntários</th>
                    <th width="120">Ação</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($atividades as $atividade) { ?>
                    <tr>
                        <td class="id"><?php echo $atividade['id_atividade']; ?></td>
                        <td class="nome">
                            <b><?php echo htmlspecialchars($atividade['titulo']); ?></b>
                            <?php if($atividade['status'] == 'on'){ ?>
                                <span class="tag tag-ativo">Ativa</span>
                            <?php }else{ ?>
                                <span class="tag tag-inativo">Desativada</span>
                            <?php } ?>
                            <?php if($atividade['descricao'] != ''){ ?>
                                <br><?php echo htmlspecialchars($atividade['descricao']); ?>
                            <?php } ?>
                        </td>
                        <td align="center">
                            <?php echo date('d/m/Y', strtotime($atividade['data_inicio'])).' - '.date('d/m/Y', strtotime($atividade['data_fim'])); ?>
                            <?php if($atividade['horario'] != NULL){ echo '<br><small>'.htmlspecialchars($atividade['horario']).'</small>'; } ?>
                        </td>
                        <td><?php echo htmlspecialchars($atividade['local']); ?></td>
                        <td align="center">
                            <?php if($atividade['vagas'] != NULL && $atividade['vagas'] > 0){
                                $percentual = floor($atividade['total_voluntarios'] / $atividade['vagas'] * 100);
                                $lotada = $percentual >= 100; ?>
                                <div class="progresso-vagas" title="<?php echo $percentual; ?>% das vagas preenchidas">
                                    <span class="progresso-valores"><b><?php echo $atividade['total_voluntarios']; ?></b> de <?php echo $atividade['vagas']; ?></span>
                                    <div class="progresso-trilho">
                                        <div class="progresso-barra<?php if($lotada){ echo ' atingida'; } ?>" style="width: <?php echo min(100, $percentual); ?>%"></div>
                                    </div>
                                    <small class="progresso-percentual<?php if($lotada){ echo ' atingida'; } ?>"><?php echo $lotada ? 'Lotada' : $percentual.'%'; ?></small>
                                </div>
                            <?php }else{
                                echo $atividade['total_voluntarios'].' <small>(sem limite)</small>';
                            } ?>
                        </td>
                        <td class="actions">
                            <?php if($gerenciaAtividades){ ?>
                                <a href="atividade_alterar.php?id=<?php echo $atividade['id_atividade']; ?>" title="Alterar atividade" class="icon-pencil">Alterar</a>
                            <?php }else{ ?>
                                <a title="Você não tem permissão para alterar atividades" class="icon-pencil desativar">Alterar</a>
                            <?php } ?>
                            <a href="inscricao_listar.php?id_atividade=<?php echo $atividade['id_atividade']; ?>" title="Voluntários da atividade" class="icon-users">Voluntários</a>
                            <?php if(!$gerenciaAtividades){ ?>
                                <a title="Você não tem permissão para ativar ou desativar atividades" class="<?php echo $atividade['status'] == 'on' ? 'icon-down-big' : 'icon-up-big'; ?> desativar"><?php echo $atividade['status'] == 'on' ? 'Desativar' : 'Ativar'; ?></a>
                            <?php }elseif($atividade['status'] == 'on'){ ?>
                                <form method="POST" action="atividade_status.php" onsubmit="return confirm('Deseja desativar esta atividade? Ela deixará de aparecer na página de inscrição do projeto.');">
                                    <input type="hidden" name="id_atividade" value="<?php echo $atividade['id_atividade']; ?>">
                                    <input type="hidden" name="status" value="off">
                                    <button type="submit" title="Desativar (fechar inscrições)" class="icon-down-big">Desativar</button>
                                </form>
                            <?php }else{ ?>
                                <form method="POST" action="atividade_status.php" onsubmit="return confirm('Deseja ativar esta atividade? Ela passará a aparecer na página de inscrição do projeto.');">
                                    <input type="hidden" name="id_atividade" value="<?php echo $atividade['id_atividade']; ?>">
                                    <input type="hidden" name="status" value="on">
                                    <button type="submit" title="Ativar (abrir inscrições)" class="icon-up-big">Ativar</button>
                                </form>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        <?php }else{
            echo '<span class="alerta">Nenhuma atividade cadastrada para este projeto.</span>';
        } ?>

        <div class="acao-buts">
            <a href="projeto_listar.php" class="but but-secundario">Voltar</a>
        </div>
    </div>
</div>
<?php include '../includes/rodape.html'; ?>
</body>
</html>
