<?php
require_once __DIR__ . '/../../vendor/autoload.php';

date_default_timezone_set('America/Fortaleza');

use App\Controllers\AtividadeController;
use App\Controllers\VoluntarioController;
use App\Helpers\Documento;
use App\Models\Voluntario;

$controllerAtividade = new AtividadeController;
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
    $encerrada = $atividade['data_fim'] < date('Y-m-d');
    if($atividade['vagas'] != NULL){
        $vagasRestantes = max(0, $atividade['vagas'] - $atividade['total_voluntarios']);
        $vagasEsgotadas = $vagasRestantes == 0;
    }
}

$dados = array(
    'nome' => '',
    'telefone' => '',
    'email' => ''
);

$erro = '';

if($disponivel && !$encerrada && !$vagasEsgotadas && isset($_POST['MM_insert']) && $_POST['MM_insert'] == 'form1'){
    $dados['nome'] = trim($_POST['nome']);
    $dados['telefone'] = trim($_POST['telefone']);
    $dados['email'] = trim($_POST['email']);

    $telefone = Documento::somenteNumeros($dados['telefone']);

    if(isset($_POST['website']) && $_POST['website'] != ''){
        // Campo invisível preenchido: envio automatizado (spam)
        $erro = 'Não foi possível realizar a inscrição.';
    }elseif($dados['nome'] == ''){
        $erro = 'Informe o seu nome.';
    }elseif(strlen($telefone) != 10 && strlen($telefone) != 11){
        $erro = 'Informe o telefone com DDD.';
    }elseif(!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)){
        $erro = 'Informe um e-mail válido.';
    }elseif($controllerVoluntario->checkVolunteerEmail($atividade['id_atividade'], $dados['email'])){
        $erro = 'Este e-mail já está inscrito nesta atividade.';
    }elseif($atividade['vagas'] != NULL && $controllerVoluntario->getVolunteersTotalByActivity($atividade['id_atividade']) >= $atividade['vagas']){
        $erro = 'Desculpe, as vagas desta atividade acabaram de ser preenchidas.';
    }else{
        $objVoluntario = new Voluntario;
        $objVoluntario->setAtividadeID($atividade['id_atividade']);
        $objVoluntario->setNome($dados['nome']);
        $objVoluntario->setTelefone($telefone);
        $objVoluntario->setEmail($dados['email']);

        if($controllerVoluntario->insertVolunteer($objVoluntario)){
            header("Location: inscricao.php?c=".$codigo."&ok=1");
            exit;
        }else{
            $erro = 'Não foi possível realizar a inscrição. Tente novamente.';
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
            <img src="../images/logo_grupo_sete_branco.png" class="logo" alt="Logo Grupo Sete">
            <span class="selo">Inscrição de voluntários</span>
        </header>

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
                        <?php if(isset($_GET['ok'])){ ?>
                            <span class="alerta sucesso">Inscrição realizada com sucesso! Obrigado por se voluntariar.</span>
                        <?php } ?>

                        <?php if($encerrada){ ?>
                            <span class="alerta notificacao">Esta atividade já foi encerrada. As inscrições estão fechadas.</span>
                        <?php }elseif($vagasEsgotadas){ ?>
                            <span class="alerta notificacao">As vagas desta atividade foram todas preenchidas.</span>
                        <?php }else{ ?>
                            <h2>Quero ser voluntário</h2>
                            <span class="subtitulo">Preencha seus dados para confirmar a inscrição.</span>

                            <?php if($erro != ''){ echo '<span class="alerta falha">'.$erro.'</span>'; } ?>

                            <form method="POST" action="inscricao.php?c=<?php echo $codigo; ?>" id="form-inscricao" data-parsley-validate="">
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
                    </section>
                </div>
            <?php } ?>
        </main>

        <?php include '../includes/rodape.html'; ?>
    </div>
</body>
</html>
