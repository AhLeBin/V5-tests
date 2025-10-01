<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<title>Où Sont Mes Jeux</title>
<script>
  (function() {
    // Applique le thème avant même le rendu de la page pour éviter le "flash"
    const theme = localStorage.getItem('theme');
    if (theme === 'dark') {
      document.documentElement.classList.add('dark-mode');
    } else if (theme === 'light') {
      document.documentElement.classList.remove('dark-mode');
    } else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
      document.documentElement.classList.add('dark-mode');
    }
  })();
</script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<link rel="icon" type="image/vnd.icon" href="/images/icone.ico">
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
</head>
<body class="<?php echo (isset($_COOKIE['theme']) && $_COOKIE['theme'] == 'dark') ? 'dark-mode' : ''; ?>">
<div class="container">
  <header>
    <img id="logo" src="images/logo_pc.png" alt="Logo Où Sont Mes Jeux">
    <div id="user-info">
        <div class="theme-switch-wrapper">
          <label>
            <input type="checkbox" id="theme-toggle">
            <div class="switch">
              <div class="slider">
                <svg class="sun" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M12,8a4,4,0,1,1-4,4A4,4,0,0,1,12,8m0-3a1,1,0,0,0,1-1V3a1,1,0,0,0-2,0V4A1,1,0,0,0,12,5M20,11H19a1,1,0,0,0,0,2h1a1,1,0,0,0,0-2M5,11H4a1,1,0,0,0,0,2H5a1,1,0,0,0,0-2M12,19a1,1,0,0,0-1,1v1a1,1,0,0,0,2,0V20A1,1,0,0,0,12,19M17.66,6.34a1,1,0,0,0,.71-.29,1,1,0,0,0,0-1.42L17.66,4.34a1,1,0,0,0-1.41,1.41l.7.71A1,1,0,0,0,17.66,6.34M6.34,17.66a1,1,0,0,0-1.41,0l-1,1a1,1,0,0,0,1.41,1.41l1-1A1,1,0,0,0,6.34,17.66M19.07,17.66a1,1,0,0,0-1.41,0l-1,1a1,1,0,0,0,0,1.41,1,1,0,0,0,1.41,0l1-1A1,1,0,0,0,19.07,17.66M4.93,6.34A1,1,0,0,0,4.22,7.05l.71.7a1,1,0,0,0,1.41-1.41L5.64,5.64A1,1,0,0,0,4.93,6.34Z"/>
                </svg>
                <svg class="moon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
                </svg>
              </div>
            </div>
          </label>
        </div>
      <span>Utilisateur <strong><?php echo htmlspecialchars($username); ?></strong></span>
      <button id="logout-btn" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i>Déconnexion</button>
    </div>
  </header>

  <main>
    <div class="views-container">
      <section class="card view" id="view-manage">
        <h2><i class="fas fa-edit"></i> Gérer un jeu</h2>
        
        <div class="form-group">
          <label for="nom">Nom du jeu</label>
          <div class="input-with-button">
            <input id="nom" placeholder="Ex: Tokaido">
            <button id="effacer_nom" type="button" class="btn-icon-inside" title="Effacer"><i class="fas fa-times"></i></button>
          </div>
        </div>

        <div class="form-group">
          <label for="menu_loc">Localisation</label>
          <select id="menu_loc" style="margin-bottom: 0.5rem;"></select>
          <div class="inline-group">
            <div class="input-with-button">
              <input id="champ_loc" placeholder="Nouvelle localisation">
              <button id="effacer_champ_loc" type="button" class="btn-icon-inside" title="Effacer"><i class="fas fa-times"></i></button>
            </div>
            <div class="button-group">
              <button id="ajouter_loc" class="btn btn-success" type="button"><i class="fas fa-plus"></i></button>
              <button id="supprimer_loc" type="button" class="btn btn-danger"><i class="fas fa-trash"></i></button>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label for="menu_sousloc">Sous-localisation</label>
          <select id="menu_sousloc" style="margin-bottom: 0.5rem;"></select>
          <div class="inline-group">
            <div class="input-with-button">
              <input id="champ_sousloc" placeholder="Nouvelle sous-loc">
              <button id="effacer_champ_sousloc" type="button" class="btn-icon-inside" title="Effacer"><i class="fas fa-times"></i></button>
            </div>
            <div class="button-group">
              <button id="ajouter_sousloc" class="btn btn-success" type="button"><i class="fas fa-plus"></i></button>
              <button id="supprimer_sousloc" type="button" class="btn btn-danger"><i class="fas fa-trash"></i></button>
            </div>
          </div>
        </div>

        <div class="actions">
          <button id="Update" type="button" class="btn btn-primary"><i class="fas fa-save"></i> Ajouter / Modifier</button>
          <button id="suppr" type="button" class="btn btn-danger"><i class="fas fa-trash-alt"></i> Supprimer le jeu</button>
        </div>
        
        <div class="mobile-backup-actions">
            <hr class="separator">
            <h2><i class="fas fa-database"></i> Sauvegarde</h2>
            <div class="actions">
                <button id="downloadBackupMobile" type="button" class="btn btn-primary"><i class="fas fa-download"></i> Télécharger</button>
                <button id="uploadBtnMobile" type="button" class="btn btn-success"><i class="fas fa-upload"></i> Restaurer</button>
            </div>
        </div>
      </section>

      <section class="card view" id="view-list">
        <h2><i class="fas fa-search"></i> Recherche & Liste</h2>
        <div class="form-group">
          <label for="search_nom">Nom du jeu</label>
          <div class="input-with-button">
            <input id="search_nom" placeholder="Rechercher par nom">
            <button id="effacer_search_nom" type="button" class="btn-icon-inside" title="Effacer"><i class="fas fa-times"></i></button>
          </div>
        </div>
        <div class="form-group">
          <label for="search_loc">Localisation</label>
          <div class="input-with-button">
            <input id="search_loc" placeholder="Rechercher par localisation">
            <button id="effacer_search_loc" type="button" class="btn-icon-inside" title="Effacer"><i class="fas fa-times"></i></button>
          </div>
        </div>
        <div class="form-group">
          <label for="search_sousloc">Sous-localisation</label>
          <div class="input-with-button">
            <input id="search_sousloc" placeholder="Rechercher par sous-localisation">
            <button id="effacer_search_sousloc" type="button" class="btn-icon-inside" title="Effacer"><i class="fas fa-times"></i></button>
          </div>
        </div>
        <div class="actions">
          <button id="cherche" type="button" class="btn btn-primary" style="flex: 1;"><i class="fas fa-search"></i> Rechercher</button>
          <button id="Random" type="button" class="btn btn-random" style="flex: 1;"><i class="fas fa-dice"></i> Jeu au hasard</button>
        </div>
        <hr class="separator">
        <div id="search-count"></div>
        <h3><i class="fas fa-list-ul"></i> Vos jeux</h3>
        <div id="liste" class="game-list"></div>
      </section>
    </div>
  </main>
  
  <footer >© 2025 — Où Sont Mes Jeux</footer>
</div>

<nav class="mobile-nav">
    <button class="mobile-nav-btn" data-view="view-manage"><i class="fas fa-edit"></i><span>Gérer</span></button>
    <button class="mobile-nav-btn" data-view="view-list"><i class="fas fa-list-ul"></i><span>Liste</span></button>
</nav>

<div id="toast-container"></div>
<input type="file" id="uploadBackup" accept=".zip" style="display:none;">
<script src="script.js"></script>
</body>
</html>