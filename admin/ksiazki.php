<?php
// panel administracyjny - lista ksiazek
// wyswietla wszystkie ksiazki z mozliwoscia edycji i usuwania

require_once '../includes/config.php';
$isAdmin = true;

requireLogin('../login.php');
requireAdmin();

// usuwanie ksiazki
if (isset($_GET['usun'])) {
    $id = (int)$_GET['usun'];
    try {
        $stmt = $pdo->prepare("DELETE FROM ksiazki WHERE id = ?");
        $stmt->execute([$id]);
        setFlashMessage('Książka została usunięta.');
    } catch (PDOException $e) {
        setFlashMessage('Nie można usunąć książki - jest używana w zamówieniach.', 'error');
    }
    header('Location: ksiazki.php');
    exit;
}

// pobieranie ksiazek
$stmt = $pdo->query("
    SELECT k.*, a.imie, a.nazwisko, kat.nazwa as kategoria_nazwa 
    FROM ksiazki k 
    JOIN autorzy a ON k.autor_id = a.id 
    JOIN kategorie kat ON k.kategoria_id = kat.id 
    ORDER BY k.data_dodania DESC
");
$ksiazki = $stmt->fetchAll();

$pageTitle = 'Zarządzanie książkami';
include '../includes/header.php';
?>

<div class="admin-header">
    <h1>Książki</h1>
    <a href="ksiazka_edycja.php" class="btn btn-primary">Dodaj książkę</a>
</div>

<table class="admin-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Tytuł</th>
            <th>Autor</th>
            <th>Kategoria</th>
            <th>Cena</th>
            <th>Stan</th>
            <th>Status</th>
            <th>Akcje</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($ksiazki as $k): ?>
            <tr>
                <td><?php echo $k['id']; ?></td>
                <td><?php echo h($k['tytul']); ?></td>
                <td><?php echo h($k['imie'] . ' ' . $k['nazwisko']); ?></td>
                <td><?php echo h($k['kategoria_nazwa']); ?></td>
                <td>
                    <?php if ($k['cena_promocyjna']): ?>
                        <span class="old-price"><?php echo formatPrice($k['cena']); ?></span>
                        <?php echo formatPrice($k['cena_promocyjna']); ?>
                    <?php else: ?>
                        <?php echo formatPrice($k['cena']); ?>
                    <?php endif; ?>
                </td>
                <td><?php echo $k['stan_magazynowy']; ?></td>
                <td>
                    <span class="badge <?php echo $k['aktywna'] ? 'badge-success' : 'badge-danger'; ?>">
                        <?php echo $k['aktywna'] ? 'Aktywna' : 'Nieaktywna'; ?>
                    </span>
                </td>
                <td class="actions">
                    <a href="ksiazka_edycja.php?id=<?php echo $k['id']; ?>" class="btn btn-small">Edytuj</a>
                    <a href="ksiazki.php?usun=<?php echo $k['id']; ?>" class="btn btn-small btn-danger" onclick="return confirm('Czy na pewno usunąć?')">Usuń</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>
