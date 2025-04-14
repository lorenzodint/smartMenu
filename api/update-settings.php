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
if (!isset($data['type']) || !isset($data['value'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Dati mancanti']);
    exit;
}

$type = $data['type'];
$value = $data['value'];
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

    switch ($type) {
        case 'coperto':
            $fullMenu['ristoranti'][$restaurantId]['coperto'] = floatval($value);
            break;
        case 'note':
            $fullMenu['ristoranti'][$restaurantId]['note'] = strval($value);
            break;
        default:
            throw new Exception('Tipo non valido');
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
