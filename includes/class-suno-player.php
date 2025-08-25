<?php
/**
 * Classe principale du plugin Suno Music Player Direct
 *
 * @package SunoMusicPlayerDirect
 * @since 2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SunoMusicPlayerDirect {
    
    /**
     * Instance unique du plugin
     */
    private static $instance = null;
    
    /**
     * Loader pour gérer les hooks
     */
    protected $loader;
    
    /**
     * Version du plugin
     */
    protected $version;
    
    /**
     * Constructeur
     */
    public function __construct() {
        $this->version = SUNO_PLAYER_VERSION;
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }
    
    /**
     * Charger les dépendances
     */
    private function load_dependencies() {
        require_once SUNO_PLAYER_PATH . 'includes/class-suno-loader.php';
        require_once SUNO_PLAYER_PATH . 'includes/class-suno-database.php';
        require_once SUNO_PLAYER_PATH . 'includes/class-suno-admin.php';
        require_once SUNO_PLAYER_PATH . 'includes/class-suno-public.php';
        require_once SUNO_PLAYER_PATH . 'includes/class-suno-shortcodes.php';
        require_once SUNO_PLAYER_PATH . 'includes/class-suno-ajax.php';
        require_once SUNO_PLAYER_PATH . 'includes/class-suno-upload.php';
        
        $this->loader = new SunoLoader();
    }
    
    /**
     * Définir les hooks admin
     */
    private function define_admin_hooks() {
        $plugin_admin = new SunoAdmin($this->version);
        
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
        $this->loader->add_action('admin_init', $plugin_admin, 'register_settings');
    }
    
    /**
     * Définir les hooks publics
     */
    private function define_public_hooks() {
        $plugin_public = new SunoPublic($this->version);
        $plugin_shortcodes = new SunoShortcodes();
        $plugin_ajax = new SunoAjax();
        
        // Styles et scripts
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        
        // Shortcodes
        $this->loader->add_shortcode('suno_playlist', $plugin_shortcodes, 'render_playlist');
        $this->loader->add_shortcode('suno_player', $plugin_shortcodes, 'render_player');
        $this->loader->add_shortcode('suno_upload_form', $plugin_shortcodes, 'render_upload_form');
        $this->loader->add_shortcode('suno_gallery', $plugin_shortcodes, 'render_gallery');
        
        // Ajax handlers
        $this->loader->add_action('wp_ajax_suno_upload_track', $plugin_ajax, 'handle_track_upload');
        $this->loader->add_action('wp_ajax_nopriv_suno_upload_track', $plugin_ajax, 'handle_track_upload');
        $this->loader->add_action('wp_ajax_suno_delete_track', $plugin_ajax, 'handle_track_delete');
        $this->loader->add_action('wp_ajax_suno_get_playlist', $plugin_ajax, 'get_playlist_data');
        $this->loader->add_action('wp_ajax_nopriv_suno_get_playlist', $plugin_ajax, 'get_playlist_data');
        $this->loader->add_action('wp_ajax_suno_update_play_count', $plugin_ajax, 'update_play_count');
        $this->loader->add_action('wp_ajax_nopriv_suno_update_play_count', $plugin_ajax, 'update_play_count');
    }
    
    /**
     * Exécuter le plugin
     */
    public function run() {
        $this->loader->run();
    }
    
    /**
     * Activation du plugin
     */
    public static function activate() {
        require_once SUNO_PLAYER_PATH . 'includes/class-suno-database.php';
        SunoDatabase::create_tables();
        
        // Créer le dossier d'upload
        $upload_dir = wp_upload_dir();
        $suno_dir = $upload_dir['basedir'] . '/suno-music';
        
        if (!file_exists($suno_dir)) {
            wp_mkdir_p($suno_dir);
            
            // Créer .htaccess pour la sécurité
            $htaccess = $suno_dir . '/.htaccess';
            $content = "Options -Indexes\nAddType audio/mpeg .mp3\nAddType audio/mp4 .m4a\nAddType audio/ogg .ogg\n";
            file_put_contents($htaccess, $content);
        }
        
        // Ajouter les capacités
        $role = get_role('administrator');
        $role->add_cap('manage_suno_music');
        $role->add_cap('upload_suno_tracks');
        $role->add_cap('delete_suno_tracks');
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Désactivation du plugin
     */
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Obtenir l'instance unique
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}