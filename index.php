<?php
session_start();
// var_dump($_SESSION);

// Controllo se l'utente è loggato
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once 'includes/config.php';
require_once 'includes/functions.php';

$menu = getMenu($_SESSION['ristoranteID']);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Menu</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- <link rel="stylesheet" href="assets/css/animazione.css"> -->
    <script>
        // Funzione per il logout automatico
        function setupAutoLogout() {
            const LOGOUT_TIME = 1 * 60 * 1000; // 3 minuti in millisecondi
            let logoutTimer;

            // Funzione per effettuare il logout
            async function performLogout() {
                try {
                    const response = await fetch('api/logout.php');
                    const data = await response.json();
                    
                    if (data.success) {
                        window.location.href = 'login.php';
                    }
                } catch (error) {
                    console.error('Errore durante il logout:', error);
                    // In caso di errore, redirect comunque alla pagina di login
                    window.location.href = 'login.php';
                }
            }

            // Funzione per resettare il timer
            function resetLogoutTimer() {
                clearTimeout(logoutTimer);
                logoutTimer = setTimeout(performLogout, LOGOUT_TIME);
            }

            // Eventi da monitorare per resettare il timer
            const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'];
            events.forEach(event => {
                document.addEventListener(event, resetLogoutTimer);
            });

            // Avvia il timer iniziale
            resetLogoutTimer();
        }

        // Inizializza l'auto logout quando la pagina è caricata
        document.addEventListener('DOMContentLoaded', setupAutoLogout);
    </script>
</head>
<body>
    <div class="container">
        <h1>Smart Menu</h1>
        
        <div class="menu-controls">
            <button id="add-category" class="control-button">➕ Categoria</button>
            <button id="add-product" class="control-button">➕ Prodotto</button>
            <button id="edit-positions" class="control-button">✏️ Posizioni</button>
            <button id="save-positions" class="control-button" style="display: none;">💾 Posizioni</button>
            <button id="edit-coperto" class="control-button">💶 Coperto</button>
            <button id="edit-note" class="control-button">📝 Note</button>
            
            <button onclick="window.location.href='qr.php?id=<?php echo $_SESSION['ristoranteID']; ?>'" class="control-button">👀 Visualizza Menu</button>
            <button id="logout" class="control-button logout-button">🚪 Logout</button>
        </div>
        
        <div id="menu-container">
            <?php if ($menu): ?>
                <?php foreach ($menu['categories'] as $category): ?>
                    <div class="category <?= $category['visible'] ? '' : 'hidden' ?>" 
                         data-id="<?= $category['id'] ?>" 
                         data-position="<?= $category['position'] ?>">
                        <div class="category-header">
                            <h2><?= htmlspecialchars($category['name']) ?></h2>
                            <div class="category-actions">
                                <button class="toggle-visibility" data-type="category" data-id="<?= $category['id'] ?>">
                                    <?= $category['visible'] ? '👁️' : '👁️‍🗨️' ?>
                                </button>
                                <a href="edit.php?type=category&id=<?= $category['id'] ?>" class="edit-button">✏️</a>
                                <span class="drag-handle">↕️</span>
                            </div>
                        </div>
                        
                        <div class="products categoria-animazione">
                            <?php foreach ($category['products'] as $product): ?>
                                <div class="product <?= $product['visible'] ? '' : 'hidden' ?>" 
                                     data-id="<?= $product['id'] ?>" 
                                     data-position="<?= $product['position'] ?>">
                                    <div class="product-content">
                                        <h3><?= htmlspecialchars($product['name']) ?></h3>
                                        <p class="description"><?= htmlspecialchars($product['description']) ?></p>
                                        <div class="cont-price-allergeni">
                                            <p class="allergeni" onclick="showAllergeniModal()">
                                                <?php 
                                                if (isset($product['allergeni']) && is_array($product['allergeni']) && !empty($product['allergeni'])) {
                                                    echo 'Allergeni: ' . implode(', ', array_map('htmlspecialchars', $product['allergeni']));
                                                } else {
                                                    echo 'Nessun allergene';
                                                }
                                                ?>
                                            </p>
                                            <p class="price">€<?= number_format($product['price'], 2) ?></p>
                                        </div>
                                    </div>
                                    <div class="product-actions">
                                        <button class="toggle-visibility" data-type="product" data-id="<?= $product['id'] ?>">
                                            <?= $product['visible'] ? '👁️' : '👁️‍🗨️' ?>
                                        </button>
                                        <a href="edit.php?type=product&id=<?= $product['id'] ?>" class="edit-button">✏️</a>
                                        <span class="drag-handle">↕️</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal per nuova categoria -->
    <div id="category-modal" class="modal">
        <div class="modal-content">
            <span class="modal-close">&times;</span>
            <h2>Aggiungi Categoria</h2>
            <form id="category-form">
                <div class="form-group">
                    <label for="category-name">Nome Categoria</label>
                    <input type="text" id="category-name" required>
                </div>
                <button type="submit" class="form-submit">Salva Categoria</button>
            </form>
        </div>
    </div>

    <!-- Modal per nuovo prodotto -->
    <div id="product-modal" class="modal">
        <div class="modal-content">
            <span class="modal-close">&times;</span>
            <h2>Aggiungi Prodotto</h2>
            <form id="product-form">
                <div class="form-group">
                    <label for="product-category">Categoria</label>
                    <select id="product-category" required>
                        <?php foreach ($menu['categories'] as $category): ?>
                            <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="product-name">Nome Prodotto</label>
                    <input type="text" id="product-name" required>
                </div>
                <div class="form-group">
                    <label for="product-description">Descrizione</label>
                    <textarea id="product-description"></textarea>
                </div>
                <div class="form-group">
                    <label for="product-price">Prezzo (€)</label>
                    <input type="number" id="product-price" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Allergeni</label>
                    <div class="allergeni-checkboxes">
                        <?php for($i = 1; $i <= 14; $i++): ?>
                            <label class="allergene-checkbox">
                                <input type="checkbox" name="allergeni[]" value="<?= $i ?>">
                                <?= $i ?>
                            </label>
                        <?php endfor; ?>
                    </div>
                </div>
                <button type="submit" class="form-submit">Salva Prodotto</button>
            </form>
        </div>
    </div>

    <!-- Modal per gli allergeni -->
    <div id="allergeni-modal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeAllergeniModal()">&times;</span>
            <div class="test-allergeni">
                <img src="assets/img/allergeni.png" alt="Tabella allergeni" style="width: 100%; height: auto;">
            </div>
        </div>
    </div>

    <!-- Modal per il coperto -->
    <div id="coperto-modal" class="modal">
        <div class="modal-content">
            <span class="modal-close">&times;</span>
            <h2>Modifica Coperto</h2>
            <form id="coperto-form">
                <div class="form-group">
                    <label for="coperto-price">Prezzo Coperto (€)</label>
                    <input type="number" id="coperto-price" step="0.01" value="<?= $menu['coperto'] ?>" required>
                </div>
                <button type="submit" class="form-submit">Salva</button>
            </form>
        </div>
    </div>

    <!-- Modal per le note -->
    <div id="note-modal" class="modal">
        <div class="modal-content">
            <span class="modal-close">&times;</span>
            <h2>Modifica Note</h2>
            <form id="note-form">
                <div class="form-group">
                    <label for="menu-note">Note</label>
                    <textarea id="menu-note" rows="4"><?= htmlspecialchars($menu['note']) ?></textarea>
                </div>
                <button type="submit" class="form-submit">Salva</button>
            </form>
        </div>
    </div>

    <script src="assets/js/menu.js"></script>
</body>
</html>














