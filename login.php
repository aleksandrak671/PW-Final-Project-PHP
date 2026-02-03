<?php
// strona logowania uzytkownika
// obsluguje formularz logowania i weryfikuje dane z baza

require_once 'includes/config.php';

// jesli juz zalogowany - przekieruj na strone glowna
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

// obsluga formularza logowania
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $haslo = $_POST['haslo'] ?? '';
    
    if (empty($login) || empty($haslo)) {
        $error = 'Wypełnij wszystkie pola.';
    } else {
        // szukanie uzytkownika w bazie
        $stmt = $pdo->prepare("SELECT * FROM uzytkownicy WHERE login = ? AND aktywny = 1");
        $stmt->execute([$login]);
        $user = $stmt->fetch();
        
        // weryfikacja hasla
        if ($user && password_verify($haslo, $user['haslo'])) {
            // logowanie udane - zapisz dane w sesji
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_login'] = $user['login'];
            $_SESSION['user_role'] = $user['rola'];
            $_SESSION['user_name'] = $user['imie'] . ' ' . $user['nazwisko'];
            
            // aktualizacja daty ostatniego logowania
            $stmt = $pdo->prepare("UPDATE uzytkownicy SET ostatnie_logowanie = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);
            
            setFlashMessage('Zalogowano pomyślnie. Witaj, ' . $user['imie'] . '!');
            
            // przekierowanie - admin do panelu, klient do sklepu
            if ($user['rola'] === 'admin') {
                header('Location: admin/index.php');
            } else {
                header('Location: index.php');
            }
            exit;
        } else {
            $error = 'Nieprawidłowy login lub hasło.';
        }
    }
}

$pageTitle = 'Logowanie';
include 'includes/header.php';
?>

<div class="auth-container">
    <h1>Logowanie</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo h($error); ?></div>
    <?php endif; ?>
    
    <form method="POST" class="auth-form">
        <div class="form-group">
            <label for="login">Login:</label>
            <input type="text" id="login" name="login" required>
        </div>
        
        <div class="form-group">
            <label for="haslo">Hasło:</label>
            <input type="password" id="haslo" name="haslo" required>
        </div>
        
        <button type="submit" class="btn btn-primary btn-large">Zaloguj się</button>
    </form>
    
    <p class="auth-link">Nie masz konta? <a href="rejestracja.php">Zarejestruj się</a></p>
</div>

<?php include 'includes/footer.php'; ?>
