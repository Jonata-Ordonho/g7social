<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../includes/verificar_login.php';

use App\Controllers\DoadorController;
use App\Controllers\LogController;
use App\Helpers\Documento;
use App\Models\Doador;

$controllerDoador = new DoadorController;
$controllerLogs = new LogController;

$idDonor = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$doador = $controllerDoador->getDonorByID($idDonor);

if($doador == NULL){
    header("Location: doador_listar.php?errorInfo=true");
    exit;
}

$dados = array(
    'tipo' => $doador['tipo'],
    'nome' => $doador['nome'],
    'documento' => $doador['tipo'] == 'empresa' ? Documento::formatarCNPJ($doador['documento']) : Documento::formatarCPF($doador['documento']),
    'email' => (string) $doador['email'],
    'telefone' => Documento::formatarTelefone((string) $doador['telefone'])
);

$erro = '';

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
    $dados['tipo'] = (isset($_POST['tipo']) && $_POST['tipo'] == 'pessoa') ? 'pessoa' : 'empresa';
    $dados['nome'] = trim($_POST['nome']);
    $dados['documento'] = trim($_POST['documento']);
    $dados['email'] = trim($_POST['email']);
    $dados['telefone'] = trim($_POST['telefone']);

    $documento = Documento::somenteNumeros($dados['documento']);
    $telefone = Documento::somenteNumeros($dados['telefone']);
    $nomeDocumento = $dados['tipo'] == 'empresa' ? 'CNPJ' : 'CPF';

    if($dados['nome'] == ''){
        $erro = 'Informe o nome do doador.';
    }elseif($dados['tipo'] == 'empresa' && !Documento::validarCNPJ($documento)){
        $erro = 'Informe um CNPJ válido.';
    }elseif($dados['tipo'] == 'pessoa' && !Documento::validarCPF($documento)){
        $erro = 'Informe um CPF válido.';
    }elseif($dados['email'] != '' && !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)){
        $erro = 'Informe um e-mail válido.';
    }elseif($telefone != '' && strlen($telefone) != 10 && strlen($telefone) != 11){
        $erro = 'Informe o telefone com DDD.';
    }elseif($controllerDoador->checkDonorDocument($dados['tipo'], $documento, $idDonor)){
        $erro = 'Já existe outro doador cadastrado com este '.$nomeDocumento.'.';
    }else{
        $objDoador = new Doador;
        $objDoador->setDoadorID($idDonor);
        $objDoador->setTipo($dados['tipo']);
        $objDoador->setNome($dados['nome']);
        $objDoador->setDocumento($documento);
        $objDoador->setEmail($dados['email'] != '' ? $dados['email'] : NULL);
        $objDoador->setTelefone($telefone != '' ? $telefone : NULL);

        if($controllerDoador->updateDonor($objDoador)){
            $controllerLogs->insertActionUserLog(7, $_SESSION['SS_UserID'], 'Doador ID '.$idDonor);

            header("Location: doador_listar.php?updateDonor=true");
            exit;
        }else{
            $erro = 'Não foi possível alterar o doador.';
        }
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

    <script>
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

            // Troca o rótulo e a máscara do documento conforme o tipo
            function alternarTipo(limpar){
                var empresa = $('input[name="tipo"]:checked').val() == 'empresa';
                $('#label-documento').text(empresa ? 'CNPJ' : 'CPF');
                $('#label-nome').text(empresa ? 'Razão social/Nome da empresa' : 'Nome completo');
                if(limpar){
                    $('#documento').val('');
                }
                $('#documento').unmask().mask(empresa ? '00.000.000/0000-00' : '000.000.000-00')
                    .attr('placeholder', empresa ? '00.000.000/0000-00' : '000.000.000-00');
            }

            $('input[name="tipo"]').on('change', function(){ alternarTipo(true); });
            alternarTipo(false);
        });
    </script>
</head>

<body>
<?php include '../includes/topo.html'; ?>
<?php include '../includes/menu.php'; ?>
<div id="container">
    <div id="conteudo">
        <h2 class="titulo">Alterar Doador</h2>

        <?php if($erro != ''){ echo '<span class="alerta">'.$erro.'</span>'; } ?>

        <form method="POST" name="form1" id="form1" action="doador_alterar.php?id=<?php echo $idDonor; ?>" data-parsley-validate="">
            <div class="formulario">
                <div id="areas">
                    <label>Tipo</label>
                    <div>
                        <label><input type="radio" name="tipo" value="empresa" <?php if($dados['tipo'] == 'empresa'){ echo 'checked'; } ?>> Empresa</label>
                        <label><input type="radio" name="tipo" value="pessoa" <?php if($dados['tipo'] == 'pessoa'){ echo 'checked'; } ?>> Pessoa</label>
                    </div>
                </div>

                <div>
                    <label id="label-nome">Nome</label>
                    <input type="text" name="nome" maxlength="200" required value="<?php echo htmlspecialchars($dados['nome']); ?>">
                </div>

                <div>
                    <label id="label-documento">CNPJ</label>
                    <input type="text" name="documento" id="documento" required value="<?php echo htmlspecialchars($dados['documento']); ?>">
                </div>

                <div>
                    <label>E-mail <span>(opcional)</span></label>
                    <input type="email" name="email" maxlength="150" value="<?php echo htmlspecialchars($dados['email']); ?>">
                </div>

                <div>
                    <label>Telefone <span>(opcional)</span></label>
                    <input type="text" name="telefone" id="telefone" placeholder="(00) 00000-0000" value="<?php echo htmlspecialchars($dados['telefone']); ?>">
                </div>

                <div class="acao-buts">
                    <input type="hidden" name="MM_update" value="form1">
                    <input type="submit" value="Atualizar" class="but">
                    <a href="doador_listar.php" class="but but-secundario">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>
<?php include '../includes/rodape.html'; ?>
</body>
</html>
