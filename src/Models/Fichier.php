<?php
// src/Models/Fichier.php

class Fichier {
    private $db;
    private $uploadDir = 'public/assets/uploads/';

    public function __construct($db) {
        $this->db = $db;
    }

    // Téléverser un fichier et le compresser si c'est une image
    public function upload($fileArray) {
        if (!isset($fileArray['tmp_name']) || $fileArray['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        $originalName = $fileArray['name'];
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $safeName = uniqid('file_', true) . '.' . $extension;
        $destPath = __DIR__ . '/../' . $this->uploadDir . $safeName;

        // Gestion de la compression pour les images (JPEG/PNG)
        if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png'])) {
            $this->compressImage($fileArray['tmp_name'], $destPath, $extension);
        } else {
            // Pour les autres fichiers (PDF, Word, Vidéo), on déplace simplement
            if (!move_uploaded_file($fileArray['tmp_name'], $destPath)) {
                return false;
            }
        }

        // Enregistrement en base de données
        $query = "INSERT INTO fichiers (nom_origine, nom_stockage, chemin, type_mime, taille, date_upload) 
                  VALUES (:nom_orig, :nom_stock, :chemin, :mime, :taille, NOW())";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            'nom_orig' => $originalName,
            'nom_stock' => $safeName,
            'chemin' => 'assets/uploads/' . $safeName,
            'mime' => $fileArray['type'],
            'taille' => $fileArray['size']
        ]);

        return $this->db->lastInsertId();
    }

    private function compressImage($source, $dest, $extension) {
        list($width, $height) = getimagesize($source);
        $newWidth = 800;
        $newHeight = ($height / $width) * $newWidth;

        $image = ($extension === 'png') ? imagecreatefrompng($source) : imagecreatefromjpeg($source);
        $canvas = imagecreatetruecolor($newWidth, $newHeight);
        
        imagecopyresampled($canvas, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        if ($extension === 'png') {
            imagepng($canvas, $dest, 6); 
        } else {
            imagejpeg($canvas, $dest, 75); 
        }
        
        imagedestroy($image);
        imagedestroy($canvas);
    }
}
