<?php
// function getMenu() {
//     if (file_exists(MENU_JSON_PATH)) {
//         $json = file_get_contents(MENU_JSON_PATH);
//         return json_decode($json, true);
//     }
//     return null;
// }

function getMenu($restaurantId){
    if (file_exists(MENU_JSON_PATH)) {
        $json = file_get_contents(MENU_JSON_PATH);
        $fullMenu = json_decode($json, true);
        return $fullMenu['ristoranti'][$restaurantId] ?? null;
    }
}

function saveMenu($menu) {
    $result = file_put_contents(MENU_JSON_PATH, json_encode($menu, JSON_PRETTY_PRINT));
    if ($result === false) {
        error_log("Errore nel salvare il menu: " . error_get_last()['message']);
        return false;
    }
    return true;
}

