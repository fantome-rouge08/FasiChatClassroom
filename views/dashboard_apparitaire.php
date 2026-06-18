<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'apparitaire') {
    header('Location: /FasiChatClassroom/public/login');
    exit();
}
$currentUser = $_SESSION['user'];
require_once __DIR__ . '/../src/Autoloader.php';
require_once __DIR__ . '/../database/Database.php';
$dbInstance = new Database();
$db = $dbInstance->getConnection();

// Récupérer les annonces depuis la DB
$stmt = $db->query("SELECT a.*, u.nom, u.prenom, u.role FROM annonces a JOIN utilisateurs u ON a.auteur_id = u.id ORDER BY a.date_publication DESC");
$annonces = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalAnnonces = count($annonces);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FasiChat — Apparitaire</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/FasiChatClassroom/public/assets/css/dashboard_apparitaire.css">
</head>
<body>
<div class="sidebar">
  <div class="sidebar-header">
    <div class="brand-mark">🗂</div>
    <div class="brand-info"><h3>FasiChat</h3><span>Espace Apparitaire</span></div>
  </div>
  <div class="role-badge"><div class="rdot"></div><span>APPARITAIRE — Gestion du Valve</span></div>
  <div class="nav-section">
    <div class="nav-label-section">Gestion Valve</div>
    <div class="nav-item active" onclick="setNav(this)"><div class="nav-icon" style="background:rgba(99,102,241,0.12);">📊</div><div><div class="nl">Tableau de bord</div><div class="ns">Vue d'ensemble</div></div></div>
    <div class="nav-item" onclick="openModal()"><div class="nav-icon" style="background:rgba(34,197,94,0.12);">➕</div><div><div class="nl">Nouvelle annonce</div><div class="ns">Publier sur le Valve</div></div></div>
    <div class="nav-item" onclick="setNav(this)"><div class="nav-icon" style="background:rgba(245,158,11,0.12);">📋</div><div><div class="nl">Gérer les annonces</div><div class="ns">Modifier / Supprimer</div></div><div class="nb">6</div></div>
    <div class="nav-item" onclick="setNav(this)"><div class="nav-icon" style="background:rgba(239,68,68,0.12);">🚨</div><div><div class="nl">Urgences</div><div class="ns">Priorité haute</div></div></div>
  </div>
  <div class="nav-section">
    <div class="nav-label-section">Navigation</div>
    <div class="nav-item" onclick="location.href='/FasiChatClassroom/public/valve'"><div class="nav-icon" style="background:rgba(99,102,241,0.08);">📣</div><div><div class="nl">Voir le Valve public</div><div class="ns">Vue utilisateur</div></div></div>
    <div class="nav-item" onclick="location.href='/FasiChatClassroom/public/dashboard_admin'"><div class="nav-icon" style="background:rgba(220,38,38,0.08);">🏛</div><div><div class="nl">Espace Doyen</div><div class="ns">Administration</div></div></div>
    <div class="nav-item" onclick="location.href='/FasiChatClassroom/public/dashboard_etudiant'"><div class="nav-icon" style="background:rgba(79,163,224,0.08);">🎓</div><div><div class="nl">Vue Étudiant</div><div class="ns">Dashboard étudiant</div></div></div>
  </div>
  <div class="sidebar-bottom">
    <div class="profile-ava"><div class="online-dot"></div>🗂</div>
    <div class="pi"><h4><?= htmlspecialchars($currentUser['prenom'] . ' ' . $currentUser['nom']) ?></h4><span>Apparitaire · Faculté</span></div>
    <a href="/FasiChatClassroom/public/login" class="logout-btn">🚪</a>
  </div>
</div>

<div class="main-area">
  <div class="topbar">
    <div>
      <div class="topbar-title">Gestion du Valve — Apparitaire</div>
      <div class="topbar-sub">Faculté des Sciences Informatiques · Tableau d'affichage officiel</div>
    </div>
    <div class="topbar-right">
      <button class="tb-btn ghost" onclick="location.href='/FasiChatClassroom/public/valve'">👁 Voir le Valve</button>
      <button class="tb-btn primary" onclick="openModal()">➕ Nouvelle annonce</button>
    </div>
  </div>

  <div class="content">
    <div class="stats-row">
      <div class="stat-card sc-indigo"><div class="si">📣</div><div class="sn"><?= $totalAnnonces ?></div><div class="sl">Annonces actives</div><div class="st">Sur le Valve public</div></div>
    </div>

    <div class="main-grid">
      <!-- LEFT: VALVE MANAGER -->
      <div style="display:flex;flex-direction:column;gap:16px;">
        <!-- COMPOSE -->
        <div class="card">
          <div class="card-header">
            <div class="card-title">✍️ Rédiger une annonce</div>
            <span style="font-size:10px;font-weight:700;background:rgba(99,102,241,0.1);color:var(--indigo);border:1px solid rgba(99,102,241,0.2);padding:3px 10px;border-radius:20px;letter-spacing:0.5px;">APPARITAIRE UNIQUEMENT</span>
          </div>
          <div class="compose-area">
            <div class="form-group">
              <label class="form-label">Titre de l'annonce *</label>
              <input type="text" class="form-input" id="compTitle" placeholder="Ex: Modification calendrier...">
            </div>
            <div class="form-group">
              <label class="form-label">Contenu *</label>
              <textarea class="form-textarea" id="compContent" placeholder="Rédigez le contenu de l'annonce..."></textarea>
            </div>
            <button class="pub-btn" onclick="publishAnnonce()">📣 Publier sur le Valve</button>
          </div>
        </div>

        <!-- ANNONCES LIST -->
        <div class="card">
          <div class="card-header">
            <div class="card-title">📋 Annonces publiées (<?= $totalAnnonces ?>)</div>
            <button class="card-action" onclick="location.href='/FasiChatClassroom/public/valve'">Voir le Valve public →</button>
          </div>
          <div class="annonces-list" id="annoncesList">
            <?php if (empty($annonces)): ?>
            <div style="padding:20px;text-align:center;color:#9ca3af;">Aucune annonce publiée.</div>
            <?php else: ?>
              <?php foreach ($annonces as $a): ?>
            <div class="annonce-item" data-id="<?= $a['id'] ?>">
              <div class="ai-cat" style="background:rgba(99,102,241,0.1);">📢</div>
              <div class="ai-body">
                <div class="ai-title"><?= htmlspecialchars($a['titre']) ?></div>
                <div class="ai-preview"><?= htmlspecialchars(substr($a['contenu'], 0, 100)) ?>...</div>
                <div class="ai-meta">
                  <span><?= date('d M · H:i', strtotime($a['date_publication'])) ?></span>
                  <span>Par <?= htmlspecialchars($a['prenom'] . ' ' . $a['nom']) ?></span>
                </div>
              </div>
              <div class="ai-actions">
                <button class="ai-btn edit" onclick="openEditModal(<?= $a['id'] ?>, '<?= htmlspecialchars($a['titre'], ENT_QUOTES) ?>', '<?= htmlspecialchars($a['contenu'], ENT_QUOTES) ?>')">✏</button>
                <button class="ai-btn del" onclick="deleteAnnonce(<?= $a['id'] ?>, this)">🗑</button>
              </div>
            </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- RIGHT COL -->
      <div class="right-col">
        <div class="stat-small">
          <div class="stat-small-title">📊 Statistiques Valve</div>
          <div style="margin-top:16px;padding-top:14px;">
            <div style="font-size:11px;font-weight:700;color:var(--navy);margin-bottom:8px;">Total annonces</div>
            <div style="font-size:28px;font-weight:700;color:var(--indigo);"><?= $totalAnnonces ?> <span style="font-size:13px;color:var(--gray-400);font-weight:400;">publications</span></div>
          </div>
        </div>

        <div class="recent-card">
          <div class="card-header"><div class="card-title">🕐 Dernières actions</div></div>
          <div>
            <div class="activity-item">
              <div class="act-ico" style="background:rgba(239,68,68,0.1);">🚨</div>
              <div class="act-t"><strong>Urgence publiée</strong><p>Calendrier examens S5 modifié</p></div>
              <div class="act-time">07:30</div>
            </div>
            <div class="activity-item">
              <div class="act-ico" style="background:rgba(99,102,241,0.1);">✏</div>
              <div class="act-t"><strong>Annonce modifiée</strong><p>Dépôt des projets</p></div>
              <div class="act-time">Hier 14:10</div>
            </div>
            <div class="activity-item">
              <div class="act-ico" style="background:rgba(245,158,11,0.1);">📅</div>
              <div class="act-t"><strong>Convocation publiée</strong><p>Conseil pédagogique Doyen</p></div>
              <div class="act-time">Hier 16:05</div>
            </div>
            <div class="activity-item">
              <div class="act-ico" style="background:rgba(239,68,68,0.1);">🗑</div>
              <div class="act-t"><strong>Annonce supprimée</strong><p>Fermeture bibliothèque expirée</p></div>
              <div class="act-time">18 Jan</div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header"><div class="card-title">⚡ Actions rapides</div></div>
          <div style="padding:14px 16px;display:flex;flex-direction:column;gap:8px;">
            <button onclick="openModal()" style="width:100%;padding:10px 14px;background:linear-gradient(135deg,var(--indigo),#4f46e5);color:white;border:none;border-radius:10px;font-family:'Sora',sans-serif;font-size:12px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:8px;transition:all 0.2s;">➕ Nouvelle annonce</button>
            <button onclick="location.href='/FasiChatClassroom/public/valve'" style="width:100%;padding:10px 14px;background:var(--gray-100);color:var(--navy);border:none;border-radius:10px;font-family:'Sora',sans-serif;font-size:12px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:8px;">👁 Prévisualiser le Valve</button>
            <button style="width:100%;padding:10px 14px;background:rgba(239,68,68,0.08);color:var(--danger);border:1px solid rgba(239,68,68,0.2);border-radius:10px;font-family:'Sora',sans-serif;font-size:12px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:8px;" onclick="alert('Annonce urgente en cours de création...');openModal();">🚨 Publier une urgence</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- MODAL NOUVELLE ANNONCE -->
<div class="modal-overlay" id="modal" onclick="closeOut(event)">
  <div class="modal">
    <div class="modal-hdr">
      <span style="font-size:26px;">📣</span>
      <div><h3>Nouvelle annonce Valve</h3><p>Visible par tous les utilisateurs de FasiChat</p></div>
      <button class="modal-close" onclick="closeModal()">✕</button>
    </div>
    <div class="modal-body">
      <div class="form-group"><label class="form-label">Titre *</label><input type="text" class="form-input" id="mTitle" placeholder="Titre de l'annonce..."></div>
      <div class="form-group"><label class="form-label">Contenu *</label><textarea class="form-textarea" id="mContent" placeholder="Contenu de l'annonce..."></textarea></div>
    </div>
    <div class="modal-footer">
      <button class="btn-cancel" onclick="closeModal()">Annuler</button>
      <button class="btn-ok" onclick="publishFromModal()">📣 Publier sur le Valve</button>
    </div>
  </div>
</div>

<!-- MODAL EDIT -->
<div class="modal-overlay" id="editModal" onclick="closeEditOut(event)">
  <div class="modal">
    <div class="modal-hdr" style="background:linear-gradient(135deg,#0ea5e9,#0284c7);">
      <span style="font-size:26px;">✏</span>
      <div><h3>Modifier l'annonce</h3><p>Mettre à jour le contenu publié sur le Valve</p></div>
      <button class="modal-close" onclick="closeEditModal()">✕</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="eId" value="">
      <div class="form-group"><label class="form-label">Titre *</label><input type="text" class="form-input" id="eTitle"></div>
      <div class="form-group"><label class="form-label">Contenu *</label><textarea class="form-textarea" id="eContent" style="min-height:100px;"></textarea></div>
    </div>
    <div class="modal-footer">
      <button class="btn-cancel" onclick="closeEditModal()">Annuler</button>
      <button class="btn-ok" onclick="saveEdit()">💾 Enregistrer les modifications</button>
    </div>
  </div>
</div>

<script>
function setNav(el){document.querySelectorAll('.nav-item').forEach(n=>n.classList.remove('active'));el.classList.add('active');}

function openModal(){document.getElementById('modal').classList.add('open');}
function closeModal(){document.getElementById('modal').classList.remove('open');}
function closeOut(e){if(e.target.id==='modal')closeModal();}

function openEditModal(id, titre, contenu){
  document.getElementById('eId').value = id;
  document.getElementById('eTitle').value = titre;
  document.getElementById('eContent').value = contenu;
  document.getElementById('editModal').classList.add('open');
}
function closeEditModal(){document.getElementById('editModal').classList.remove('open');}
function closeEditOut(e){if(e.target.id==='editModal')closeEditModal();}

function publishAnnonce(){
  const t = document.getElementById('compTitle').value.trim();
  const c = document.getElementById('compContent').value.trim();
  if (!t || !c) { alert('Veuillez remplir le titre et le contenu.'); return; }
  const fd = new FormData();
  fd.append('titre', t);
  fd.append('contenu', c);
  fetch('/FasiChatClassroom/public/valve-publish', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(d => {
      if (d.success) { alert('Annonce publiée !'); location.reload(); }
      else { alert('Erreur : ' + (d.error || 'Inconnue')); }
    })
    .catch(() => alert('Erreur réseau'));
}

function publishFromModal(){
  const t = document.getElementById('mTitle').value.trim();
  const c = document.getElementById('mContent').value.trim();
  if (!t || !c) { alert('Veuillez remplir le titre et le contenu.'); return; }
  const fd = new FormData();
  fd.append('titre', t);
  fd.append('contenu', c);
  fetch('/FasiChatClassroom/public/valve-publish', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(d => {
      if (d.success) {
        alert('Annonce publiée !');
        closeModal();
        document.getElementById('mTitle').value = '';
        document.getElementById('mContent').value = '';
        location.reload();
      } else { alert('Erreur : ' + (d.error || 'Inconnue')); }
    })
    .catch(() => alert('Erreur réseau'));
}

function saveEdit(){
  const id = document.getElementById('eId').value;
  const t = document.getElementById('eTitle').value.trim();
  const c = document.getElementById('eContent').value.trim();
  if (!t || !c) { alert('Veuillez remplir le titre et le contenu.'); return; }
  const fd = new FormData();
  fd.append('id', id);
  fd.append('titre', t);
  fd.append('contenu', c);
  fetch('/FasiChatClassroom/public/valve-edit', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(d => {
      if (d.success) { alert('Annonce modifiée !'); closeEditModal(); location.reload(); }
      else { alert('Erreur : ' + (d.error || 'Inconnue')); }
    })
    .catch(() => alert('Erreur réseau'));
}

function deleteAnnonce(id, btn){
  if (!confirm('Supprimer cette annonce du Valve ?')) return;
  const fd = new FormData();
  fd.append('id', id);
  fetch('/FasiChatClassroom/public/valve-delete', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(d => {
      if (d.success) {
        btn.closest('.annonce-item').style.animation = 'fadeOut 0.3s ease forwards';
        setTimeout(() => btn.closest('.annonce-item').remove(), 300);
      } else { alert('Erreur : ' + (d.error || 'Inconnue')); }
    })
    .catch(() => alert('Erreur réseau'));
}

const style=document.createElement('style');
style.textContent='@keyframes fadeOut{to{opacity:0;transform:translateX(20px);}}';
document.head.appendChild(style);
</script>
</body>
</html>
