<?php
// strona z lista kategorii
// wyswietla ksiazki pogrupowane wg kategorii

require_once 'includes/config.php';

// pobieranie kategorii
$stmt = $pdo->query("SELECT * FROM kategorie ORDER BY nazwa");
$kategorie = $stmt->fetchAll();

$pageTitle = 'Kategorie';
include 'includes/header.php';
?>

<div class="page-header">
    <h1>Kategorie</h1>
</div>

<?php foreach ($kategorie as $kat): ?>
    <?php
    // pobieranie ksiazek z tej kategorii (max 5)
    $stmt = $pdo->prepare("
        SELECT k.*, a.imie, a.nazwisko 
        FROM ksiazki k 
        JOIN autorzy a ON k.autor_id = a.id 
        WHERE k.kategoria_id = ? AND k.aktywna = 1 
        ORDER BY k.data_dodania DESC 
        LIMIT 5
    ");
    $stmt->execute([$kat['id']]);
    $ksiazki = $stmt->fetchAll();
    
    // liczba wszystkich ksiazek w kategorii
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM ksiazki WHERE kategoria_id = ? AND aktywna = 1");
    $stmt->execute([$kat['id']]);
    $total = $stmt->fetchColumn();
    
    if ($total == 0) continue;
    ?>
    
    <div class="category-section">
        <div class="category-header">
            <h2><?php echo h($kat['nazwa']); ?></h2>
            <a href="index.php?kategoria=<?php echo $kat['id']; ?>" class="view-all">Zobacz wszystkie (<?php echo $total; ?>)</a>
        </div>
        
        <div class="books-row">
            <?php foreach ($ksiazki as $ksiazka): ?>
                <a href="ksiazka.php?id=<?php echo $ksiazka['id']; ?>" class="book-card-small">
                    <div class="book-cover">
                        <?php if ($ksiazka['okladka']): ?>
                            <img src="<?php echo h($ksiazka['okladka']); ?>" alt="<?php echo h($ksiazka['tytul']); ?>">
                        <?php else: ?>
                            <div class="no-cover">Brak okładki</div>
                        <?php endif; ?>
                    </div>
                    <h4><?php echo h($ksiazka['tytul']); ?></h4>
                    <p class="price"><?php echo formatPrice($ksiazka['cena_promocyjna'] ?: $ksiazka['cena']); ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>

<?php include 'includes/footer.php'; ?>
