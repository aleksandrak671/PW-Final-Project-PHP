<?php
/**
 * plik konfiguracyjny - polaczenie z baza danych i funkcje pomocnicze
 * ten plik jest dolaczany na poczatku kazdej strony (require_once)
 * 
 * INSTRUKCJA:
 * 1. skopiuj ten plik jako config.php
 * 2. uzupelnij dane dostepowe do swojej bazy danych
 */

// rozpoczecie sesji - potrzebna do koszyka i logowania
session_start();

// dane dostepowe do bazy danych
define('DB_HOST', 'localhost');          // adres serwera mysql
define('DB_NAME', 'ksiegarnia_online');  // nazwa bazy danych
define('DB_USER', 'twoj_uzytkownik');    // nazwa uzytkownika mysql
define('DB_PASS', 'twoje_haslo');        // haslo do mysql

// ustawienia sklepu
define('SHOP_NAME', 'Internetowa Księgarnia');  // nazwa wyswietlana w naglowku
define('SHOP_EMAIL', 'kontakt@ksiegarnia.pl');  // email kontaktowy
define('ITEMS_PER_PAGE', 12);                    // produktow na strone

// polaczenie z baza danych (pdo)
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ]
    );
    $pdo->exec("SET NAMES utf8mb4");
} catch (PDOException $e) {
    die("Błąd połączenia z bazą danych: " . $e->getMessage());
}


// funkcje pomocnicze:

// bezpieczne wyswietlanie tekstu (ochrona przed xss)
function h($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// formatowanie ceny w formacie polskim (1 234,56 zl)
function formatPrice($price) {
    return number_format($price, 2, ',', ' ') . ' zł';
}

// sprawdzenie czy uzytkownik jest zalogowany
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// sprawdzenie czy uzytkownik jest administratorem
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

// przekierowanie z komunikatem
function redirect($url, $message = '', $type = 'success') {
    if ($message) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    header("Location: $url");
    exit;
}
