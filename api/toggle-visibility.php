<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Log della richiesta
$inputData = file_get_contents('php://input');
error_log('Toggle visibility request: ' . $inputData);

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    error_log('Auth failed: ' . json_encode($_SESSION));
    echo json_encode(['success' => false, 'message' => 'Non autorizzato']);
    exit;
}

$data = json_decode($inputData, true);
$type = $data['type'] ?? '';
$id = $data['id'] ?? '';

// Usa 'RistoranteCalaLuna' come ID del ristorante di default
$restaurantId = 'RistoranteCalaLuna';

// Log dei dati ricevuti
error_log("Processing request - Type: $type, ID: $id, Restaurant: $restaurantId");

if (!$type || !$id) {
    echo json_encode(['success' => false, 'message' => 'Dati mancanti']);
    exit;
}

$fullMenu = json_decode(file_get_contents(MENU_JSON_PATH), true);
if (!$fullMenu || !isset($fullMenu['ristoranti'][$restaurantId])) {
    error_log("Menu not found for restaurant: $restaurantId");
    echo json_encode(['success' => false, 'message' => 'Menu non trovato']);
    exit;
}

$menu = $fullMenu['ristoranti'][$restaurantId];
$success = false;
$newState = false;

if ($type === 'category') {
    foreach ($menu['categories'] as &$category) {
        if ($category['id'] == $id) {
            $category['visible'] = !$category['visible'];
            $newState = $category['visible'];
            $success = true;
            break;
        }
    }
} elseif ($type === 'product') {
    foreach ($menu['categories'] as &$category) {
        foreach ($category['products'] as &$product) {
            if ($product['id'] == $id) {
                $product['visible'] = !$product['visible'];
                $newState = $product['visible'];
                $success = true;
                break 2;
            }
        }
    }
}

if ($success) {
    try {
        $fullMenu['ristoranti'][$restaurantId] = $menu;
        
        if (!file_put_contents(MENU_JSON_PATH, json_encode($fullMenu, JSON_PRETTY_PRINT))) {
            throw new Exception('Failed to write menu file');
        }
        
        echo json_encode([
            'success' => true, 
            'visible' => $newState,
            'message' => 'Visibilità aggiornata con successo'
        ]);
    } catch (Exception $e) {
        error_log('Error saving menu: ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Errore durante il salvataggio: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Elemento non trovato']);
}


