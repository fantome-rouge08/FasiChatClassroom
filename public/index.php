<?php

$request = $_SERVER['REQUEST_URI'];

$basePath = '/FasiChatClassroom/public';


// Nettoie l'URL pour n'avoir que le chemin relatif
$path = parse_url($request, PHP_URL_PATH);
$path = str_replace($basePath, '', $path);
$path = trim($path, '/');


switch ($path) {
    case '':
    case 'login':
    case 'login.php':
        require __DIR__ . '/../views/login.php';
        break;
    case 'login-handler':
        require __DIR__ . '/../src/Controllers/LoginController.php';
        break;

    case 'dashboard_etudiant':
    case 'dashboard_etudiant.php':
        require __DIR__ . '/../views/dashboard_etudiant.php';
        break;
    case 'dashboard_enseignant':
    case 'dashboard_enseignant.php':
        require __DIR__ . '/../views/dashboard_enseignant.php';
        break;
    case 'dashboard_admin':
    case 'dashboard_admin.php':
        require __DIR__ . '/../views/dashboard_admin.php';
        break;
    case 'dashboard_vicedoyen':
    case 'dashboard_vicedoyen.php':
        require __DIR__ . '/../views/dashboard_vicedoyen.php';
        break;
    case 'dashboard_apparitaire':
    case 'dashboard_apparitaire.php':
        require __DIR__ . '/../views/dashboard_apparitaire.php';
        break;
    case 'file-upload':
        session_start();
        if (isset($_SESSION['user'])) {
            require_once __DIR__ . '/../src/Autoloader.php';
            require_once __DIR__ . '/../database/Database.php';
            
            $dbInstance = new Database();
            $db = $dbInstance->getConnection();
            
            $fileModel = new Fichier($db);
            if (isset($_FILES['file'])) {
                $fileId = $fileModel->upload($_FILES['file']);
                ob_clean();
                header('Content-Type: application/json');
                echo json_encode(['success' => (bool)$fileId, 'file_id' => $fileId]);
            } else {
                ob_clean();
                header('HTTP/1.1 400 Bad Request');
                echo json_encode(['error' => 'Aucun fichier reçu']);
            }
        } else {
            ob_clean();
            header('HTTP/1.1 403 Forbidden');
            echo json_encode(['error' => 'Non connecté']);
        }
        exit();
        break;
    case 'message-send':
        ob_clean();
        session_start();
        if (isset($_SESSION['user'])) {
            require_once __DIR__ . '/../src/Autoloader.php';
            require_once __DIR__ . '/../database/Database.php';
            
            $dbInstance = new Database();
            $db = $dbInstance->getConnection();
            
            $msgModel = new Message($db);
            
            try {
                $expediteurId = $_SESSION['user']['id'];
                $contenu = $_POST['contenu'] ?? '';
                $type = $_POST['type'] ?? 'prive';
                $destinataireId = !empty($_POST['destinataire_id']) ? intval($_POST['destinataire_id']) : null;
                $coursId = !empty($_POST['cours_id']) ? intval($_POST['cours_id']) : null;
                $promotionId = !empty($_POST['promotion_id']) ? intval($_POST['promotion_id']) : null;
                $fichierId = !empty($_POST['fichier_id']) ? intval($_POST['fichier_id']) : null;
                
                $success = $msgModel->envoyer($expediteurId, $contenu, $type, $destinataireId, $coursId, $promotionId, $fichierId);
                
                ob_clean();
                header('Content-Type: application/json');
                echo json_encode(['success' => $success]);
            } catch (Exception $e) {
                ob_clean();
                header('Content-Type: application/json');
                header('HTTP/1.1 400 Bad Request');
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        } else {
            header('HTTP/1.1 403 Forbidden');
            echo json_encode(['error' => 'Non connecté']);
        }
        exit();
        break;
    case 'message-poll':
        session_start();
        if (isset($_SESSION['user'])) {
            require_once __DIR__ . '/../src/Autoloader.php';
            require_once __DIR__ . '/../database/Database.php';
            
            $dbInstance = new Database();
            $db = $dbInstance->getConnection();
            
            $msgModel = new Message($db);
            
            $type = $_GET['type'] ?? 'prive';
            $id = !empty($_GET['id']) ? intval($_GET['id']) : 0;
            $currentUserId = $_SESSION['user']['id'];
            
            $messages = [];
            if ($type === 'prive') {
                $messages = $msgModel->recupererPrives($currentUserId, $id);
            } elseif ($type === 'public') {
                $messages = $msgModel->recupererPublics($id);
            } elseif ($type === 'mur') {
                $messages = $msgModel->recupererMur($id);
            }
            
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode($messages);
        } else {
            ob_clean();
            header('HTTP/1.1 403 Forbidden');
            echo json_encode(['error' => 'Non connecté']);
        }
        exit();
        break;
    case 'convocation-send':
        session_start();
        if (isset($_SESSION['user']) && in_array($_SESSION['user']['role'], ['doyen', 'vice-doyen'])) {
            require_once __DIR__ . '/../src/Autoloader.php';
            require_once __DIR__ . '/../database/Database.php';
            
            $dbInstance = new Database();
            $db = $dbInstance->getConnection();
            
            $user = $_SESSION['user'];
            // On instancie la classe correspondante (Doyen ou ViceDoyen)
            $roleClass = ucfirst($user['role']) === 'Doyen' ? 'Doyen' : 'ViceDoyen';
            $admin = new $roleClass($db, $user);
            
            if ($admin->convoquer($db, $_POST['objet'], $_POST['date'], $_POST['heure'], $_POST['lieu'], $_POST['message'] ?? '')) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
            } else {
                header('HTTP/1.1 500 Internal Server Error');
                echo json_encode(['success' => false, 'error' => 'Erreur lors de l\'envoi']);
            }
        } else {
            header('HTTP/1.1 403 Forbidden');
            echo json_encode(['error' => 'Accès refusé']);
        }
        exit();
        break;

    case 'valve-publish':
        session_start();
        ob_clean();
        header('Content-Type: application/json');
        if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'apparitaire') {
            require_once __DIR__ . '/../src/Autoloader.php';
            require_once __DIR__ . '/../database/Database.php';
            $dbInstance = new Database();
            $db = $dbInstance->getConnection();
            $apparitaire = new Apparitaire($db, $_SESSION['user']);
            try {
                $success = $apparitaire->publierAnnonce($_POST['titre'], $_POST['contenu']);
                echo json_encode(['success' => $success]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        } else {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Accès refusé']);
        }
        exit();
        break;

    case 'valve-delete':
        session_start();
        ob_clean();
        header('Content-Type: application/json');
        if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'apparitaire') {
            require_once __DIR__ . '/../src/Autoloader.php';
            require_once __DIR__ . '/../database/Database.php';
            $dbInstance = new Database();
            $db = $dbInstance->getConnection();
            $apparitaire = new Apparitaire($db, $_SESSION['user']);
            $success = $apparitaire->supprimerAnnonce(intval($_POST['id']));
            echo json_encode(['success' => $success]);
        } else {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Accès refusé']);
        }
        exit();
        break;

    case 'valve-edit':
        session_start();
        ob_clean();
        header('Content-Type: application/json');
        if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'apparitaire') {
            require_once __DIR__ . '/../src/Autoloader.php';
            require_once __DIR__ . '/../database/Database.php';
            $dbInstance = new Database();
            $db = $dbInstance->getConnection();
            $apparitaire = new Apparitaire($db, $_SESSION['user']);
            $success = $apparitaire->modifierAnnonce(intval($_POST['id']), $_POST['titre'], $_POST['contenu']);
            echo json_encode(['success' => $success]);
        } else {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Accès refusé']);
        }
        exit();
        break;

    case 'valve-annonces':
        session_start();
        ob_clean();
        header('Content-Type: application/json');
        if (isset($_SESSION['user'])) {
            require_once __DIR__ . '/../src/Autoloader.php';
            require_once __DIR__ . '/../database/Database.php';
            $dbInstance = new Database();
            $db = $dbInstance->getConnection();
            $stmt = $db->query("SELECT a.*, u.nom, u.prenom, u.role FROM annonces a JOIN utilisateurs u ON a.auteur_id = u.id ORDER BY a.date_publication DESC");
            $annonces = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($annonces);
        } else {
            http_response_code(403);
            echo json_encode([]);
        }
        exit();
        break;

    case 'valve':

    case 'valve.php':
        require __DIR__ . '/../views/valve.php';
        break;

    case 'seed':
        require_once __DIR__ . '/../database/Database.php';
        $db = new Database();
        $db->seed();
        echo "<h3 style='color:green;font-family:sans-serif;'>✅ Base de données initialisée</h3>";
        echo "<p><a href='/FasiChatClassroom/public/login'>→ Connexion</a></p>";
        exit();

    default:
        require __DIR__ . '/../views/login.php';
        break;
}
