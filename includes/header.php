<?php
// naglowek strony - dolaczany na poczatku kazdej strony
// zawiera doctype, meta tagi, css i nawigacje
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($pageTitle ?? SHOP_NAME); ?></title>
    <link rel="stylesheet" href="<?php echo isset($isAdmin) ? '../style.css' : 'style.css'; ?>">
</head>
<body>
    <header class="header">
        <div class="container">
            <a href="<?php echo isset($isAdmin) ? '../index.php' : 'index.php'; ?>" class="logo">
                <?php echo SHOP_NAME; ?>
            </a>
            
            <nav class="nav">
                <?php if (isset($isAdmin)): ?>
                    <!-- nawigacja panelu admina -->
                    <a href="index.php" class="nav-link">Panel</a>
                    <a href="ksiazki.php" class="nav-link">Książki</a>
                    <a href="autorzy.php" class="nav-link">Autorzy</a>
                    <a href="kategorie.php" class="nav-link">Kategorie</a>
                    <a href="zamowienia.php" class="nav-link">Zamówienia</a>
                    <a href="uzytkownicy.php" class="nav-link">Użytkownicy</a>
                    <a href="../index.php" class="nav-link">Sklep</a>
                <?php else: ?>
                    <!-- nawigacja sklepu -->
                    <a href="index.php" class="nav-link">Książki</a>
                    <a href="kategorie.php" class="nav-link">Kategorie</a>
                    <a href="koszyk.php" class="nav-link">Koszyk</a>
                    <?php if (isLoggedIn()): ?>
                        <a href="moje_zamowienia.php" class="nav-link">Moje zamówienia</a>
                        <?php if (isAdmin()): ?>
                            <a href="admin/index.php" class="nav-link">Panel admina</a>
                        <?php endif; ?>
                        <a href="wyloguj.php" class="nav-link">Wyloguj</a>
                    <?php else: ?>
                        <a href="login.php" class="nav-link">Zaloguj się</a>
                        <a href="rejestracja.php" class="btn btn-register">Zarejestruj się</a>
                    <?php endif; ?>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    
    <main class="main-content">
        <div class="container">
            <?php
            // wyswietlanie komunikatow flash
            $successMsg = getFlashMessage('success');
            $errorMsg = getFlashMessage('error');
            if ($successMsg): ?>
                <div class="alert alert-success"><?php echo h($successMsg); ?></div>
            <?php endif; ?>
            <?php if ($errorMsg): ?>
                <div class="alert alert-error"><?php echo h($errorMsg); ?></div>
            <?php endif; ?>
