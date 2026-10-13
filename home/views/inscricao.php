<?php
require_once __DIR__ . '/../../vendor/autoload.php';

date_default_timezone_set('America/Fortaleza');

use App\Controllers\AtividadeController;
use App\Controllers\InscricaoController;
use App\Controllers\ProjetoController;
use App\Controllers\VoluntarioController;
use App\Helpers\Documento;

$controllerAtividade = new AtividadeController;
$controllerInscricao = new InscricaoController;
$controllerVoluntario = new VoluntarioController;

$codigo = isset($_GET['c']) ? preg_replace('/[^a-f0-9]/', '', strtolower($_GET['c'])) : '';

$atividade = NULL;
if(strlen($codigo) == 32){
    $atividade = $controllerAtividade->getActivityByCode($codigo);
}

// A página só fica disponível com a atividade ativa
$disponivel = $atividade != NULL && $atividade['status'] == 'on';

$encerrada = false;
$vagasEsgotadas = false;
$vagasRestantes = NULL;
if($disponivel){
    // Encerrada quando a atividade ou o projeto já terminaram
    $encerrada = $atividade['data_fim'] < date('Y-m-d') || $atividade['projeto_data_fim'] < date('Y-m-d');
    if($atividade['vagas'] != NULL){
        $vagasRestantes = max(0, $atividade['vagas'] - $atividade['total_voluntarios']);
        $vagasEsgotadas = $vagasRestantes == 0;
    }
}

$dados = array(
    'nome' => '',
    'cpf' => '',
    'telefone' => '',
    'email' => ''
);

$erro = '';

// Etapa 1: só o CPF. Etapa 2: nome, telefone e e-mail (preenchidos se o CPF já está no banco de voluntários)
$etapa = 'cpf';
$voluntarioExistente = NULL;

$aberta = $disponivel && !$encerrada && !$vagasEsgotadas;
$envioCpf = $aberta && isset($_POST['MM_cpf']) && $_POST['MM_cpf'] == 'form_cpf';
$envioDados = $aberta && isset($_POST['MM_insert']) && $_POST['MM_insert'] == 'form1';

if($envioCpf || $envioDados){
    $dados['cpf'] = isset($_POST['cpf']) ? trim($_POST['cpf']) : '';
    $cpf = Documento::somenteNumeros($dados['cpf']);

    if(isset($_POST['website']) && $_POST['website'] != ''){
        // Campo invisível preenchido: envio automatizado (spam)
        $erro = 'Não foi possível realizar a inscrição.';
    }elseif(!Documento::validarCPF($cpf)){
        $erro = 'Informe um CPF válido.';
    }else{
        $dados['cpf'] = Documento::formatarCPF($cpf);
        $voluntarioExistente = $controllerVoluntario->getVolunteerByCPF($cpf);

        if($voluntarioExistente != NULL && $controllerInscricao->checkInscription($voluntarioExistente['id_voluntario'], $atividade['id_atividade'])){
            $erro = 'Este CPF já está inscrito nesta atividade.';
        }else{
            $etapa = 'dados';
        }
    }
}

if($etapa == 'dados' && $envioCpf && $voluntarioExistente != NULL){
    // Traz os dados cadastrados para o voluntário conferir e atualizar
    $dados['nome'] = $voluntarioExistente['nome'];
    $dados['email'] = (string) $voluntarioExistente['email'];
    $dados['telefone'] = Documento::formatarTelefone((string) $voluntarioExistente['telefone']);
}

if($etapa == 'dados' && $envioDados){
    $dados['nome'] = trim($_POST['nome']);
    $dados['telefone'] = trim($_POST['telefone']);
    $dados['email'] = trim($_POST['email']);

    $telefone = Documento::somenteNumeros($dados['telefone']);

    if($dados['nome'] == ''){
        $erro = 'Informe o seu nome.';
    }elseif(strlen($telefone) != 10 && strlen($telefone) != 11){
        $erro = 'Informe o telefone com DDD.';
    }elseif(!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)){
        $erro = 'Informe um e-mail válido.';
    }else{
        // CPF novo: cria o voluntário. CPF já cadastrado: atualiza os dados conforme o formulário.
        $resultado = $controllerInscricao->registerInscription(array(
            'nome' => $dados['nome'], 'cpf' => $cpf, 'email' => $dados['email'], 'telefone' => $telefone
        ), $atividade, true);

        if($resultado['erro'] == ''){
            // Volta para a listagem de atividades do projeto com a mensagem de sucesso
            $codigoProjeto = (new ProjetoController)->getProjectCode($atividade['id_projeto'], $atividade['projeto_codigo']);
            header("Location: projeto.php?c=".$codigoProjeto."&inscricao=ok");
            exit;
        }else{
            $erro = $resultado['erro'];
        }
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <title><?php echo $disponivel ? htmlspecialchars($atividade['titulo']).' - ' : ''; ?>Inscrição de Voluntários - Grupo Sete</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="GTEC - Gerência de Tecnologia">
    <meta name="robots" content="noindex,nofollow">
    <link rel="icon" href="../images/favicon.png">
    <link href="../css/inscricao.css" rel="stylesheet" type="text/css" media="screen">
    <script type="text/javascript" src="../../js/jquery.js"></script>
    <script type="text/javascript" src="../../js/jquery.mask.js"></script>
    <script type="text/javascript" src="../../js/parsley.min.js"></script>

    <script>
        // Mensagens de validação em português
        window.Parsley.addMessages('pt-br', {
            defaultMessage: 'Este valor parece ser inválido.',
            type: { email: 'Informe um e-mail válido.' },
            required: 'Este campo é obrigatório.'
        });
        window.Parsley.setLocale('pt-br');

        $(document).ready(function(){
            $('#cpf').mask('000.000.000-00');

            // Telefone fixo (10 dígitos) ou celular (11 dígitos)
            var mascaraTelefone = function (val) {
                return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009';
            };
            $('#telefone').mask(mascaraTelefone, {
                onKeyPress: function(val, e, field, options) {
                    field.mask(mascaraTelefone.apply({}, arguments), options);
                }
            });
        });
    </script>
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

        <?php if($atividade != NULL && $atividade['projeto_codigo'] != NULL){ ?>
            <a href="projeto.php?c=<?php echo $atividade['projeto_codigo']; ?>" class="voltar-projeto">« Ver todas as atividades do projeto</a>
        <?php } ?>

        <main class="cartao">
            <?php if(!$disponivel){ ?>
                <div class="indisponivel icon-lock">
                    <h1>Inscrições indisponíveis</h1>
                    <p>A página de inscrição desta atividade não está disponível no momento.</p>
                </div>
            <?php }else{ ?>
                <div class="cartao-cabecalho">
                    <span class="projeto-tag"><?php echo htmlspecialchars($atividade['projeto_titulo']); ?></span>
                    <h1><?php echo htmlspecialchars($atividade['titulo']); ?></h1>
                </div>

                <div class="cartao-corpo">
                    <section class="info-atividade">
                        <?php if($atividade['descricao'] != ''){ ?>
                            <p class="descricao"><?php echo nl2br(htmlspecialchars($atividade['descricao'])); ?></p>
                        <?php } ?>

                        <ul class="dados-atividade">
                            <li class="icon-calendar">
                                <div>
                                    <span>Data</span>
                                    <strong>
                                        <?php
                                        if($atividade['data_inicio'] == $atividade['data_fim']){
                                            echo date('d/m/Y', strtotime($atividade['data_inicio']));
                                        }else{
                                            echo date('d/m/Y', strtotime($atividade['data_inicio'])).' a '.date('d/m/Y', strtotime($atividade['data_fim']));
                                        }
                                        ?>
                                    </strong>
                                </div>
                            </li>
                            <?php if($atividade['horario'] != NULL){ ?>
                                <li class="icon-clock">
                                    <div>
                                        <span>Horário</span>
                                        <strong><?php echo htmlspecialchars($atividade['horario']); ?></strong>
                                    </div>
                                </li>
                            <?php } ?>
                            <li class="icon-home">
                                <div>
                                    <span>Local</span>
                                    <strong><?php echo htmlspecialchars($atividade['local']); ?></strong>
                                </div>
                            </li>
                            <?php if($vagasRestantes !== NULL){ ?>
                                <li class="icon-users">
                                    <div>
                                        <span>Vagas</span>
                                        <strong><?php echo $atividade['vagas']; ?></strong>
                                    </div>
                                </li>
                            <?php } ?>
                        </ul>
                    </section>

                    <section class="area-formulario">
                        <?php if($encerrada){ ?>
                            <span class="alerta notificacao"><?php echo $atividade['projeto_data_fim'] < date('Y-m-d') ? 'Este projeto foi finalizado. As inscrições estão encerradas.' : 'Esta atividade já foi encerrada. As inscrições estão fechadas.'; ?></span>
                        <?php }elseif($vagasEsgotadas){ ?>
                            <span class="alerta notificacao">As vagas desta atividade foram todas preenchidas.</span>
                        <?php }else{ ?>
                            <h2>Quero ser voluntário</h2>

                            <?php if($etapa == 'cpf'){ ?>
                                <span class="subtitulo">Informe o seu CPF para começar a inscrição.</span>

                                <?php if($erro != ''){ echo '<span class="alerta falha">'.$erro.'</span>'; } ?>

                                <form method="POST" action="inscricao.php?c=<?php echo $codigo; ?>" id="form-inscricao" data-parsley-validate="">
                                    <div class="campo">
                                        <label for="cpf">CPF</label>
                                        <input type="text" name="cpf" id="cpf" placeholder="000.000.000-00" required inputmode="numeric" autofocus value="<?php echo htmlspecialchars($dados['cpf']); ?>">
                                    </div>

                                    <div class="campo-oculto" aria-hidden="true">
                                        <label for="website">Não preencha este campo</label>
                                        <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
                                    </div>

                                    <input type="hidden" name="MM_cpf" value="form_cpf">
                                    <input type="submit" value="Continuar" class="but">
                                </form>
                            <?php }else{ ?>
                                <?php if($voluntarioExistente != NULL){ ?>
                                    <span class="subtitulo">Encontramos o seu cadastro. Confira se os seus dados estão atualizados e confirme a inscrição.</span>
                                <?php }else{ ?>
                                    <span class="subtitulo">Preencha seus dados para confirmar a inscrição.</span>
                                <?php } ?>

                                <?php if($erro != ''){ echo '<span class="alerta falha">'.$erro.'</span>'; } ?>

                                <form method="POST" action="inscricao.php?c=<?php echo $codigo; ?>" id="form-inscricao" data-parsley-validate="">
                                    <div class="campo">
                                        <label for="cpf-confirmado">CPF</label>
                                        <div class="cpf-confirmado">
                                            <input type="text" id="cpf-confirmado" value="<?php echo htmlspecialchars($dados['cpf']); ?>" readonly>
                                            <a href="inscricao.php?c=<?php echo $codigo; ?>">Trocar CPF</a>
                                        </div>
                                        <input type="hidden" name="cpf" value="<?php echo htmlspecialchars($dados['cpf']); ?>">
                                    </div>
    
                                    <div class="campo">
                                        <label for="nome">Nome completo</label>
                                        <input type="text" name="nome" id="nome" maxlength="150" required autocomplete="name" value="<?php echo htmlspecialchars($dados['nome']); ?>">
                                    </div>
    
                                    <div class="campo">
                                        <label for="telefone">Telefone</label>
                                        <input type="text" name="telefone" id="telefone" placeholder="(00) 00000-0000" required autocomplete="tel" inputmode="tel" value="<?php echo htmlspecialchars($dados['telefone']); ?>">
                                    </div>
    
                                    <div class="campo">
                                        <label for="email">E-mail</label>
                                        <input type="email" name="email" id="email" maxlength="150" required autocomplete="email" value="<?php echo htmlspecialchars($dados['email']); ?>">
                                    </div>
    
                                    <div class="campo-oculto" aria-hidden="true">
                                        <label for="website">Não preencha este campo</label>
                                        <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
                                    </div>
    
                                    <input type="hidden" name="MM_insert" value="form1">
                                    <input type="submit" value="Confirmar inscrição" class="but">
                                    <small class="aviso-privacidade">Seus dados serão usados apenas para contato sobre esta atividade.</small>
                                </form>
                            <?php } ?>
                        <?php } ?>
                    </section>
                </div>
            <?php } ?>
        </main>

        <?php include '../includes/rodape.html'; ?>
    </div>
</body>
</html>
