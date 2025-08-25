<?php
/**
 * Gestionnaire d'upload
 */

if (!defined('ABSPATH')) {
    exit;
}

class SunoUpload {
    
    private $allowed_types = array('mp3', 'm4a', 'ogg', 'wav', 'aac');
    private $max_file_size = 104857600; // 100 MB
    
    /**
     * Gérer l'upload d'un fichier
     */
    public function handle_upload($file) {
        if (!$file || !isset($file['tmp_name'])) {
            return array(
                'success' => false,
                'message' => 'Aucun fichier reçu'
            );
        }
        
        // Vérifier les erreurs d'upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return array(
                'success' => false,
                'message' => $this->get_upload_error_message($file['error'])
            );
        }
        
        // Vérifier la taille
        if ($file['size'] > $this->max_file_size) {
            return array(
                'success' => false,
                'message' => 'Le fichier est trop volumineux (max 100 MB)'
            );
        }
        
        // Vérifier l'extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $this->allowed_types)) {
            return array(
                'success' => false,
                'message' => 'Type de fichier non autorisé. Formats acceptés : ' . implode(', ', $this->allowed_types)
            );
        }
        
        // Vérifier le type MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $allowed_mimes = array(
            'audio/mpeg',
            'audio/mp3',
            'audio/mp4',
            'audio/m4a',
            'audio/ogg',
            'audio/wav',
            'audio/x-wav',
            'audio/aac'
        );
        
        if (!in_array($mime_type, $allowed_mimes)) {
            return array(
                'success' => false,
                'message' => 'Type MIME invalide : ' . $mime_type
            );
        }
        
        // Créer le dossier de destination
        $upload_dir = wp_upload_dir();
        $suno_dir = $upload_dir['basedir'] . '/suno-music';
        $year_month = date('Y/m');
        $target_dir = $suno_dir . '/' . $year_month;
        
        if (!file_exists($target_dir)) {
            wp_mkdir_p($target_dir);
        }
        
        // Générer un nom unique
        $filename = uniqid('suno_') . '_' . sanitize_file_name($file['name']);
        $target_path = $target_dir . '/' . $filename;
        $target_url = $upload_dir['baseurl'] . '/suno-music/' . $year_month . '/' . $filename;
        
        // Déplacer le fichier
        if (!move_uploaded_file($file['tmp_name'], $target_path)) {
            return array(
                'success' => false,
                'message' => 'Erreur lors du déplacement du fichier'
            );
        }
        
        // Obtenir les métadonnées audio
        $metadata = $this->get_audio_metadata($target_path);
        
        return array(
            'success' => true,
            'path' => $target_path,
            'url' => $target_url,
            'filename' => $filename,
            'metadata' => $metadata
        );
    }
    
    /**
     * Obtenir les métadonnées d'un fichier audio
     */
    private function get_audio_metadata($file_path) {
        $metadata = array(
            'duration' => 0,
            'bitrate' => 0,
            'sample_rate' => 0
        );
        
        // Utiliser getID3 si disponible
        if (class_exists('getID3')) {
            $getID3 = new getID3();
            $info = $getID3->analyze($file_path);
            
            if (isset($info['playtime_seconds'])) {
                $metadata['duration'] = round($info['playtime_seconds']);
            }
            if (isset($info['bitrate'])) {
                $metadata['bitrate'] = $info['bitrate'];
            }
            if (isset($info['audio']['sample_rate'])) {
                $metadata['sample_rate'] = $info['audio']['sample_rate'];
            }
        }
        
        return $metadata;
    }
    
    /**
     * Obtenir le message d'erreur d'upload
     */
    private function get_upload_error_message($error_code) {
        switch ($error_code) {
            case UPLOAD_ERR_INI_SIZE:
                return 'Le fichier dépasse la limite de upload_max_filesize';
            case UPLOAD_ERR_FORM_SIZE:
                return 'Le fichier dépasse la limite MAX_FILE_SIZE';
            case UPLOAD_ERR_PARTIAL:
                return 'Le fichier n\'a été que partiellement téléchargé';
            case UPLOAD_ERR_NO_FILE:
                return 'Aucun fichier n\'a été téléchargé';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Dossier temporaire manquant';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Échec de l\'écriture du fichier sur le disque';
            case UPLOAD_ERR_EXTENSION:
                return 'Une extension PHP a arrêté l\'upload';
            default:
                return 'Erreur inconnue lors de l\'upload';
        }
    }
}