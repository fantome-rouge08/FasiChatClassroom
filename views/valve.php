<?php
session_start();
require_once __DIR__ . '/../src/Autoloader.php';
require_once __DIR__ . '/../database/Database.php';
$dbInstance = new Database();
$db = $dbInstance->getConnection();

// Récupérer les annonces depuis la DB
$stmt = $db->query("SELECT a.*, u.nom, u.prenom, u.role FROM annonces a JOIN utilisateurs u ON a.auteur_id = u.id ORDER BY a.date_publication DESC");
$annonces = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalAnnonces = count($annonces);

// Récupérer l'utilisateur connecté
$currentUser = $_SESSION['user'] ?? null;
$estApparitaire = $currentUser && $currentUser['role'] === 'apparitaire';
$prenomNom = $currentUser ? htmlspecialchars($currentUser['prenom'] . ' ' . $currentUser['nom']) : 'Invité';
$roleLabel = $currentUser ? htmlspecialchars(ucfirst($currentUser['role'])) : 'Visiteur';

// Déterminer la route du dashboard selon le rôle
function dashboardRoute($role) {
    $map = ['etudiant' => 'dashboard_etudiant', 'enseignant' => 'dashboard_enseignant', 'assistant' => 'dashboard_enseignant', 'doyen' => 'dashboard_admin', 'vice-doyen' => 'dashboard_vicedoyen', 'apparitaire' => 'dashboard_apparitaire'];
    return $map[$role] ?? 'login';
}
$dashRoute = $currentUser ? '/FasiChatClassroom/public/' . dashboardRoute($currentUser['role']) : '/FasiChatClassroom/public/login';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FasiChat — Valve Faculté</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/FasiChatClassroom/public/assets/css/valve.css">
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
  <div class="sidebar-header">
    <div class="brand-mark">💬</div>
    <div class="brand-info"><h3>FasiChat</h3><span>Valve — Tableau d'affichage</span></div>
  </div>
  <div class="nav-tabs">
    <button class="nav-tab" onclick="location.href='<?= $dashRoute ?>'">💬 Dashboard</button>
    <button class="nav-tab active">📣 Valve</button>
  </div>
  <div class="sidebar-cats">
    <div class="section-label">Catégories</div>
    <div class="cat-item active">
      <div class="cat-icon" style="background:rgba(79,163,224,0.15);">📋</div>
      <div class="cat-info"><div class="cat-name">Toutes les annonces</div><div class="cat-count"><?= $totalAnnonces ?> publication<?= $totalAnnonces > 1 ? 's' : '' ?></div></div>
    </div>
    <div class="section-label" style="margin-top:10px;">Navigation rapide</div>
    <div class="cat-item" onclick="location.href='<?= $dashRoute ?>'">
      <div class="cat-icon" style="background:rgba(79,163,224,0.1);">🎓</div>
      <div class="cat-info"><div class="cat-name">Mon espace</div><div class="cat-count">Retour au dashboard</div></div>
    </div>
  </div>
  <div class="sidebar-profile">
    <div class="profile-avatar" style="background:linear-gradient(135deg,#6366f1,#4f46e5);">
      <div class="online-dot"></div>🗂
    </div>
    <div class="profile-info">
      <h4><?= $prenomNom ?></h4>
      <span style="color:#a5b4fc;font-size:10px;"><?= $roleLabel ?> · Faculté</span>
    </div>
    <a href="/FasiChatClassroom/public/login" class="icon-btn">🚪</a>
  </div>
</div>

<!-- MAIN AREA -->
<div class="main-area">
  <!-- Topbar -->
  <div class="valve-topbar">
    <div class="valve-topbar-icon">📣</div>
    <div class="valve-topbar-info">
      <h3>Valve — Faculté des Sciences Informatiques</h3>
      <p>Tableau d'affichage officiel · Géré par l'Apparitaire</p>
    </div>
    <div class="valve-topbar-actions">
      <button class="vt-btn ghost">📊 Statistiques</button>
      <?php if ($estApparitaire): ?>
      <button class="vt-btn primary" onclick="openModal()">+ Nouvelle annonce</button>
      <?php endif; ?>
    </div>
  </div>

  <!-- Filter bar -->
  <div class="filter-bar">
    <button class="filter-chip active">Toutes</button>
    <button class="filter-chip">🚨 Urgences</button>
    <button class="filter-chip">📅 Convocations</button>
    <button class="filter-chip">📢 Infos</button>
    <button class="filter-chip">🎓 Académique</button>
    <div class="filter-spacer"></div>
    <div class="search-valve">
      <span class="s-ico">🔍</span>
      <input type="text" placeholder="Rechercher une annonce...">
    </div>
  </div>

  <!-- Content -->
  <div class="valve-content">
    <!-- Hero -->
    <div class="valve-hero">
      <div>
        <div class="hero-badge">TABLEAU D'AFFICHAGE OFFICIEL</div>
        <h2>Bienvenue sur le Valve 📣</h2>
        <p>Toutes les annonces officielles de la Faculté des Sciences Informatiques. Consultez régulièrement cet espace pour rester informé des actualités, convocations et événements importants.</p>
        <div class="hero-stats">
          <div class="hero-stat"><div class="n"><?= $totalAnnonces ?></div><div class="l">ANNONCES</div></div>
        </div>
      </div>
    </div>

    <!-- Annonces grid -->
    <div class="annonces-grid">

      <?php if (empty($annonces)): ?>
      <div class="annonce-card" style="grid-column:1/-1;text-align:center;padding:40px;color:#9ca3af;">
        <p>Aucune annonce pour le moment.</p>
      </div>
      <?php else: ?>
        <?php foreach ($annonces as $a): ?>
      <div class="annonce-card">
        <div class="ac-header">
          <div class="ac-cat-icon" style="background:rgba(99,102,241,0.12);">📢</div>
          <div class="ac-meta">
            <div class="ac-cat-label" style="color:#6366f1;">ANNONCE</div>
            <div class="ac-title"><?= htmlspecialchars($a['titre']) ?></div>
          </div>
        </div>
        <div class="ac-body">
          <div class="ac-text"><?= nl2br(htmlspecialchars($a['contenu'])) ?></div>
          <div class="ac-footer">
            <div class="ac-author">
              <div class="ac-author-ava" style="background:linear-gradient(135deg,#6366f1,#4f46e5);"><?= strtoupper(substr($a['prenom'],0,1).substr($a['nom'],0,1)) ?></div>
              <div><div class="ac-author-name"><?= htmlspecialchars($a['prenom'] . ' ' . $a['nom']) ?> · <?= htmlspecialchars(ucfirst($a['role'])) ?></div></div>
            </div>
            <div class="ac-date"><?= date('d M · H:i', strtotime($a['date_publication'])) ?></div>
          </div>
        </div>
      </div>
        <?php endforeach; ?>
      <?php endif; ?>

    </div>
  </div>
</div>

<?php if ($estApparitaire): ?>
<!-- COMPOSE MODAL -->
<div class="modal-overlay" id="modal" onclick="closeModalOutside(event)">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-icon">📣</div>
      <div>
        <h3>Nouvelle annonce</h3>
        <p>Publication sur le Valve — visible par tous les utilisateurs</p>
      </div>
      <button class="modal-close" onclick="closeModal()">✕</button>
    </div>
    <div class="modal-body">
      <div class="form-group">
        <label class="form-label">Titre de l'annonce *</label>
        <input type="text" class="form-input" id="vTitre" placeholder="Ex: Réunion du conseil pédagogique...">
      </div>
      <div class="form-group">
        <label class="form-label">Contenu de l'annonce *</label>
        <textarea class="form-textarea" id="vContenu" placeholder="Rédigez le contenu de votre annonce ici..."></textarea>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn-cancel" onclick="closeModal()">Annuler</button>
      <button class="btn-publish" onclick="publishAnnonce()">📣 Publier sur le Valve</button>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
function openModal() { document.getElementById('modal').classList.add('open'); }
function closeModal() { document.getElementById('modal').classList.remove('open'); }
function closeModalOutside(e) { if (e.target === document.getElementById('modal')) closeModal(); }
<?php if ($estApparitaire): ?>
function publishAnnonce() {
  const titre = document.getElementById('vTitre').value.trim();
  const contenu = document.getElementById('vContenu').value.trim();
  if (!titre || !contenu) { alert('Veuillez remplir le titre et le contenu.'); return; }
  const fd = new FormData();
  fd.append('titre', titre);
  fd.append('contenu', contenu);
  fetch('/FasiChatClassroom/public/valve-publish', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(d => {
      if (d.success) {
        alert('Annonce publiée avec succès !');
        closeModal();
        document.getElementById('vTitre').value = '';
        document.getElementById('vContenu').value = '';
        location.reload();
      } else { alert('Erreur : ' + (d.error || 'Inconnue')); }
    })
    .catch(() => alert('Erreur réseau'));
}
<?php endif; ?>
</script>
</body>
</html>
