<?php
// Destaca no menu o item da seção da página atual
$paginaAtual = basename($_SERVER['PHP_SELF'], '.php');
$secaoProjetos = preg_match('/^(projeto|atividade|inscricao|arrecadacao)_/', $paginaAtual) && $paginaAtual != 'projeto_cadastrar';
$secaoVoluntarios = in_array($paginaAtual, array('voluntario_listar', 'voluntario_detalhar', 'voluntario_alterar'));
$gerenciaProjetos = \App\Helpers\Permissao::podeGerenciarProjetos($_SESSION['SS_UserType']);
$secaoDoadores = in_array($paginaAtual, array('doador_listar', 'doador_detalhar', 'doador_alterar'));
$secaoUsuarios = in_array($paginaAtual, array('usuario_listar', 'usuario_alterar'));
$gerenciaUsuarios = \App\Helpers\Permissao::podeGerenciarUsuarios($_SESSION['SS_UserType']);
?>
<nav id="menu">
    <ul>
        <li>
            <ul>
                <li><span class="titulo">Início</span></li>
                <li><a href="../views/painel.php" title="Painel com o resumo das atividades sociais" class="icon-home<?php if($paginaAtual == 'painel'){ echo ' ativo'; } ?>">Painel</a></li>
            </ul>

            <ul>
                <li><span class="titulo">Projetos Sociais</span></li>
                <li><a href="../views/projeto_listar.php" title="Listagem de projetos sociais" class="icon-list<?php if($secaoProjetos){ echo ' ativo'; } ?>">Listagem</a></li>
                <?php if($gerenciaProjetos){ ?>
                    <li><a href="../views/projeto_cadastrar.php" title="Cadastrar projeto social" class="icon-plus-squared<?php if($paginaAtual == 'projeto_cadastrar'){ echo ' ativo'; } ?>">Cadastrar projeto</a></li>
                <?php } ?>
            </ul>

            <ul>
                <li><span class="titulo">Voluntários</span></li>
                <li><a href="../views/voluntario_listar.php" title="Banco de voluntários" class="icon-heart<?php if($secaoVoluntarios){ echo ' ativo'; } ?>">Cadastrados</a></li>
            </ul>

            <ul>
                <li><span class="titulo">Doadores</span></li>
                <li><a href="../views/doador_listar.php" title="Doadores cadastrados" class="icon-users<?php if($secaoDoadores){ echo ' ativo'; } ?>">Cadastrados</a></li>
                <li><a href="../views/doador_cadastrar.php" title="Cadastrar doador" class="icon-user-plus<?php if($paginaAtual == 'doador_cadastrar'){ echo ' ativo'; } ?>">Cadastrar doador</a></li>
            </ul>

            <ul>
                <li><span class="titulo">Relatórios</span></li>
                <li><a href="../views/relatorio_geral.php" title="Relatório geral de todos os projetos" class="icon-chart-bar<?php if($paginaAtual == 'relatorio_geral'){ echo ' ativo'; } ?>">Geral</a></li>
                <li><a href="../views/relatorio_projeto.php" title="Relatório de um projeto" class="icon-doc-text<?php if($paginaAtual == 'relatorio_projeto'){ echo ' ativo'; } ?>">Por projeto</a></li>
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
