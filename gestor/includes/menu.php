<?php
// Destaca no menu o item da seção da página atual
$paginaAtual = basename($_SERVER['PHP_SELF'], '.php');
$secaoProjetos = preg_match('/^(projeto|atividade|voluntario|arrecadacao)_/', $paginaAtual);
$secaoDoadores = in_array($paginaAtual, array('doador_listar', 'doador_detalhar', 'doador_alterar'));
$secaoUsuarios = in_array($paginaAtual, array('usuario_listar', 'usuario_alterar'));
$gerenciaUsuarios = \App\Helpers\Permissao::podeGerenciarUsuarios($_SESSION['SS_UserType']);
?>
<nav id="menu">
    <ul>
        <li>
            <ul>
                <li><span class="titulo">Projetos Sociais</span></li>
                <li><a href="../views/projeto_listar.php" title="Listagem de projetos sociais" class="icon-list<?php if($secaoProjetos){ echo ' ativo'; } ?>">Listagem</a></li>
            </ul>

            <ul>
                <li><span class="titulo">Doadores</span></li>
                <li><a href="../views/doador_listar.php" title="Doadores cadastrados" class="icon-users<?php if($secaoDoadores){ echo ' ativo'; } ?>">Cadastrados</a></li>
                <li><a href="../views/doador_cadastrar.php" title="Cadastrar doador" class="icon-user-plus<?php if($paginaAtual == 'doador_cadastrar'){ echo ' ativo'; } ?>">Cadastrar doador</a></li>
            </ul>

            <?php if($gerenciaUsuarios){ ?>
                <ul>
                    <li><span class="titulo">Usuários</span></li>
                    <li><a href="../views/usuario_listar.php" title="Usuários cadastrados" class="icon-user<?php if($secaoUsuarios){ echo ' ativo'; } ?>">Cadastrados</a></li>
                    <li><a href="../views/usuario_cadastrar.php" title="Cadastrar usuário" class="icon-plus-circled<?php if($paginaAtual == 'usuario_cadastrar'){ echo ' ativo'; } ?>">Cadastrar usuário</a></li>
                </ul>
            <?php } ?>
        </li>
        <li>
            <a href="../includes/logout.php" title="Sair do sistema" id="sair" class="icon-logout">Sair</a>
        </li>
    </ul>
</nav>
<script src="../includes/dynamic_table_view.js"></script>
