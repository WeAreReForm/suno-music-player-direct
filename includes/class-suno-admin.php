<?php
/**
 * Partie administration du plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class SunoAdmin {
    
    private $version;
    
    public function __construct($version) {
        $this->version = $version;
    }
    
    /**
     * Enregistrer les styles admin
     */
    public function enqueue_styles($hook) {
        // Vérifier qu'on est sur une page du plugin
        if (strpos($hook, 'suno-music') === false && strpos($hook, 'suno-') === false) {
            return;
        }
        
        wp_enqueue_style(
            'suno-admin',
            SUNO_PLAYER_URL . 'assets/css/admin.css',
            array(),
            $this->version
        );
        
        // Ajouter les dashicons
        wp_enqueue_style('dashicons');
    }
    
    /**
     * Enregistrer les scripts admin
     */
    public function enqueue_scripts($hook) {
        // Vérifier qu'on est sur une page du plugin
        if (strpos($hook, 'suno-music') === false && strpos($hook, 'suno-') === false) {
            return;
        }
        
        // S'assurer que jQuery est chargé
        wp_enqueue_script('jquery');
        
        wp_enqueue_script(
            'suno-admin',
            SUNO_PLAYER_URL . 'assets/js/admin.js',
            array('jquery'),
            $this->version,
            true
        );
        
        wp_localize_script('suno-admin', 'suno_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('suno_admin_nonce'),
            'debug' => WP_DEBUG
        ));
    }
    
    /**
     * Ajouter le menu admin
     */
    public function add_plugin_admin_menu() {
        add_menu_page(
            'Suno Music Player',
            'Suno Music',
            'manage_suno_music',
            'suno-music',
            array($this, 'display_admin_page'),
            'dashicons-playlist-audio',
            25
        );
        
        add_submenu_page(
            'suno-music',
            'Toutes les pistes',
            'Pistes',
            'manage_suno_music',
            'suno-music',
            array($this, 'display_admin_page')
        );
        
        add_submenu_page(
            'suno-music',
            'Playlists',
            'Playlists',
            'manage_suno_music',
            'suno-playlists',
            array($this, 'display_playlists_page')
        );
        
        add_submenu_page(
            'suno-music',
            'Statistiques',
            'Statistiques',
            'manage_suno_music',
            'suno-stats',
            array($this, 'display_stats_page')
        );
        
        add_submenu_page(
            'suno-music',
            'Réglages',
            'Réglages',
            'manage_suno_music',
            'suno-settings',
            array($this, 'display_settings_page')
        );
        
        // Ajouter la page de débogage
        add_submenu_page(
            'suno-music',
            'Débogage',
            'Débogage',
            'manage_suno_music',
            'suno-debug',
            array($this, 'display_debug_page')
        );
    }
    
    /**
     * Page principale admin
     */
    public function display_admin_page() {
        // Vérifier que les tables existent
        global $wpdb;
        $table_name = $wpdb->prefix . 'suno_tracks';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            // Créer les tables si elles n'existent pas
            require_once SUNO_PLAYER_PATH . 'includes/class-suno-database.php';
            SunoDatabase::create_tables();
        }
        
        $tracks = SunoDatabase::get_tracks(array('limit' => 100));
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?>
                <a href="#" class="page-title-action" id="suno-add-track">Ajouter une piste</a>
            </h1>
            
            <!-- Message de débogage -->
            <?php if (WP_DEBUG): ?>
            <div class="notice notice-info">
                <p>Mode débogage activé. <a href="<?php echo admin_url('admin.php?page=suno-debug'); ?>">Voir la page de débogage</a></p>
            </div>
            <?php endif; ?>
            
            <div class="suno-admin-tracks">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Titre</th>
                            <th>Artiste</th>
                            <th>Album</th>
                            <th>Lectures</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($tracks)): ?>
                            <?php foreach ($tracks as $track): ?>
                            <tr>
                                <td><?php echo $track->id; ?></td>
                                <td><strong><?php echo esc_html($track->title); ?></strong></td>
                                <td><?php echo esc_html($track->artist); ?></td>
                                <td><?php echo esc_html($track->album); ?></td>
                                <td><?php echo number_format($track->play_count); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($track->created_at)); ?></td>
                                <td>
                                    <a href="#" class="button button-small suno-edit-track" data-id="<?php echo $track->id; ?>">Modifier</a>
                                    <a href="#" class="button button-small button-link-delete suno-delete-track" data-id="<?php echo $track->id; ?>">Supprimer</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">Aucune piste pour le moment. Cliquez sur "Ajouter une piste" pour commencer.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Test rapide JavaScript -->
            <div style="margin-top: 20px; padding: 10px; background: #f1f1f1; border-radius: 5px;">
                <h3>Test rapide</h3>
                <button class="button" onclick="alert('JavaScript fonctionne !'); return false;">Test JavaScript</button>
                <button class="button" onclick="jQuery('#test-jquery').text('jQuery fonctionne !'); return false;">Test jQuery</button>
                <span id="test-jquery" style="margin-left: 10px;"></span>
            </div>
        </div>
        <?php
    }
    
    /**
     * Page des playlists
     */
    public function display_playlists_page() {
        ?>
        <div class="wrap">
            <h1>Playlists
                <a href="#" class="page-title-action" id="suno-add-playlist">Créer une playlist</a>
            </h1>
            
            <div class="suno-playlists-manager">
                <p>Gestion des playlists à venir...</p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Page des statistiques
     */
    public function display_stats_page() {
        global $wpdb;
        
        // Stats globales
        $table_tracks = $wpdb->prefix . 'suno_tracks';
        $total_tracks = $wpdb->get_var("SELECT COUNT(*) FROM $table_tracks");
        $total_plays = $wpdb->get_var("SELECT SUM(play_count) FROM $table_tracks");
        
        // Top 10 des pistes
        $top_tracks = $wpdb->get_results(
            "SELECT * FROM $table_tracks ORDER BY play_count DESC LIMIT 10"
        );
        
        ?>
        <div class="wrap">
            <h1>Statistiques</h1>
            
            <div class="suno-stats-overview">
                <div class="suno-stat-box">
                    <h3>Total des pistes</h3>
                    <p class="suno-stat-number"><?php echo number_format($total_tracks ?: 0); ?></p>
                </div>
                
                <div class="suno-stat-box">
                    <h3>Total des lectures</h3>
                    <p class="suno-stat-number"><?php echo number_format($total_plays ?: 0); ?></p>
                </div>
            </div>
            
            <?php if (!empty($top_tracks)): ?>
            <div class="suno-top-tracks">
                <h2>Top 10 des pistes</h2>
                <ol>
                    <?php foreach ($top_tracks as $track): ?>
                    <li>
                        <strong><?php echo esc_html($track->title); ?></strong>
                        - <?php echo number_format($track->play_count); ?> lectures
                    </li>
                    <?php endforeach; ?>
                </ol>
            </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Page des réglages
     */
    public function display_settings_page() {
        ?>
        <div class="wrap">
            <h1>Réglages Suno Music Player</h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('suno_player_settings');
                do_settings_sections('suno_player_settings');
                
                $settings = get_option('suno_player_settings', array());
                ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Autoriser les téléchargements</th>
                        <td>
                            <input type="checkbox" name="suno_player_settings[allow_downloads]" value="1" 
                                <?php checked($settings['allow_downloads'] ?? false, 1); ?>>
                            <p class="description">Permettre aux visiteurs de télécharger les pistes</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Autoplay</th>
                        <td>
                            <input type="checkbox" name="suno_player_settings[autoplay]" value="1" 
                                <?php checked($settings['autoplay'] ?? false, 1); ?>>
                            <p class="description">Lancer automatiquement la lecture</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Thème par défaut</th>
                        <td>
                            <select name="suno_player_settings[default_theme]">
                                <option value="dark" <?php selected($settings['default_theme'] ?? 'dark', 'dark'); ?>>Sombre</option>
                                <option value="light" <?php selected($settings['default_theme'] ?? 'dark', 'light'); ?>>Clair</option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Couleur principale</th>
                        <td>
                            <input type="color" name="suno_player_settings[primary_color]" 
                                value="<?php echo esc_attr($settings['primary_color'] ?? '#6366f1'); ?>">
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Page de débogage
     */
    public function display_debug_page() {
        require_once SUNO_PLAYER_PATH . 'includes/debug.php';
        SunoDebug::display_debug_page();
    }
    
    /**
     * Enregistrer les réglages
     */
    public function register_settings() {
        register_setting('suno_player_settings', 'suno_player_settings');
    }
}