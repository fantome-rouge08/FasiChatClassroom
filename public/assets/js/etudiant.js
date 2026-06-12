console.log('etudiant.js loaded successfully');

// Variables globales
let currentChatType = 'public';
let currentChatId = 1;
let currentChatName = 'Promo';
let currentUserId = null;
let selectedFileId = null;

window.initChat = function(userId, promoId) {
    currentUserId = userId;
    currentChatId = promoId;
    loadMessages();
    setInterval(loadMessages, 3000);
};

//Envoi des fichiers
window.triggerUpload = function(type) {
    const input = document.getElementById('fileInput');
    input.accept = (type === 'image') ? 'image/*' : (type === 'pdf') ? '.pdf' : (type === 'video') ? 'video/*' : '*';
    input.click();
};

window.handleFileUpload = function(input) {
    if (input.files && input.files[0]) {
        const formData = new FormData();
        formData.append('file', input.files[0]);
        fetch('/FasiChatClassroom/public/file-upload', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                selectedFileId = data.file_id;
                alert('Fichier prêt !');
            } else {
                alert('Erreur upload');
            }
        });
    }
};

window.changerDiscussion = function(element, type, id, name) {
    document.querySelectorAll('.conv-item').forEach(i => i.classList.remove('active'));
    element.classList.add('active');
    currentChatType = type;
    currentChatId = id;
    currentChatName = name;
    document.getElementById('topbarTitle').textContent = name;
    
    const badge = document.getElementById('statusBadge');
    badge.textContent = (type === 'prive') ? 'Message Privé' : (type === 'public') ? 'Message Public' : 'Mur Pédagogique';
    badge.className = 'status-badge ' + type;
    
    loadMessages();
};

window.handleKey = function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        window.sendMsg();
    }
};

window.sendMsg = function() {
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
    
    console.log('Sending message:', Object.fromEntries(formData.entries())); // DEBUG
    
    fetch('/FasiChatClassroom/public/message-send', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            ta.value = '';
            selectedFileId = null;
            loadMessages();
        } else {
            alert('Erreur: ' + (data.error || 'Échec'));
        }
    });
};

function loadMessages() {
    if (!currentChatId) return;
    fetch(`/FasiChatClassroom/public/message-poll?type=${currentChatType}&id=${currentChatId}`)
    .then(res => res.json())
    .then(messages => {
        const container = document.getElementById('messages');
        container.innerHTML = '';
        if (messages.length === 0) {
            container.innerHTML = '<div class="date-sep">Aucun message.</div>';
            return;
        }
        
        messages.forEach(msg => {
            const estLeMien = (parseInt(msg.expediteur_id) === parseInt(currentUserId));
            const row = document.createElement('div');
            row.className = estLeMien ? 'msg-row mine' : 'msg-row';
            const heure = new Date(msg.date_envoi).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            
            let fileHtml = '';
            if (msg.fichier_id && msg.nom_stockage) {
                fileHtml = `<div class="file-bubble">
                                 <div class="file-icon">📎</div>
                                 <div class="file-info">Fichier Joint</div>
                                 <a href="/FasiChatClassroom/public/assets/uploads/${msg.nom_stockage}" target="_blank">⬇</a>
                             </div>`;
            }
            
            row.innerHTML = `
                <div class="msg-avatar">${(msg.prenom.charAt(0) + msg.nom.charAt(0)).toUpperCase()}</div>
                <div class="msg-group">
                    <div class="msg-sender">${msg.prenom} ${msg.nom}</div>
                    ${fileHtml}
                    <div class="bubble ${estLeMien ? 'mine' : 'theirs'}">${escapeHTML(msg.contenu)}</div>
                    <div class="msg-meta">${heure}</div>
                </div>`;
            container.appendChild(row);
        });
        container.scrollTop = container.scrollHeight;
    });
}

function escapeHTML(str) {
    const p = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
    return str.replace(/[&<>"']/g, m => p[m]);
}
