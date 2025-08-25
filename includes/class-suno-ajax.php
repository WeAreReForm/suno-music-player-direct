<?php
/**
 * Gestionnaire AJAX
 */

if (!defined('ABSPATH')) {
    exit;
}

class SunoAjax {
    
    /**
     * Gérer l'upload de piste
     */
    public function handle_track_upload() {
        check_ajax_referer('suno_upload_nonce', 'nonce');
        
        if (!is_user_logged_in() || !current_user_can('upload_suno_tracks')) {
            wp_send_json_error('Permission refusée');
        }
        
        require_once SUNO_PLAYER_PATH . 'includes/class-suno-upload.php';
        $uploader = new SunoUpload();
        
        $result = $uploader->handle_upload($_FILES['audio_file'] ?? null);
        
        if ($result['success']) {
            // Enregistrer en base de données
            $track_data = array(
                'title' => sanitize_text_field($_POST['title'] ?? 'Sans titre'),
                'artist' => sanitize_text_field($_POST['artist'] ?? ''),
                'album' => sanitize_text_field($_POST['album'] ?? ''),
                'genre' => sanitize_text_field($_POST['genre'] ?? ''),
                'description' => sanitize_textarea_field($_POST['description'] ?? ''),
                'file_url' => $result['url'],
                'file_path' => $result['path'],
                'user_id' => get_current_user_id(),
                'status' => 'public'
            );
            
            if (SunoDatabase::insert_track($track_data)) {
                wp_send_json_success(array(
                    'message' => 'Piste uploadée avec succès',
                    'track' => $track_data
                ));
            } else {
                wp_send_json_error('Erreur lors de l\'enregistrement en base de données');
            }
        } else {
            wp_send_json_error($result['message']);
        }
    }
    
    /**
     * Gérer la suppression de piste
     */
    public function handle_track_delete() {
        check_ajax_referer('suno_admin_nonce', 'nonce');
        
        if (!current_user_can('delete_suno_tracks')) {
            wp_send_json_error('Permission refusée');
        }
        
        $track_id = intval($_POST['track_id'] ?? 0);
        
        if (!$track_id) {
            wp_send_json_error('ID de piste invalide');
        }
        
        // Récupérer la piste pour supprimer le fichier
        global $wpdb;
        $table = $wpdb->prefix . 'suno_tracks';
        $track = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $track_id
        ));
        
        if ($track) {
            // Supprimer le fichier physique
            if (file_exists($track->file_path)) {
                unlink($track->file_path);
            }
            
            // Supprimer de la base de données
            if (SunoDatabase::delete_track($track_id)) {
                wp_send_json_success('Piste supprimée');
            } else {
                wp_send_json_error('Erreur lors de la suppression');
            }
        } else {
            wp_send_json_error('Piste introuvable');
        }
    }
    
    /**
     * Obtenir les données de playlist
     */
    public function get_playlist_data() {
        $playlist_id = intval($_POST['playlist_id'] ?? 0);
        $user_id = intval($_POST['user_id'] ?? null);
        
        $tracks = SunoDatabase::get_tracks(array(
            'user_id' => $user_id,
            'limit' => 50
        ));
        
        $playlist_data = array();
        
        foreach ($tracks as $track) {
            $playlist_data[] = array(
                'id' => $track->id,
                'title' => $track->title,
                'artist' => $track->artist,
                'album' => $track->album,
                'url' => $track->file_url,
                'thumbnail' => $track->thumbnail_url,
                'duration' => $track->duration
            );
        }
        
        wp_send_json_success($playlist_data);
    }
    
    /**
     * Mettre à jour le compteur de lecture
     */
    public function update_play_count() {
        $track_id = intval($_POST['track_id'] ?? 0);
        
        if (!$track_id) {
            wp_send_json_error('ID de piste invalide');
        }
        
        SunoDatabase::increment_play_count($track_id);
        
        wp_send_json_success('Compteur mis à jour');
    }
}