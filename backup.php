<?php
session_start();
if(!isset($_SESSION['user'])){
    http_response_code(403);
    echo json_encode(['status'=>'error','message'=>'Non connecté']);
    exit;
}

$username = $_SESSION['user'];
$userDir = __DIR__ . '/data/' . $username;

if(!is_dir($userDir)){
    mkdir($userDir, 0777, true);
}

$action = $_GET['action'] ?? '';

function zipData($files, $zipPath){
    $zip = new ZipArchive();
    if($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE){
        return false;
    }
    foreach($files as $file){
        if(file_exists($file)){
            $zip->addFile($file, basename($file));
        }
    }
    $zip->close();
    return true;
}

function unzipData($zipFile, $extractPath){
    $zip = new ZipArchive();
    if($zip->open($zipFile) === TRUE){
        $zip->extractTo($extractPath);
        $zip->close();
        return true;
    }
    return false;
}

if($action === 'download'){
    $backupFile = sys_get_temp_dir() . "/backup_{$username}.zip";

    // Inclure tous les fichiers JSON de l'utilisateur
    $userFiles = glob("$userDir/*.json");

    if(zipData($userFiles, $backupFile)){
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="backup_'.$username.'.zip"');
        header('Content-Length: ' . filesize($backupFile));
        readfile($backupFile);
        unlink($backupFile); // supprimer le fichier temporaire
        exit;
    } else {
        echo "Erreur lors de la création du backup";
    }
}

if($action === 'upload'){
    if(!isset($_FILES['file'])){
        echo json_encode(['status'=>'error','message'=>'Aucun fichier reçu']);
        exit;
    }

    $uploadedFile = $_FILES['file']['tmp_name'];
    if(!is_file($uploadedFile)){
        echo json_encode(['status'=>'error','message'=>'Fichier invalide']);
        exit;
    }

    if(unzipData($uploadedFile, $userDir)){
        echo json_encode(['status'=>'ok','message'=>'Sauvegarde restaurée avec succès !']);
        exit;
    } else {
        echo json_encode(['status'=>'error','message'=>'Erreur lors de l\'extraction']);
        exit;
    }
}

// Si action inconnue
echo json_encode(['status'=>'error','message'=>'Action inconnue']);
