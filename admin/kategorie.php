<?php
// panel administracyjny - lista kategorii
// wyswietla wszystkie kategorie z mozliwoscia dodawania, edycji i usuwania

require_once '../includes/config.php';
$isAdmin = true;

requireLogin('../login.php');
requireAdmin();

$errors = [];
$editKategoria = null;

// usuwanie kategorii
if (isset($_GET['usun'])) {
    $id = (int)$_GET['usun'];
    try {
        $stmt = $pdo->prepare("DELETE FROM kategorie WHERE id = ?");
        $stmt->execute([$id]);
        setFlashMessage('Kategoria została usunięta.');
    } catch (PDOException $e) {
        setFlashMessage('Nie można usunąć kategorii - ma przypisane książki.', 'error');
    }
    header('Location: kategorie.php');
    exit;
}

// edycja kategorii - pobranie danych
if (isset($_GET['edytuj'])) {
    $id = (int)$_GET['edytuj'];
    $stmt = $pdo->prepare("SELECT * FROM kategorie WHERE id = ?");
    $stmt->execute([$id]);
    $editKategoria = $stmt->fetch();
}

// obsluga formularza
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nazwa = trim($_POST['nazwa'] ?? '');
    $opis = trim($_POST['opis'] ?? '');
    $id = (int)($_POST['id'] ?? 0);
    
    if (empty($nazwa)) $errors[] = 'Nazwa jest wymagana.';
    
    if (empty($errors)) {
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE kategorie SET nazwa = ?, opis = ? WHERE id = ?");
            $stmt->execute([$nazwa, $opis, $id]);
            setFlashMessage('Kategoria została zaktualizowana.');
        } else {
            $stmt = $pdo->prepare("INSERT INTO kategorie (nazwa, opis) VALUES (?, ?)");
            $stmt->execute([$nazwa, $opis]);
            setFlashMessage('Kategoria została dodana.');
        }
        header('Location: kategorie.php');
        exit;
    }
}

// pobieranie kategorii
$stmt = $pdo->query("
    SELECT k.*, COUNT(ks.id) as liczba_ksiazek 
    FROM kategorie k 
    LEFT JOIN ksiazki ks ON k.id = ks.kategoria_id 
    GROUP BY k.id 
    ORDER BY k.nazwa
");
$kategorie = $stmt->fetchAll();

$pageTitle = 'Zarządzanie kategoriami';
include '../includes/header.php';
?>

<div class="admin-header">
    <h1>Kategorie</h1>
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
    <h3><?php echo $editKategoria ? 'Edytuj kategorię' : 'Dodaj nową kategorię'; ?></h3>
    <form method="POST" class="inline-form">
        <input type="hidden" name="id" value="<?php echo $editKategoria['id'] ?? 0; ?>">
        <div class="form-row">
            <div class="form-group">
                <label for="nazwa">Nazwa:</label>
                <input type="text" id="nazwa" name="nazwa" value="<?php echo h($editKategoria['nazwa'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="opis">Opis:</label>
                <input type="text" id="opis" name="opis" value="<?php echo h($editKategoria['opis'] ?? ''); ?>">
            </div>
        </div>
        <button type="submit" class="btn btn-primary"><?php echo $editKategoria ? 'Zapisz zmiany' : 'Dodaj kategorię'; ?></button>
        <?php if ($editKategoria): ?>
            <a href="kategorie.php" class="btn btn-secondary">Anuluj</a>
        <?php endif; ?>
    </form>
</div>

<table class="admin-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nazwa</th>
            <th>Opis</th>
            <th>Liczba książek</th>
            <th>Akcje</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($kategorie as $k): ?>
            <tr>
                <td><?php echo $k['id']; ?></td>
                <td><?php echo h($k['nazwa']); ?></td>
                <td><?php echo h($k['opis']); ?></td>
                <td><?php echo $k['liczba_ksiazek']; ?></td>
                <td class="actions">
                    <a href="kategorie.php?edytuj=<?php echo $k['id']; ?>" class="btn btn-small">Edytuj</a>
                    <a href="kategorie.php?usun=<?php echo $k['id']; ?>" class="btn btn-small btn-danger" onclick="return confirm('Czy na pewno usunąć?')">Usuń</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>
