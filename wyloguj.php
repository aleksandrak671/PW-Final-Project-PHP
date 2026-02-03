<?php
// wylogowanie uzytkownika

require_once 'includes/config.php';

// usuniecie danych z sesji
$_SESSION = [];

// zniszczenie sesji
session_destroy();

// przekierowanie na strone glowna
header('Location: index.php');
exit;
