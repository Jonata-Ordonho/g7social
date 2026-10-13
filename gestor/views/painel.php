<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../includes/verificar_login.php';

date_default_timezone_set('America/Fortaleza');

use App\Controllers\PainelController;
use App\Controllers\ProjetoController;
use App\Helpers\Moeda;

$controllerPainel = new PainelController;
$controllerProjeto = new ProjetoController;

$hoje = date('Y-m-d');

$indicadores = $controllerPainel->getIndicadores($hoje);
$arrecadacaoMes = $controllerPainel->getArrecadacaoPorMes($hoje, 6);
$proximasAtividades = $controllerPainel->getProximasAtividades($hoje, 5);
$ultimasArrecadacoes = $controllerPainel->getUltimasArrecadacoes(5);
$projetosAtivos = $controllerProjeto->getProjectsList(array('situacao' => 'ativos', 'hoje' => $hoje), 0, 6);

$nomesMeses = array('01' => 'jan', '02' => 'fev', '03' => 'mar', '04' => 'abr', '05' => 'mai', '06' => 'jun',
                    '07' => 'jul', '08' => 'ago', '09' => 'set', '10' => 'out', '11' => 'nov', '12' => 'dez');
$maiorMes = 0;
$totalPeriodo = 0;
foreach ($arrecadacaoMes as $m) {
    $maiorMes = max($maiorMes, $m['total']);
    $totalPeriodo += $m['total'];
}

$primeiroNome = explode(' ', trim($_SESSION['SS_UserName']))[0];

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
    <div class="painel">
        <header class="painel-cabecalho">
            <h2>Olá, <?php echo htmlspecialchars($primeiroNome); ?></h2>
            <span>Resumo das atividades sociais em <?php echo date('d/m/Y'); ?></span>
        </header>

        <!-- Indicadores -->
        <section class="painel-indicadores" aria-label="Indicadores">
            <a href="projeto_listar.php?situacao=ativos" class="indicador icon-heart">
                <span class="indicador-rotulo">Projetos ativos</span>
                <strong class="indicador-valor"><?php echo $indicadores['projetos_ativos']; ?></strong>
                <span class="indicador-contexto">de <?php echo $indicadores['projetos_total']; ?> cadastrados</span>
            </a>
            <div class="indicador icon-calendar">
                <span class="indicador-rotulo">Atividades com inscrições abertas</span>
                <strong class="indicador-valor"><?php echo $indicadores['atividades_abertas']; ?></strong>
                <span class="indicador-contexto">de <?php echo $indicadores['atividades_total']; ?> atividades</span>
            </div>
            <a href="voluntario_listar.php" class="indicador icon-users">
                <span class="indicador-rotulo">Voluntários cadastrados</span>
                <strong class="indicador-valor"><?php echo $indicadores['voluntarios_total']; ?></strong>
                <span class="indicador-contexto">+<?php echo $indicadores['voluntarios_30_dias']; ?> nos últimos 30 dias · <?php echo $indicadores['inscricoes_total']; ?> inscrições</span>
            </a>
            <div class="indicador icon-bank">
                <span class="indicador-rotulo">Arrecadado em dinheiro</span>
                <strong class="indicador-valor"><?php echo Moeda::formatar($indicadores['arrecadado_dinheiro']); ?></strong>
                <span class="indicador-contexto"><?php echo $indicadores['doacoes_dinheiro']; ?> doações em dinheiro · <?php echo $indicadores['doacoes_itens']; ?> de itens</span>
            </div>
            <a href="doador_listar.php" class="indicador icon-user">
                <span class="indicador-rotulo">Doadores cadastrados</span>
                <strong class="indicador-valor"><?php echo $indicadores['doadores_empresas'] + $indicadores['doadores_pessoas']; ?></strong>
                <span class="indicador-contexto"><?php echo $indicadores['doadores_empresas']; ?> empresas · <?php echo $indicadores['doadores_pessoas']; ?> pessoas</span>
            </a>
        </section>

        <div class="painel-grade">
            <!-- Arrecadação por mês -->
            <section class="painel-cartao">
                <h3>Arrecadação em dinheiro por mês</h3>
                <span class="painel-subtitulo">Últimos 6 meses · total de <?php echo Moeda::formatar($totalPeriodo); ?></span>

                <?php if($maiorMes == 0){ ?>
                    <p class="painel-vazio">Nenhuma doação em dinheiro nos últimos 6 meses.</p>
                <?php }else{ ?>
                    <div class="grafico-colunas" role="img" aria-label="Gráfico de colunas da arrecadação em dinheiro por mês. Os valores estão na tabela abaixo.">
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

            <!-- Projetos ativos -->
            <section class="painel-cartao">
                <h3>Projetos ativos</h3>
                <span class="painel-subtitulo">Arrecadação em dinheiro em relação à estimativa</span>

                <?php if(count($projetosAtivos) == 0){ ?>
                    <p class="painel-vazio">Nenhum projeto ativo no momento.</p>
                <?php }else{ ?>
                    <ul class="painel-lista">
                        <?php foreach ($projetosAtivos as $p) { ?>
                            <li>
                                <a href="arrecadacao_listar.php?id_projeto=<?php echo $p['id_projeto']; ?>" class="painel-item-projeto">
                                    <span class="painel-item-titulo"><?php echo htmlspecialchars($p['titulo']); ?></span>
                                    <?php if($p['arrecadacao_estimada'] != NULL && $p['arrecadacao_estimada'] > 0){
                                        $percentual = floor($p['total_dinheiro'] / $p['arrecadacao_estimada'] * 100);
                                        $atingida = $percentual >= 100; ?>
                                        <span class="painel-item-detalhe">
                                            <?php echo Moeda::formatar($p['total_dinheiro']); ?> de <?php echo Moeda::formatar($p['arrecadacao_estimada']); ?>
                                            <b class="progresso-percentual<?php if($atingida){ echo ' atingida'; } ?>"><?php echo $percentual; ?>%<?php if($atingida){ echo ' · meta atingida'; } ?></b>
                                        </span>
                                        <span class="progresso-trilho"><span class="progresso-barra<?php if($atingida){ echo ' atingida'; } ?>" style="width: <?php echo min(100, $percentual); ?>%"></span></span>
                                    <?php }else{ ?>
                                        <span class="painel-item-detalhe"><?php echo Moeda::formatar($p['total_dinheiro']); ?> arrecadados · sem estimativa</span>
                                    <?php } ?>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } ?>
            </section>

            <!-- Próximas atividades -->
            <section class="painel-cartao">
                <h3>Próximas atividades</h3>
                <span class="painel-subtitulo">Atividades ativas que ainda não terminaram</span>

                <?php if(count($proximasAtividades) == 0){ ?>
                    <p class="painel-vazio">Nenhuma atividade programada.</p>
                <?php }else{ ?>
                    <ul class="painel-lista">
                        <?php foreach ($proximasAtividades as $a) { ?>
                            <li>
                                <a href="inscricao_listar.php?id_atividade=<?php echo $a['id_atividade']; ?>" class="painel-item-atividade">
                                    <span class="painel-data">
                                        <b><?php echo date('d', strtotime($a['data_inicio'])); ?></b>
                                        <?php echo $nomesMeses[date('m', strtotime($a['data_inicio']))]; ?>
                                    </span>
                                    <span class="painel-item-texto">
                                        <span class="painel-item-titulo"><?php echo htmlspecialchars($a['titulo']); ?></span>
                                        <span class="painel-item-detalhe"><?php echo htmlspecialchars($a['projeto_titulo']); ?></span>
                                    </span>
                                    <span class="painel-vagas">
                                        <?php if($a['vagas'] != NULL){ ?>
                                            <b><?php echo $a['total_voluntarios']; ?>/<?php echo $a['vagas']; ?></b> vagas
                                        <?php }else{ ?>
                                            <b><?php echo $a['total_voluntarios']; ?></b> inscritos
                                        <?php } ?>
                                    </span>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } ?>
            </section>

            <!-- Últimas arrecadações -->
            <section class="painel-cartao">
                <h3>Últimas arrecadações</h3>
                <span class="painel-subtitulo">Doações registradas mais recentemente</span>

                <?php if(count($ultimasArrecadacoes) == 0){ ?>
                    <p class="painel-vazio">Nenhuma arrecadação registrada.</p>
                <?php }else{ ?>
                    <ul class="painel-lista">
                        <?php foreach ($ultimasArrecadacoes as $r) { ?>
                            <li>
                                <a href="arrecadacao_listar.php?id_projeto=<?php echo $r['id_projeto']; ?>" class="painel-item-arrecadacao">
                                    <span class="painel-item-texto">
                                        <span class="painel-item-titulo"><?php echo htmlspecialchars($r['doador_nome']); ?></span>
                                        <span class="painel-item-detalhe"><?php echo htmlspecialchars($r['projeto_titulo']); ?> · <?php echo date('d/m/Y', strtotime($r['data_arrecadacao'])); ?></span>
                                    </span>
                                    <span class="painel-valor">
                                        <?php echo $r['tipo'] == 'dinheiro' ? Moeda::formatar($r['valor']) : '<small>Itens:</small> '.htmlspecialchars($r['descricao']); ?>
                                    </span>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } ?>
            </section>
        </div>
    </div>
</div>
<?php include '../includes/rodape.html'; ?>
</body>
</html>
