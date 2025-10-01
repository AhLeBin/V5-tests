<?php
session_start();

function getUserDir($username){
    $dir = __DIR__ . '/data/' . $username;
    if(!is_dir($dir)) mkdir($dir, 0777, true);
    return $dir;
}

function getUserFile($username, $type){
    $dir = getUserDir($username);
    return $dir . '/' . $type . '.json';
}

function load_json($file, $default){
    if(!file_exists($file)) return $default;
    $data = json_decode(file_get_contents($file), true);
    if(!is_array($data)) return $default;
    return $data;
}

function save_json($file, $data){
    return file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
}

$raw = file_get_contents('php://input');
$input = $raw ? json_decode($raw, true) : [];
$action = $input['action'] ?? '';

$usersFile = __DIR__ . '/data/users.json';
$users = load_json($usersFile, []);

if($action === 'login'){
    $username = preg_replace('/[^a-zA-Z0-9_-]/','', strtolower($input['username'] ?? ''));
    $password = $input['password'] ?? '';

    if(!$username || !$password){
        echo json_encode(['status'=>'error','message'=>'Champs requis']); exit;
    }

    $personalDir = getUserDir($username);
    $personalJeux = getUserFile($username, 'jeux');
    $personalLoc = getUserFile($username, 'loc');
    $personalSousLoc = getUserFile($username, 'sous_loc');

    if(!isset($users[$username])){
        // Création compte
        $users[$username] = password_hash($password, PASSWORD_DEFAULT);
        save_json($usersFile, $users);
        save_json($personalJeux, []);
        save_json($personalLoc, [""]);
        save_json($personalSousLoc, [""]);
    } elseif(!password_verify($password, $users[$username])){
        echo json_encode(['status'=>'error','message'=>'Mot de passe incorrect']); exit;
    }

    $_SESSION['user'] = $username;
    echo json_encode(['status'=>'ok','message'=>'Connexion réussie']); exit;
}

// Vérification session
if($action === 'check_session'){
    echo json_encode(['status'=> isset($_SESSION['user']) ? 'ok':'error']); exit;
}

// Déconnexion
if($action === 'logout'){
    session_destroy();
    echo json_encode(['status'=>'ok']); exit;
}

// Vérifie que l'utilisateur est connecté
if(!isset($_SESSION['user'])){
    http_response_code(403);
    echo json_encode(['status'=>'error','message'=>'Non connecté']); exit;
}

$username = $_SESSION['user'];
$jeux = load_json(getUserFile($username,'jeux'), []);
$loc = load_json(getUserFile($username,'loc'), [""]);
$sous_loc = load_json(getUserFile($username,'sous_loc'), [""]);

// --- Actions CRUD ---
switch($action){
    case 'read_all':
        echo json_encode(['status'=>'ok','jeux'=>$jeux,'loc'=>$loc,'sous_loc'=>$sous_loc]);
        break;

    case 'add_or_update':
        $nom = trim($input['nom'] ?? '');
        $locSel = $input['loc'] ?? '';
        $sousSel = $input['sousloc'] ?? '';
        if(!$nom) exit(json_encode(['status'=>'error','html'=>'<div class="msg err">Nom requis.</div>']));

        $found=false;
        foreach($jeux as &$j){
            if(strtolower($j['nom'])===strtolower($nom)){
                $j['loc']=$locSel;
                $j['sousloc']=$sousSel;
                $found=true; break;
            }
        }
        if(!$found) $jeux[]= ['nom'=>$nom,'loc'=>$locSel,'sousloc'=>$sousSel];

        save_json(getUserFile($username,'jeux'), $jeux);
        echo json_encode(['status'=>'ok','html'=>"<div class='msg ok'>Jeu ".htmlspecialchars($nom)." ".($found?'mis à jour':'ajouté')."</div>"]);
        break;

    case 'delete_game':
        $nom = trim($input['nom'] ?? '');
        if(!$nom) exit(json_encode(['status'=>'error','html'=>'<div class="msg err">Nom requis.</div>']));
        $newJeux = array_filter($jeux, fn($j)=>strtolower($j['nom'])!==strtolower($nom));
        if(count($newJeux)===count($jeux)) exit(json_encode(['status'=>'error','html'=>'<div class="msg err">Jeu introuvable</div>']));
        $jeux = array_values($newJeux);
        save_json(getUserFile($username,'jeux'), $jeux);
        echo json_encode(['status'=>'ok','html'=>'<div class="msg ok">Jeu supprimé</div>']);
        break;

    case 'random':
        if(!$jeux) exit(json_encode(['status'=>'error','html'=>'<div class="msg err">Aucun jeu dispo</div>']));
        $j = $jeux[array_rand($jeux)];
        $html = "<div class='msg ok'><strong>".htmlspecialchars($j['nom'])."</strong> — ".htmlspecialchars($j['loc'])." → ".htmlspecialchars($j['sousloc'])."</div>";
        echo json_encode(['status'=>'ok','html'=>$html]);
        break;

    case 'add_loc':
        $val = trim($input['val'] ?? '');
        if(!$val) exit(json_encode(['status'=>'error','html'=>'<div class="msg err">Valeur vide</div>']));
        if(!in_array($val,$loc)) $loc[]=$val;
        save_json(getUserFile($username,'loc'),$loc);
        echo json_encode(['status'=>'ok','html'=>'<div class="msg ok">Nouvelle localisation "'.htmlspecialchars($val).'" ajoutée !</div>']);
        break;

    case 'delete_loc':
        $val = trim($input['val'] ?? '');
        $loc = array_values(array_filter($loc, fn($x)=>$x!==$val));
        save_json(getUserFile($username,'loc'),$loc);
        echo json_encode(['status'=>'ok','html'=>'<div class="msg ok">Localisation "'.htmlspecialchars($val).'" supprimée !</div>']);
        break;

    case 'add_sousloc':
        $val = trim($input['val'] ?? '');
        if(!$val) exit(json_encode(['status'=>'error','html'=>'<div class="msg err">Valeur vide</div>']));
        if(!in_array($val,$sous_loc)) $sous_loc[]=$val;
        save_json(getUserFile($username,'sous_loc'),$sous_loc);
        echo json_encode(['status'=>'ok','html'=>'<div class="msg ok">Nouvelle sous-localisation "'.htmlspecialchars($val).'" ajoutée !</div>']);
        break;

    case 'delete_sousloc':
        $val = trim($input['val'] ?? '');
        $sous_loc = array_values(array_filter($sous_loc, fn($x)=>$x!==$val));
        save_json(getUserFile($username,'sous_loc'),$sous_loc);
        echo json_encode(['status'=>'ok','html'=>'<div class="msg ok">Sous-localisation "'.htmlspecialchars($val).'" supprimée !</div>']);
        break;

    case 'search':
        $query = strtolower(trim($input['query'] ?? ''));
        $res = array_filter($jeux, fn($j)=> !$query || str_contains(strtolower($j['nom']),$query) || str_contains(strtolower($j['loc']),$query) || str_contains(strtolower($j['sousloc']),$query));
        if(!$res) echo json_encode(['status'=>'ok','html'=>'<div class="msg err">Aucun résultat</div>']);
        else {
            $lines=array_map(fn($r)=>htmlspecialchars($r['nom'])." — ".htmlspecialchars($r['loc'])." → ".htmlspecialchars($r['sousloc']), $res);
            echo json_encode(['status'=>'ok','html'=>'<div class="msg ok">'.implode("<br>",$lines).'</div>']);
        }
        break;

    default:
        echo json_encode(['status'=>'error','html'=>'<div class="msg err">Action inconnue</div>']);
}
