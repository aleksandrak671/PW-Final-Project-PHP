<?php
// strona glowna ksiegarni - lista ksiazek
// wyswietla wszystkie dostepne ksiazki z mozliwoscia filtrowania

require_once 'includes/config.php';

// parametry filtrowania i sortowania
$kategoria_id = isset($_GET['kategoria']) ? (int)$_GET['kategoria'] : 0;
$szukaj = isset($_GET['szukaj']) ? trim($_GET['szukaj']) : '';
$sortowanie = isset($_GET['sort']) ? $_GET['sort'] : 'tytul';

// budowanie zapytania sql
$sql = "SELECT k.*, a.imie, a.nazwisko, kat.nazwa as kategoria_nazwa 
        FROM ksiazki k 
        JOIN autorzy a ON k.autor_id = a.id 
        JOIN kategorie kat ON k.kategoria_id = kat.id 
        WHERE k.aktywna = 1";
$params = [];

// filtrowanie po kategorii
if ($kategoria_id > 0) {
    $sql .= " AND k.kategoria_id = ?";
    $params[] = $kategoria_id;
}

// wyszukiwanie po tytule lub autorze
if (!empty($szukaj)) {
    $sql .= " AND (k.tytul LIKE ? OR a.imie LIKE ? OR a.nazwisko LIKE ?)";
    $params[] = "%$szukaj%";
    $params[] = "%$szukaj%";
    $params[] = "%$szukaj%";
}

// sortowanie
switch ($sortowanie) {
    case 'cena_asc':
        $sql .= " ORDER BY COALESCE(k.cena_promocyjna, k.cena) ASC";
        break;
    case 'cena_desc':
        $sql .= " ORDER BY COALESCE(k.cena_promocyjna, k.cena) DESC";
        break;
    case 'najnowsze':
        $sql .= " ORDER BY k.data_dodania DESC";
        break;
    default:
        $sql .= " ORDER BY k.tytul ASC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$ksiazki = $stmt->fetchAll();

// pobieranie kategorii do filtra
$stmt = $pdo->query("SELECT * FROM kategorie ORDER BY nazwa");
$kategorie = $stmt->fetchAll();

// pobierz nazwe wybranej kategorii
$aktualna_kategoria_nazwa = 'Wszystkie książki';
if ($kategoria_id > 0) {
    foreach ($kategorie as $kat) {
        if ($kat['id'] == $kategoria_id) {
            $aktualna_kategoria_nazwa = $kat['nazwa'];
            break;
        }
    }
}

$pageTitle = 'Książki';
include 'includes/header.php';
?>

<!-- pasek kategorii -->
<div class="categories-bar">
    <a href="index.php" class="category-tab <?php echo $kategoria_id == 0 ? 'active' : ''; ?>">Wszystkie</a>
    <?php foreach ($kategorie as $kat): ?>
        <a href="index.php?kategoria=<?php echo $kat['id']; ?>" class="category-tab <?php echo $kategoria_id == $kat['id'] ? 'active' : ''; ?>">
            <?php echo h($kat['nazwa']); ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="page-header">
    <h1><?php echo h($aktualna_kategoria_nazwa); ?></h1>
</div>

<!-- filtry i wyszukiwanie -->
<div class="filters-bar">
    <form method="GET" class="filters-form">
        <div class="search-box">
            <input type="text" name="szukaj" value="<?php echo h($szukaj); ?>" placeholder="Szukaj książki, autora...">
            <button type="submit" class="btn">Szukaj</button>
        </div>
        
        <div class="filter-options">
            <label>Sortuj:</label>
            <select name="sort" onchange="this.form.submit()">
                <option value="tytul" <?php echo $sortowanie == 'tytul' ? 'selected' : ''; ?>>Tytuł A-Z</option>
                <option value="cena_asc" <?php echo $sortowanie == 'cena_asc' ? 'selected' : ''; ?>>Cena rosnąco</option>
                <option value="cena_desc" <?php echo $sortowanie == 'cena_desc' ? 'selected' : ''; ?>>Cena malejąco</option>
                <option value="najnowsze" <?php echo $sortowanie == 'najnowsze' ? 'selected' : ''; ?>>Najnowsze</option>
            </select>
        </div>
    </form>
</div>

<p class="results-info"><?php echo count($ksiazki); ?> pozycji</p>

<!-- lista ksiazek -->
<?php if (empty($ksiazki)): ?>
    <div class="empty-state">
        <p>Nie znaleziono książek spełniających kryteria.</p>
        <a href="index.php" class="btn">Pokaż wszystkie</a>
    </div>
<?php else: ?>
    <div class="books-grid">
        <?php foreach ($ksiazki as $ksiazka): ?>
            <div class="book-card">
                <a href="ksiazka.php?id=<?php echo $ksiazka['id']; ?>" class="book-cover-link">
                    <div class="book-cover">
                        <?php if ($ksiazka['okladka']): ?>
                            <img src="<?php echo h($ksiazka['okladka']); ?>" alt="<?php echo h($ksiazka['tytul']); ?>">
                        <?php else: ?>
                            <div class="no-cover">Brak okładki</div>
                        <?php endif; ?>
                        <?php if ($ksiazka['cena_promocyjna']): ?>
                            <span class="badge-promo">Promocja</span>
                        <?php endif; ?>
                    </div>
                </a>
                <div class="book-info">
                    <h3><?php echo h($ksiazka['tytul']); ?></h3>
                    <p class="author"><?php echo h($ksiazka['imie'] . ' ' . $ksiazka['nazwisko']); ?></p>
                    <p class="category"><?php echo h($ksiazka['kategoria_nazwa']); ?></p>
                    <div class="price">
                        <?php if ($ksiazka['cena_promocyjna']): ?>
                            <span class="old-price"><?php echo formatPrice($ksiazka['cena']); ?></span>
                            <span class="new-price"><?php echo formatPrice($ksiazka['cena_promocyjna']); ?></span>
                        <?php else: ?>
                            <?php echo formatPrice($ksiazka['cena']); ?>
                        <?php endif; ?>
                    </div>
                    <div class="book-actions">
                        <a href="ksiazka.php?id=<?php echo $ksiazka['id']; ?>" class="btn btn-secondary">Informacje</a>
                        <form action="dodaj_do_koszyka.php" method="POST" style="flex:1;">
                            <input type="hidden" name="ksiazka_id" value="<?php echo $ksiazka['id']; ?>">
                            <button type="submit" class="btn btn-primary">Dodaj do koszyka</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
