<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Abilita il logging degli errori
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['type']) || !isset($data['positions'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Dati mancanti']);
    exit;
}

$type = $data['type'];
$positions = $data['positions'];

// Log dei dati ricevuti
error_log('Updating positions: ' . json_encode($data));

try {
    // Leggi il file JSON del menu
    $fullMenu = json_decode(file_get_contents(MENU_JSON_PATH), true);
    if (!$fullMenu) {
        throw new Exception('Impossibile leggere il menu');
    }

    // Usa RistoranteCalaLuna come default
    $restaurantId = 'RistoranteCalaLuna';
    $menu = &$fullMenu['ristoranti'][$restaurantId];

    if ($type === 'category') {
        // Aggiorna le posizioni delle categorie
        foreach ($positions as $position) {
            foreach ($menu['categories'] as &$category) {
                if ($category['id'] == $position['id']) {
                    $category['position'] = $position['position'];
                    break;
                }
            }
        }
        
        // Riordina le categorie
        usort($menu['categories'], function($a, $b) {
            return $a['position'] - $b['position'];
        });
    } 
    else if ($type === 'product') {
        // Crea un array associativo per le nuove posizioni
        $newPositions = [];
        foreach ($positions as $position) {
            $newPositions[$position['id']] = $position;
        }

        // Aggiorna le posizioni dei prodotti
        foreach ($menu['categories'] as &$category) {
            foreach ($category['products'] as &$product) {
                if (isset($newPositions[$product['id']])) {
                    $product['position'] = $newPositions[$product['id']]['position'];
                }
            }

            // Riordina i prodotti all'interno di ogni categoria
            usort($category['products'], function($a, $b) {
                return $a['position'] - $b['position'];
            });
        }
    }

    // Salva il menu aggiornato
    if (!file_put_contents(MENU_JSON_PATH, json_encode($fullMenu, JSON_PRETTY_PRINT))) {
        throw new Exception('Errore durante il salvataggio del file');
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    error_log('Error updating positions: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage()
    ]);
}


