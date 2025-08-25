<?php
/**
 * Partie publique du plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class SunoPublic {
    
    private $version;
    
    public function __construct($version) {
        $this->version = $version;
    }
    
    /**
     * Enregistrer les styles publics
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            'suno-player',
            SUNO_PLAYER_URL . 'assets/css/player.css',
            array(),
            $this->version
        );
        
        // Ajouter WaveSurfer pour le waveform
        wp_enqueue_style(
            'suno-wavesurfer',
            'https://unpkg.com/wavesurfer.js@7/dist/wavesurfer.min.css',
            array(),
            '7.0.0'
        );
    }
    
    /**
     * Enregistrer les scripts publics
     */
    public function enqueue_scripts() {
        // WaveSurfer.js pour le waveform
        wp_enqueue_script(
            'wavesurfer',
            'https://unpkg.com/wavesurfer.js@7/dist/wavesurfer.min.js',
            array(),
            '7.0.0',
            true
        );
        
        // Amplitude.js comme alternative
        wp_enqueue_script(
            'amplitudejs',
            'https://cdn.jsdelivr.net/npm/amplitudejs@5.3.2/dist/amplitude.min.js',
            array(),
            '5.3.2',
            true
        );
        
        // Script principal du player
        wp_enqueue_script(
            'suno-player',
            SUNO_PLAYER_URL . 'assets/js/player.js',
            array('jquery', 'wavesurfer'),
            $this->version,
            true
        );
        
        // Script d'upload
        if (is_user_logged_in()) {
            wp_enqueue_script(
                'suno-upload',
                SUNO_PLAYER_URL . 'assets/js/upload.js',
                array('jquery'),
                $this->version,
                true
            );
        }
        
        // Localisation
        wp_localize_script('suno-player', 'suno_player', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('suno_player_nonce'),
            'upload_nonce' => wp_create_nonce('suno_upload_nonce'),
            'is_logged_in' => is_user_logged_in(),
            'settings' => get_option('suno_player_settings', array()),
            'i18n' => array(
                'play' => __('Lecture', 'suno-music-player'),
                'pause' => __('Pause', 'suno-music-player'),
                'next' => __('Suivant', 'suno-music-player'),
                'previous' => __('Précédent', 'suno-music-player'),
                'mute' => __('Muet', 'suno-music-player'),
                'unmute' => __('Son', 'suno-music-player'),
                'download' => __('Télécharger', 'suno-music-player'),
                'share' => __('Partager', 'suno-music-player'),
                'loading' => __('Chargement...', 'suno-music-player'),
                'error' => __('Erreur de chargement', 'suno-music-player')
            )
        ));
    }
}