<?php
/**
 * Admin functionality for Suno Music Player Direct
 */

if (!defined('ABSPATH')) {
    exit;
}

class SunoPlayerAdmin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'Suno Music Player',
            'Suno Music',
            'manage_options',
            'suno-music-player',
            array($this, 'admin_page'),
            'dashicons-playlist-audio',
            30
        );
        
        add_submenu_page(
            'suno-music-player',
            'Toutes les chansons',
            'Chansons',
            'manage_options',
            'suno-music-player',
            array($this, 'admin_page')
        );
        
        add_submenu_page(
            'suno-music-player',
            'Ajouter une chanson',
            'Ajouter',
            'manage_options',
            'suno-add-track',
            array($this, 'add_track_page')
        );
        
        add_submenu_page(
            'suno-music-player',
            'Paramètres',
            'Paramètres',
            'manage_options',
            'suno-settings',
            array($this, 'settings_page')
        );
    }
    
    /**
     * Main admin page
     */
    public function admin_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'suno_tracks';
        
        // Handle bulk actions
        if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['tracks'])) {
            $this->bulk_delete_tracks($_POST['tracks']);
        }
        
        // Get tracks
        $tracks = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");
        
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Suno Music Player</h1>
            <a href="<?php echo admin_url('admin.php?page=suno-add-track'); ?>" class="page-title-action">Ajouter une chanson</a>
            
            <?php if (isset($_GET['message'])): ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php echo esc_html($_GET['message']); ?></p>
                </div>
            <?php endif; ?>
            
            <form method="post">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <td class="manage-column column-cb check-column">
                                <input type="checkbox" id="cb-select-all">
                            </td>
                            <th>Titre</th>
                            <th>Artiste</th>
                            <th>Durée</th>
                            <th>Lectures</th>
                            <th>Likes</th>
                            <th>Date d'ajout</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($tracks): ?>
                            <?php foreach ($tracks as $track): ?>
                            <tr>
                                <th scope="row" class="check-column">
                                    <input type="checkbox" name="tracks[]" value="<?php echo $track->id; ?>">
                                </th>
                                <td>
                                    <strong><?php echo esc_html($track->title); ?></strong>
                                    <?php if ($track->file_url): ?>
                                        <br>
                                        <audio controls style="margin-top: 5px; max-width: 200px;">
                                            <source src="<?php echo esc_url($track->file_url); ?>" type="audio/mpeg">
                                        </audio>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html($track->artist); ?></td>
                                <td><?php echo $this->format_duration($track->duration); ?></td>
                                <td><?php echo intval($track->play_count); ?></td>
                                <td><?php echo intval($track->likes); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($track->created_at)); ?></td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=suno-edit-track&id=' . $track->id); ?>" class="button button-small">Modifier</a>
                                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=suno-music-player&action=delete&track=' . $track->id), 'delete_track_' . $track->id); ?>" 
                                       class="button button-small button-link-delete" 
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette chanson ?');">
                                        Supprimer
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8">Aucune chanson trouvée. <a href="<?php echo admin_url('admin.php?page=suno-add-track'); ?>">Ajouter une chanson</a></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <?php if ($tracks): ?>
                <div class="tablenav bottom">
                    <div class="alignleft actions bulkactions">
                        <select name="action">
                            <option value="-1">Actions groupées</option>
                            <option value="delete">Supprimer</option>
                        </select>
                        <input type="submit" class="button action" value="Appliquer">
                    </div>
                </div>
                <?php endif; ?>
            </form>
            
            <div style="margin-top: 30px;">
                <h2>Utilisation des shortcodes</h2>
                <p><code>[suno_playlist]</code> - Affiche la playlist complète avec player</p>
                <p><code>[suno_playlist title="Ma Playlist" limit="10"]</code> - Playlist personnalisée</p>
                <p><code>[suno_upload_form]</code> - Formulaire d'upload public</p>
                <p><code>[suno_player id="123"]</code> - Player pour une chanson spécifique</p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Add track page
     */
    public function add_track_page() {
        if (isset($_POST['submit'])) {
            $this->handle_track_upload();
        }
        
        ?>
        <div class="wrap">
            <h1>Ajouter une chanson</h1>
            
            <form method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('add_track', 'suno_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="track_file">Fichier audio *</label></th>
                        <td>
                            <input type="file" name="track_file" id="track_file" accept="audio/*" required>
                            <p class="description">Formats acceptés : MP3, WAV (max 50MB)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="track_title">Titre *</label></th>
                        <td><input type="text" name="track_title" id="track_title" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="track_artist">Artiste</label></th>
                        <td><input type="text" name="track_artist" id="track_artist" class="regular-text" value="Suno AI"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="track_description">Description</label></th>
                        <td><textarea name="track_description" id="track_description" rows="4" class="large-text"></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="track_genre">Genre</label></th>
                        <td>
                            <select name="track_genre" id="track_genre">
                                <option value="">-- Sélectionner --</option>
                                <option value="pop">Pop</option>
                                <option value="rock">Rock</option>
                                <option value="electronic">Électronique</option>
                                <option value="hip-hop">Hip-Hop</option>
                                <option value="jazz">Jazz</option>
                                <option value="classical">Classique</option>
                                <option value="other">Autre</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="allow_download">Autoriser le téléchargement</label></th>
                        <td><input type="checkbox" name="allow_download" id="allow_download" value="1" checked></td>
                    </tr>
                </table>
                
                <?php submit_button('Ajouter la chanson'); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>Paramètres Suno Music Player</h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('suno_player_settings');
                do_settings_sections('suno_player_settings');
                ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Couleur principale</th>
                        <td>
                            <input type="color" name="suno_player_settings[primary_color]" 
                                   value="<?php echo esc_attr(get_option('suno_player_settings')['primary_color'] ?? '#6366f1'); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Autoplay</th>
                        <td>
                            <input type="checkbox" name="suno_player_settings[autoplay]" value="1" 
                                   <?php checked(get_option('suno_player_settings')['autoplay'] ?? 0, 1); ?>>
                            <label>Lecture automatique au chargement</label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Upload public</th>
                        <td>
                            <input type="checkbox" name="suno_player_settings[public_upload]" value="1" 
                                   <?php checked(get_option('suno_player_settings')['public_upload'] ?? 0, 1); ?>>
                            <label>Autoriser l'upload public via shortcode</label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Limite de taille</th>
                        <td>
                            <input type="number" name="suno_player_settings[max_file_size]" 
                                   value="<?php echo esc_attr(get_option('suno_player_settings')['max_file_size'] ?? 50); ?>" 
                                   min="1" max="500">
                            <label>MB (mégaoctets)</label>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
            
            <h2>Statistiques</h2>
            <?php
            global $wpdb;
            $table_name = $wpdb->prefix . 'suno_tracks';
            
            $total_tracks = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
            $total_plays = $wpdb->get_var("SELECT SUM(play_count) FROM $table_name");
            $total_likes = $wpdb->get_var("SELECT SUM(likes) FROM $table_name");
            $total_downloads = $wpdb->get_var("SELECT SUM(download_count) FROM $table_name");
            ?>
            
            <table class="widefat" style="max-width: 500px;">
                <tr>
                    <td>Total de chansons</td>
                    <td><strong><?php echo intval($total_tracks); ?></strong></td>
                </tr>
                <tr>
                    <td>Total de lectures</td>
                    <td><strong><?php echo intval($total_plays); ?></strong></td>
                </tr>
                <tr>
                    <td>Total de likes</td>
                    <td><strong><?php echo intval($total_likes); ?></strong></td>
                </tr>
                <tr>
                    <td>Total de téléchargements</td>
                    <td><strong><?php echo intval($total_downloads); ?></strong></td>
                </tr>
            </table>
        </div>
        <?php
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('suno_player_settings', 'suno_player_settings');
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'suno') === false) {
            return;
        }
        
        wp_enqueue_media();
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
    }
    
    /**
     * Handle track upload
     */
    private function handle_track_upload() {
        if (!isset($_POST['suno_nonce']) || !wp_verify_nonce($_POST['suno_nonce'], 'add_track')) {
            wp_die('Security check failed');
        }
        
        if (!isset($_FILES['track_file']) || $_FILES['track_file']['error'] !== UPLOAD_ERR_OK) {
            wp_die('Erreur lors de l\'upload du fichier');
        }
        
        $upload = wp_handle_upload($_FILES['track_file'], array('test_form' => false));
        
        if (isset($upload['error'])) {
            wp_die($upload['error']);
        }
        
        // Get audio duration
        $duration = $this->get_audio_duration($upload['file']);
        
        // Save to database
        global $wpdb;
        $table_name = $wpdb->prefix . 'suno_tracks';
        
        $wpdb->insert($table_name, array(
            'title' => sanitize_text_field($_POST['track_title']),
            'artist' => sanitize_text_field($_POST['track_artist']),
            'description' => sanitize_textarea_field($_POST['track_description']),
            'genre' => sanitize_text_field($_POST['track_genre']),
            'file_url' => $upload['url'],
            'file_path' => $upload['file'],
            'duration' => $duration,
            'allow_download' => isset($_POST['allow_download']) ? 1 : 0,
            'user_id' => get_current_user_id(),
            'created_at' => current_time('mysql')
        ));
        
        wp_redirect(admin_url('admin.php?page=suno-music-player&message=Chanson ajoutée avec succès'));
        exit;
    }
    
    /**
     * Get audio duration
     */
    private function get_audio_duration($file_path) {
        // This is a simplified version
        // In production, you'd use getID3 library or similar
        return 180; // Default 3 minutes
    }
    
    /**
     * Format duration
     */
    private function format_duration($seconds) {
        $minutes = floor($seconds / 60);
        $seconds = $seconds % 60;
        return sprintf('%d:%02d', $minutes, $seconds);
    }
    
    /**
     * Bulk delete tracks
     */
    private function bulk_delete_tracks($track_ids) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'suno_tracks';
        
        foreach ($track_ids as $id) {
            // Get file path
            $track = $wpdb->get_row($wpdb->prepare("SELECT file_path FROM $table_name WHERE id = %d", $id));
            
            if ($track && file_exists($track->file_path)) {
                unlink($track->file_path);
            }
            
            // Delete from database
            $wpdb->delete($table_name, array('id' => $id));
        }
    }
}

// Initialize admin
new SunoPlayerAdmin();