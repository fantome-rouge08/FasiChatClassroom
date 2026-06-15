<?php

require_once __DIR__ . '/../database/Database.php';

try {
    $dbInstance = new Database();
    $db = $dbInstance->getConnection();

    if ($db) {
        // 1. S'assurer que toutes les tables nécessaires existent
        
        // Table des promotions
        $db->exec("CREATE TABLE IF NOT EXISTS promotions (id INT AUTO_INCREMENT PRIMARY KEY, nom VARCHAR(50) NOT NULL)");

        // Table des cours
        $db->exec("CREATE TABLE IF NOT EXISTS cours (id INT AUTO_INCREMENT PRIMARY KEY, nom VARCHAR(100) NOT NULL, promotion_id INT, FOREIGN KEY (promotion_id) REFERENCES promotions(id) ON DELETE CASCADE)");

        // Table des fichiers (MUST BE FIRST for foreign key)
        $db->exec("CREATE TABLE IF NOT EXISTS fichiers (id INT AUTO_INCREMENT PRIMARY KEY, nom_origine VARCHAR(255), nom_stockage VARCHAR(255), chemin VARCHAR(255), type_mime VARCHAR(100), taille INT, date_upload DATETIME DEFAULT CURRENT_TIMESTAMP)");

        // Table des messages
        $db->exec("CREATE TABLE IF NOT EXISTS messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            expediteur_id INT,
            contenu TEXT,
            date_envoi DATETIME DEFAULT CURRENT_TIMESTAMP,
            type VARCHAR(20), -- 'prive', 'public', 'mur', 'convocation'
            cours_id INT NULL,
            promotion_id INT NULL,
            fichier_id INT NULL,
            FOREIGN KEY (expediteur_id) REFERENCES utilisateurs(id),
            FOREIGN KEY (cours_id) REFERENCES cours(id) ON DELETE CASCADE,
            FOREIGN KEY (promotion_id) REFERENCES promotions(id) ON DELETE CASCADE,
            FOREIGN KEY (fichier_id) REFERENCES fichiers(id) ON DELETE SET NULL
        )");

        // Table des destinataires des messages privés
        $db->exec("CREATE TABLE IF NOT EXISTS messages_destinataires (
            message_id INT,
            destinataire_id INT,
            lu BOOLEAN DEFAULT FALSE,
            PRIMARY KEY (message_id, destinataire_id),
            FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
            FOREIGN KEY (destinataire_id) REFERENCES utilisateurs(id)
        )");

        // Table des convocations
        $db->exec("CREATE TABLE IF NOT EXISTS convocations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            message_id INT,
            objet VARCHAR(255),
            date_heure DATETIME,
            lieu VARCHAR(255),
            FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE
        )");

        // Table des annonces (Valve)
        $db->exec("CREATE TABLE IF NOT EXISTS annonces (
            id INT AUTO_INCREMENT PRIMARY KEY,
            titre VARCHAR(255),
            contenu TEXT,
            date_publication DATETIME DEFAULT CURRENT_TIMESTAMP,
            auteur_id INT
        )");

        // On génère le hash sécurisé pour le mot de passe "password123"
        $hash = password_hash('password123', PASSWORD_BCRYPT);
        
        // Vider la table des utilisateurs pour insérer les nouveaux comptes proprement
        $db->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $db->exec("TRUNCATE TABLE utilisateurs");
        $db->exec("SET FOREIGN_KEY_CHECKS = 1;");

        // Insérer tous les comptes de test (dont le DEUXIÈME étudiant pour la messagerie privée)
        $stmt = $db->prepare("INSERT INTO utilisateurs (id, nom, prenom, email, mot_de_passe, role) VALUES (?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([1, 'Banzolele', 'Samiel', 'etudiant@test.com', $hash, 'etudiant']);
        $stmt->execute([2, 'Bemba', 'Daniel', 'etudiant2@test.com', $hash, 'etudiant']);
        $stmt->execute([3, 'Mampuya', 'Professeur', 'enseignant@test.com', $hash, 'enseignant']);
        $stmt->execute([4, 'Bahati', 'Assistant', 'assistant@test.com', $hash, 'assistant']);
        $stmt->execute([5, 'Kutangila', 'Doyen', 'doyen@test.com', $hash, 'doyen']);
        $stmt->execute([6, 'Manpuya', 'Vice-Doyen', 'vicedoyen@test.com', $hash, 'vice-doyen']);
        $stmt->execute([7, 'Rolly', 'Apparitaire', 'apparitaire@test.com', $hash, 'apparitaire']);
        
        // Promotions et Cours
        $db->exec("INSERT IGNORE INTO promotions (id, nom) VALUES (1, 'L2 FASI'), (2, 'L3 Info')");
        $db->exec("INSERT IGNORE INTO cours (id, nom, promotion_id) VALUES (1, 'PHP POO', 1), (2, 'Système Embarqué', 1)");

        echo "<h3 style='color: green; font-family: sans-serif;'>✅ Base de données structurée et mise à jour avec succès !</h3>";
        echo "<h4 style='font-family: sans-serif;'>Comptes d'évaluation opérationnels (mot de passe : <strong>password123</strong>) :</h4>";
        echo "<table border='1' cellpadding='8' style='border-collapse: collapse; font-family: sans-serif;'>
                <tr style='background: #f3f4f6;'>
                    <th>Rôle</th>
                    <th>Nom Complet</th>
                    <th>Email</th>
                    <th>Mot de passe</th>
                </tr>
                <tr><td>Étudiant 1 (Vous)</td><td>Samiel Banzolele</td><td>etudiant@test.com</td><td>password123</td></tr>
                <tr><td>Étudiant 2 (Camarade)</td><td>Daniel Bemba</td><td>etudiant2@test.com</td><td>password123</td></tr>
                <tr><td>Enseignant</td><td>Prof. Mampuya</td><td>enseignant@test.com</td><td>password123</td></tr>
                <tr><td>Assistant</td><td>Ass. Bahati</td><td>assistant@test.com</td><td>password123</td></tr>
                <tr><td>Doyen</td><td>Doyen Kutangila</td><td>doyen@test.com</td><td>password123</td></tr>
                <tr><td>Vice-Doyen</td><td>Vice-Doyen Manpuya</td><td>vicedoyen@test.com</td><td>password123</td></tr>
                <tr><td>Apparitaire</td><td>DJ Rolly</td><td>apparitaire@test.com</td><td>password123</td></tr>
              </table>";
        echo "<p style='font-family: sans-serif; margin-top: 15px;'><a href='/FasiChatClassroom/public/login'>👉 Aller à la page de connexion</a></p>";
    } else {
        echo "<h3 style='color: red; font-family: sans-serif;'>❌ Connexion à la base de données impossible.</h3>";
    }
} catch (Exception $e) {
    echo "<h3 style='color: red; font-family: sans-serif;'>❌ Erreur : " . $e->getMessage() . "</h3>";
}
