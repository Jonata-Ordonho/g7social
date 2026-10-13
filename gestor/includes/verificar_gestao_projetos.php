<?php
// Restringe o cadastro e a alteração de projetos ao admin master e aos administradores
require_once __DIR__.'/verificar_login.php';

if (!\App\Helpers\Permissao::podeGerenciarProjetos($_SESSION['SS_UserType'])) {
    header("Location: projeto_listar.php?acessoNegado=true");
    exit;
}
