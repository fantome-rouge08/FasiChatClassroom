<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'vice-doyen') {
    header('Location: /FasiChatClassroom/public/login');
    exit();
}
$currentUser = $_SESSION['user'];
require_once __DIR__ . '/../src/Autoloader.php';
require_once __DIR__ . '/../database/Database.php';
$dbInstance = new Database();
$db = $dbInstance->getConnection();

$stmt = $db->query("SELECT a.*, u.nom, u.prenom FROM annonces a JOIN utilisateurs u ON a.auteur_id = u.id ORDER BY a.date_publication DESC LIMIT 3");
$annoncesValve = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalAnnoncesValve = $db->query("SELECT COUNT(*) FROM annonces")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FasiChat — Vice-Doyen</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/FasiChatClassroom/public/assets/css/dashboard_vicedoyen.css">
<style>
.valve-mini { background:#fff; border-radius:12px; padding:16px; border:1px solid #e2e8f0; }
.valve-mini-title { font-size:13px; font-weight:700; color:#1e293b; margin-bottom:12px; display:flex; align-items:center; gap:6px; }
.valve-mini-item { padding:10px 0; border-bottom:1px solid #f1f5f9; }
.valve-mini-item:last-child { border-bottom:none; }
.valve-mini-item-title { font-size:12px; font-weight:600; color:#334155; }
.valve-mini-item-meta { font-size:10px; color:#94a3b8; margin-top:2px; }
.valve-mini-link { display:block; text-align:center; margin-top:10px; font-size:11px; font-weight:600; color:#6366f1; text-decoration:none; }
.valve-mini-link:hover { text-decoration:underline; }
.valve-mini-empty { font-size:12px; color:#94a3b8; text-align:center; padding:10px; }
</style>
</head>
<body>
<div class="sidebar">
  <div class="sidebar-header">
    <div class="brand-mark">🏅</div>
    <div class="brand-info"><h3>FasiChat Admin</h3><span>Espace Vice-Doyen</span></div>
  </div>
  <div class="role-badge-sidebar"><div class="rdot"></div><span>VICE-DOYEN — Accès administratif</span></div>
  <div class="nav-section">
    <div class="nav-section-label">Administration</div>
    <div class="nav-item active" onclick="setNav(this)"><div class="nav-icon" style="background:rgba(124,58,237,0.12);">📊</div><div><div class="nav-label">Tableau de bord</div><div class="nav-sub">Vue d'ensemble</div></div></div>
    <div class="nav-item" onclick="setNav(this)"><div class="nav-icon" style="background:rgba(245,158,11,0.12);">📅</div><div><div class="nav-label">Convoquer réunion</div><div class="nav-sub">Commission de recherche</div></div></div>
    <div class="nav-item" onclick="setNav(this)"><div class="nav-icon" style="background:rgba(34,197,94,0.12);">🔬</div><div><div class="nav-label">Commission Recherche</div><div class="nav-sub">Suivi des projets</div></div></div>
  </div>
  <div class="nav-section">
    <div class="nav-section-label">Communication</div>
    <div class="nav-item" onclick="location.href='/FasiChatClassroom/public/valve'"><div class="nav-icon" style="background:rgba(124,58,237,0.12);">📣</div><div><div class="nav-label">Valve</div><div class="nav-sub">Tableau d'affichage</div></div></div>
    <div class="nav-item" onclick="setNav(this)"><div class="nav-icon" style="background:rgba(220,38,38,0.12);">🔒</div><div><div class="nav-label">Message — Doyen</div><div class="nav-sub">Confidentiel</div></div><div class="nav-badge">2</div></div>
  </div>
  <div class="nav-section">
    <div class="nav-section-label">Navigation</div>
    <div class="nav-item" onclick="location.href='/FasiChatClassroom/public/dashboard_admin'"><div class="nav-icon" style="background:rgba(220,38,38,0.08);">🏛</div><div><div class="nav-label">Espace Doyen</div><div class="nav-sub">Dashboard principal</div></div></div>
    <div class="nav-item" onclick="location.href='/FasiChatClassroom/public/dashboard_etudiant'"><div class="nav-icon" style="background:rgba(79,163,224,0.08);">🎓</div><div><div class="nav-label">Vue Étudiant</div><div class="nav-sub">Dashboard étudiant</div></div></div>
  </div>
  <div class="sidebar-bottom">
    <div class="profile-ava"><div class="online-dot"></div>🏅</div>
    <div class="profile-info"><h4><?= htmlspecialchars($currentUser['prenom'] . ' ' . $currentUser['nom']) ?></h4><span>Vice-Doyen</span></div>
    <a href="/FasiChatClassroom/public/login" class="logout-btn">🚪</a>
  </div>
</div>

<div class="main-area">
  <div class="admin-topbar">
    <div>
      <div class="topbar-title">Tableau de bord — Vice-Doyen</div>
      <div class="topbar-sub">Faculté des Sciences Informatiques </div>
    </div>
    <div class="topbar-right">
      <button class="tb-btn ghost" onclick="location.href='/FasiChatClassroom/public/valve'">📣 Valve</button>
      <button class="tb-btn ghost" onclick="location.href='/FasiChatClassroom/public/dashboard_admin'">🏛 Espace Doyen</button>
      <button class="tb-btn primary" onclick="openModal()">📅 Convoquer une réunion</button>
    </div>
  </div>
  <div class="admin-content">
    <div class="stats-row">
      <div class="stat-card purple"><div class="stat-icon">🔬</div><div class="stat-number">8</div><div class="stat-label">Projets de recherche</div><div class="stat-trend">En cours ce semestre</div></div>
      <div class="stat-card blue"><div class="stat-icon">👨‍🏫</div><div class="stat-number">18</div><div class="stat-label">Enseignants-chercheurs</div><div class="stat-trend">Commission de recherche</div></div>
      <div class="stat-card gold"><div class="stat-icon">📅</div><div class="stat-number">1</div><div class="stat-label">Convocation envoyée</div><div class="stat-trend">Ce mois-ci</div></div>
      <div class="stat-card green"><div class="stat-icon">📣</div><div class="stat-number"><?= $totalAnnoncesValve ?></div><div class="stat-label">Annonces Valve</div><div class="stat-trend">Publications actives</div></div>
    </div>
    <div class="two-col">
      <!-- CONVOC -->
      <div class="card">
        <div class="card-header">
          <div class="card-title">📅 Convoquer une réunion</div>
          <span class="conf-badge">🏅 Vice-Doyen uniquement</span>
        </div>
        <div class="convoc-form">
          <div class="form-group"><label class="form-label">Objet *</label><input type="text" class="form-input" placeholder="Ex: Commission de recherche S5..."></div>
          <div class="form-row-2">
            <div class="form-group"><label class="form-label">Date *</label><input type="date" class="form-input"></div>
            <div class="form-group"><label class="form-label">Heure *</label><input type="time" class="form-input"></div>
          </div>
          <div class="form-group"><label class="form-label">Lieu / Lien *</label><input type="text" class="form-input" placeholder="Salle de Conférence B..."></div>
          <div class="form-group"><label class="form-label">Message complémentaire</label><textarea class="form-textarea" placeholder="Ordre du jour, documents à préparer..."></textarea></div>
          <div class="form-group"><label class="form-label">Destinataires</label>
            <div class="recipients-box">
              <div class="recipient-tag">👨‍🏫 Tous les enseignants (24)</div>
              <div class="recipient-tag">📋 Tous les assistants (6)</div>
            </div>
          </div>
          <button class="send-btn" onclick="sendConvoc()">📨 Envoyer la convocation</button>
        </div>
      </div>
      <!-- RIGHT -->
      <div style="display:flex;flex-direction:column;gap:16px;">
        <!-- MSG PRIVE DOYEN -->
        <div class="card">
          <div class="card-header">
            <div class="card-title">🔒 Message privé — Doyen</div>
            <span class="conf-badge">Confidentiel</span>
          </div>
          <div class="priv-chat">
            <div class="priv-messages" id="privMsgs">
              <div class="msg-row">
                <div class="msg-av" style="background:linear-gradient(135deg,#dc2626,#991b1b);">D</div>
                <div class="msg-group">
                  <div class="bubble theirs">VD, avez-vous les résultats de la commission de recherche pour vendredi ?</div>
                  <div class="msg-time">09:00</div>
                </div>
              </div>
              <div class="msg-row mine">
                <div class="msg-av" style="background:linear-gradient(135deg,var(--purple),#5b21b6);">VD</div>
                <div class="msg-group">
                  <div class="bubble mine">Oui Professeur, je les ferai parvenir avant jeudi midi. 3 nouveaux projets validés.</div>
                  <div class="msg-time">09:15 ✓✓</div>
                </div>
              </div>
              <div class="msg-row">
                <div class="msg-av" style="background:linear-gradient(135deg,#dc2626,#991b1b);">D</div>
                <div class="msg-group">
                  <div class="bubble theirs">Parfait. Préparez aussi le bilan budgétaire pour la réunion.</div>
                  <div class="msg-time">09:22</div>
                </div>
              </div>
              <div class="msg-row mine">
                <div class="msg-av" style="background:linear-gradient(135deg,var(--purple),#5b21b6);">VD</div>
                <div class="msg-group">
                  <div class="bubble mine">Bien reçu. Je prépare tout cela.</div>
                  <div class="msg-time">09:30 ✓✓</div>
                </div>
              </div>
            </div>
            <div class="priv-input">
              <textarea class="priv-textarea" placeholder="Message confidentiel au Doyen..." id="privInput" onkeydown="handlePrivKey(event)" rows="1"></textarea>
              <button class="priv-send" onclick="sendPrivMsg()">➤</button>
            </div>
          </div>
        </div>
        <!-- ACTIVITY -->
        <div class="card">
          <div class="card-header"><div class="card-title">🕐 Activité récente</div></div>
          <div class="activity-list">
            <div class="activity-item">
              <div class="act-icon-wrap" style="background:rgba(245,158,11,0.1);">📅</div>
              <div class="act-text"><strong>Convocation envoyée</strong><p>Commission recherche · 27 Jan · 30 destinataires</p></div>
              <div class="act-time">Hier 09:15</div>
            </div>
            <div class="activity-item">
              <div class="act-icon-wrap" style="background:rgba(124,58,237,0.1);">💬</div>
              <div class="act-text"><strong>Message du Doyen</strong><p>Résultats commission de recherche</p></div>
              <div class="act-time">Auj. 09:00</div>
            </div>
            <div class="activity-item">
              <div class="act-icon-wrap" style="background:rgba(34,197,94,0.1);">🔬</div>
              <div class="act-text"><strong>Projet validé</strong><p>Cybersécurité des systèmes embarqués</p></div>
              <div class="act-time">18 Jan</div>
            </div>
          </div>
        </div>

        <!-- VALVE WIDGET -->
        <div class="valve-mini">
          <div class="valve-mini-title">📣 Dernières annonces (Valve)</div>
          <?php if (empty($annoncesValve)): ?>
            <div class="valve-mini-empty">Aucune annonce pour le moment.</div>
          <?php else: ?>
            <?php foreach ($annoncesValve as $a): ?>
            <div class="valve-mini-item">
              <div class="valve-mini-item-title"><?= htmlspecialchars($a['titre']) ?></div>
              <div class="valve-mini-item-meta"><?= htmlspecialchars($a['prenom'] . ' ' . $a['nom']) ?> · <?= date('d/m H:i', strtotime($a['date_publication'])) ?></div>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>
          <a href="/FasiChatClassroom/public/valve" class="valve-mini-link">Voir toutes les annonces →</a>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- MODAL -->
<div class="modal-overlay" id="convocModal" onclick="closeOut(event)">
  <div class="modal">
    <div class="modal-header">
      <span style="font-size:26px;">📅</span>
      <div><h3>Convoquer une réunion</h3><p>Envoyée à tous les enseignants et assistants</p></div>
      <button class="modal-close-btn" onclick="closeModal()">✕</button>
    </div>
    <div class="modal-body">
      <div class="form-group"><label class="form-label">Objet *</label><input type="text" class="form-input" id="mObj" placeholder="Objet de la réunion..."></div>
      <div class="form-row-2">
        <div class="form-group"><label class="form-label">Date *</label><input type="date" class="form-input" id="mDate"></div>
        <div class="form-group"><label class="form-label">Heure *</label><input type="time" class="form-input" id="mHeure"></div>
      </div>
      <div class="form-group"><label class="form-label">Lieu *</label><input type="text" class="form-input" id="mLieu" placeholder="Salle ou lien..."></div>
      <div class="form-group"><label class="form-label">Message</label><textarea class="form-textarea" id="mMsg" placeholder="Ordre du jour..."></textarea></div>
      <div class="form-group"><label class="form-label">Destinataires</label>
        <div class="recipients-box">
          <div class="recipient-tag">👨‍🏫 Tous les enseignants (24)</div>
          <div class="recipient-tag">📋 Tous les assistants (6)</div>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn-cancel" onclick="closeModal()">Annuler</button>
      <button class="btn-send-modal" onclick="sendModal()">📨 Envoyer</button>
    </div>
  </div>
</div>

<script>
function setNav(el){document.querySelectorAll('.nav-item').forEach(n=>n.classList.remove('active'));el.classList.add('active');}
function openModal(){document.getElementById('convocModal').classList.add('open');}
function closeModal(){document.getElementById('convocModal').classList.remove('open');}
function closeOut(e){if(e.target.id==='convocModal')closeModal();}
function sendModal(){const o=document.getElementById('mObj').value.trim();if(!o){alert('Veuillez saisir l\'objet.');return;}closeModal();alert('Convocation envoyée avec succès !');}
function sendConvoc(){const f=document.querySelector('.convoc-form .form-input');if(!f.value.trim()){alert('Veuillez saisir l\'objet.');return;}alert('Convocation envoyée à 30 destinataires !');}
function handlePrivKey(e){if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();sendPrivMsg();}}
function sendPrivMsg(){
  const ta=document.getElementById('privInput');
  const text=ta.value.trim();if(!text)return;
  const box=document.getElementById('privMsgs');
  const now=new Date();const time=now.getHours().toString().padStart(2,'0')+':'+now.getMinutes().toString().padStart(2,'0');
  const row=document.createElement('div');row.className='msg-row mine';
  row.innerHTML=`<div class="msg-av" style="background:linear-gradient(135deg,var(--purple),#5b21b6);">VD</div><div class="msg-group"><div class="bubble mine">${text.replace(/</g,'&lt;')}</div><div class="msg-time">${time} ✓</div></div>`;
  box.appendChild(row);ta.value='';box.scrollTop=box.scrollHeight;
}
window.addEventListener('load',()=>{const b=document.getElementById('privMsgs');b.scrollTop=b.scrollHeight;});
</script>
</body>
</html>
