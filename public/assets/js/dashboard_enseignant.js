// Variables globales
let currentChatType = 'public'; // 'prive', 'public', 'mur'
let currentChatId = 1;         // ID de la cible
let currentChatName = 'PHP POO — Promo L2';
let currentUserId = null;
let selectedFileId = null;

// Initialisation (appelée depuis le PHP)
function initChat(userId, defaultPromoId) {
    currentUserId = userId;
    currentChatId = defaultPromoId;
    loadMessages();
    setInterval(loadMessages, 3000);
}

function showView(view, btn) {
    document.getElementById('view-students').classList.remove('visible');
    document.getElementById('view-mur').classList.remove('visible');
    document.getElementById('view-msgs').classList.remove('visible');
    document.getElementById('input-area').style.display = 'none';
    if (view === 'students') document.getElementById('view-students').classList.add('visible');
    else if (view === 'mur') document.getElementById('view-mur').classList.add('visible');
    else if (view === 'msgs') {
        document.getElementById('view-msgs').classList.add('visible');
        document.getElementById('input-area').style.display = 'block';
    }
    if (btn) { document.querySelectorAll('.nav-tab').forEach(b => b.classList.remove('active')); btn.classList.add('active'); }
}

function selectConv(item, title, icon, bg, sub, type, id) {
    document.querySelectorAll('.conv-item').forEach(i => i.classList.remove('active'));
    if (item) item.classList.add('active');
    currentChatType = type;
    currentChatId = id;
    currentChatName = title;
    document.getElementById('topbarTitle').textContent = title;
    document.getElementById('topbarSub').textContent = sub;
    const avatar = document.getElementById('topbarAvatar');
    avatar.textContent = icon || '💬';
    avatar.style.background = bg || 'linear-gradient(135deg,#6366f1,#4f46e5)';
    showView('msgs', null);
    loadMessages();
}

function handleKey(e) { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMsg(); } }

function sendMsg() {
    const ta = document.getElementById('msgInput');
    const text = ta.value.trim();
    if (!text && !selectedFileId) return;

    const formData = new FormData();
    formData.append('contenu', text);
    formData.append('type', currentChatType);
    if (selectedFileId) formData.append('fichier_id', selectedFileId);

    if (currentChatType === 'prive') formData.append('destinataire_id', currentChatId);
    else if (currentChatType === 'public') formData.append('promotion_id', currentChatId);
    else if (currentChatType === 'mur') formData.append('cours_id', currentChatId);

    fetch('/FasiChatClassroom/public/message-send', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            ta.value = '';
            selectedFileId = null;
            loadMessages();
        } else {
            alert('Erreur: ' + (data.error || 'Échec de l\'envoi'));
        }
    });
}

function loadMessages() {
    if (!currentChatId) return;
    fetch(`/FasiChatClassroom/public/message-poll?type=${currentChatType}&id=${currentChatId}`)
    .then(res => res.json())
    .then(messages => {
        const container = document.getElementById('view-msgs');
        container.innerHTML = '';
        messages.forEach(msg => {
            const estLeMien = (parseInt(msg.expediteur_id) === parseInt(currentUserId));
            const row = document.createElement('div');
            row.className = estLeMien ? 'msg-row mine' : 'msg-row';
            const heure = new Date(msg.date_envoi).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            
            let fileHtml = '';
            if (msg.fichier_id) {
                fileHtml = `<div class="file-bubble">
                                <div class="file-icon">📎</div>
                                <div class="file-info">Fichier Joint</div>
                                <a href="/FasiChatClassroom/public/assets/uploads/${msg.nom_stockage}" target="_blank" style="color:white; text-decoration:underline; margin-left:auto;">⬇</a>
                            </div>`;
            }

            row.innerHTML = `
                <div class="msg-avatar" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                    ${(msg.prenom.charAt(0) + msg.nom.charAt(0)).toUpperCase()}
                </div>
                <div class="msg-group">
                    <div class="msg-sender">${msg.prenom} ${msg.nom} · ${heure}</div>
                    ${fileHtml}
                    <div class="bubble ${estLeMien ? 'mine' : 'theirs'}">${escapeHTML(msg.contenu)}</div>
                    <div class="msg-meta">${heure} ${estLeMien ? '<span class="check-read">✓✓</span>' : ''}</div>
                </div>`;
            container.appendChild(row);
        });
        container.scrollTop = container.scrollHeight;
    });
}

function escapeHTML(str) {
    return str.replace(/[&<>'"]/g, tag => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'':'&#39;','"':'&quot;'}[tag] || tag));
}

function triggerUpload() {
    document.getElementById('fileInput').click();
}

function handleFileUpload(input) {
    if (input.files && input.files[0]) {
        const formData = new FormData();
        formData.append('file', input.files[0]);
        fetch('/FasiChatClassroom/public/file-upload', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                selectedFileId = data.file_id;
                alert('Fichier prêt pour l\'envoi !');
            }
        });
    }
}

document.getElementById('msgInput').addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 120) + 'px';
});
