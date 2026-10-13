<?php
require_once __DIR__ . '/../../vendor/autoload.php';

date_default_timezone_set('America/Fortaleza');

use App\Controllers\ProjetoController;
use App\Controllers\AtividadeController;

$controllerProjeto = new ProjetoController;
$controllerAtividade = new AtividadeController;

$codigo = isset($_GET['c']) ? preg_replace('/[^a-f0-9]/', '', strtolower($_GET['c'])) : '';

$projeto = NULL;
if(strlen($codigo) == 32){
    $projeto = $controllerProjeto->getProjectByCode($codigo);
}

// Projeto finalizado (data fim já passou): as inscrições ficam encerradas
$projetoFinalizado = $projeto != NULL && $projeto['data_fim'] < date('Y-m-d');

$atividades = array();
if($projeto != NULL && !$projetoFinalizado){
    $atividades = $controllerAtividade->getOpenActivitiesByProject($projeto['id_projeto'], date('Y-m-d'));
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <title><?php echo $projeto != NULL ? htmlspecialchars($projeto['titulo']).' - ' : ''; ?>Inscrição de Voluntários - Grupo Sete</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="GTEC - Gerência de Tecnologia">
    <meta name="robots" content="noindex,nofollow">
    <link rel="icon" href="../images/favicon.png">
    <link href="../css/inscricao.css" rel="stylesheet" type="text/css" media="screen">
</head>

<body>
    <div class="container">
        <header class="topo-publico">
            <div class="marca">
                <img src="../images/logo_g7.png" alt="">
                <span class="marca-nome">G7<span>social</span></span>
            </div>
            <span class="selo">Inscrição de voluntários</span>
        </header>

        <main class="cartao">
            <?php if($projeto == NULL){ ?>
                <div class="indisponivel icon-lock">
                    <h1>Página indisponível</h1>
                    <p>O link de inscrição informado não foi encontrado.</p>
                </div>
            <?php }else{ ?>
                <div class="cartao-cabecalho">
                    <span class="projeto-tag">Projeto social</span>
                    <h1><?php echo htmlspecialchars($projeto['titulo']); ?></h1>
                    <?php if($projeto['descricao'] != ''){ ?>
                        <p class="descricao-projeto"><?php echo nl2br(htmlspecialchars($projeto['descricao'])); ?></p>
                    <?php } ?>
                </div>

                <section class="area-atividades">
                    <?php if(isset($_GET['inscricao'])){ ?>
                        <span class="alerta sucesso">Inscrição realizada com sucesso! Obrigado por se voluntariar.</span>
                    <?php } ?>

                    <h2>Atividades com inscrições abertas</h2>
                    <span class="subtitulo">Escolha uma atividade para se inscrever como voluntário.</span>

                    <?php if($projetoFinalizado){ ?>
                        <span class="alerta notificacao">Este projeto foi finalizado em <?php echo date('d/m/Y', strtotime($projeto['data_fim'])); ?>. As inscrições estão encerradas.</span>
                    <?php }elseif(count($atividades) == 0){ ?>
                        <span class="alerta notificacao">No momento não há atividades com inscrições abertas neste projeto.</span>
                    <?php }else{ ?>
                        <div class="lista-atividades">
                            <?php foreach ($atividades as $atividade) {
                                $esgotada = $atividade['vagas'] != NULL && $atividade['total_voluntarios'] >= $atividade['vagas']; ?>
                                <article class="item-atividade<?php if($esgotada){ echo ' esgotada'; } ?>">
                                    <h3>
                                        <?php echo htmlspecialchars($atividade['titulo']); ?>
                                        <?php if($esgotada){ ?><span class="tag-esgotada">Vagas esgotadas</span><?php } ?>
                                    </h3>

                                    <?php if($atividade['descricao'] != ''){ ?>
                                        <p class="descricao"><?php echo nl2br(htmlspecialchars($atividade['descricao'])); ?></p>
                                    <?php } ?>

                                    <ul class="dados-resumo">
                                        <li class="icon-calendar">
                                            <?php
                                            if($atividade['data_inicio'] == $atividade['data_fim']){
                                                echo date('d/m/Y', strtotime($atividade['data_inicio']));
                                            }else{
                                                echo date('d/m/Y', strtotime($atividade['data_inicio'])).' a '.date('d/m/Y', strtotime($atividade['data_fim']));
                                            }
                                            ?>
                                        </li>
                                        <?php if($atividade['horario'] != NULL){ ?>
                                            <li class="icon-clock"><?php echo htmlspecialchars($atividade['horario']); ?></li>
                                        <?php } ?>
                                        <li class="icon-home"><?php echo htmlspecialchars($atividade['local']); ?></li>
                                        <?php if($atividade['vagas'] != NULL){ ?>
                                            <li class="icon-users"><?php echo $atividade['vagas']; ?> vagas</li>
                                        <?php } ?>
                                    </ul>

                                    <?php if($esgotada){ ?>
                                        <span class="botao-inscrever desativado">Inscrições encerradas</span>
                                    <?php }else{ ?>
                                        <a href="inscricao.php?c=<?php echo $atividade['codigo']; ?>" class="botao-inscrever">Quero me inscrever</a>
                                    <?php } ?>
                                </article>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </section>
            <?php } ?>
        </main>

        <?php include '../includes/rodape.html'; ?>
    </div>
</body>
</html>
