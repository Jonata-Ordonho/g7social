<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../includes/verificar_login.php';

use App\Controllers\ProjetoController;
use App\Controllers\ArrecadacaoController;
use App\Controllers\DoadorController;
use App\Controllers\LogController;
use App\Helpers\Comprovante;
use App\Helpers\Documento;
use App\Models\Arrecadacao;

$controllerProjeto = new ProjetoController;
$controllerArrecadacao = new ArrecadacaoController;
$controllerDoador = new DoadorController;
$controllerLogs = new LogController;

$idDonation = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$arrecadacao = $controllerArrecadacao->getDonationByID($idDonation);

if($arrecadacao == NULL){
    header("Location: projeto_listar.php?errorInfo=true");
    exit;
}

$idProject = $arrecadacao['id_projeto'];
$projeto = $controllerProjeto->getProjectByID($idProject);

$dados = array(
    'id_doador' => $arrecadacao['id_doador'],
    'tipo' => $arrecadacao['tipo'],
    'valor' => $arrecadacao['valor'] != NULL ? number_format($arrecadacao['valor'], 2, ',', '.') : '',
    'descricao' => (string) $arrecadacao['descricao'],
    'data_arrecadacao' => $arrecadacao['data_arrecadacao']
);

// Comprovante já gravado: confere se o arquivo ainda existe na pasta antes de exibi-lo
$comprovanteAtual = isset($arrecadacao['comprovante']) ? (string) $arrecadacao['comprovante'] : '';
$comprovanteExiste = Comprovante::existe($comprovanteAtual);

$erro = '';

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
    $dados['id_doador'] = isset($_POST['id_doador']) ? (int) $_POST['id_doador'] : 0;
    $dados['tipo'] = (isset($_POST['tipo']) && $_POST['tipo'] == 'itens') ? 'itens' : 'dinheiro';
    $dados['valor'] = isset($_POST['valor']) ? trim($_POST['valor']) : '';
    $dados['descricao'] = isset($_POST['descricao']) ? trim($_POST['descricao']) : '';
    $dados['data_arrecadacao'] = $_POST['data_arrecadacao'];

    // Valor no formato 1.500,00
    $valor = str_replace(',', '.', str_replace('.', '', $dados['valor']));

    // Comprovante: um novo arquivo substitui o atual; sem arquivo novo, mantém o atual ou remove se marcado
    $arquivoComprovante = isset($_FILES['comprovante']) ? $_FILES['comprovante'] : NULL;
    $temComprovante = Comprovante::foiEnviado($arquivoComprovante);
    $removerComprovante = isset($_POST['remover_comprovante']);
    $nomeComprovante = NULL;

    if($dados['id_doador'] == 0 || $controllerDoador->getDonorByID($dados['id_doador']) == NULL){
        $erro = 'Selecione o doador.';
    }elseif(!strtotime($dados['data_arrecadacao'])){
        $erro = 'Informe a data da arrecadação.';
    }elseif($dados['tipo'] == 'dinheiro' && (!is_numeric($valor) || $valor <= 0)){
        $erro = 'Informe um valor válido para a doação em dinheiro.';
    }elseif($dados['tipo'] == 'itens' && $dados['descricao'] == ''){
        $erro = 'Descreva os itens doados.';
    }elseif($temComprovante && Comprovante::validar($arquivoComprovante) != ''){
        $erro = Comprovante::validar($arquivoComprovante);
    }elseif($temComprovante && ($nomeComprovante = Comprovante::salvar($arquivoComprovante)) == NULL){
        $erro = 'Não foi possível salvar o comprovante. Tente novamente.';
    }else{
        // Sem arquivo novo: mantém o atual, a não ser que tenha sido removido ou não exista mais na pasta
        if(!$temComprovante && !$removerComprovante && $comprovanteExiste){
            $nomeComprovante = $comprovanteAtual;
        }

        $objArrecadacao = new Arrecadacao;
        $objArrecadacao->setArrecadacaoID($idDonation);
        $objArrecadacao->setDoadorID($dados['id_doador']);
        $objArrecadacao->setTipo($dados['tipo']);
        $objArrecadacao->setValor($dados['tipo'] == 'dinheiro' ? number_format($valor, 2, '.', '') : NULL);
        $objArrecadacao->setDescricao($dados['tipo'] == 'itens' ? $dados['descricao'] : NULL);
        $objArrecadacao->setDataArrecadacao($dados['data_arrecadacao']);
        $objArrecadacao->setComprovante($nomeComprovante);

        if($controllerArrecadacao->updateDonation($objArrecadacao)){
            // Arquivo anterior substituído ou removido: apaga da pasta
            if($comprovanteAtual != '' && $nomeComprovante != $comprovanteAtual){
                Comprovante::excluir($comprovanteAtual);
            }

            $controllerLogs->insertActionUserLog(8, $_SESSION['SS_UserID'], 'Arrecadação ID '.$idDonation.' - Projeto ID '.$idProject);

            header("Location: arrecadacao_listar.php?id_projeto=".$idProject."&updateDonation=true");
            exit;
        }else{
            // Não deixa arquivo órfão quando o registro não foi gravado
            if($temComprovante){
                Comprovante::excluir($nomeComprovante);
            }
            $erro = 'Não foi possível alterar a arrecadação.';
        }
    }
}

// Texto exibido no campo de busca para o doador escolhido
$rotuloDoador = '';
if($dados['id_doador'] != ''){
    $doador = $controllerDoador->getDonorByID($dados['id_doador']);
    if($doador != NULL){
        $rotuloDoador = $doador['nome'].' - '.($doador['tipo'] == 'empresa' ? 'CNPJ '.Documento::formatarCNPJ($doador['documento']) : 'CPF '.Documento::formatarCPF($doador['documento']));
    }else{
        $dados['id_doador'] = '';
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
            $('#valor').mask('#.##0,00', {reverse: true});

            // Exibe o campo de valor (dinheiro) ou de descrição (itens)
            function alternarTipo(){
                var tipo = $('input[name="tipo"]:checked').val();
                $('#campo-valor').toggle(tipo == 'dinheiro');
                $('#valor').prop('disabled', tipo != 'dinheiro').prop('required', tipo == 'dinheiro');
                $('#campo-descricao').toggle(tipo == 'itens');
                $('#descricao').prop('disabled', tipo != 'itens').prop('required', tipo == 'itens');
            }

            $('input[name="tipo"]').on('change', alternarTipo);
            alternarTipo();
        });
    </script>
</head>

<body>
<?php include '../includes/topo.html'; ?>
<?php include '../includes/menu.php'; ?>
<div id="container">
    <div id="conteudo">
        <h2 class="titulo">Alterar Arrecadação - <b><?php echo htmlspecialchars($projeto['titulo']); ?></b></h2>

        <?php if($erro != ''){ echo '<span class="alerta">'.$erro.'</span>'; } ?>

        <form method="POST" name="form1" id="form1" action="arrecadacao_alterar.php?id=<?php echo $idDonation; ?>" enctype="multipart/form-data" data-parsley-validate="">
            <div class="formulario">
                <div>
                    <label for="busca-doador">Doador</label>
                    <div class="autocomplete">
                        <input type="text" id="busca-doador" placeholder="Digite o nome, CPF/CNPJ ou e-mail" autocomplete="off" required data-url="doador_buscar.php" data-parsley-selecionado="#id_doador" data-parsley-selecionado-message="Selecione um doador da lista." data-parsley-errors-container="#erro-doador" value="<?php echo htmlspecialchars($rotuloDoador); ?>">
                        <input type="hidden" name="id_doador" id="id_doador" value="<?php echo htmlspecialchars($dados['id_doador']); ?>">
                        <ul class="autocomplete-lista"></ul>
                    </div>
                    <div id="erro-doador"></div>
                </div>

                <div id="areas">
                    <label>Tipo</label>
                    <div>
                        <label><input type="radio" name="tipo" value="dinheiro" <?php if($dados['tipo'] == 'dinheiro'){ echo 'checked'; } ?>> Dinheiro</label>
                        <label><input type="radio" name="tipo" value="itens" <?php if($dados['tipo'] == 'itens'){ echo 'checked'; } ?>> Itens</label>
                    </div>
                </div>

                <div id="campo-valor">
                    <label>Valor (R$)</label>
                    <input type="text" name="valor" id="valor" placeholder="0,00" value="<?php echo htmlspecialchars($dados['valor']); ?>">
                </div>

                <div id="campo-descricao">
                    <label>Descrição dos itens</label>
                    <textarea name="descricao" id="descricao" rows="4" placeholder="Ex.: 200 cobertores"><?php echo htmlspecialchars($dados['descricao']); ?></textarea>
                </div>

                <div>
                    <label>Data da arrecadação</label>
                    <input type="date" name="data_arrecadacao" required value="<?php echo htmlspecialchars($dados['data_arrecadacao']); ?>">
                </div>

                <div>
                    <label for="comprovante">Comprovante de recebimento <span>(opcional)</span></label>
                    <?php if($comprovanteExiste){ ?>
                        <div class="comprovante-atual">
                            <a href="arrecadacao_comprovante.php?id=<?php echo $idDonation; ?>" target="_blank" title="Abrir o comprovante em uma nova aba" class="icon-attach">Ver comprovante atual</a>
                            <label class="opcao-check"><input type="checkbox" name="remover_comprovante" value="1"> Remover comprovante</label>
                        </div>
                    <?php }elseif($comprovanteAtual != ''){ ?>
                        <span class="alerta">O arquivo do comprovante desta arrecadação não foi encontrado no servidor. Envie o arquivo novamente.</span>
                    <?php } ?>
                    <input type="file" name="comprovante" id="comprovante" accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf">
                    <span class="dica-campo">Nota fiscal, recibo etc. Arquivo JPG, PNG ou PDF de até 5 MB.<?php if($comprovanteExiste){ echo ' Enviar um novo arquivo substitui o atual.'; } ?></span>
                </div>

                <div class="acao-buts">
                    <input type="hidden" name="MM_update" value="form1">
                    <input type="submit" value="Atualizar" class="but">
                    <a href="arrecadacao_listar.php?id_projeto=<?php echo $idProject; ?>" class="but but-secundario">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>
<?php include '../includes/rodape.html'; ?>
</body>
</html>
