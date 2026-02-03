<?php
// strona rejestracji nowego uzytkownika
// obsluguje formularz rejestracji i zapisuje dane do bazy

require_once 'includes/config.php';

// jesli juz zalogowany - przekieruj na strone glowna
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$errors = [];
$formData = [
    'imie' => '',
    'nazwisko' => '',
    'login' => '',
    'email' => '',
    'telefon' => '',
    'ulica' => '',
    'numer_domu' => '',
    'kod_pocztowy' => '',
    'miasto' => ''
];

// obsluga formularza rejestracji
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // pobranie danych z formularza
    $formData['imie'] = trim($_POST['imie'] ?? '');
    $formData['nazwisko'] = trim($_POST['nazwisko'] ?? '');
    $formData['login'] = trim($_POST['login'] ?? '');
    $formData['email'] = trim($_POST['email'] ?? '');
    $formData['telefon'] = trim($_POST['telefon'] ?? '');
    $formData['ulica'] = trim($_POST['ulica'] ?? '');
    $formData['numer_domu'] = trim($_POST['numer_domu'] ?? '');
    $formData['kod_pocztowy'] = trim($_POST['kod_pocztowy'] ?? '');
    $formData['miasto'] = trim($_POST['miasto'] ?? '');
    $haslo = $_POST['haslo'] ?? '';
    $haslo_powtorz = $_POST['haslo_powtorz'] ?? '';
    
    // walidacja
    if (empty($formData['imie'])) $errors[] = 'Imię jest wymagane.';
    if (empty($formData['nazwisko'])) $errors[] = 'Nazwisko jest wymagane.';
    if (empty($formData['login'])) $errors[] = 'Login jest wymagany.';
    if (empty($formData['email'])) $errors[] = 'Email jest wymagany.';
    if (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Nieprawidłowy format email.';
    if (empty($haslo)) $errors[] = 'Hasło jest wymagane.';
    if (strlen($haslo) < 6) $errors[] = 'Hasło musi mieć minimum 6 znaków.';
    if ($haslo !== $haslo_powtorz) $errors[] = 'Hasła nie są identyczne.';
    
    // sprawdzenie unikalnosci loginu
    $stmt = $pdo->prepare("SELECT id FROM uzytkownicy WHERE login = ?");
    $stmt->execute([$formData['login']]);
    if ($stmt->fetch()) $errors[] = 'Login jest już zajęty.';
    
    // sprawdzenie unikalnosci emaila
    $stmt = $pdo->prepare("SELECT id FROM uzytkownicy WHERE email = ?");
    $stmt->execute([$formData['email']]);
    if ($stmt->fetch()) $errors[] = 'Email jest już zarejestrowany.';
    
    // jesli brak bledow - zapisz do bazy
    if (empty($errors)) {
        $haslo_hash = password_hash($haslo, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO uzytkownicy 
            (imie, nazwisko, login, haslo, email, telefon, ulica, numer_domu, kod_pocztowy, miasto)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $formData['imie'], $formData['nazwisko'], $formData['login'], $haslo_hash,
            $formData['email'], $formData['telefon'], $formData['ulica'], 
            $formData['numer_domu'], $formData['kod_pocztowy'], $formData['miasto']
        ]);
        
        setFlashMessage('Rejestracja zakończona pomyślnie. Możesz się teraz zalogować.');
        header('Location: login.php');
        exit;
    }
}

$pageTitle = 'Rejestracja';
include 'includes/header.php';
?>

<div class="auth-container">
    <h1>Rejestracja</h1>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo h($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form method="POST" class="auth-form">
        <div class="form-row">
            <div class="form-group">
                <label for="imie">Imię: <span class="required">*</span></label>
                <input type="text" id="imie" name="imie" value="<?php echo h($formData['imie']); ?>" required>
            </div>
            <div class="form-group">
                <label for="nazwisko">Nazwisko: <span class="required">*</span></label>
                <input type="text" id="nazwisko" name="nazwisko" value="<?php echo h($formData['nazwisko']); ?>" required>
            </div>
        </div>
        
        <div class="form-group">
            <label for="login">Login: <span class="required">*</span></label>
            <input type="text" id="login" name="login" value="<?php echo h($formData['login']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email: <span class="required">*</span></label>
            <input type="email" id="email" name="email" value="<?php echo h($formData['email']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="telefon">Telefon:</label>
            <input type="tel" id="telefon" name="telefon" value="<?php echo h($formData['telefon']); ?>">
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="haslo">Hasło: <span class="required">*</span></label>
                <input type="password" id="haslo" name="haslo" required>
            </div>
            <div class="form-group">
                <label for="haslo_powtorz">Powtórz hasło: <span class="required">*</span></label>
                <input type="password" id="haslo_powtorz" name="haslo_powtorz" required>
            </div>
        </div>
        
        <h3>Adres (opcjonalnie)</h3>
        
        <div class="form-row">
            <div class="form-group">
                <label for="ulica">Ulica:</label>
                <input type="text" id="ulica" name="ulica" value="<?php echo h($formData['ulica']); ?>">
            </div>
            <div class="form-group">
                <label for="numer_domu">Numer domu:</label>
                <input type="text" id="numer_domu" name="numer_domu" value="<?php echo h($formData['numer_domu']); ?>">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="kod_pocztowy">Kod pocztowy:</label>
                <input type="text" id="kod_pocztowy" name="kod_pocztowy" value="<?php echo h($formData['kod_pocztowy']); ?>">
            </div>
            <div class="form-group">
                <label for="miasto">Miasto:</label>
                <input type="text" id="miasto" name="miasto" value="<?php echo h($formData['miasto']); ?>">
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary btn-large">Zarejestruj się</button>
    </form>
    
    <p class="auth-link">Masz już konto? <a href="login.php">Zaloguj się</a></p>
</div>

<?php include 'includes/footer.php'; ?>
