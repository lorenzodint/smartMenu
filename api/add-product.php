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

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['categoryId']) || !isset($data['name']) || !isset($data['price'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Dati prodotto mancanti']);
    exit;
}

$categoryId = $data['categoryId'];
$name = $data['name'];
$description = $data['description'] ?? '';
$price = floatval($data['price']);
$allergeni = isset($data['allergeni']) ? $data['allergeni'] : [];
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

    // Trova il massimo ID dei prodotti nel ristorante specifico
    $maxId = 0;
    foreach ($fullMenu['ristoranti'][$restaurantId]['categories'] as $category) {
        foreach ($category['products'] as $product) {
            $maxId = max($maxId, $product['id']);
        }
    }

    // Crea il nuovo prodotto
    $newProduct = [
        'id' => $maxId + 1,
        'name' => $name,
        'description' => $description,
        'price' => $price,
        'position' => 1, // Sarà aggiornato più avanti
        'visible' => true,
        'allergeni' => $allergeni
    ];

    // Trova la categoria e aggiungi il prodotto
    $categoryFound = false;
    foreach ($fullMenu['ristoranti'][$restaurantId]['categories'] as &$category) {
        if ($category['id'] == $categoryId) {
            $newProduct['position'] = count($category['products']) + 1;
            $category['products'][] = $newProduct;
            $categoryFound = true;
            break;
        }
    }

    if (!$categoryFound) {
        throw new Exception('Categoria non trovata');
    }

    // Salva il menu aggiornato
    if (!file_put_contents(MENU_JSON_PATH, json_encode($fullMenu, JSON_PRETTY_PRINT))) {
        throw new Exception('Errore durante il salvataggio');
    }

    echo json_encode(['success' => true, 'product' => $newProduct]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

