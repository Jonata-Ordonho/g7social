<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../includes/verificar_login.php';

date_default_timezone_set('America/Fortaleza');

use App\Controllers\RelatorioController;
use App\Controllers\ProjetoController;
use App\Controllers\LogController;
use App\Helpers\Documento;
use App\Helpers\Moeda;
use App\Helpers\Planilha;

$controllerRelatorio = new RelatorioController;
$controllerProjeto = new ProjetoController;

$listaProjetos = $controllerRelatorio->getProjetosSelect();

$idProject = isset($_GET['id_projeto']) ? (int) $_GET['id_projeto'] : 0;
$projeto = $idProject > 0 ? $controllerProjeto->getProjectByID($idProject) : NULL;

$erro = '';
if($idProject > 0 && $projeto == NULL){
    $erro = 'Projeto não encontrado.';
}

$hoje = date('Y-m-d');
$emitidoEm = date('d/m/Y \à\s H:i');

if($projeto != NULL){
    $resumo = $controllerRelatorio->getResumoProjeto($idProject);
    $atividades = $controllerRelatorio->getAtividadesProjeto($idProject);
    $voluntarios = $controllerRelatorio->getVoluntariosProjeto($idProject);
    $arrecadacoes = $controllerRelatorio->getArrecadacoesProjeto($idProject);
    $doadores = $controllerRelatorio->getDoadoresProjeto($idProject);

    $periodoTexto = date('d/m/Y', strtotime($projeto['data_inicio'])).' a '.date('d/m/Y', strtotime($projeto['data_fim']));
    $situacao = $projeto['data_fim'] >= $hoje ? 'Ativo' : 'Finalizado';
    $estimado = $projeto['arrecadacao_estimada'] != NULL ? (float) $projeto['arrecadacao_estimada'] : 0;

    /***** EXPORTAÇÃO EM EXCEL *****/
    if(isset($_GET['exportar']) && $_GET['exportar'] == 'xlsx'){
        (new LogController)->insertActionUserLog(18, $_SESSION['SS_UserID'], 'Relatório do projeto ID '.$idProject);

        $linhasAtividades = array();
        foreach ($atividades as $a) {
            $linhasAtividades[] = array(
                $a['titulo'], $a['data_inicio'], $a['data_fim'], (string) $a['horario'], $a['local'],
                $a['vagas'], $a['inscritos'], $a['vagas'] > 0 ? $a['inscritos'] / $a['vagas'] : NULL,
                $a['status'] == 'on' ? 'Ativa' : 'Desativada'
            );
        }

        $linhasVoluntarios = array();
        foreach ($voluntarios as $v) {
            $linhasVoluntarios[] = array(
                $v['atividade_titulo'], $v['data_inicio'], $v['nome'], $v['cpf'] != NULL ? Documento::mascararCPF($v['cpf']) : 'Não informado',
                Documento::formatarTelefone((string) $v['telefone']), (string) $v['email'], $v['data_inscricao']
            );
        }

        $linhasArrecadacoes = array();
        foreach ($arrecadacoes as $r) {
            $linhasArrecadacoes[] = array(
                $r['data_arrecadacao'], $r['doador_nome'], $r['doador_tipo'] == 'empresa' ? 'Empresa' : 'Pessoa',
                $r['tipo'] == 'dinheiro' ? 'Dinheiro' : 'Itens', $r['tipo'] == 'dinheiro' ? $r['valor'] : NULL, (string) $r['descricao']
            );
        }

        $linhasDoadores = array();
        foreach ($doadores as $d) {
            $linhasDoadores[] = array($d['nome'], $d['tipo'] == 'empresa' ? 'Empresa' : 'Pessoa', $d['doacoes'], $d['doacoes_itens'], $d['total']);
        }

        $nomeArquivo = 'relatorio_projeto_'.$idProject.'_'.trim(preg_replace('/[^a-z0-9]+/', '_', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $projeto['titulo']))), '_').'.xlsx';

        Planilha::baixar($nomeArquivo, $projeto['titulo'], 'Período '.$periodoTexto.' · '.$situacao.' · Emitido em '.$emitidoEm, array(
            array(
                'nome' => 'Resumo',
                'colunas' => array(array('titulo' => 'Indicador', 'largura' => 38), array('titulo' => 'Valor', 'largura' => 24)),
                'linhas' => array(
                    array('Projeto', $projeto['titulo']),
                    array('Período', $periodoTexto),
                    array('Situação', $situacao),
                    array('Atividades', $resumo['atividades']),
                    array('Inscrições em atividades', $resumo['inscricoes']),
                    array('Voluntários (pessoas distintas)', $resumo['voluntarios']),
                    array('Arrecadação estimada (R$)', $estimado > 0 ? number_format($estimado, 2, ',', '.') : 'Não informada'),
                    array('Arrecadado em dinheiro (R$)', number_format($resumo['arrecadado'], 2, ',', '.')),
                    array('Percentual da estimativa', $estimado > 0 ? floor($resumo['arrecadado'] / $estimado * 100).'%' : '-'),
                    array('Doações em dinheiro', $resumo['doacoes_dinheiro']),
                    array('Doações em itens', $resumo['doacoes_itens']),
                    array('Doadores (distintos)', $resumo['doadores']),
                ),
            ),
            array(
                'nome' => 'Atividades',
                'colunas' => array(
                    array('titulo' => 'Atividade', 'largura' => 36),
                    array('titulo' => 'Início', 'formato' => 'data', 'largura' => 12),
                    array('titulo' => 'Fim', 'formato' => 'data', 'largura' => 12),
                    array('titulo' => 'Horário', 'largura' => 16),
                    array('titulo' => 'Local', 'largura' => 28),
                    array('titulo' => 'Vagas', 'formato' => 'inteiro', 'largura' => 8),
                    array('titulo' => 'Inscritos', 'formato' => 'inteiro', 'largura' => 10),
                    array('titulo' => '% preenchido', 'formato' => 'percentual', 'largura' => 13),
                    array('titulo' => 'Status', 'largura' => 12),
                ),
                'linhas' => $linhasAtividades,
            ),
            array(
                'nome' => 'Voluntários',
                'colunas' => array(
                    array('titulo' => 'Atividade', 'largura' => 32),
                    array('titulo' => 'Data da atividade', 'formato' => 'data', 'largura' => 16),
                    array('titulo' => 'Voluntário', 'largura' => 32),
                    array('titulo' => 'CPF', 'largura' => 16),
                    array('titulo' => 'Telefone', 'largura' => 16),
                    array('titulo' => 'E-mail', 'largura' => 32),
                    array('titulo' => 'Inscrição', 'formato' => 'datahora', 'largura' => 17),
                ),
                'linhas' => $linhasVoluntarios,
            ),
            array(
                'nome' => 'Arrecadações',
                'colunas' => array(
                    array('titulo' => 'Data', 'formato' => 'data', 'largura' => 12),
                    array('titulo' => 'Doador', 'largura' => 34),
                    array('titulo' => 'Tipo de doador', 'largura' => 15),
                    array('titulo' => 'Tipo', 'largura' => 10),
                    array('titulo' => 'Valor', 'formato' => 'moeda', 'largura' => 15),
                    array('titulo' => 'Itens', 'largura' => 40),
                ),
                'linhas' => $linhasArrecadacoes,
                'total' => array('', 'Total em dinheiro', '', '', $resumo['arrecadado'], ''),
            ),
            array(
                'nome' => 'Doadores',
                'colunas' => array(
                    array('titulo' => 'Doador', 'largura' => 34),
                    array('titulo' => 'Tipo', 'largura' => 10),
                    array('titulo' => 'Doações', 'formato' => 'inteiro', 'largura' => 10),
                    array('titulo' => 'Doações em itens', 'formato' => 'inteiro', 'largura' => 16),
                    array('titulo' => 'Total em dinheiro', 'formato' => 'moeda', 'largura' => 18),
                ),
                'linhas' => $linhasDoadores,
            ),
        ));
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>Relatório do projeto - G7social</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="GTEC - Gerência de Tecnologia">
    <meta name="reply-to" content="web@unirios.edu.br">
    <meta name="robots" content="noindex,nofollow">
    <link href="../css/css.css" rel="stylesheet" type="text/css">
    <link rel="icon" href="../images/favicon.png">
</head>

<body>
<?php include '../includes/topo.html'; ?>
<?php include '../includes/menu.php'; ?>
<div id="container">
    <div id="conteudo" class="relatorio">
        <h2 class="titulo titulo-acao">
            <span>Relatório do projeto<?php if($projeto != NULL){ echo ' - <b>'.htmlspecialchars($projeto['titulo']).'</b>'; } ?></span>
            <?php if($projeto != NULL){ ?>
                <span class="relatorio-acoes">
                    <a href="relatorio_projeto.php?id_projeto=<?php echo $idProject; ?>&amp;exportar=xlsx" class="but-novo icon-file-excel" title="Baixar o relatório em planilha do Excel">Exportar Excel</a>
                    <button type="button" class="but but-secundario icon-print" onclick="window.print()" title="Imprimir ou salvar em PDF">Imprimir / PDF</button>
                </span>
            <?php } ?>
        </h2>

        <?php if($erro != ''){ echo '<span class="alerta">'.$erro.'</span>'; } ?>

        <div id="filtro">
            <form name="form-busca" method="GET" action="" id="form-busca">
                <select name="id_projeto" class="relatorio-seletor" required onchange="this.form.submit()">
                    <option value="">Escolha o projeto</option>
                    <?php foreach ($listaProjetos as $p) { ?>
                        <option value="<?php echo $p['id_projeto']; ?>" <?php if($p['id_projeto'] == $idProject){ echo 'selected'; } ?>>
                            <?php echo htmlspecialchars($p['titulo']).' ('.date('m/Y', strtotime($p['data_inicio'])).')'; ?>
                        </option>
                    <?php } ?>
                </select>
                <input type="submit" value="Gerar relatório" class="but-procura">
            </form>
        </div>

        <?php if($projeto == NULL){ ?>
            <span class="alerta notificacao">Escolha um projeto para gerar o relatório.</span>
        <?php }else{ ?>
            <p class="relatorio-info">
                <b>Período:</b> <?php echo $periodoTexto; ?>
                <span class="tag <?php echo $situacao == 'Ativo' ? 'tag-ativo' : 'tag-inativo'; ?>"><?php echo $situacao; ?></span>
                <span>Emitido em <?php echo $emitidoEm; ?> por <?php echo htmlspecialchars($_SESSION['SS_UserName']); ?></span>
            </p>

            <?php if($projeto['descricao'] != ''){ ?>
                <p class="relatorio-descricao"><?php echo nl2br(htmlspecialchars($projeto['descricao'])); ?></p>
            <?php } ?>

            <section class="painel-indicadores" aria-label="Resumo do projeto">
                <div class="indicador icon-calendar">
                    <span class="indicador-rotulo">Atividades</span>
                    <strong class="indicador-valor"><?php echo $resumo['atividades']; ?></strong>
                    <span class="indicador-contexto"><?php echo $resumo['inscricoes']; ?> inscrições</span>
                </div>
                <div class="indicador icon-users">
                    <span class="indicador-rotulo">Voluntários</span>
                    <strong class="indicador-valor"><?php echo $resumo['voluntarios']; ?></strong>
                    <span class="indicador-contexto">pessoas distintas</span>
                </div>
                <div class="indicador icon-bank">
                    <span class="indicador-rotulo">Arrecadado em dinheiro</span>
                    <strong class="indicador-valor"><?php echo Moeda::formatar($resumo['arrecadado']); ?></strong>
                    <?php if($estimado > 0){
                        $percentual = floor($resumo['arrecadado'] / $estimado * 100);
                        $atingida = $percentual >= 100; ?>
                        <span class="progresso-trilho"><span class="progresso-barra<?php if($atingida){ echo ' atingida'; } ?>" style="width: <?php echo min(100, $percentual); ?>%"></span></span>
                        <span class="indicador-contexto"><?php echo $percentual; ?>% de <?php echo Moeda::formatar($estimado); ?></span>
                    <?php }else{ ?>
                        <span class="indicador-contexto">sem estimativa cadastrada</span>
                    <?php } ?>
                </div>
                <div class="indicador icon-inbox">
                    <span class="indicador-rotulo">Doações</span>
                    <strong class="indicador-valor"><?php echo $resumo['doacoes_dinheiro'] + $resumo['doacoes_itens']; ?></strong>
                    <span class="indicador-contexto"><?php echo $resumo['doacoes_dinheiro']; ?> em dinheiro · <?php echo $resumo['doacoes_itens']; ?> de itens</span>
                </div>
                <div class="indicador icon-user">
                    <span class="indicador-rotulo">Doadores</span>
                    <strong class="indicador-valor"><?php echo $resumo['doadores']; ?></strong>
                    <span class="indicador-contexto">empresas e pessoas</span>
                </div>
            </section>

            <!-- Atividades -->
            <h3>Atividades (<?php echo count($atividades); ?>)</h3>
            <?php if(count($atividades) > 0){ ?>
                <table cellspacing="0" cellpadding="0" class="tab-listagem">
                    <thead>
                    <tr>
                        <th>Atividade</th>
                        <th width="190">Período</th>
                        <th width="200">Local</th>
                        <th width="140">Vagas</th>
                        <th width="100">Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($atividades as $a) { ?>
                        <tr>
                            <td class="nome"><b><?php echo htmlspecialchars($a['titulo']); ?></b></td>
                            <td align="center">
                                <?php echo date('d/m/Y', strtotime($a['data_inicio'])).' - '.date('d/m/Y', strtotime($a['data_fim'])); ?>
                                <?php if($a['horario'] != NULL){ echo '<br><small>'.htmlspecialchars($a['horario']).'</small>'; } ?>
                            </td>
                            <td><?php echo htmlspecialchars($a['local']); ?></td>
                            <td align="center">
                                <?php if($a['vagas'] > 0){
                                    $percentual = floor($a['inscritos'] / $a['vagas'] * 100);
                                    $lotada = $percentual >= 100; ?>
                                    <div class="progresso-vagas">
                                        <span class="progresso-valores"><b><?php echo $a['inscritos']; ?></b> de <?php echo $a['vagas']; ?></span>
                                        <div class="progresso-trilho"><div class="progresso-barra<?php if($lotada){ echo ' atingida'; } ?>" style="width: <?php echo min(100, $percentual); ?>%"></div></div>
                                        <small class="progresso-percentual<?php if($lotada){ echo ' atingida'; } ?>"><?php echo $lotada ? 'Lotada' : $percentual.'%'; ?></small>
                                    </div>
                                <?php }else{
                                    echo $a['inscritos'].' <small>(sem limite)</small>';
                                } ?>
                            </td>
                            <td align="center">
                                <span class="tag <?php echo $a['status'] == 'on' ? 'tag-ativo' : 'tag-inativo'; ?>"><?php echo $a['status'] == 'on' ? 'Ativa' : 'Desativada'; ?></span>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            <?php }else{
                echo '<span class="alerta">Nenhuma atividade cadastrada.</span>';
            } ?>

            <!-- Voluntários por atividade -->
            <h3>Voluntários inscritos (<?php echo count($voluntarios); ?>)</h3>
            <?php if(count($voluntarios) > 0){ ?>
                <table cellspacing="0" cellpadding="0" class="tab-listagem tab-agrupada">
                    <thead>
                    <tr>
                        <th>Voluntário</th>
                        <th width="140">CPF</th>
                        <th width="140">Telefone</th>
                        <th width="240">E-mail</th>
                        <th width="130">Inscrição</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $atividadeAtual = 0;
                    foreach ($voluntarios as $v) {
                        if($v['id_atividade'] != $atividadeAtual){
                            $atividadeAtual = $v['id_atividade']; ?>
                            <tr class="grupo">
                                <td colspan="5"><?php echo htmlspecialchars($v['atividade_titulo']); ?> <small><?php echo date('d/m/Y', strtotime($v['data_inicio'])); ?></small></td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <td class="nome"><?php echo htmlspecialchars($v['nome']); ?></td>
                            <td align="center"><?php echo $v['cpf'] != NULL ? Documento::mascararCPF($v['cpf']) : '<i>Não informado</i>'; ?></td>
                            <td align="center"><?php echo Documento::formatarTelefone((string) $v['telefone']); ?></td>
                            <td><?php echo htmlspecialchars((string) $v['email']); ?></td>
                            <td align="center"><?php echo date('d/m/Y H:i', strtotime($v['data_inscricao'])); ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            <?php }else{
                echo '<span class="alerta">Nenhum voluntário inscrito.</span>';
            } ?>

            <!-- Arrecadações -->
            <h3>Arrecadações (<?php echo count($arrecadacoes); ?>)</h3>
            <?php if(count($arrecadacoes) > 0){ ?>
                <table cellspacing="0" cellpadding="0" class="tab-listagem">
                    <thead>
                    <tr>
                        <th width="100">Data</th>
                        <th>Doador</th>
                        <th width="90">Tipo</th>
                        <th>Valor/Itens</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($arrecadacoes as $r) { ?>
                        <tr>
                            <td align="center"><?php echo date('d/m/Y', strtotime($r['data_arrecadacao'])); ?></td>
                            <td class="nome"><?php echo htmlspecialchars($r['doador_nome']); ?> <small>(<?php echo $r['doador_tipo'] == 'empresa' ? 'Empresa' : 'Pessoa'; ?>)</small></td>
                            <td align="center"><?php echo $r['tipo'] == 'dinheiro' ? 'Dinheiro' : 'Itens'; ?></td>
                            <td><?php echo $r['tipo'] == 'dinheiro' ? Moeda::formatar($r['valor']) : htmlspecialchars((string) $r['descricao']); ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                    <tfoot>
                    <tr>
                        <td colspan="3" align="right"><b>Total em dinheiro</b></td>
                        <td><b><?php echo Moeda::formatar($resumo['arrecadado']); ?></b> <small>· <?php echo $resumo['doacoes_itens']; ?> doações de itens</small></td>
                    </tr>
                    </tfoot>
                </table>
            <?php }else{
                echo '<span class="alerta">Nenhuma arrecadação registrada.</span>';
            } ?>

            <!-- Doadores -->
            <h3>Doadores (<?php echo count($doadores); ?>)</h3>
            <?php if(count($doadores) > 0){ ?>
                <table cellspacing="0" cellpadding="0" class="tab-listagem">
                    <thead>
                    <tr>
                        <th>Doador</th>
                        <th width="90">Tipo</th>
                        <th width="90">Doações</th>
                        <th width="130">Doações de itens</th>
                        <th width="150">Total em dinheiro</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($doadores as $d) { ?>
                        <tr>
                            <td class="nome"><b><?php echo htmlspecialchars($d['nome']); ?></b></td>
                            <td align="center"><?php echo $d['tipo'] == 'empresa' ? 'Empresa' : 'Pessoa'; ?></td>
                            <td align="center"><?php echo $d['doacoes']; ?></td>
                            <td align="center"><?php echo $d['doacoes_itens']; ?></td>
                            <td align="right"><?php echo Moeda::formatar($d['total']); ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            <?php }else{
                echo '<span class="alerta">Nenhum doador para este projeto.</span>';
            } ?>
        <?php } ?>
    </div>
</div>
<?php include '../includes/rodape.html'; ?>
</body>
</html>
