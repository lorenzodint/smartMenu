<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Verifica autenticazione
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorizzato']);
    exit;
}

// Verifica che l'ID del ristorante sia presente nella sessione
if (!isset($_SESSION['ristoranteID'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID ristorante non trovato']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Leggi il corpo della richiesta
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Log per debug
error_log("Received data: " . print_r($data, true));

if (!isset($data['id']) || !isset($data['name'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Dati prodotto mancanti']);
    exit;
}

$id = intval($data['id']);
$name = trim($data['name']);
$description = isset($data['description']) ? trim($data['description']) : '';
$price = isset($data['price']) ? floatval($data['price']) : 0;
$allergeni = isset($data['allergeni']) ? array_map('intval', $data['allergeni']) : [];
$newCategoryId = isset($data['categoryId']) ? intval($data['categoryId']) : null;
$position = isset($data['position']) ? intval($data['position']) : 1;
$visible = isset($data['visible']) ? (bool)$data['visible'] : true;
$restaurantId = $_SESSION['ristoranteID'];

try {
    // Leggi il menu completo
    $fullMenu = json_decode(file_get_contents(MENU_JSON_PATH), true);
    if (!$fullMenu) {
        throw new Exception('Impossibile leggere il menu');
    }

    // Verifica che il ristorante esista
    if (!isset($fullMenu['ristoranti'][$restaurantId])) {
        throw new Exception('Ristorante non trovato');
    }

    // Trova il prodotto e rimuovilo dalla categoria corrente
    $productData = null;
    $oldCategoryKey = null;
    $productKey = null;

    foreach ($fullMenu['ristoranti'][$restaurantId]['categories'] as $catKey => &$category) {
        foreach ($category['products'] as $prodKey => $product) {
            if ($product['id'] == $id) {
                $productData = $product;
                $oldCategoryKey = $catKey;
                $productKey = $prodKey;
                break 2;
            }
        }
    }

    if (!$productData) {
        throw new Exception('Prodotto non trovato');
    }

    // Aggiorna i dati del prodotto
    $updatedProduct = [
        'id' => $id,
        'name' => $name,
        'description' => $description,
        'price' => $price,
        'position' => $productData['position'],  // Manteniamo la posizione originale
        'visible' => $visible,
        'allergeni' => $allergeni
    ];

    // Se è stato specificato un nuovo categoryId e è diverso dalla categoria corrente
    if ($newCategoryId !== null && $newCategoryId !== $fullMenu['ristoranti'][$restaurantId]['categories'][$oldCategoryKey]['id']) {
        // Rimuovi il prodotto dalla vecchia categoria
        unset($fullMenu['ristoranti'][$restaurantId]['categories'][$oldCategoryKey]['products'][$productKey]);
        $fullMenu['ristoranti'][$restaurantId]['categories'][$oldCategoryKey]['products'] = 
            array_values($fullMenu['ristoranti'][$restaurantId]['categories'][$oldCategoryKey]['products']);

        // Trova la nuova categoria e aggiungi il prodotto
        $newCategoryFound = false;
        foreach ($fullMenu['ristoranti'][$restaurantId]['categories'] as &$category) {
            if ($category['id'] == $newCategoryId) {
                // Assegna l'ultima posizione quando si sposta in una nuova categoria
                $updatedProduct['position'] = count($category['products']) + 1;
                $category['products'][] = $updatedProduct;
                $newCategoryFound = true;
                break;
            }
        }

        if (!$newCategoryFound) {
            throw new Exception('Nuova categoria non trovata');
        }
    } else {
        // Aggiorna il prodotto nella categoria corrente mantenendo la posizione
        $fullMenu['ristoranti'][$restaurantId]['categories'][$oldCategoryKey]['products'][$productKey] = $updatedProduct;
        
        // Riordina i prodotti in base alla loro posizione
        usort($fullMenu['ristoranti'][$restaurantId]['categories'][$oldCategoryKey]['products'], 
            function($a, $b) {
                return $a['position'] - $b['position'];
            }
        );
    }

    // Salva il menu aggiornato
    if (!file_put_contents(MENU_JSON_PATH, json_encode($fullMenu, JSON_PRETTY_PRINT))) {
        throw new Exception('Errore durante il salvataggio');
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}







