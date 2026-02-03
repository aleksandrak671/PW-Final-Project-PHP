<?php
// strona skladania zamowienia
// formularz z danymi dostawy i podsumowaniem

require_once 'includes/config.php';

requireLogin();

$userId = getCurrentUser()['id'];

// pobieranie koszyka z bazy danych
$stmt = $pdo->prepare("
    SELECT k.id as koszyk_id, k.ilosc, k.ksiazka_id, ks.*, a.imie as autor_imie, a.nazwisko as autor_nazwisko 
    FROM koszyk k 
    JOIN ksiazki ks ON k.ksiazka_id = ks.id 
    JOIN autorzy a ON ks.autor_id = a.id 
    WHERE k.uzytkownik_id = ?
");
$stmt->execute([$userId]);
$produkty = $stmt->fetchAll();

// sprawdzenie czy koszyk nie jest pusty
if (empty($produkty)) {
    setFlashMessage('Twój koszyk jest pusty.', 'error');
    header('Location: koszyk.php');
    exit;
}

$errors = [];

// obliczanie sumy
$suma = 0;
foreach ($produkty as $p) {
    $cena = $p['cena_promocyjna'] ?: $p['cena'];
    $suma += $cena * $p['ilosc'];
}

// dane formularza - pobierz z sesji uzytkownika jesli zalogowany
$formData = [
    'imie' => '',
    'nazwisko' => '',
    'email' => '',
    'telefon' => '',
    'ulica' => '',
    'numer_domu' => '',
    'kod_pocztowy' => '',
    'miasto' => '',
    'uwagi' => ''
];

if (isLoggedIn()) {
    $user = getCurrentUser();
    if ($user) {
        $formData['imie'] = $user['imie'];
        $formData['nazwisko'] = $user['nazwisko'];
        $formData['email'] = $user['email'];
        $formData['telefon'] = $user['telefon'] ?? '';
        $formData['ulica'] = $user['ulica'] ?? '';
        $formData['numer_domu'] = $user['numer_domu'] ?? '';
        $formData['kod_pocztowy'] = $user['kod_pocztowy'] ?? '';
        $formData['miasto'] = $user['miasto'] ?? '';
    }
}

// obsluga formularza
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['imie'] = trim($_POST['imie'] ?? '');
    $formData['nazwisko'] = trim($_POST['nazwisko'] ?? '');
    $formData['email'] = trim($_POST['email'] ?? '');
    $formData['telefon'] = trim($_POST['telefon'] ?? '');
    $formData['ulica'] = trim($_POST['ulica'] ?? '');
    $formData['numer_domu'] = trim($_POST['numer_domu'] ?? '');
    $formData['kod_pocztowy'] = trim($_POST['kod_pocztowy'] ?? '');
    $formData['miasto'] = trim($_POST['miasto'] ?? '');
    $formData['uwagi'] = trim($_POST['uwagi'] ?? '');
    
    // walidacja
    if (empty($formData['imie'])) $errors[] = 'Imię jest wymagane.';
    if (empty($formData['nazwisko'])) $errors[] = 'Nazwisko jest wymagane.';
    if (empty($formData['email'])) $errors[] = 'Email jest wymagany.';
    if (empty($formData['ulica'])) $errors[] = 'Ulica jest wymagana.';
    if (empty($formData['numer_domu'])) $errors[] = 'Numer domu jest wymagany.';
    if (empty($formData['kod_pocztowy'])) $errors[] = 'Kod pocztowy jest wymagany.';
    if (empty($formData['miasto'])) $errors[] = 'Miasto jest wymagane.';
    
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // utworzenie zamowienia
            $stmt = $pdo->prepare("
                INSERT INTO zamowienia 
                (uzytkownik_id, imie_odbiorcy, nazwisko_odbiorcy, email, telefon, 
                 ulica, numer_domu, kod_pocztowy, miasto, uwagi, suma_zamowienia)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                isLoggedIn() ? $_SESSION['user_id'] : null,
                $formData['imie'], $formData['nazwisko'], $formData['email'], $formData['telefon'],
                $formData['ulica'], $formData['numer_domu'], $formData['kod_pocztowy'], 
                $formData['miasto'], $formData['uwagi'], $suma
            ]);
            
            $zamowienie_id = $pdo->lastInsertId();
            
            // dodanie pozycji zamowienia
            $stmt = $pdo->prepare("
                INSERT INTO pozycje_zamowienia (zamowienie_id, ksiazka_id, ilosc, cena_jednostkowa)
                VALUES (?, ?, ?, ?)
            ");
            
            foreach ($produkty as $p) {
                $cena = $p['cena_promocyjna'] ?: $p['cena'];
                $stmt->execute([$zamowienie_id, $p['ksiazka_id'], $p['ilosc'], $cena]);
                
                // aktualizacja stanu magazynowego
                $pdo->prepare("UPDATE ksiazki SET stan_magazynowy = stan_magazynowy - ? WHERE id = ?")
                    ->execute([$p['ilosc'], $p['ksiazka_id']]);
            }
            
            $pdo->commit();
            
            // wyczyszczenie koszyka z bazy danych
            $stmt = $pdo->prepare("DELETE FROM koszyk WHERE uzytkownik_id = ?");
            $stmt->execute([$userId]);
            $_SESSION['ostatnie_zamowienie'] = $zamowienie_id;
            
            header('Location: potwierdzenie.php');
            exit;
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = 'Wystąpił błąd podczas składania zamówienia.';
        }
    }
}

$pageTitle = 'Zamówienie';
include 'includes/header.php';
?>

<div class="page-header">
    <h1>Złóż zamówienie</h1>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo h($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="order-page">
    <form method="POST" class="order-form">
        <div class="form-section">
            <h3>Dane odbiorcy</h3>
            
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
            
            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email: <span class="required">*</span></label>
                    <input type="email" id="email" name="email" value="<?php echo h($formData['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="telefon">Telefon:</label>
                    <input type="tel" id="telefon" name="telefon" value="<?php echo h($formData['telefon']); ?>">
                </div>
            </div>
        </div>
        
        <div class="form-section">
            <h3>Adres dostawy</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="ulica">Ulica: <span class="required">*</span></label>
                    <input type="text" id="ulica" name="ulica" value="<?php echo h($formData['ulica']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="numer_domu">Numer domu: <span class="required">*</span></label>
                    <input type="text" id="numer_domu" name="numer_domu" value="<?php echo h($formData['numer_domu']); ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="kod_pocztowy">Kod pocztowy: <span class="required">*</span></label>
                    <input type="text" id="kod_pocztowy" name="kod_pocztowy" value="<?php echo h($formData['kod_pocztowy']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="miasto">Miasto: <span class="required">*</span></label>
                    <input type="text" id="miasto" name="miasto" value="<?php echo h($formData['miasto']); ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="uwagi">Uwagi do zamówienia:</label>
                <textarea id="uwagi" name="uwagi" rows="3"><?php echo h($formData['uwagi']); ?></textarea>
            </div>
        </div>
        
        <div class="order-summary">
            <h3>Podsumowanie</h3>
            <table>
                <?php foreach ($produkty as $p): ?>
                    <?php $cena = $p['cena_promocyjna'] ?: $p['cena']; ?>
                    <tr>
                        <td><?php echo h($p['tytul']); ?> x <?php echo $p['ilosc']; ?></td>
                        <td><?php echo formatPrice($cena * $p['ilosc']); ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="total">
                    <td><strong>Razem:</strong></td>
                    <td><strong><?php echo formatPrice($suma); ?></strong></td>
                </tr>
            </table>
        </div>
        
        <div class="form-actions">
            <a href="koszyk.php" class="btn btn-secondary">Wróć do koszyka</a>
            <button type="submit" class="btn btn-primary btn-large">Złóż zamówienie</button>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
