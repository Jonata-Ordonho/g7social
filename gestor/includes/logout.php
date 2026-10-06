<?php
// *** Logout the current user.
$logoutGoTo = "../";
if (!isset($_SESSION)) {
    session_start();
}

$_SESSION['SS_UserID'] = NULL;
$_SESSION['SS_UserEmail'] = NULL;
$_SESSION['SS_UserName'] = NULL;
$_SESSION['SS_UserType'] = NULL;
$_SESSION['SS_Access'] = NULL;
unset($_SESSION['SS_UserID']);
unset($_SESSION['SS_UserEmail']);
unset($_SESSION['SS_UserName']);
unset($_SESSION['SS_UserType']);
unset($_SESSION['SS_Access']);
unset($_SESSION['PrevUrl']);
if ($logoutGoTo != "") {header("Location: $logoutGoTo");
    exit;
}
?>
