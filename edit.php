<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

$type = $_GET['type'] ?? '';
$id = $_GET['id'] ?? '';
$menu = getMenu($_SESSION['ristoranteID']);
$item = null;

if ($type === 'category') {
    foreach ($menu['categories'] as $category) {
        if ($category['id'] == $id) {
            $item = $category;
            break;
        }
    }
} else if ($type === 'product') {
    foreach ($menu['categories'] as $category) {
        foreach ($category['products'] as $product) {
            if ($product['id'] == $id) {
                $item = $product;
                $item['categoryId'] = $category['id'];
                break 2;
            }
        }
    }
}

if (!$item) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifica - Smart Menu</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Modifica <?= $type === 'category' ? 'Categoria' : 'Prodotto' ?></h1>
        
        <?php if ($type === 'category'): ?>
            <form id="edit-form" class="edit-form">
                <input type="hidden" id="item-id" value="<?= $item['id'] ?>">
                <input type="hidden" id="item-type" value="category">
                
                <div class="form-group">
                    <label for="category-name">Nome Categoria</label>
                    <input type="text" id="category-name" value="<?= htmlspecialchars($item['name']) ?>" required>
                </div>

                <div class="button-group">
                    <button type="submit" class="save-button">Salva Modifiche</button>
                    <button type="button" class="delete-button" onclick="deleteItem('category', <?= $item['id'] ?>)">
                        Elimina Categoria
                    </button>
                </div>
            </form>

        <?php else: ?>
            <form id="edit-form" class="edit-form">
                <input type="hidden" id="item-id" value="<?= $item['id'] ?>">
                <input type="hidden" id="item-type" value="product">
                <input type="hidden" id="item-position" value="<?= $item['position'] ?>">
                
                <div class="form-group">
                    <label for="product-category">Categoria</label>
                    <select id="product-category" required>
                        <?php foreach ($menu['categories'] as $category): ?>
                            <option value="<?= $category['id'] ?>" 
                                <?= $category['id'] == $item['categoryId'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="product-name">Nome Prodotto</label>
                    <input type="text" id="product-name" value="<?= htmlspecialchars($item['name']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="product-description">Descrizione</label>
                    <textarea id="product-description"><?= htmlspecialchars($item['description']) ?></textarea>
                </div>

                <div class="form-group">
                    <label for="product-price">Prezzo (€)</label>
                    <input type="number" id="product-price" step="0.01" value="<?= $item['price'] ?>" required>
                </div>

                <div class="form-group">
                    <label>Allergeni</label>
                    <div class="allergeni-checkboxes">
                        <?php for($i = 1; $i <= 14; $i++): ?>
                            <label class="allergene-checkbox">
                                <input type="checkbox" name="allergeni[]" value="<?= $i ?>"
                                    <?= in_array($i, $item['allergeni'] ?? []) ? 'checked' : '' ?>>
                                <?= $i ?>
                            </label>
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="button-group">
                    <button type="submit" class="save-button">Salva Modifiche</button>
                    <button type="button" class="delete-button" onclick="deleteItem('product', <?= $item['id'] ?>)">
                        Elimina Prodotto
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script src="assets/js/edit.js"></script>
</body>
</html>




