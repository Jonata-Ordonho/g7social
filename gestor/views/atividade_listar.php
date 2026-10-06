<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../includes/verificar_login.php';

use App\Controllers\ProjetoController;
use App\Controllers\AtividadeController;

$controllerProjeto = new ProjetoController;
$controllerAtividade = new AtividadeController;

$idProject = isset($_GET['id_projeto']) ? (int) $_GET['id_projeto'] : 0;

$projeto = $controllerProjeto->getProjectByID($idProject);

if($projeto == NULL){
    header("Location: projeto_listar.php?errorInfo=true");
    exit;
}

$atividades = $controllerAtividade->getActivitiesByProject($idProject);

// Endereço base da página pública de inscrição (home/views/inscricao.php)
$raizSistema = rtrim(str_replace('\\', '/', dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])))), '/');
$protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https' : 'http';
$linkInscricao = $protocolo.'://'.$_SERVER['HTTP_HOST'].$raizSistema.'/home/views/inscricao.php?c=';

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
            <a href="atividade_cadastrar.php?id_projeto=<?php echo $idProject; ?>" title="Cadastrar atividade" class="but-novo icon-plus">Cadastrar atividade</a>
        </h2>

        <?php if(isset($_GET['newActivity'])){echo '<span class="alerta sucesso">Atividade cadastrada com sucesso!</span>';}?>
        <?php if(isset($_GET['updateActivity'])){echo '<span class="alerta sucesso">Atividade alterada com sucesso!</span>';}?>
        <?php if(isset($_GET['errorInfo'])){echo '<span class="alerta">Nenhum dado de atividade encontrado!</span>';}?>
        <?php if(isset($_GET['statusActivity'])){echo '<span class="alerta sucesso">Atividade '.($_GET['statusActivity'] == 'off' ? 'desativada. A página de inscrição ficou indisponível.' : 'ativada. A página de inscrição está disponível.').'</span>';}?>

        <?php if(count($atividades) > 0){ ?>
            <table cellspacing="0" cellpadding="0" class="tab-listagem">
                <thead>
                <tr>
                    <th width="50">ID</th>
                    <th>Atividade</th>
                    <th width="190">Período</th>
                    <th width="200">Local</th>
                    <th width="110">Voluntários</th>
                    <th width="170">Ação</th>
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
                            <?php echo $atividade['vagas'] != NULL ? $atividade['total_voluntarios'].' de '.$atividade['vagas'] : $atividade['total_voluntarios'].' <small>(sem limite)</small>'; ?>
                        </td>
                        <td class="actions">
                            <a href="atividade_alterar.php?id=<?php echo $atividade['id_atividade']; ?>" title="Alterar atividade" class="icon-pencil">Alterar</a>
                            <a href="voluntario_listar.php?id_atividade=<?php echo $atividade['id_atividade']; ?>" title="Voluntários da atividade" class="icon-users">Voluntários</a>
                            <?php if($atividade['status'] == 'on'){ ?>
                                <a href="<?php echo $linkInscricao.$atividade['codigo']; ?>" target="_blank" rel="noopener" title="Abrir página de inscrição" class="icon-link-ext">Abrir link</a>
                                <a href="#" data-link="<?php echo $linkInscricao.$atividade['codigo']; ?>" title="Copiar link de inscrição" class="icon-docs copiar-link">Copiar link</a>
                                <a href="atividade_status.php?id=<?php echo $atividade['id_atividade']; ?>&status=off" onclick="return confirm('Deseja desativar esta atividade? A página de inscrição ficará indisponível.');" title="Desativar (fechar inscrições)" class="icon-down-big">Desativar</a>
                            <?php }else{ ?>
                                <a title="Ative a atividade para liberar o link de inscrição" class="icon-link-ext desativar">Link indisponível</a>
                                <a title="Ative a atividade para liberar o link de inscrição" class="icon-docs desativar">Link indisponível</a>
                                <a href="atividade_status.php?id=<?php echo $atividade['id_atividade']; ?>&status=on" onclick="return confirm('Deseja ativar esta atividade? A página de inscrição ficará disponível.');" title="Ativar (abrir inscrições)" class="icon-up-big">Ativar</a>
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
<script>
    // Copia o link de inscrição (com alternativa para navegadores sem acesso à área de transferência)
    document.querySelectorAll('.copiar-link').forEach(function (botao) {
        botao.addEventListener('click', function (e) {
            e.preventDefault();
            var link = botao.getAttribute('data-link');
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(link).then(function () {
                    alert('Link de inscrição copiado!');
                });
            } else {
                window.prompt('Copie o link de inscrição:', link);
            }
        });
    });
</script>
</body>
</html>
