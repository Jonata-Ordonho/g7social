<?php

if (!isset($_SESSION)) {
    session_start();
}

// TODO: ajustar a chave de sessão quando a forma de acesso do público for definida
$MM_restrictGoTo = "../?acesso_restrito=true";
if (!isset($_SESSION['SS_PublicUser'])) {
    header("Location: ". $MM_restrictGoTo);
    exit;
}
