<?php
// strona szczegolowa ksiazki
// wyswietla wszystkie informacje o wybranej ksiazce

require_once 'includes/config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

// pobranie danych ksiazki z bazy
$stmt = $pdo->prepare("
    SELECT k.*, a.imie, a.nazwisko, a.biografia, kat.nazwa as kategoria_nazwa 
    FROM ksiazki k 
    JOIN autorzy a ON k.autor_id = a.id 
    JOIN kategorie kat ON k.kategoria_id = kat.id 
    WHERE k.id = ? AND k.aktywna = 1
");
$stmt->execute([$id]);
$ksiazka = $stmt->fetch();

if (!$ksiazka) {
    setFlashMessage('Nie znaleziono książki.', 'error');
    header('Location: index.php');
    exit;
}

$pageTitle = $ksiazka['tytul'];
include 'includes/header.php';
?>

<div class="breadcrumb">
    <a href="index.php">← Książki</a> / <?php echo h($ksiazka['kategoria_nazwa']); ?> / <?php echo h($ksiazka['tytul']); ?>
</div>

<div class="book-detail">
    <div class="book-cover-large">
        <?php if ($ksiazka['okladka']): ?>
            <img src="<?php echo h($ksiazka['okladka']); ?>" alt="<?php echo h($ksiazka['tytul']); ?>">
        <?php else: ?>
            <div class="no-cover">Brak okładki</div>
        <?php endif; ?>
    </div>
    
    <div class="book-info-detail">
        <h1><?php echo h($ksiazka['tytul']); ?></h1>
        
        <p class="author-name">
            <strong>Autor:</strong> <?php echo h($ksiazka['imie'] . ' ' . $ksiazka['nazwisko']); ?>
        </p>
        
        <p class="category">
            <strong>Kategoria:</strong> 
            <a href="index.php?kategoria=<?php echo $ksiazka['kategoria_id']; ?>">
                <?php echo h($ksiazka['kategoria_nazwa']); ?>
            </a>
        </p>
        
        <div class="price-box">
            <?php if ($ksiazka['cena_promocyjna']): ?>
                <span class="old-price"><?php echo formatPrice($ksiazka['cena']); ?></span>
                <span class="new-price"><?php echo formatPrice($ksiazka['cena_promocyjna']); ?></span>
                <span class="discount">
                    -<?php echo round((1 - $ksiazka['cena_promocyjna'] / $ksiazka['cena']) * 100); ?>%
                </span>
            <?php else: ?>
                <span class="current-price"><?php echo formatPrice($ksiazka['cena']); ?></span>
            <?php endif; ?>
        </div>
        
        <div class="stock-info">
            <?php if ($ksiazka['stan_magazynowy'] > 0): ?>
                <span class="in-stock">Dostępna (<?php echo $ksiazka['stan_magazynowy']; ?> szt.)</span>
            <?php else: ?>
                <span class="out-of-stock">Niedostępna</span>
            <?php endif; ?>
        </div>
        
        <?php if ($ksiazka['stan_magazynowy'] > 0): ?>
            <form action="dodaj_do_koszyka.php" method="POST" class="add-to-cart-form">
                <input type="hidden" name="ksiazka_id" value="<?php echo $ksiazka['id']; ?>">
                <div class="quantity-input">
                    <label for="ilosc">Ilość:</label>
                    <input type="number" id="ilosc" name="ilosc" value="1" min="1" max="<?php echo $ksiazka['stan_magazynowy']; ?>">
                </div>
                <button type="submit" class="btn btn-primary btn-large">Dodaj do koszyka</button>
            </form>
        <?php endif; ?>
        
        <div class="book-details">
            <h3>Szczegóły</h3>
            <table>
                <?php if ($ksiazka['isbn']): ?>
                    <tr><th>ISBN:</th><td><?php echo h($ksiazka['isbn']); ?></td></tr>
                <?php endif; ?>
                <?php if ($ksiazka['wydawnictwo']): ?>
                    <tr><th>Wydawnictwo:</th><td><?php echo h($ksiazka['wydawnictwo']); ?></td></tr>
                <?php endif; ?>
                <?php if ($ksiazka['rok_wydania']): ?>
                    <tr><th>Rok wydania:</th><td><?php echo $ksiazka['rok_wydania']; ?></td></tr>
                <?php endif; ?>
                <?php if ($ksiazka['liczba_stron']): ?>
                    <tr><th>Liczba stron:</th><td><?php echo $ksiazka['liczba_stron']; ?></td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<?php if ($ksiazka['opis']): ?>
    <div class="book-description">
        <h2>Opis</h2>
        <p><?php echo nl2br(h($ksiazka['opis'])); ?></p>
    </div>
<?php endif; ?>

<?php if ($ksiazka['biografia']): ?>
    <div class="author-bio">
        <h2>O autorze</h2>
        <p><?php echo nl2br(h($ksiazka['biografia'])); ?></p>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
