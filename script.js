// --- FONCTION UTILITAIRE POUR LES BOUTONS "EFFACER" ---
function setupClearButton(inputId, buttonId) {
    const input = document.getElementById(inputId);
    const button = document.getElementById(buttonId);
    if (!input || !button) return;

    const toggleButton = () => {
        button.style.display = input.value.trim() !== '' ? 'inline-flex' : 'none';
    };

    input.addEventListener('input', toggleButton);
    button.addEventListener('click', () => {
        input.value = '';
        toggleButton();
        input.focus();
    });
    toggleButton();
}

// --- NOUVELLE FONCTION POUR METTRE À JOUR LE LOGO ---
function updateLogo() {
    const logo = document.getElementById('logo');
    if (!logo) return; // Ne fait rien si le logo n'est pas sur la page

    const isMobile = /Mobi|Android|iPhone/i.test(navigator.userAgent);
    const isDarkMode = document.documentElement.classList.contains('dark-mode');

    let logoSrc = '';

    if (isMobile) {
        logoSrc = isDarkMode ? 'images/logo_mobile_dark.png' : 'images/logo_mobile.png';
    } else {
        logoSrc = isDarkMode ? 'images/logo_pc_dark.png' : 'images/logo_pc.png';
    }

    logo.src = logoSrc;
}


// --- SETUP DES ÉVÉNEMENTS ---
document.addEventListener('DOMContentLoaded', function() {
  
  // --- LOGIQUE POUR LE DARK MODE ---
  const themeToggle = document.getElementById('theme-toggle');
  if (themeToggle) {
    if (document.documentElement.classList.contains('dark-mode')) {
      themeToggle.checked = true;
    }

    themeToggle.addEventListener('change', function() {
      if (this.checked) {
        document.documentElement.classList.add('dark-mode');
        localStorage.setItem('theme', 'dark');
      } else {
        document.documentElement.classList.remove('dark-mode');
        localStorage.setItem('theme', 'light');
      }
      // Met à jour le logo à chaque changement de thème
      updateLogo();
    });
  }

  loadInitial();

  // Met à jour le logo au chargement initial de la page
  updateLogo();

  // Initialisation de tous les boutons "effacer"
  setupClearButton('nom', 'effacer_nom');
  setupClearButton('champ_loc', 'effacer_champ_loc');
  setupClearButton('champ_sousloc', 'effacer_champ_sousloc');
  setupClearButton('search_nom', 'effacer_search_nom');
  setupClearButton('search_loc', 'effacer_search_loc');
  setupClearButton('search_sousloc', 'effacer_search_sousloc');

  // --- Le reste du script est identique ---
  document.getElementById('logout-btn').addEventListener('click', () => window.location.href = 'logout.php');
  
  const addLocBtn = document.getElementById('ajouter_loc');
  if (addLocBtn) addLocBtn.addEventListener('click', addLoc);

  const addSousLocBtn = document.getElementById('ajouter_sousloc');
  if(addSousLocBtn) addSousLocBtn.addEventListener('click', addSousloc);

  const delLocBtn = document.getElementById('supprimer_loc');
  if(delLocBtn) delLocBtn.addEventListener('click', deleteLoc);

  const delSousLocBtn = document.getElementById('supprimer_sousloc');
  if(delSousLocBtn) delSousLocBtn.addEventListener('click', deleteSousloc);
  
  const updateBtn = document.getElementById('Update');
  if(updateBtn) updateBtn.addEventListener('click', addOrUpdateGame);
  
  const supprBtn = document.getElementById('suppr');
  if(supprBtn) supprBtn.addEventListener('click', deleteGame);

  const chercheBtn = document.getElementById('cherche');
  if(chercheBtn) chercheBtn.addEventListener('click', recherche);

  const randomBtn = document.getElementById('Random');
  if(randomBtn) randomBtn.addEventListener('click', randomGame);
  
  function setupMobileNav() {
    const navButtons = document.querySelectorAll('.mobile-nav-btn');
    if (navButtons.length === 0) return;

    const views = document.querySelectorAll('.view');
    navButtons[0].classList.add('active');
    views[0].classList.add('active');
    navButtons.forEach(button => {
        button.addEventListener('click', () => {
            const targetViewId = button.dataset.view;
            navButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            views.forEach(view => view.classList.remove('active'));
            document.getElementById(targetViewId)?.classList.add('active');
        });
    });
  }
  setupMobileNav();

  const fileInput = document.getElementById('uploadBackup');
  if (fileInput) {
    function handleBackupDownload() { window.location.href = 'backup.php?action=download'; }
    function handleBackupUpload() { fileInput.click(); }

    const downloadBtnPC = document.getElementById('downloadBackup');
    if(downloadBtnPC) downloadBtnPC.addEventListener('click', handleBackupDownload);
    
    const uploadBtnPC = document.getElementById('uploadBtn');
    if(uploadBtnPC) uploadBtnPC.addEventListener('click', handleBackupUpload);
    
    const downloadBtnMobile = document.getElementById('downloadBackupMobile');
    if(downloadBtnMobile) downloadBtnMobile.addEventListener('click', handleBackupDownload);

    const uploadBtnMobile = document.getElementById('uploadBtnMobile');
    if(uploadBtnMobile) uploadBtnMobile.addEventListener('click', handleBackupUpload);
    
    fileInput.addEventListener('change', async (e) => {
      const file = e.target.files[0];
      if (!file) return;
      if (!file.name.endsWith('.zip')) { showMessage('Veuillez sélectionner un fichier ZIP.', 'err'); return; }
      if (!confirm("⚠️ La restauration écrasera toutes vos données actuelles. Continuer ?")) { return; }
      const chars='ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';let code='';for(let i=0;i<5;i++)code+=chars.charAt(Math.floor(Math.random()*chars.length));const userInput=prompt(`Pour confirmer, tapez ce code : ${code}`);if(userInput!==code){showMessage('Code incorrect. Restauration annulée.','err');fileInput.value='';return}
      const formData = new FormData();
      formData.append('file', file);
      try {
          const resp = await fetch('backup.php?action=upload', { method: 'POST', body: formData, credentials: 'same-origin' });
          const data = await resp.json();
          showMessage(data.message, data.status);
          if (data.status === 'ok') setTimeout(() => location.reload(), 1500);
      } catch (err) {
          showMessage('⚠️ Erreur lors de l\'envoi : ' + err.message, 'err');
      } finally {
          fileInput.value = '';
      }
    });
  }

  var locs = [], souslocs = [], jeux = [];
  function escapeHtml(s) { if (!s) return ''; return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');}
  function showMessage(message, type = 'ok') { const container = document.getElementById('toast-container'); const toast = document.createElement('div'); toast.className = `toast ${type}`; const cleanMessage = message.replace(/<[^>]*>/g, ''); toast.textContent = cleanMessage; container.appendChild(toast); setTimeout(() => { toast.style.opacity = '0'; toast.style.transform = 'translateX(100%)'; setTimeout(() => toast.remove(), 500); }, 4000); }
  async function callApi(action, payload = {}) { const body = JSON.stringify({ action, ...payload }); try { const resp = await fetch('api.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: body, credentials: 'same-origin' }); if (!resp.ok) { throw new Error(`Erreur HTTP: ${resp.status}`); } const text = await resp.text(); if (!text.trim()) { showMessage('Réponse vide du serveur.', 'err'); throw new Error('Empty response'); } if (text.trim().startsWith('<')) { console.error("Réponse inattendue du serveur:", text); showMessage('Erreur serveur inattendue.', 'err'); throw new Error('HTML response'); } return JSON.parse(text); } catch (err) { console.error('Erreur API:', err); showMessage("Une erreur de communication est survenue.", 'err'); throw err; } }
  function makeOption(value) { const opt = document.createElement('option'); opt.value = value; opt.textContent = value === "" ? "(Aucune)" : value; return opt; }
  function populateSelects(data) { const selLoc = document.getElementById('menu_loc'); const selSousloc = document.getElementById('menu_sousloc'); if(!selLoc || !selSousloc) return; selLoc.innerHTML = ''; selSousloc.innerHTML = ''; selLoc.appendChild(makeOption('')); selSousloc.appendChild(makeOption('')); locs = Array.isArray(data.loc) ? [...data.loc] : []; souslocs = Array.isArray(data.sous_loc) ? [...data.sous_loc] : []; locs.forEach(v => { if (v) selLoc.appendChild(makeOption(v)); }); souslocs.forEach(v => { if (v) selSousloc.appendChild(makeOption(v)); }); }
  function refreshListe(jeuxAffiches = jeux) { const liste = document.getElementById('liste'); if (!liste) return; if (!jeuxAffiches.length) { liste.innerHTML = '<p style="text-align:center; padding: 1rem;">(aucun jeu à afficher)</p>'; return; } liste.innerHTML = jeuxAffiches.map(j => ` <div class="game-card"> <div> <div class="game-name">${escapeHtml(j.nom)}</div> <div class="game-location">${escapeHtml(j.loc)} &rarr; ${escapeHtml(j.sousloc)}</div> </div> </div> `).join(''); }
  async function loadInitial() { try { const data = await callApi('read_all'); if (data.status === 'ok') { jeux = Array.isArray(data.jeux) ? data.jeux : []; populateSelects(data); refreshListe(); populateSearchLists(); } else { showMessage('Erreur de chargement des données.', 'err'); } } catch (err) { } }
  async function addLoc() { const input = document.getElementById('champ_loc'); const val = input.value.trim(); if (!val) return showMessage('Le nom de la localisation ne peut pas être vide.', 'err'); if (locs.some(x => x.toLowerCase() === val.toLowerCase())) return showMessage('Cette localisation existe déjà.', 'err'); const data = await callApi('add_loc', { val }); showMessage(data.html, data.status); if(data.status === 'ok') { input.value = ''; await loadInitial(); } }
  async function addSousloc() { const input = document.getElementById('champ_sousloc'); const val = input.value.trim(); if (!val) return showMessage('Le nom de la sous-localisation ne peut pas être vide.', 'err'); if (souslocs.some(x => x.toLowerCase() === val.toLowerCase())) return showMessage('Cette sous-localisation existe déjà.', 'err'); const data = await callApi('add_sousloc', { val }); showMessage(data.html, data.status); if (data.status === 'ok') { input.value = ''; await loadInitial(); } }
  async function deleteLoc() { const val = document.getElementById('menu_loc').value; if (!val) return showMessage('Veuillez choisir une localisation à supprimer.', 'err'); if (!confirm(`Voulez-vous vraiment supprimer la localisation "${val}" ?`)) return; const data = await callApi('delete_loc', { val }); showMessage(data.html, data.status); if (data.status === 'ok') await loadInitial(); }
  async function deleteSousloc() { const val = document.getElementById('menu_sousloc').value; if (!val) return showMessage('Veuillez choisir une sous-localisation à supprimer.', 'err'); if (!confirm(`Voulez-vous vraiment supprimer la sous-localisation "${val}" ?`)) return; const data = await callApi('delete_sousloc', { val }); showMessage(data.html, data.status); if (data.status === 'ok') await loadInitial(); }
  async function addOrUpdateGame() { const nom = document.getElementById('nom').value.trim(); const locSel = document.getElementById('menu_loc').value; const sousSel = document.getElementById('menu_sousloc').value; if (!nom) return showMessage('Le nom du jeu est requis.', 'err'); const res = await callApi('add_or_update', { nom, loc: locSel, sousloc: sousSel }); showMessage(res.html, res.status); if (res.status === 'ok') { document.getElementById('nom').value = ''; await loadInitial(); document.getElementById('menu_loc').value = locSel; document.getElementById('menu_sousloc').value = sousSel; } }
  async function deleteGame() { const nom = document.getElementById('nom').value.trim(); if (!nom) return showMessage('Veuillez entrer le nom du jeu à supprimer.', 'err'); if (!confirm(`Supprimer le jeu "${nom}" ?`)) return; const res = await callApi('delete_game', { nom }); showMessage(res.html, res.status); if (res.status === 'ok') { document.getElementById('nom').value = ''; await loadInitial(); } }
  function populateSearchLists() { const uniqueNoms = [...new Set(jeux.map(j => j.nom))]; const uniqueLocs = [...new Set(locs.filter(l => l))]; const uniqueSousLocs = [...new Set(souslocs.filter(s => s))]; if($('#nom').length) $("#nom").autocomplete({ source: uniqueNoms }); if($('#search_nom').length) $("#search_nom").autocomplete({ source: uniqueNoms }); if($('#search_loc').length) $("#search_loc").autocomplete({ source: uniqueLocs }); if($('#search_sousloc').length) $("#search_sousloc").autocomplete({ source: uniqueSousLocs }); }
  function recherche() { const nomQuery = document.getElementById('search_nom').value.trim().toLowerCase(); const locQuery = document.getElementById('search_loc').value.trim().toLowerCase(); const sousQuery = document.getElementById('search_sousloc').value.trim().toLowerCase(); const searchCount = document.getElementById('search-count'); const filtres = jeux.filter(j =>  (!nomQuery || j.nom.toLowerCase().includes(nomQuery)) && (!locQuery || j.loc.toLowerCase().includes(locQuery)) && (!sousQuery || j.sousloc.toLowerCase().includes(sousQuery)) ); if(searchCount) searchCount.textContent = `${filtres.length} résultat${filtres.length > 1 ? 's' : ''} trouvé${filtres.length > 1 ? 's' : ''}`; refreshListe(filtres); }
  async function randomGame() { const res = await callApi('random'); if (res.status === 'ok') { const cleanHtml = res.html.replace(/<[^>]*>/g, ''); alert('🎲 Et si vous jouiez à : ' + cleanHtml); } else { showMessage(res.html, res.status); } }
});