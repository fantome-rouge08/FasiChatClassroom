<?php
// views/dashboard_etudiant.php
session_start();

// Protection de la session : seul un étudiant connecté peut voir cette page
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'etudiant') {
    header('Location: /FasiChatClassroom/public/login');
    exit();
}

$currentUser = $_SESSION['user'];

require_once __DIR__ . '/../src/Autoloader.php';
require_once __DIR__ . '/../database/Database.php';

$dbInstance = new Database();
$db = $dbInstance->getConnection();

// 1. Récupérer la promotion de l'étudiant (par défaut, on utilise la première s'il n'est pas lié)
$stmtPromo = $db->query("SELECT * FROM promotions ORDER BY id ASC LIMIT 1");
$promotion = $stmtPromo->fetch();
$promotionId = $promotion ? $promotion['id'] : 1;
$promotionNom = $promotion ? $promotion['nom'] : 'L2 FASI';

// 2. Récupérer les cours de cette promotion (pour le mur pédagogique)
$stmtCours = $db->prepare("SELECT * FROM cours WHERE promotion_id = :promo_id");
$stmtCours->execute(['promo_id' => $promotionId]);
$listeCours = $stmtCours->fetchAll();

// 3. Récupérer la liste des étudiants de la même promotion (pour les messages privés)
$stmtEtud = $db->prepare("SELECT id, nom, prenom FROM utilisateurs WHERE role = 'etudiant' AND id != :my_id");
$stmtEtud->execute(['my_id' => $currentUser['id']]);
$autresEtudiants = $stmtEtud->fetchAll();

// 4. Récupérer les enseignants (pour le chat public/privé)
$stmtEns = $db->query("SELECT id, nom, prenom, role FROM utilisateurs WHERE role IN ('enseignant', 'assistant')");
$enseignants = $stmtEns->fetchAll();

// 5. Récupérer les dernières annonces du Valve
$stmtAnn = $db->query("SELECT a.*, u.nom, u.prenom FROM annonces a JOIN utilisateurs u ON a.auteur_id = u.id ORDER BY a.date_publication DESC LIMIT 3");
$annoncesValve = $stmtAnn->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FasiChat — Espace Étudiant</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/FasiChatClassroom/public/assets/css/etudiant.css">
</head>
<body>

<!-- ===== SIDEBAR ===== -->
<div class="sidebar">
  <div class="sidebar-header">
    <div class="brand-mark">💬</div>
    <div class="brand-info">
      <h3>FasiChat</h3>
      <span>Classroom Edition</span>
    </div>
  </div>

  <div class="nav-tabs">
    <button class="nav-tab active" onclick="switchTab(this,'msgs')">💬 Messages</button>
    <button class="nav-tab" onclick="location.href='/FasiChatClassroom/public/valve'">📣 Valve</button>
  </div>

  <div class="sidebar-search">
    <div class="search-wrap">
      <span class="search-icon">🔍</span>
      <input type="text" class="search-input" placeholder="Rechercher...">
    </div>
  </div>

  <div class="conv-list" id="msgs-panel">
    
    <!-- CANAL PUBLIC DE PROMOTION -->
    <div class="section-label">Canal Public de Promotion</div>
    <div class="conv-item active" onclick="changerDiscussion(this, 'public', <?= $promotionId ?>, '<?= htmlspecialchars($promotionNom, ENT_QUOTES) ?>')">
      <div class="avatar avatar-group">👥</div>
      <div class="conv-info">
        <div class="conv-name"><?= htmlspecialchars($promotionNom) ?></div>
        <div class="conv-preview">Chat ouvert de la promotion</div>
      </div>
    </div>

    <!-- MURS PÉDAGOGIQUES DES COURS -->
    <div class="section-label">Murs Pédagogiques (Cours)</div>
    <?php foreach ($listeCours as $cours): ?>
    <div class="conv-item" onclick="changerDiscussion(this, 'mur', <?= $cours['id'] ?>, 'Mur - <?= htmlspecialchars($cours['nom'], ENT_QUOTES) ?>')">
      <div class="avatar avatar-teal">📚</div>
      <div class="conv-info">
        <div class="conv-name"><?= htmlspecialchars($cours['nom']) ?></div>
        <div class="conv-preview">Poser des questions sur le cours</div>
      </div>
    </div>
    <?php endforeach; ?>

    <!-- MESSAGES PRIVÉS AVEC ÉTUDIANTS -->
    <div class="section-label">Messages Privés (Camarades)</div>
    <?php foreach ($autresEtudiants as $etud): ?>
    <div class="conv-item" onclick="changerDiscussion(this, 'prive', <?= $etud['id'] ?>, '<?= htmlspecialchars($etud['prenom'] . ' ' . $etud['nom'], ENT_QUOTES) ?>')">
      <div class="avatar avatar-sky"><?= strtoupper(substr($etud['prenom'], 0, 1) . substr($etud['nom'], 0, 1)) ?></div>
      <div class="conv-info">
        <div class="conv-name"><?= htmlspecialchars($etud['prenom'] . ' ' . $etud['nom']) ?></div>
        <div class="conv-preview">Lancer un chat privé</div>
      </div>
    </div>
    <?php endforeach; ?>

    <!-- MESSAGES PRIVÉS AVEC ENSEIGNANTS -->
    <div class="section-label">Messages Publics (Enseignants)</div>
    <?php foreach ($enseignants as $ens): ?>
    <div class="conv-item" onclick="changerDiscussion(this, 'prive', <?= $ens['id'] ?>, '<?= htmlspecialchars($ens['prenom'] . ' ' . $ens['nom'], ENT_QUOTES) ?>')">
      <div class="avatar avatar-indigo"><?= strtoupper(substr($ens['prenom'], 0, 1) . substr($ens['nom'], 0, 1)) ?></div>
      <div class="conv-info">
        <div class="conv-name"><?= htmlspecialchars($ens['prenom'] . ' ' . $ens['nom']) ?> <span class="tag-public" style="margin-left:4px; font-size:9px;"><?= strtoupper($ens['role']) ?></span></div>
        <div class="conv-preview">Discuter avec l'enseignant</div>
      </div>
    </div>
    <?php endforeach; ?>

  </div>

  <div class="sidebar-profile">
    <div class="profile-avatar">
      <div class="online-dot"></div>
      🎓
    </div>
    <div class="profile-info">
      <h4><?= htmlspecialchars($currentUser['prenom'] . ' ' . $currentUser['nom']) ?></h4>
      <span>Étudiant · <?= htmlspecialchars($promotionNom) ?></span>
    </div>
    <div class="profile-actions">
      <a href="/FasiChatClassroom/public/login" class="icon-btn" title="Déconnexion">🚪</a>
    </div>
  </div>
</div>

<!-- ===== MAIN CHAT ===== -->
<div class="main-area">
  <div class="chat-topbar">
    <div class="chat-topbar-avatar">👥</div>
    <div class="chat-topbar-info">
      <h3 id="topbarTitle"><?= htmlspecialchars($promotionNom) ?></h3>
      <p>Messagerie active</p>
    </div>
    <div class="status-badge public" id="statusBadge">
      <div class="status-dot"></div>
      Message Public
    </div>
  </div>

  <div class="chat-messages" id="messages">
    <!-- Chargé dynamiquement via JS -->
  </div>

  <!-- Input area -->
  <div class="chat-input-area">
    <div class="input-toolbar">
      <button class="toolbar-btn" onclick="triggerUpload('pdf')">📊 PDF</button>
      <button class="toolbar-btn" onclick="triggerUpload('image')">🖼 Image</button>
      <button class="toolbar-btn" onclick="triggerUpload('video')">🎥 Vidéo</button>
      <button class="toolbar-btn" onclick="triggerUpload('doc')">📎 Document</button>
      <input type="file" id="fileInput" style="display:none" onchange="handleFileUpload(this)">
    </div>
    <div class="input-row">
      <div class="msg-textarea-wrap">
        <textarea class="msg-textarea" placeholder="Écrire un message... (Appuyez sur Entrée pour envoyer)" rows="1" id="msgInput" onkeydown="handleKey(event)"></textarea>
      </div>
      <button class="send-btn" onclick="sendMsg()">➤</button>
    </div>
  </div>
</div>

<!-- ===== RIGHT PANEL ===== -->
<div class="right-panel">
  <div class="panel-section">
    <div class="panel-title">Infos de votre promotion</div>
    <div class="info-card">
      <h4><?= htmlspecialchars($promotionNom) ?></h4>
      <p>Faculté des Sciences Informatiques</p>
      <div class="tag-row">
        <span class="tag tag-blue"><?= htmlspecialchars($promotionNom) ?></span>
        <span class="tag tag-navy">Promo 2026</span>
      </div>
    </div>
  </div>

  <div class="panel-section">
    <div class="panel-title">Enseignants Actifs</div>
    <?php foreach ($enseignants as $ens): ?>
    <div class="member-item">
      <div class="member-ava" style="background: linear-gradient(135deg,#3b82f6,#1d4ed8);"><?= strtoupper(substr($ens['prenom'], 0, 1) . substr($ens['nom'], 0, 1)) ?></div>
      <div class="member-info">
        <h5><?= htmlspecialchars($ens['prenom'] . ' ' . $ens['nom']) ?></h5>
        <p><?= htmlspecialchars(ucfirst($ens['role'])) ?></p>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="panel-section">
    <div class="panel-title">📣 Valve — Dernières annonces</div>
    <?php if (empty($annoncesValve)): ?>
      <div style="font-size:12px;color:#94a3a8;padding:8px;">Aucune annonce.</div>
    <?php else: ?>
      <?php foreach ($annoncesValve as $a): ?>
      <div style="padding:10px 0;border-bottom:1px solid #f1f5f9;">
        <div style="font-size:12px;font-weight:600;color:#334155;"><?= htmlspecialchars($a['titre']) ?></div>
        <div style="font-size:10px;color:#94a3b8;margin-top:2px;"><?= htmlspecialchars($a['prenom'] . ' ' . $a['nom']) ?> · <?= date('d/m H:i', strtotime($a['date_publication'])) ?></div>
      </div>
      <?php endforeach; ?>
      <a href="/FasiChatClassroom/public/valve" style="display:block;text-align:center;margin-top:8px;font-size:11px;font-weight:600;color:#6366f1;text-decoration:none;">Voir tout →</a>
    <?php endif; ?>
  </div>
</div>

<script src="/FasiChatClassroom/public/assets/js/etudiant.js"></script>
<script>
    // Initialise la messagerie dynamique pour l'étudiant connecté
    initChat(<?= $currentUser['id'] ?>, <?= $promotionId ?>);
</script>
</body>
</html>
