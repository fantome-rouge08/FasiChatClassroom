<?php

class Database {
    private $host = 'localhost';
    private $dbname = 'fasichat';
    private $username = 'root';
    private $password = 'michel';

    private ? PDO $conn = null;


    public function __construct() {
        $this->connect();
    }


    private function connect(){
        try{
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";
            $this->conn = new PDO($dsn, $this->username, $this->password);

            $this->conn->setAttribute(
                PDO::ATTR_ERRMODE, 
                PDO::ERRMODE_EXCEPTION
            );

            $this->conn->setAttribute(
                PDO::ATTR_DEFAULT_FETCH_MODE, 
                PDO::FETCH_ASSOC
            );

            // echo "Connexion réussie à la base de données.";
        } catch (PDOException $e) {
            // echo "Erreur de connexion : " . $e->getMessage();
            throw $e;
        }
    }

    public function getConnection() {
        return $this->conn;
    }

    public function seed() {
        $db = $this->conn;
        $db->exec("CREATE TABLE IF NOT EXISTS promotions (id INT AUTO_INCREMENT PRIMARY KEY, nom VARCHAR(50) NOT NULL)");
        $db->exec("CREATE TABLE IF NOT EXISTS cours (id INT AUTO_INCREMENT PRIMARY KEY, nom VARCHAR(100) NOT NULL, promotion_id INT, FOREIGN KEY (promotion_id) REFERENCES promotions(id) ON DELETE CASCADE)");
        $db->exec("CREATE TABLE IF NOT EXISTS fichiers (id INT AUTO_INCREMENT PRIMARY KEY, nom_origine VARCHAR(255), nom_stockage VARCHAR(255), chemin VARCHAR(255), type_mime VARCHAR(100), taille INT, date_upload DATETIME DEFAULT CURRENT_TIMESTAMP)");
        $db->exec("CREATE TABLE IF NOT EXISTS messages (id INT AUTO_INCREMENT PRIMARY KEY, expediteur_id INT, contenu TEXT, date_envoi DATETIME DEFAULT CURRENT_TIMESTAMP, type VARCHAR(20), cours_id INT NULL, promotion_id INT NULL, fichier_id INT NULL, FOREIGN KEY (expediteur_id) REFERENCES utilisateurs(id), FOREIGN KEY (cours_id) REFERENCES cours(id) ON DELETE CASCADE, FOREIGN KEY (promotion_id) REFERENCES promotions(id) ON DELETE CASCADE, FOREIGN KEY (fichier_id) REFERENCES fichiers(id) ON DELETE SET NULL)");
        $db->exec("CREATE TABLE IF NOT EXISTS messages_destinataires (message_id INT, destinataire_id INT, lu BOOLEAN DEFAULT FALSE, PRIMARY KEY (message_id, destinataire_id), FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE, FOREIGN KEY (destinataire_id) REFERENCES utilisateurs(id))");
        $db->exec("CREATE TABLE IF NOT EXISTS convocations (id INT AUTO_INCREMENT PRIMARY KEY, message_id INT, objet VARCHAR(255), date_heure DATETIME, lieu VARCHAR(255), FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE)");
        $db->exec("CREATE TABLE IF NOT EXISTS annonces (id INT AUTO_INCREMENT PRIMARY KEY, titre VARCHAR(255), contenu TEXT, date_publication DATETIME DEFAULT CURRENT_TIMESTAMP, auteur_id INT)");
        $hash = password_hash('password123', PASSWORD_BCRYPT);
        $db->exec("SET FOREIGN_KEY_CHECKS = 0");
        $db->exec("TRUNCATE TABLE utilisateurs");
        $db->exec("SET FOREIGN_KEY_CHECKS = 1");
        $stmt = $db->prepare("INSERT INTO utilisateurs (id, nom, prenom, email, mot_de_passe, role) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([1, 'Banzolele', 'Samiel', 'etudiant@test.com', $hash, 'etudiant']);
        $stmt->execute([2, 'Bemba', 'Daniel', 'etudiant2@test.com', $hash, 'etudiant']);
        $stmt->execute([3, 'Mampuya', 'Professeur', 'enseignant@test.com', $hash, 'enseignant']);
        $stmt->execute([4, 'Kabeya', 'Deo', 'enseignant2@test.com', $hash, 'enseignant']);
        $stmt->execute([5, 'Bahati', 'Assistant', 'assistant@test.com', $hash, 'assistant']);
        $stmt->execute([6, 'Kutangila', 'Doyen', 'doyen@test.com', $hash, 'doyen']);
        $stmt->execute([7, 'Manpuya', 'Vice-Doyen', 'vicedoyen@test.com', $hash, 'vice-doyen']);
        $stmt->execute([8, 'Rolly', 'Apparitaire', 'apparitaire@test.com', $hash, 'apparitaire']);
        $db->exec("INSERT IGNORE INTO promotions (id, nom) VALUES (1, 'L2 FASI'), (2, 'L3 Info')");
        $db->exec("INSERT IGNORE INTO cours (id, nom, promotion_id) VALUES (1, 'PHP POO', 1), (2, 'Système Embarqué', 1)");
    }

}

