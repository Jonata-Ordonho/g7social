<?php
// *** Logout the current user.
$logoutGoTo = "../";
if (!isset($_SESSION)) {
    session_start();
}

$_SESSION['SS_PublicUser'] = NULL;
unset($_SESSION['SS_PublicUser']);
unset($_SESSION['PrevUrl']);
if ($logoutGoTo != "") {header("Location: $logoutGoTo");
    exit;
}
?>
