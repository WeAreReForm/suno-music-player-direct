<?php
/**
 * Gestion des requêtes AJAX
 *
 * @package SunoMusicPlayerDirect
 */

if (!defined('ABSPATH')) {
    exit;
}

class Suno_Ajax {
    
    public function __construct() {
        // Upload
        add_action('wp_ajax_suno_upload_track', array($this, 'handle_upload'));
        add_action('wp_ajax_nopriv_suno_upload_track', array($this, 'handle_upload_public'));
        
        // Statistiques
        add_action('wp_ajax_suno_track_play', array($this, 'track_play'));
        add_action('wp_ajax_nopriv_suno_track_play', array($this, 'track_play'));
        
        add_action('wp_ajax_suno_track_download', array($this, 'track_download'));
        add_action('wp_ajax_nopriv_suno_track_download', array($this, 'track_download'));
        
        // Playlist
        add_action('wp_ajax_suno_get_playlist', array($this, 'get_playlist'));
        add_action('wp_ajax_nopriv_suno_get_playlist', array($this, 'get_playlist'));
    }
    
    /**
     * Gère l'upload d'une piste
     */
    public function handle_upload() {
        check_ajax_referer('suno_upload', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error('Vous devez être connecté.');
        }
        
        if (!isset($_FILES['audio_file'])) {
            wp_send_json_error('Aucun fichier audio fourni.');
        }
        
        $settings = get_option('suno_player_settings');
        $audio_file = $_FILES['audio_file'];
        
        // Vérifier la taille
        $max_size = $settings['max_file_size'] * 1024 * 1024;
        if ($audio_file['size'] > $max_size) {
            wp_send_json_error('Fichier trop volumineux. Maximum : ' . $settings['max_file_size'] . ' MB');
        }
        
        // Vérifier le type MIME
        $allowed_types = array('audio/mpeg', 'audio/mp3', 'audio/ogg', 'audio/wav', 'audio/x-wav');
        if (!in_array($audio_file['type'], $allowed_types)) {
            wp_send_json_error('Type de fichier non autorisé.');
        }
        
        // Upload du fichier
        $upload = wp_handle_upload($audio_file, array('test_form' => false));
        
        if (isset($upload['error'])) {
            wp_send_json_error($upload['error']);
        }
        
        // Traiter l'image de couverture
        $cover_url = '';
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === 0) {
            $cover_upload = wp_handle_upload($_FILES['cover_image'], array('test_form' => false));
            if (!isset($cover_upload['error'])) {
                $cover_url = $cover_upload['url'];
            }
        }
        
        // Sauvegarder en base de données
        $track_id = Suno_Database::insert_track(array(
            'title' => sanitize_text_field($_POST['title']),
            'artist' => sanitize_text_field($_POST['artist'] ?: 'Créé avec Suno AI'),
            'album' => sanitize_text_field($_POST['album']),
            'genre' => sanitize_text_field($_POST['genre']),
            'description' => sanitize_textarea_field($_POST['description']),
            'file_path' => $upload['file'],
            'file_url' => $upload['url'],
            'cover_url' => $cover_url,
            'user_id' => get_current_user_id(),
            'status' => 'published'
        ));
        
        if (!$track_id) {
            wp_send_json_error('Erreur lors de la sauvegarde.');
        }
        
        wp_send_json_success(array(
            'message' => 'Chanson uploadée avec succès !',
            'track_id' => $track_id,
            'file_url' => $upload['url']
        ));
    }
    
    /**
     * Upload public (si autorisé)
     */
    public function handle_upload_public() {
        wp_send_json_error('Upload public non autorisé. Veuillez vous connecter.');
    }
    
    /**
     * Enregistre une lecture
     */
    public function track_play() {
        $track_id = intval($_POST['track_id']);
        
        if ($track_id > 0) {
            Suno_Database::increment_plays($track_id);
            wp_send_json_success();
        }
        
        wp_send_json_error();
    }
    
    /**
     * Enregistre un téléchargement
     */
    public function track_download() {
        $track_id = intval($_POST['track_id']);
        
        if ($track_id > 0) {
            Suno_Database::increment_downloads($track_id);
            wp_send_json_success();
        }
        
        wp_send_json_error();
    }
    
    /**
     * Récupère une playlist en JSON
     */
    public function get_playlist() {
        $args = array(
            'limit' => intval($_POST['limit'] ?? 20),
            'offset' => intval($_POST['offset'] ?? 0),
            'genre' => sanitize_text_field($_POST['genre'] ?? ''),
            'user_id' => intval($_POST['user_id'] ?? 0)
        );
        
        $tracks = Suno_Database::get_tracks($args);
        
        wp_send_json_success($tracks);
    }
}

new Suno_Ajax();
