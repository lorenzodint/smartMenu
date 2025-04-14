<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Ottieni l'ID del ristorante dalla query string
$restaurantId = $_GET['id'] ?? null;

if (!$restaurantId) {
    die('Nessun ristorante specificato');
} else {
    $_SESSION['ristoranteID'] = $restaurantId;
    header('Location: test.php');
}


?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
</head>

<body>

</body>

</html>