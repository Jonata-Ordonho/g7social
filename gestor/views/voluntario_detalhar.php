<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../includes/verificar_login.php';

use App\Controllers\VoluntarioController;
use App\Helpers\Documento;
use App\Helpers\Permissao;

$controllerVoluntario = new VoluntarioController;

$idVolunteer = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$voluntario = $controllerVoluntario->getVolunteerByID($idVolunteer);

if($voluntario == NULL){
    header("Location: voluntario_listar.php?errorInfo=true");
    exit;
}

$participacoes = $controllerVoluntario->getParticipations($idVolunteer);

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
            <span>Voluntário - <b><?php echo htmlspecialchars($voluntario['nome']); ?></b></span>
            <?php if(Permissao::podeGerenciarVoluntarios($_SESSION['SS_UserType'])){ ?>
                <a href="voluntario_alterar.php?id=<?php echo $idVolunteer; ?>" title="Alterar dados do voluntário" class="but-novo icon-pencil">Alterar</a>
            <?php } ?>
        </h2>

        <?php if(isset($_GET['updateVolunteer'])){echo '<span class="alerta sucesso">Dados do voluntário alterados com sucesso!</span>';}?>
        <?php if(isset($_GET['semPermissao'])){echo '<span class="alerta">Somente administradores podem alterar os dados dos voluntários.</span>';}?>

        <table id="tab-detalhes" cellspacing="0" cellpadding="0">
            <tr>
                <td class="rotulo">ID:</td>
                <td><?php echo $voluntario['id_voluntario']; ?></td>
            </tr>
            <tr>
                <td class="rotulo">Nome:</td>
                <td><?php echo htmlspecialchars($voluntario['nome']); ?></td>
            </tr>
            <tr>
                <td class="rotulo">CPF:</td>
                <td><?php echo $voluntario['cpf'] != NULL ? Documento::formatarCPF($voluntario['cpf']) : '<i>Não informado</i>'; ?></td>
            </tr>
            <tr>
                <td class="rotulo">E-mail:</td>
                <td><?php echo $voluntario['email'] != NULL ? htmlspecialchars($voluntario['email']) : '<i>Não informado</i>'; ?></td>
            </tr>
            <tr>
                <td class="rotulo">Telefone:</td>
                <td><?php echo $voluntario['telefone'] != NULL ? Documento::formatarTelefone($voluntario['telefone']) : '<i>Não informado</i>'; ?></td>
            </tr>
            <tr>
                <td class="rotulo">Cadastrado em:</td>
                <td><?php echo date('d/m/Y \à\s H:i', strtotime($voluntario['data_registro'])); ?></td>
            </tr>
            <tr>
                <td class="rotulo">Última atualização:</td>
                <td><?php echo date('d/m/Y \à\s H:i', strtotime($voluntario['data_atualizacao'])); ?></td>
            </tr>
        </table>

        <h3>Participações em projetos/atividades (<?php echo count($participacoes); ?>)</h3>

        <?php if(count($participacoes) > 0){ ?>
            <table cellspacing="0" cellpadding="0" class="tab-listagem">
                <thead>
                <tr>
                    <th>Projeto</th>
                    <th>Atividade</th>
                    <th width="190">Período</th>
                    <th width="140">Inscrição</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($participacoes as $p) { ?>
                    <tr>
                        <td class="nome"><b><?php echo htmlspecialchars($p['projeto_titulo']); ?></b></td>
                        <td><a href="inscricao_listar.php?id_atividade=<?php echo $p['id_atividade']; ?>" title="Ver voluntários da atividade"><?php echo htmlspecialchars($p['atividade_titulo']); ?></a></td>
                        <td align="center"><?php echo date('d/m/Y', strtotime($p['data_inicio'])).' - '.date('d/m/Y', strtotime($p['data_fim'])); ?></td>
                        <td align="center"><?php echo date('d/m/Y H:i', strtotime($p['data_inscricao'])); ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        <?php }else{
            echo '<span class="alerta">Este voluntário ainda não participou de nenhuma atividade.</span>';
        } ?>

        <div class="acao-buts">
            <a href="voluntario_listar.php" class="but but-secundario">Voltar</a>
        </div>
    </div>
</div>
<?php include '../includes/rodape.html'; ?>
</body>
</html>
