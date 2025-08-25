<?php
/**
 * Upload handler for Suno Music Player Direct
 */

if (!defined('ABSPATH')) {
    exit;
}

class SunoUploadHandler {
    
    private $allowed_types = array('audio/mpeg', 'audio/mp3', 'audio/wav');
    private $max_file_size = 52428800; // 50MB
    
    public function __construct() {
        add_action('wp_ajax_suno_upload_track', array($this, 'handle_ajax_upload'));
        add_action('wp_ajax_nopriv_suno_upload_track', array($this, 'handle_ajax_upload'));
    }
    
    /**
     * Handle AJAX upload
     */
    public function handle_ajax_upload() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'suno_player_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check if public upload is allowed
        $settings = get_option('suno_player_settings', array());
        if (!is_user_logged_in() && empty($settings['public_upload'])) {
            wp_send_json_error('Upload public non autorisé');
        }
        
        // Check file
        if (!isset($_FILES['file'])) {
            wp_send_json_error('Aucun fichier reçu');
        }
        
        $file = $_FILES['file'];
        
        // Validate file type
        if (!in_array($file['type'], $this->allowed_types)) {
            wp_send_json_error('Type de fichier non autorisé. Utilisez MP3 ou WAV.');
        }
        
        // Validate file size
        $max_size = isset($settings['max_file_size']) ? $settings['max_file_size'] * 1048576 : $this->max_file_size;
        if ($file['size'] > $max_size) {
            wp_send_json_error('Fichier trop volumineux. Maximum : ' . ($max_size / 1048576) . 'MB');
        }
        
        // Handle upload
        $upload = wp_handle_upload($file, array('test_form' => false));
        
        if (isset($upload['error'])) {
            wp_send_json_error($upload['error']);
        }
        
        // Get metadata
        $title = !empty($_POST['title']) ? sanitize_text_field($_POST['title']) : pathinfo($file['name'], PATHINFO_FILENAME);
        $artist = !empty($_POST['artist']) ? sanitize_text_field($_POST['artist']) : 'Unknown Artist';
        $description = !empty($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';
        
        // Get duration (simplified)
        $duration = $this->get_audio_duration($upload['file']);
        
        // Save to database
        global $wpdb;
        $table_name = $wpdb->prefix . 'suno_tracks';
        
        $result = $wpdb->insert($table_name, array(
            'title' => $title,
            'artist' => $artist,
            'description' => $description,
            'file_url' => $upload['url'],
            'file_path' => $upload['file'],
            'file_size' => $file['size'],
            'file_type' => $file['type'],
            'duration' => $duration,
            'user_id' => get_current_user_id(),
            'allow_download' => 1,
            'created_at' => current_time('mysql')
        ));
        
        if ($result === false) {
            // Delete uploaded file on database error
            if (file_exists($upload['file'])) {
                unlink($upload['file']);
            }
            wp_send_json_error('Erreur lors de l\'enregistrement en base de données');
        }
        
        wp_send_json_success(array(
            'id' => $wpdb->insert_id,
            'title' => $title,
            'artist' => $artist,
            'url' => $upload['url'],
            'duration' => $duration
        ));
    }
    
    /**
     * Get audio duration (simplified version)
     */
    private function get_audio_duration($file_path) {
        // In a production environment, you would use getID3 library
        // For now, return a default value
        if (class_exists('getID3')) {
            $getID3 = new getID3();
            $file_info = $getID3->analyze($file_path);
            if (isset($file_info['playtime_seconds'])) {
                return round($file_info['playtime_seconds']);
            }
        }
        
        return 180; // Default 3 minutes
    }
    
    /**
     * Clean filename
     */
    private function clean_filename($filename) {
        $filename = sanitize_file_name($filename);
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        return $filename;
    }
    
    /**
     * Generate unique filename
     */
    private function generate_unique_filename($filename) {
        $info = pathinfo($filename);
        $ext = !empty($info['extension']) ? '.' . $info['extension'] : '';
        $name = basename($filename, $ext);
        
        return $name . '_' . uniqid() . $ext;
    }
}

// Initialize upload handler
new SunoUploadHandler();