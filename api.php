<?php
session_start();
header('Content-Type: application/json');

// --- Fonctions Utilitaires ---
function getUserDir($username){
    $dir = __DIR__ . '/data/' . $username;
    if(!is_dir($dir)) mkdir($dir, 0777, true);
    return $dir;
}

function getUserFile($username, $type){
    return getUserDir($username) . '/' . $type . '.json';
}

function load_json($file, $default = []){
    if(!file_exists($file)) return $default;
    $data = json_decode(file_get_contents($file), true);
    return is_array($data) ? $data : $default;
}

function save_json($file, $data){
    return file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// --- Gestion de l'entrée ---
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $input['action'] ?? '';

// --- Authentification ---
if ($action === 'login') {
    $usersFile = __DIR__ . '/data/users.json';
    $users = load_json($usersFile);
    $username = preg_replace('/[^a-zA-Z0-9_-]/','', strtolower($input['username'] ?? ''));
    $password = $input['password'] ?? '';

    if(!$username || !$password) exit(json_encode(['status'=>'error','message'=>'Champs requis']));

    if(!isset($users[$username])){ // Création de compte
        $users[$username] = password_hash($password, PASSWORD_DEFAULT);
        save_json($usersFile, $users);
        // Initialiser les fichiers pour le nouvel utilisateur
        save_json(getUserFile($username, 'jeux'), []);
        save_json(getUserFile($username, 'loc'), [""]);
        save_json(getUserFile($username, 'sous_loc'), [""]);
        save_json(getUserFile($username, 'categories'), ["Stratégie", "Party Game", "Coopératif", "Deck-building"]);
    } elseif(!password_verify($password, $users[$username])){
        exit(json_encode(['status'=>'error','message'=>'Mot de passe incorrect']));
    }

    $_SESSION['user'] = $username;
    exit(json_encode(['status'=>'ok','message'=>'Connexion réussie']));
}

if (!isset($_SESSION['user'])) {
    http_response_code(403);
    exit(json_encode(['status'=>'error','message'=>'Non connecté']));
}

$username = $_SESSION['user'];

// --- Actions sur les données ---
switch($action){
    case 'read_all':
        echo json_encode([
            'status'=>'ok',
            'jeux'=>load_json(getUserFile($username,'jeux')),
            'loc'=>load_json(getUserFile($username,'loc'), [""]),
            'sous_loc'=>load_json(getUserFile($username,'sous_loc'), [""]),
            'categories'=>load_json(getUserFile($username,'categories'), [])
        ]);
        break;

    case 'save_game':
        $gameData = $input['game'];
        if (!$gameData || empty($gameData['nom'])) {
            exit(json_encode(['status'=>'error', 'message'=>'Le nom du jeu est requis.']));
        }

        $jeux = load_json(getUserFile($username, 'jeux'));
        $gameId = $gameData['id'] ?? null;

        if ($gameId) { // Mise à jour
            $found = false;
            foreach ($jeux as &$jeu) {
                if ($jeu['id'] === $gameId) {
                    $jeu = array_merge($jeu, $gameData);
                    $found = true;
                    break;
                }
            }
            if (!$found) exit(json_encode(['status'=>'error', 'message'=>'Jeu à mettre à jour non trouvé.']));
        } else { // Ajout
            $gameData['id'] = 'game_' . time() . rand(100, 999);
            $jeux[] = $gameData;
        }

        save_json(getUserFile($username, 'jeux'), $jeux);
        echo json_encode(['status'=>'ok', 'message'=>'Jeu sauvegardé !']);
        break;

    case 'delete_game':
        $gameId = $input['id'] ?? null;
        if (!$gameId) exit(json_encode(['status'=>'error', 'message'=>'ID de jeu manquant.']));

        $jeux = load_json(getUserFile($username, 'jeux'));
        $jeux = array_values(array_filter($jeux, fn($j) => $j['id'] !== $gameId));
        save_json(getUserFile($username, 'jeux'), $jeux);
        echo json_encode(['status'=>'ok', 'message'=>'Jeu supprimé.']);
        break;

    // --- Gestion Loc, Sous-Loc, Catégories ---
    case 'add_item':
    case 'delete_item':
        $type = $input['type'] ?? ''; // 'loc', 'sous_loc', 'categories'
        $value = trim($input['value'] ?? '');
        if (!in_array($type, ['loc', 'sous_loc', 'categories']) || !$value) {
            exit(json_encode(['status'=>'error', 'message'=>'Type ou valeur invalide.']));
        }
        $file = getUserFile($username, $type);
        $items = load_json($file, ($type === 'loc' || $type === 'sous_loc') ? [""] : []);

        if ($action === 'add_item') {
            if (!in_array($value, $items)) $items[] = $value;
            sort($items);
            $message = 'Élément ajouté.';
        } else { // delete_item
            $items = array_values(array_filter($items, fn($i) => $i !== $value));
            $message = 'Élément supprimé.';
        }
        
        save_json($file, $items);
        echo json_encode(['status'=>'ok', 'message' => $message]);
        break;
    
    default:
        echo json_encode(['status'=>'error', 'message'=>'Action inconnue.']);
}