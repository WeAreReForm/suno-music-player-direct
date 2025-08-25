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
    
    private $upload_dir;
    private $upload_url;
    private $version;
    
    public function __construct() {
        $this->version = SUNO_PLAYER_VERSION;
        $this->setup_upload_directories();
    }
    
    /**
     * Initialise le plugin
     */
    public function run() {
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->register_shortcodes();
    }
    
    /**
     * Configure les répertoires d'upload
     */
    private function setup_upload_directories() {
        $upload = wp_upload_dir();
        $this->upload_dir = $upload['basedir'] . '/suno-music';
        $this->upload_url = $upload['baseurl'] . '/suno-music';
        
        if (!file_exists($this->upload_dir)) {
            wp_mkdir_p($this->upload_dir);
            $this->create_htaccess();
        }
    }
    
    /**
     * Crée le fichier .htaccess pour sécuriser le dossier
     */
    private function create_htaccess() {
        $htaccess = $this->upload_dir . '/.htaccess';
        if (!file_exists($htaccess)) {
            $content = "Options -Indexes\n";
            $content .= "<FilesMatch '\.(mp3|ogg|wav)$'>\n";
            $content .= "Order Allow,Deny\n";
            $content .= "Allow from all\n";
            $content .= "</FilesMatch>\n";
            file_put_contents($htaccess, $content);
        }
    }
    
    /**
     * Charge les dépendances
     */
    private function load_dependencies() {
        require_once SUNO_PLAYER_PATH . 'includes/class-suno-database.php';
        require_once SUNO_PLAYER_PATH . 'includes/class-suno-admin.php';
        require_once SUNO_PLAYER_PATH . 'includes/class-suno-public.php';
        require_once SUNO_PLAYER_PATH . 'includes/class-suno-ajax.php';
        require_once SUNO_PLAYER_PATH . 'includes/class-suno-shortcodes.php';
    }
    
    /**
     * Enregistre les hooks admin
     */
    private function define_admin_hooks() {
        $admin = new Suno_Admin($this->version);
        
        add_action('admin_menu', array($admin, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($admin, 'enqueue_admin_scripts'));
        add_action('admin_init', array($admin, 'register_settings'));
    }
    
    /**
     * Enregistre les hooks publics
     */
    private function define_public_hooks() {
        $public = new Suno_Public($this->version);
        
        add_action('wp_enqueue_scripts', array($public, 'enqueue_scripts'));
        add_action('init', array($public, 'init'));
    }
    
    /**
     * Enregistre les shortcodes
     */
    private function register_shortcodes() {
        $shortcodes = new Suno_Shortcodes($this->upload_url);
        
        add_shortcode('suno_playlist', array($shortcodes, 'render_playlist'));
        add_shortcode('suno_upload_form', array($shortcodes, 'render_upload_form'));
        add_shortcode('suno_player', array($shortcodes, 'render_single_player'));
    }
    
    /**
     * Activation du plugin
     */
    public static function activate() {
        require_once SUNO_PLAYER_PATH . 'includes/class-suno-database.php';
        Suno_Database::create_tables();
        
        // Options par défaut
        add_option('suno_player_settings', array(
            'allow_downloads' => true,
            'show_playlist' => true,
            'autoplay' => false,
            'loop' => false,
            'primary_color' => '#6366f1',
            'max_file_size' => 50
        ));
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Désactivation du plugin
     */
    public static function deactivate() {
        flush_rewrite_rules();
    }
    
    /**
     * Accesseurs
     */
    public function get_upload_dir() {
        return $this->upload_dir;
    }
    
    public function get_upload_url() {
        return $this->upload_url;
    }
}
