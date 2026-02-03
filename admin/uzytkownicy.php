<?php
// panel administracyjny - lista uzytkownikow

require_once '../includes/config.php';
$isAdmin = true;

requireLogin('../login.php');
requireAdmin();

// pobieranie uzytkownikow
$stmt = $pdo->query("SELECT * FROM uzytkownicy ORDER BY data_rejestracji DESC");
$uzytkownicy = $stmt->fetchAll();

$pageTitle = 'Zarządzanie użytkownikami';
include '../includes/header.php';
?>

<div class="admin-header">
    <h1>Użytkownicy</h1>
</div>

<table class="admin-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Login</th>
            <th>Imię i nazwisko</th>
            <th>Email</th>
            <th>Rola</th>
            <th>Data rejestracji</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($uzytkownicy as $u): ?>
            <tr>
                <td><?php echo $u['id']; ?></td>
                <td><?php echo h($u['login']); ?></td>
                <td><?php echo h($u['imie'] . ' ' . $u['nazwisko']); ?></td>
                <td><?php echo h($u['email']); ?></td>
                <td>
                    <span class="badge <?php echo $u['rola'] === 'admin' ? 'badge-primary' : 'badge-secondary'; ?>">
                        <?php echo $u['rola']; ?>
                    </span>
                </td>
                <td><?php echo date('d.m.Y', strtotime($u['data_rejestracji'])); ?></td>
                <td>
                    <span class="badge <?php echo $u['aktywny'] ? 'badge-success' : 'badge-danger'; ?>">
                        <?php echo $u['aktywny'] ? 'Aktywny' : 'Nieaktywny'; ?>
                    </span>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>
