<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../includes/verificar_login.php';

use App\Controllers\ProjetoController;
use App\Controllers\AtividadeController;
use App\Controllers\InscricaoController;
use App\Controllers\VoluntarioController;
use App\Controllers\LogController;
use App\Helpers\Documento;
use App\Helpers\Permissao;

$controllerProjeto = new ProjetoController;
$controllerAtividade = new AtividadeController;
$controllerInscricao = new InscricaoController;
$controllerVoluntario = new VoluntarioController;
$controllerLogs = new LogController;

$idActivity = isset($_GET['id_atividade']) ? (int) $_GET['id_atividade'] : 0;

$atividade = $controllerAtividade->getActivityByID($idActivity);

if($atividade == NULL){
    header("Location: projeto_listar.php?errorInfo=true");
    exit;
}

// Confere a permissão para inscrever voluntários (regras em Permissao)
if(!Permissao::podeGerenciarVoluntarios($_SESSION['SS_UserType'])){
    header("Location: inscricao_listar.php?id_atividade=".$idActivity."&semPermissaoInscrever=true");
    exit;
}

$projeto = $controllerProjeto->getProjectByID($atividade['id_projeto']);

// modo: 'existente' (escolhe do banco de voluntários) ou 'novo' (cadastra e inscreve)
$dados = array(
    'modo' => 'existente',
    'id_voluntario' => '',
    'nome' => '',
    'cpf' => '',
    'telefone' => '',
    'email' => '',
    'grupo_sete' => false
);

$erro = '';

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
    $dados['modo'] = (isset($_POST['modo']) && $_POST['modo'] == 'novo') ? 'novo' : 'existente';

    if($dados['modo'] == 'existente'){
        $dados['id_voluntario'] = isset($_POST['id_voluntario']) ? (int) $_POST['id_voluntario'] : 0;
        $voluntario = $dados['id_voluntario'] ? $controllerVoluntario->getVolunteerByID($dados['id_voluntario']) : NULL;

        if($voluntario == NULL){
            $erro = 'Selecione o voluntário.';
        }else{
            $dadosInscricao = array(
                'nome' => $voluntario['nome'], 'cpf' => $voluntario['cpf'], 'email' => $voluntario['email'], 'telefone' => (string) $voluntario['telefone'],
                'grupo_sete' => !empty($voluntario['grupo_sete'])
            );
        }
    }else{
        $dados['nome'] = isset($_POST['nome']) ? trim($_POST['nome']) : '';
        $dados['cpf'] = isset($_POST['cpf']) ? trim($_POST['cpf']) : '';
        $dados['telefone'] = isset($_POST['telefone']) ? trim($_POST['telefone']) : '';
        $dados['email'] = isset($_POST['email']) ? trim($_POST['email']) : '';
        $dados['grupo_sete'] = isset($_POST['grupo_sete']);

        $cpf = Documento::somenteNumeros($dados['cpf']);
        $telefone = Documento::somenteNumeros($dados['telefone']);

        if($dados['nome'] == ''){
            $erro = 'Informe o nome do voluntário.';
        }elseif(!Documento::validarCPF($cpf)){
            $erro = 'Informe um CPF válido.';
        }elseif($controllerVoluntario->checkVolunteerCPF($cpf)){
            $erro = 'Este CPF já está no banco de voluntários. Escolha a opção "Voluntário já cadastrado" para inscrevê-lo.';
        }elseif(strlen($telefone) != 10 && strlen($telefone) != 11){
            $erro = 'Informe o telefone com DDD.';
        }elseif(!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)){
            $erro = 'Informe um e-mail válido.';
        }else{
            $dadosInscricao = array(
                'nome' => $dados['nome'], 'cpf' => $cpf, 'email' => $dados['email'], 'telefone' => $telefone, 'grupo_sete' => $dados['grupo_sete']
            );
        }
    }

    if($erro == ''){
        $resultado = $controllerInscricao->registerInscription($dadosInscricao, $atividade);

        if($resultado['erro'] == ''){
            $controllerLogs->insertActionUserLog(9, $_SESSION['SS_UserID'], 'Inscrição ID '.$resultado['id_inscricao'].' - Atividade ID '.$idActivity);

            header("Location: inscricao_listar.php?id_atividade=".$idActivity."&newVolunteer=".($resultado['novo_voluntario'] ? 'novo' : 'existente'));
            exit;
        }else{
            $erro = $resultado['erro'];
        }
    }
}

// Texto exibido no campo de busca quando já há voluntário escolhido (ex.: após erro de validação)
$rotuloVoluntario = '';
if($dados['id_voluntario'] != ''){
    $voluntario = $controllerVoluntario->getVolunteerByID($dados['id_voluntario']);
    if($voluntario != NULL){
        $rotuloVoluntario = $voluntario['nome'].' - CPF '.Documento::formatarCPF($voluntario['cpf']).' - '.$voluntario['email'];
    }else{
        $dados['id_voluntario'] = '';
    }
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
    <script type="text/javascript" src="../../js/jquery.js"></script>
    <script type="text/javascript" src="../../js/jquery.mask.js"></script>
    <script type="text/javascript" src="../../js/parsley.min.js"></script>
    <script type="text/javascript" src="../../js/autocomplete.js"></script>

    <script>
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

            // Exibe a busca no banco de voluntários ou os campos de cadastro de um novo voluntário
            function alternarModo(){
                var modo = $('input[name="modo"]:checked').val();
                $('.modo-existente').toggle(modo == 'existente').find('input').prop('disabled', modo != 'existente');
                $('.modo-novo').toggle(modo == 'novo').find('input').prop('disabled', modo != 'novo');
                $('#form1').parsley().reset();
            }

            $('input[name="modo"]').on('change', alternarModo);
            alternarModo();
        });
    </script>
</head>

<body>
<?php include '../includes/topo.html'; ?>
<?php include '../includes/menu.php'; ?>
<div id="container">
    <div id="conteudo">
        <h2 class="titulo">Inscrever Voluntário - <b><?php echo htmlspecialchars($atividade['titulo']); ?></b> <small>(<?php echo htmlspecialchars($projeto['titulo']); ?>)</small></h2>

        <?php if($erro != ''){ echo '<span class="alerta">'.$erro.'</span>'; } ?>

        <form method="POST" name="form1" id="form1" action="inscricao_cadastrar.php?id_atividade=<?php echo $idActivity; ?>" data-parsley-validate="">
            <div class="formulario">
                <div id="areas">
                    <label>Voluntário</label>
                    <div>
                        <label><input type="radio" name="modo" value="existente" <?php if($dados['modo'] == 'existente'){ echo 'checked'; } ?>> Voluntário já cadastrado</label>
                        <label><input type="radio" name="modo" value="novo" <?php if($dados['modo'] == 'novo'){ echo 'checked'; } ?>> Novo voluntário</label>
                    </div>
                </div>

                <div class="modo-existente">
                    <label for="busca-voluntario">Buscar no banco de voluntários</label>
                    <div class="autocomplete">
                        <input type="text" id="busca-voluntario" placeholder="Digite o nome, CPF ou e-mail" autocomplete="off" required data-url="voluntario_buscar.php" data-parsley-selecionado="#id_voluntario" data-parsley-selecionado-message="Selecione um voluntário da lista." data-parsley-errors-container="#erro-voluntario" value="<?php echo htmlspecialchars($rotuloVoluntario); ?>">
                        <input type="hidden" name="id_voluntario" id="id_voluntario" value="<?php echo htmlspecialchars($dados['id_voluntario']); ?>">
                        <ul class="autocomplete-lista"></ul>
                    </div>
                    <div id="erro-voluntario"></div>
                    <span class="dica-campo">Não encontrou? Escolha "Novo voluntário" para cadastrar a pessoa e inscrevê-la nesta atividade.</span>
                </div>

                <div class="modo-novo">
                    <label>Nome</label>
                    <input type="text" name="nome" maxlength="150" required value="<?php echo htmlspecialchars($dados['nome']); ?>">
                </div>

                <div class="modo-novo">
                    <label>CPF</label>
                    <input type="text" name="cpf" id="cpf" placeholder="000.000.000-00" required value="<?php echo htmlspecialchars($dados['cpf']); ?>">
                </div>

                <div class="modo-novo">
                    <label>Telefone</label>
                    <input type="text" name="telefone" id="telefone" placeholder="(00) 00000-0000" required value="<?php echo htmlspecialchars($dados['telefone']); ?>">
                </div>

                <div class="modo-novo">
                    <label>E-mail</label>
                    <input type="email" name="email" maxlength="150" required value="<?php echo htmlspecialchars($dados['email']); ?>">
                </div>

                <div class="modo-novo">
                    <label class="opcao-check"><input type="checkbox" name="grupo_sete" value="1" <?php if($dados['grupo_sete']){ echo 'checked'; } ?>> Faz parte do Grupo Sete (voluntário interno)</label>
                </div>

                <div class="acao-buts">
                    <input type="hidden" name="MM_insert" value="form1">
                    <input type="submit" value="Inscrever" class="but">
                    <a href="inscricao_listar.php?id_atividade=<?php echo $idActivity; ?>" class="but but-secundario">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>
<?php include '../includes/rodape.html'; ?>
</body>
</html>
