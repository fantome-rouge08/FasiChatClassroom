<?php
// views/dashboard_enseignant.php
session_start();

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['enseignant', 'assistant'])) {
    header('Location: /FasiChatClassroom/public/login');
    exit();
}

$currentUser = $_SESSION['user'];

require_once __DIR__ . '/../src/Autoloader.php';
require_once __DIR__ . '/../database/Database.php';

$dbInstance = new Database();
$db = $dbInstance->getConnection();

// Récupérer les étudiants
$stmtEtud = $db->query("SELECT * FROM utilisateurs WHERE role = 'etudiant'");
$etudiants = $stmtEtud->fetchAll();

// Récupérer les collègues enseignants/assistants
$stmtColl = $db->prepare("SELECT * FROM utilisateurs WHERE role IN ('enseignant', 'assistant') AND id != :my_id");
$stmtColl->execute(['my_id' => $currentUser['id']]);
$collegues = $stmtColl->fetchAll();

// Récupérer les convocations reçues
$convocModel = new Convocation($db);
$convocations = $convocModel->recupererToutes();

// Récupérer les dernières annonces du Valve
$stmtAnn = $db->query("SELECT a.*, u.nom, u.prenom FROM annonces a JOIN utilisateurs u ON a.auteur_id = u.id ORDER BY a.date_publication DESC LIMIT 3");
$annoncesValve = $stmtAnn->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
<title>FasiChat — Dashboard Enseignant</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/FasiChatClassroom/public/assets/css/dashboard_enseignant.css">
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
  <div class="sidebar-header">
    <div class="brand-mark">💬</div>
    <div class="brand-info"><h3>FasiChat</h3><span>Espace Enseignant</span></div>
  </div>
  <div class="nav-tabs">
    <button class="nav-tab active" onclick="showView('students',this)">👥 Étudiants</button>
    <button class="nav-tab" onclick="showView('mur',this)">📋 Mur</button>
    <button class="nav-tab" onclick="showView('msgs',this)">💬 Messages</button>
    <button class="nav-tab" onclick="location.href='/FasiChatClassroom/public/valve'">📣 Valve</button>
  </div>
  <div class="sidebar-search">
    <div class="search-wrap">
      <span class="search-icon">🔍</span>
      <input type="text" class="search-input" placeholder="Rechercher...">
    </div>
  </div>
  <div class="conv-list">
    <div class="section-label">Mes Cours</div>
    <div class="conv-item active" onclick="selectConv(this,'Programation Web — L2 FASI','🖥','linear-gradient(135deg,#3b82f6,#1d4ed8)','450 étudiants','public', 1)">
      <div class="avatar" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);">🖥</div>
      <div class="conv-info"><div class="conv-name">Programation Web — L2 FASI</div><div class="conv-preview">Cours Public</div></div>
      <div class="conv-meta"><div class="conv-time">Actif</div><div class="conv-badge">5</div></div>
    </div>

    <div class="section-label">Collègues (Privé)</div>
    <?php foreach ($collegues as $col): ?>
    <div class="conv-item" onclick="selectConv(this,'<?= htmlspecialchars($col['prenom'].' '.$col['nom'], ENT_QUOTES) ?>','👤','linear-gradient(135deg,#6366f1,#4f46e5)','<?= ucfirst($col['role']) ?>','prive', <?= $col['id'] ?>)">
      <div class="avatar" style="background:linear-gradient(135deg,#6366f1,#4f46e5);font-size:12px;font-weight:700;"><?= strtoupper(substr($col['prenom'],0,1).substr($col['nom'],0,1)) ?></div>
      <div class="conv-info"><div class="conv-name"><?= htmlspecialchars($col['prenom'].' '.$col['nom']) ?></div><div class="conv-preview">Chat privé</div></div>
    </div>
    <?php endforeach; ?>

    <div class="section-label">Convocations reçues</div>
    <?php if (empty($convocations)): ?>
        <div style="padding: 10px; font-size: 12px; color: #9ca3af; text-align: center;">Aucune convocation</div>
    <?php else: ?>
        <?php foreach ($convocations as $conv): ?>
        <div class="conv-item" onclick="alert('Détails : <?= htmlspecialchars($conv['objet']) ?>\nLieu : <?= htmlspecialchars($conv['lieu']) ?>\nDate : <?= htmlspecialchars($conv['date_heure']) ?>')">
          <div class="avatar" style="background:linear-gradient(135deg,#dc2626,#991b1b);">🏛</div>
          <div class="conv-info">
            <div class="conv-name"><?= htmlspecialchars($conv['expediteur_prenom'] . ' ' . $conv['expediteur_nom']) ?></div>
            <div class="conv-preview"><?= htmlspecialchars(substr($conv['objet'], 0, 30)) ?>...</div>
          </div>
          <div class="conv-meta">
            <div class="conv-time"><?= date('H:i', strtotime($conv['date_heure'])) ?></div>
            <div class="conv-badge-warn">!</div>
          </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
  </div>
  <div class="sidebar-profile">
    <div class="profile-avatar"><div class="online-dot"></div>👨‍🏫</div>
    <div class="profile-info"><h4><?= htmlspecialchars($currentUser['prenom'] . ' ' . $currentUser['nom']) ?></h4><span><?= ucfirst($currentUser['role']) ?></span></div>
    <div class="profile-actions"><a href="/FasiChatClassroom/public/login" class="icon-btn">🚪</a></div>
  </div>
</div>

<!-- MAIN AREA -->
<div class="main-area">
  <div class="chat-topbar">
    <div class="chat-topbar-avatar" id="topbarAvatar" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);">🖥</div>
    <div class="chat-topbar-info">
      <h3 id="topbarTitle">Programation Web — L2 FASI</h3>
      <p id="topbarSub">450 étudiants · 3 en ligne</p>
    </div>
    <div class="status-badge public"><div class="status-dot"></div>Cours Public</div>
    <div class="topbar-actions">
      <button class="topbar-btn" onclick="showView('students',null)">👥 Étudiants</button>
      <button class="topbar-btn" onclick="showView('mur',null)">📋 Mur péda.</button>
      <button class="topbar-btn primary" onclick="showView('msgs',null)">💬 Messagerie</button>
    </div>
  </div>

  <!-- Students view -->
    <div class="students-panel visible" id="view-students">
      <div class="panel-header">
        <div><h2>Liste des étudiants</h2><p>Faculté des Sciences Informatiques · <?= count($etudiants) ?> inscrits</p></div>
      </div>
      <div class="search-students">
        <div class="search-field">
          <span class="s-icon">🔍</span>
          <input type="text" placeholder="Rechercher un étudiant...">
        </div>
        <button class="filter-btn active">Tous</button>
        <button class="filter-btn">En ligne</button>
        <button class="filter-btn">Hors ligne</button>
      </div>
      <div class="students-grid">
        <?php foreach ($etudiants as $etud): ?>
        <div class="student-card">
          <div class="sc-header">
            <div class="sc-avatar" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);"><?= strtoupper(substr($etud['prenom'],0,1).substr($etud['nom'],0,1)) ?></div>
            <div><div class="sc-name"><?= htmlspecialchars($etud['prenom'] . ' ' . $etud['nom']) ?></div></div>
          </div>
          <div class="sc-actions">
            <button class="sc-btn msg" onclick="selectConv(this, '<?= htmlspecialchars($etud['prenom'].' '.$etud['nom'], ENT_QUOTES) ?>','👤','linear-gradient(135deg,#3b82f6,#1d4ed8)','Étudiant','prive', <?= $etud['id'] ?>)">💬 Message</button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

  <!-- Mur pédagogique view -->
  <div class="mur-panel" id="view-mur">
    <div class="mur-compose">
      <h3>📋 Publier sur le mur — PHP POO L3</h3>
      <textarea class="mur-textarea" placeholder="Rédigez une question, un rappel ou une annonce pour vos étudiants..."></textarea>
      <div class="mur-footer">
        <div class="mur-attach">
          <button class="attach-btn">📎 Fichier</button>
          <button class="attach-btn">🔗 Lien</button>
        </div>
        <button class="publish-btn" onclick="publishPost()">Publier →</button>
      </div>
    </div>
    <div id="mur-posts">
      <div class="mur-post">
        <div class="post-header">
          <div class="post-avatar" style="background:linear-gradient(135deg,#f59e0b,#d97706);">PM</div>
          <div><div class="post-author">Prof. Mbaye</div><div class="post-meta">Aujourd'hui à 08:45 · PHP POO L2 FASI</div></div>
          <div class="post-actions"><button class="post-action-btn">✏️</button><button class="post-action-btn">🗑</button></div>
        </div>
        <div class="post-content">Rappel important : le projet FasiChat est à rendre avant <strong>vendredi 23h59</strong>. Assurez-vous que votre diagramme UML est complet et que votre code respecte les principes de la POO vus en cours. Bon courage à tous ! 🎯</div>
      </div>
    </div>
  </div>

  <!-- Chat messages view -->
  <div class="chat-messages" id="view-msgs">
    <div class="date-sep">Chargement des messages...</div>
  </div>

  <!-- Input (shown for msgs view) -->
  <div class="chat-input-area" id="input-area" style="display:none;">
    <div class="input-row">
      <div class="msg-textarea-wrap">
        <textarea class="msg-textarea" placeholder="Répondre..." rows="1" id="msgInput" onkeydown="handleKey(event)"></textarea>
      </div>
      <button class="attach-btn" onclick="triggerUpload()">📎</button>
      <input type="file" id="fileInput" style="display:none;" onchange="handleFileUpload(this)">
      <button class="send-btn" onclick="sendMsg()">➤</button>
    </div>
  </div>
</div>

<div class="right-panel">
  <div class="panel-section">
    <div class="panel-title">Statistiques</div>
    <div class="info-card">
      <h4>PHP POO — L3 Info</h4>
      <div class="stat-row">
        <div class="stat-box"><div class="num">28</div><div class="lbl">Étudiants</div></div>
        <div class="stat-box"><div class="num">3</div><div class="lbl">En ligne</div></div>
        <div class="stat-box"><div class="num">14</div><div class="lbl">Messages</div></div>
      </div>
    </div>
  </div>
  <div class="panel-section">
    <div class="panel-title">Convocations reçues</div>
    <div id="convoc-list-right">
        <?php if (empty($convocations)): ?>
            <div style="font-size:12px;color:#94a3a8;padding:8px;">Aucune convocation.</div>
        <?php else: ?>
            <?php foreach ($convocations as $conv): ?>
            <div style="padding:8px 0;border-bottom:1px solid #f1f5f9;font-size:12px;">
                <strong><?= htmlspecialchars($conv['objet']) ?></strong><br>
                <span style="color:#94a3b8;"><?= htmlspecialchars($conv['lieu']) ?> · <?= date('d/m H:i', strtotime($conv['date_heure'])) ?></span>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
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

<script src="/FasiChatClassroom/public/assets/js/dashboard_enseignant.js?t=<?= time() ?>"></script>
<script>initChat(<?= $currentUser['id'] ?>, 1);</script>
</body>
</html>
