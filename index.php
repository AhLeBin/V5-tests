<?php
session_start();
// Redirige vers la page de connexion si l'utilisateur n'est pas connecté
if (!isset($_SESSION['user'])) {
    header('Location: login.html');
    exit;
}
$username = $_SESSION['user'];

/**
 * Détecte si l'appareil est un mobile en se basant sur l'en-tête User-Agent.
 * @return bool True si c'est un mobile, sinon False.
 */
function isMobileDevice() {
    return preg_match(
        "/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i",
        $_SERVER["HTTP_USER_AGENT"]
    );
}

// Charge la vue appropriée en fonction du type d'appareil
if (isMobileDevice()) {
    require 'mobile_view.php';
} else {
    require 'pc_view.php';
}