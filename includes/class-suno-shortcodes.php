<?php
/**
 * Gestion des shortcodes
 *
 * @package SunoMusicPlayerDirect
 */

if (!defined('ABSPATH')) {
    exit;
}

class Suno_Shortcodes {
    
    private $upload_url;
    
    public function __construct($upload_url) {
        $this->upload_url = $upload_url;
    }
    
    /**
     * Shortcode [suno_playlist]
     */
    public function render_playlist($atts) {
        $atts = shortcode_atts(array(
            'title' => 'Ma Playlist Suno',
            'limit' => 20,
            'user_id' => 0,
            'genre' => '',
            'show_upload' => false,
            'show_download' => true
        ), $atts);
        
        // Récupérer les pistes
        $tracks = Suno_Database::get_tracks(array(
            'limit' => intval($atts['limit']),
            'user_id' => intval($atts['user_id']),
            'genre' => sanitize_text_field($atts['genre'])
        ));
        
        // Générer un ID unique pour cette playlist
        $playlist_id = 'suno-playlist-' . wp_rand(1000, 9999);
        
        ob_start();
        include SUNO_PLAYER_PATH . 'templates/playlist.php';
        return ob_get_clean();
    }
    
    /**
     * Shortcode [suno_upload_form]
     */
    public function render_upload_form($atts) {
        if (!is_user_logged_in()) {
            return '<div class="suno-notice">Vous devez être connecté pour uploader des chansons.</div>';
        }
        
        $atts = shortcode_atts(array(
            'redirect' => '',
            'categories' => true
        ), $atts);
        
        ob_start();
        include SUNO_PLAYER_PATH . 'templates/upload-form.php';
        return ob_get_clean();
    }
    
    /**
     * Shortcode [suno_player] pour un player unique
     */
    public function render_single_player($atts) {
        $atts = shortcode_atts(array(
            'id' => 0,
            'url' => '',
            'title' => '',
            'artist' => '',
            'autoplay' => false,
            'loop' => false
        ), $atts);
        
        if ($atts['id']) {
            $track = Suno_Database::get_track($atts['id']);
            if (!$track) {
                return '<div class="suno-notice">Piste introuvable.</div>';
            }
            $atts['url'] = $track->file_url;
            $atts['title'] = $track->title;
            $atts['artist'] = $track->artist;
        }
        
        if (empty($atts['url'])) {
            return '<div class="suno-notice">URL du fichier audio requise.</div>';
        }
        
        ob_start();
        include SUNO_PLAYER_PATH . 'templates/single-player.php';
        return ob_get_clean();
    }
}
