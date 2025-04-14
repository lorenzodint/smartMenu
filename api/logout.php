<?php
session_start();

// Distrugge tutte le variabili di sessione
$_SESSION = array();

// Distrugge il cookie di sessione
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Distrugge la sessione
session_destroy();

// Restituisce una risposta JSON
echo json_encode(['success' => true]);