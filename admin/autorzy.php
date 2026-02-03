<?php
// panel administracyjny - lista autorow
// wyswietla wszystkich autorow z mozliwoscia dodawania, edycji i usuwania

require_once '../includes/config.php';
$isAdmin = true;

requireLogin('../login.php');
requireAdmin();

$errors = [];
$editAutor = null;

// usuwanie autora
if (isset($_GET['usun'])) {
    $id = (int)$_GET['usun'];
    try {
        $stmt = $pdo->prepare("DELETE FROM autorzy WHERE id = ?");
        $stmt->execute([$id]);
        setFlashMessage('Autor został usunięty.');
    } catch (PDOException $e) {
        setFlashMessage('Nie można usunąć autora - ma przypisane książki.', 'error');
    }
    header('Location: autorzy.php');
    exit;
}

// edycja autora - pobranie danych
if (isset($_GET['edytuj'])) {
    $id = (int)$_GET['edytuj'];
    $stmt = $pdo->prepare("SELECT * FROM autorzy WHERE id = ?");
    $stmt->execute([$id]);
    $editAutor = $stmt->fetch();
}

// obsluga formularza
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $imie = trim($_POST['imie'] ?? '');
    $nazwisko = trim($_POST['nazwisko'] ?? '');
    $biografia = trim($_POST['biografia'] ?? '');
    $id = (int)($_POST['id'] ?? 0);
    
    if (empty($imie)) $errors[] = 'Imię jest wymagane.';
    if (empty($nazwisko)) $errors[] = 'Nazwisko jest wymagane.';
    
    if (empty($errors)) {
        if ($id > 0) {
            // aktualizacja
            $stmt = $pdo->prepare("UPDATE autorzy SET imie = ?, nazwisko = ?, biografia = ? WHERE id = ?");
            $stmt->execute([$imie, $nazwisko, $biografia, $id]);
            setFlashMessage('Autor został zaktualizowany.');
        } else {
            // dodawanie
            $stmt = $pdo->prepare("INSERT INTO autorzy (imie, nazwisko, biografia) VALUES (?, ?, ?)");
            $stmt->execute([$imie, $nazwisko, $biografia]);
            setFlashMessage('Autor został dodany.');
        }
        header('Location: autorzy.php');
        exit;
    }
}

// pobieranie autorow
$stmt = $pdo->query("
    SELECT a.*, COUNT(k.id) as liczba_ksiazek 
    FROM autorzy a 
    LEFT JOIN ksiazki k ON a.id = k.autor_id 
    GROUP BY a.id 
    ORDER BY a.nazwisko, a.imie
");
$autorzy = $stmt->fetchAll();

$pageTitle = 'Zarządzanie autorami';
include '../includes/header.php';
?>

<div class="admin-header">
    <h1>Autorzy</h1>
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

<div class="admin-form-section">
    <h3><?php echo $editAutor ? 'Edytuj autora' : 'Dodaj nowego autora'; ?></h3>
    <form method="POST" class="inline-form">
        <input type="hidden" name="id" value="<?php echo $editAutor['id'] ?? 0; ?>">
        <div class="form-row">
            <div class="form-group">
                <label for="imie">Imię:</label>
                <input type="text" id="imie" name="imie" value="<?php echo h($editAutor['imie'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="nazwisko">Nazwisko:</label>
                <input type="text" id="nazwisko" name="nazwisko" value="<?php echo h($editAutor['nazwisko'] ?? ''); ?>" required>
            </div>
        </div>
        <div class="form-group">
            <label for="biografia">Biografia:</label>
            <textarea id="biografia" name="biografia" rows="3"><?php echo h($editAutor['biografia'] ?? ''); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary"><?php echo $editAutor ? 'Zapisz zmiany' : 'Dodaj autora'; ?></button>
        <?php if ($editAutor): ?>
            <a href="autorzy.php" class="btn btn-secondary">Anuluj</a>
        <?php endif; ?>
    </form>
</div>

<table class="admin-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Imię</th>
            <th>Nazwisko</th>
            <th>Liczba książek</th>
            <th>Akcje</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($autorzy as $a): ?>
            <tr>
                <td><?php echo $a['id']; ?></td>
                <td><?php echo h($a['imie']); ?></td>
                <td><?php echo h($a['nazwisko']); ?></td>
                <td><?php echo $a['liczba_ksiazek']; ?></td>
                <td class="actions">
                    <a href="autorzy.php?edytuj=<?php echo $a['id']; ?>" class="btn btn-small">Edytuj</a>
                    <a href="autorzy.php?usun=<?php echo $a['id']; ?>" class="btn btn-small btn-danger" onclick="return confirm('Czy na pewno usunąć?')">Usuń</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>
