<?php
// Restringe a página ao admin (módulo de usuários)
require_once __DIR__.'/verificar_login.php';

if (!\App\Helpers\Permissao::podeGerenciarUsuarios($_SESSION['SS_UserType'])) {
    header("Location: projeto_listar.php?acessoNegado=true");
    exit;
}
