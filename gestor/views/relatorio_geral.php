<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../includes/verificar_login.php';

date_default_timezone_set('America/Fortaleza');

use App\Controllers\RelatorioController;
use App\Controllers\LogController;
use App\Helpers\Moeda;
use App\Helpers\Planilha;

$controllerRelatorio = new RelatorioController;

// Período: padrão é o ano atual
function dataValida($data){
    $d = DateTime::createFromFormat('Y-m-d', (string) $data);
    return $d && $d->format('Y-m-d') == $data;
}

$inicio = isset($_GET['inicio']) && dataValida($_GET['inicio']) ? $_GET['inicio'] : date('Y').'-01-01';
$fim = isset($_GET['fim']) && dataValida($_GET['fim']) ? $_GET['fim'] : date('Y').'-12-31';

$erro = '';
if($fim < $inicio){
    $erro = 'A data final não pode ser anterior à data inicial. Mostrando o ano atual.';
}elseif(strtotime($fim) > strtotime($inicio.' +5 years')){
    $erro = 'Escolha um período de até 5 anos. Mostrando o ano atual.';
}
if($erro != ''){
    $inicio = date('Y').'-01-01';
    $fim = date('Y').'-12-31';
}

$resumo = $controllerRelatorio->getResumoGeral($inicio, $fim);
$projetos = $controllerRelatorio->getProjetosGeral($inicio, $fim);
$arrecadacaoMes = $controllerRelatorio->getArrecadacaoPorMes($inicio, $fim);
$topDoadores = $controllerRelatorio->getTopDoadores($inicio, $fim, 10);
$topVoluntarios = $controllerRelatorio->getTopVoluntarios($inicio, $fim, 10);

$periodoTexto = date('d/m/Y', strtotime($inicio)).' a '.date('d/m/Y', strtotime($fim));
$emitidoEm = date('d/m/Y \à\s H:i');

/***** EXPORTAÇÃO EM EXCEL *****/
if(isset($_GET['exportar']) && $_GET['exportar'] == 'xlsx'){
    (new LogController)->insertActionUserLog(18, $_SESSION['SS_UserID'], 'Relatório geral - período '.$periodoTexto);

    $linhasProjetos = array();
    foreach ($projetos as $p) {
        $linhasProjetos[] = array(
            $p['id_projeto'], $p['titulo'], $p['data_inicio'], $p['data_fim'], $p['atividades'], $p['inscricoes'],
            $p['arrecadacao_estimada'], $p['arrecadado'],
            $p['arrecadacao_estimada'] > 0 ? $p['arrecadado'] / $p['arrecadacao_estimada'] : NULL,
            $p['doacoes_dinheiro'], $p['doacoes_itens']
        );
    }

    $linhasMeses = array();
    foreach ($arrecadacaoMes as $m) {
        $linhasMeses[] = array($m['mes'].'-01', $m['doacoes'], $m['total']);
    }

    $linhasDoadores = array();
    foreach ($topDoadores as $d) {
        $linhasDoadores[] = array($d['nome'], $d['tipo'] == 'empresa' ? 'Empresa' : 'Pessoa', $d['projetos'], $d['doacoes'], $d['total']);
    }

    $linhasVoluntarios = array();
    foreach ($topVoluntarios as $v) {
        $linhasVoluntarios[] = array($v['nome'], $v['projetos'], $v['inscricoes'], $v['ultima_atividade']);
    }

    Planilha::baixar('relatorio_geral_'.$inicio.'_a_'.$fim.'.xlsx', 'Relatório geral', 'Projetos com período entre '.$periodoTexto.' · Emitido em '.$emitidoEm, array(
        array(
            'nome' => 'Resumo',
            'colunas' => array(array('titulo' => 'Indicador', 'largura' => 38), array('titulo' => 'Valor', 'largura' => 20)),
            'linhas' => array(
                array('Projetos', $resumo['projetos']),
                array('Atividades', $resumo['atividades']),
                array('Inscrições em atividades', $resumo['inscricoes']),
                array('Voluntários (pessoas distintas)', $resumo['voluntarios']),
                array('Arrecadado em dinheiro (R$)', number_format($resumo['arrecadado'], 2, ',', '.')),
                array('Arrecadação estimada (R$)', number_format($resumo['estimado'], 2, ',', '.')),
                array('Doações em dinheiro', $resumo['doacoes_dinheiro']),
                array('Doações em itens', $resumo['doacoes_itens']),
                array('Doadores (distintos)', $resumo['doadores']),
            ),
        ),
        array(
            'nome' => 'Projetos',
            'colunas' => array(
                array('titulo' => 'ID', 'formato' => 'inteiro', 'largura' => 8),
                array('titulo' => 'Projeto', 'largura' => 40),
                array('titulo' => 'Início', 'formato' => 'data', 'largura' => 12),
                array('titulo' => 'Fim', 'formato' => 'data', 'largura' => 12),
                array('titulo' => 'Atividades', 'formato' => 'inteiro', 'largura' => 11),
                array('titulo' => 'Inscrições', 'formato' => 'inteiro', 'largura' => 11),
                array('titulo' => 'Estimado', 'formato' => 'moeda', 'largura' => 15),
                array('titulo' => 'Arrecadado', 'formato' => 'moeda', 'largura' => 15),
                array('titulo' => '% da estimativa', 'formato' => 'percentual', 'largura' => 15),
                array('titulo' => 'Doações em dinheiro', 'formato' => 'inteiro', 'largura' => 19),
                array('titulo' => 'Doações em itens', 'formato' => 'inteiro', 'largura' => 16),
            ),
            'linhas' => $linhasProjetos,
            'total' => array('', 'Total', '', '', $resumo['atividades'], $resumo['inscricoes'], $resumo['estimado'], $resumo['arrecadado'], '', $resumo['doacoes_dinheiro'], $resumo['doacoes_itens']),
        ),
        array(
            'nome' => 'Arrecadação por mês',
            'colunas' => array(
                array('titulo' => 'Mês', 'formato' => 'mes', 'largura' => 12),
                array('titulo' => 'Doações em dinheiro', 'formato' => 'inteiro', 'largura' => 19),
                array('titulo' => 'Valor', 'formato' => 'moeda', 'largura' => 16),
            ),
            'linhas' => $linhasMeses,
        ),
        array(
            'nome' => 'Maiores doadores',
            'colunas' => array(
                array('titulo' => 'Doador', 'largura' => 38),
                array('titulo' => 'Tipo', 'largura' => 10),
                array('titulo' => 'Projetos', 'formato' => 'inteiro', 'largura' => 10),
                array('titulo' => 'Doações', 'formato' => 'inteiro', 'largura' => 10),
                array('titulo' => 'Total em dinheiro', 'formato' => 'moeda', 'largura' => 18),
            ),
            'linhas' => $linhasDoadores,
        ),
        array(
            'nome' => 'Voluntários mais ativos',
            'colunas' => array(
                array('titulo' => 'Voluntário', 'largura' => 38),
                array('titulo' => 'Projetos', 'formato' => 'inteiro', 'largura' => 10),
                array('titulo' => 'Inscrições', 'formato' => 'inteiro', 'largura' => 11),
                array('titulo' => 'Última atividade', 'formato' => 'data', 'largura' => 16),
            ),
            'linhas' => $linhasVoluntarios,
        ),
    ));
}

$nomesMeses = array('01' => 'jan', '02' => 'fev', '03' => 'mar', '04' => 'abr', '05' => 'mai', '06' => 'jun',
                    '07' => 'jul', '08' => 'ago', '09' => 'set', '10' => 'out', '11' => 'nov', '12' => 'dez');
$maiorMes = 0;
foreach ($arrecadacaoMes as $m) {
    $maiorMes = max($maiorMes, $m['total']);
}

$queryExportar = http_build_query(array('inicio' => $inicio, 'fim' => $fim, 'exportar' => 'xlsx'));

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>Relatório geral - G7social</title>
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
            <span>Relatório geral</span>
            <span class="relatorio-acoes">
                <a href="relatorio_geral.php?<?php echo htmlspecialchars($queryExportar); ?>" class="but-novo icon-file-excel" title="Baixar o relatório em planilha do Excel">Exportar Excel</a>
                <button type="button" class="but but-secundario icon-print" onclick="window.print()" title="Imprimir ou salvar em PDF">Imprimir / PDF</button>
            </span>
        </h2>

        <?php if($erro != ''){ echo '<span class="alerta">'.$erro.'</span>'; } ?>

        <div id="filtro">
            <form name="form-busca" method="GET" action="" id="form-busca">
                <label for="inicio">De</label>
                <input type="date" name="inicio" id="inicio" required value="<?php echo $inicio; ?>">
                <label for="fim">até</label>
                <input type="date" name="fim" id="fim" required value="<?php echo $fim; ?>">
                <input type="submit" value="Gerar relatório" class="but-procura">
                <a href="relatorio_geral.php" title="Voltar ao ano atual">» Ano atual</a>
            </form>
        </div>

        <p class="relatorio-info">
            <b>Período:</b> <?php echo $periodoTexto; ?> · considera os projetos cujo período cruza essas datas.
            <span>Emitido em <?php echo $emitidoEm; ?> por <?php echo htmlspecialchars($_SESSION['SS_UserName']); ?></span>
        </p>

        <section class="painel-indicadores" aria-label="Resumo do período">
            <div class="indicador icon-heart">
                <span class="indicador-rotulo">Projetos</span>
                <strong class="indicador-valor"><?php echo $resumo['projetos']; ?></strong>
                <span class="indicador-contexto"><?php echo $resumo['atividades']; ?> atividades</span>
            </div>
            <div class="indicador icon-users">
                <span class="indicador-rotulo">Voluntários</span>
                <strong class="indicador-valor"><?php echo $resumo['voluntarios']; ?></strong>
                <span class="indicador-contexto"><?php echo $resumo['inscricoes']; ?> inscrições em atividades</span>
            </div>
            <div class="indicador icon-bank">
                <span class="indicador-rotulo">Arrecadado em dinheiro</span>
                <strong class="indicador-valor"><?php echo Moeda::formatar($resumo['arrecadado']); ?></strong>
                <span class="indicador-contexto">
                    <?php echo $resumo['estimado'] > 0 ? 'de '.Moeda::formatar($resumo['estimado']).' estimados' : 'sem estimativa cadastrada'; ?>
                </span>
            </div>
            <div class="indicador icon-inbox">
                <span class="indicador-rotulo">Doações</span>
                <strong class="indicador-valor"><?php echo $resumo['doacoes_dinheiro'] + $resumo['doacoes_itens']; ?></strong>
                <span class="indicador-contexto"><?php echo $resumo['doacoes_dinheiro']; ?> em dinheiro · <?php echo $resumo['doacoes_itens']; ?> de itens</span>
            </div>
            <div class="indicador icon-user">
                <span class="indicador-rotulo">Doadores</span>
                <strong class="indicador-valor"><?php echo $resumo['doadores']; ?></strong>
                <span class="indicador-contexto">que doaram para esses projetos</span>
            </div>
        </section>

        <h3>Projetos do período (<?php echo count($projetos); ?>)</h3>

        <?php if(count($projetos) > 0){ ?>
            <table cellspacing="0" cellpadding="0" class="tab-listagem">
                <thead>
                <tr>
                    <th width="50">ID</th>
                    <th>Projeto</th>
                    <th width="180">Período</th>
                    <th width="90">Atividades</th>
                    <th width="90">Inscrições</th>
                    <th width="210">Arrecadado</th>
                    <th width="110">Doações</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($projetos as $p) { ?>
                    <tr>
                        <td class="id"><?php echo $p['id_projeto']; ?></td>
                        <td class="nome"><b><?php echo htmlspecialchars($p['titulo']); ?></b></td>
                        <td align="center"><?php echo date('d/m/Y', strtotime($p['data_inicio'])).' - '.date('d/m/Y', strtotime($p['data_fim'])); ?></td>
                        <td align="center"><?php echo $p['atividades']; ?></td>
                        <td align="center"><?php echo $p['inscricoes']; ?></td>
                        <td align="right">
                            <?php if($p['arrecadacao_estimada'] > 0){
                                $percentual = floor($p['arrecadado'] / $p['arrecadacao_estimada'] * 100);
                                $atingida = $percentual >= 100; ?>
                                <div class="progresso-arrecadacao" title="<?php echo $percentual; ?>% da arrecadação estimada">
                                    <div class="progresso-valores">
                                        <b><?php echo Moeda::formatar($p['arrecadado']); ?></b>
                                        <small>de <?php echo Moeda::formatar($p['arrecadacao_estimada']); ?></small>
                                    </div>
                                    <div class="progresso-trilho">
                                        <div class="progresso-barra<?php if($atingida){ echo ' atingida'; } ?>" style="width: <?php echo min(100, $percentual); ?>%"></div>
                                    </div>
                                    <small class="progresso-percentual<?php if($atingida){ echo ' atingida'; } ?>"><?php echo $percentual; ?>%<?php if($atingida){ echo ' · meta atingida'; } ?></small>
                                </div>
                            <?php }else{
                                echo Moeda::formatar($p['arrecadado']);
                            } ?>
                        </td>
                        <td align="center"><?php echo $p['doacoes_dinheiro']; ?> <small>em dinheiro</small><br><?php echo $p['doacoes_itens']; ?> <small>de itens</small></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        <?php }else{
            echo '<span class="alerta">Nenhum projeto com período entre essas datas.</span>';
        } ?>

        <div class="painel-grade relatorio-grade">
            <section class="painel-cartao relatorio-grafico">
                <h3>Arrecadação em dinheiro por mês</h3>
                <span class="painel-subtitulo">Doações dos projetos acima com data entre <?php echo $periodoTexto; ?></span>

                <?php if($maiorMes == 0){ ?>
                    <p class="painel-vazio">Nenhuma doação em dinheiro no período.</p>
                <?php }else{ ?>
                    <div class="grafico-colunas<?php if(count($arrecadacaoMes) > 12){ echo ' grafico-denso'; } ?>" role="img" aria-label="Gráfico de colunas da arrecadação em dinheiro por mês. Os valores estão na tabela abaixo.">
                        <?php foreach ($arrecadacaoMes as $m) {
                            $mesNome = $nomesMeses[substr($m['mes'], 5, 2)].'/'.substr($m['mes'], 2, 2);
                            $altura = $m['total'] > 0 ? max(2, round($m['total'] / $maiorMes * 100)) : 0; ?>
                            <div class="grafico-coluna" tabindex="0">
                                <div class="grafico-area">
                                    <?php if($m['total'] > 0){ ?>
                                        <span class="grafico-valor"><?php echo Moeda::compacto($m['total']); ?></span>
                                        <span class="grafico-barra" style="height: <?php echo $altura; ?>%"></span>
                                    <?php } ?>
                                </div>
                                <span class="grafico-mes"><?php echo $mesNome; ?></span>
                                <span class="grafico-dica" role="tooltip">
                                    <strong><?php echo Moeda::formatar($m['total']); ?></strong>
                                    <?php echo $mesNome; ?> · <?php echo $m['doacoes']; ?> <?php echo $m['doacoes'] == 1 ? 'doação' : 'doações'; ?>
                                </span>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>

                <details class="painel-tabela">
                    <summary>Ver em tabela</summary>
                    <table cellspacing="0" cellpadding="0" class="tab-listagem">
                        <thead>
                        <tr><th>Mês</th><th>Doações</th><th>Valor</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($arrecadacaoMes as $m) { ?>
                            <tr>
                                <td align="center"><?php echo $nomesMeses[substr($m['mes'], 5, 2)].'/'.substr($m['mes'], 0, 4); ?></td>
                                <td align="center"><?php echo $m['doacoes']; ?></td>
                                <td align="right"><?php echo Moeda::formatar($m['total']); ?></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </details>
            </section>

            <section class="painel-cartao">
                <h3>Maiores doadores</h3>
                <span class="painel-subtitulo">Os 10 que mais doaram em dinheiro para os projetos do período</span>

                <?php if(count($topDoadores) == 0){ ?>
                    <p class="painel-vazio">Nenhuma doação registrada.</p>
                <?php }else{ ?>
                    <table cellspacing="0" cellpadding="0" class="tab-listagem">
                        <thead>
                        <tr><th>Doador</th><th width="80">Tipo</th><th width="80">Doações</th><th width="120">Total</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($topDoadores as $d) { ?>
                            <tr>
                                <td class="nome"><?php echo htmlspecialchars($d['nome']); ?></td>
                                <td align="center"><?php echo $d['tipo'] == 'empresa' ? 'Empresa' : 'Pessoa'; ?></td>
                                <td align="center"><?php echo $d['doacoes']; ?></td>
                                <td align="right"><?php echo Moeda::formatar($d['total']); ?></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                <?php } ?>
            </section>

            <section class="painel-cartao">
                <h3>Voluntários mais ativos</h3>
                <span class="painel-subtitulo">Os 10 com mais inscrições nas atividades dos projetos do período</span>

                <?php if(count($topVoluntarios) == 0){ ?>
                    <p class="painel-vazio">Nenhuma inscrição registrada.</p>
                <?php }else{ ?>
                    <table cellspacing="0" cellpadding="0" class="tab-listagem">
                        <thead>
                        <tr><th>Voluntário</th><th width="80">Projetos</th><th width="90">Inscrições</th><th width="120">Última atividade</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($topVoluntarios as $v) { ?>
                            <tr>
                                <td class="nome"><?php echo htmlspecialchars($v['nome']); ?></td>
                                <td align="center"><?php echo $v['projetos']; ?></td>
                                <td align="center"><?php echo $v['inscricoes']; ?></td>
                                <td align="center"><?php echo date('d/m/Y', strtotime($v['ultima_atividade'])); ?></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                <?php } ?>
            </section>
        </div>
    </div>
</div>
<?php include '../includes/rodape.html'; ?>
</body>
</html>
