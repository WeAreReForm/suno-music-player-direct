<?php
/**
 * Gestion des shortcodes
 */

if (!defined('ABSPATH')) {
    exit;
}

class SunoShortcodes {
    
    /**
     * Shortcode [suno_playlist]
     */
    public function render_playlist($atts) {
        $atts = shortcode_atts(array(
            'title' => 'Ma Playlist Suno',
            'limit' => 20,
            'user_id' => null,
            'show_upload' => true,
            'show_download' => true,
            'autoplay' => false,
            'theme' => 'dark'
        ), $atts);
        
        // Récupérer les pistes
        $tracks = SunoDatabase::get_tracks(array(
            'user_id' => $atts['user_id'],
            'limit' => $atts['limit']
        ));
        
        ob_start();
        include SUNO_PLAYER_PATH . 'templates/playlist.php';
        return ob_get_clean();
    }
    
    /**
     * Shortcode [suno_player]
     */
    public function render_player($atts) {
        $atts = shortcode_atts(array(
            'id' => 0,
            'autoplay' => false,
            'loop' => false,
            'theme' => 'dark'
        ), $atts);
        
        if (!$atts['id']) {
            return '<p>ID de piste manquant</p>';
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'suno_tracks';
        $track = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $atts['id']
        ));
        
        if (!$track) {
            return '<p>Piste non trouvée</p>';
        }
        
        ob_start();
        include SUNO_PLAYER_PATH . 'templates/single-player.php';
        return ob_get_clean();
    }
    
    /**
     * Shortcode [suno_upload_form]
     */
    public function render_upload_form($atts) {
        if (!is_user_logged_in()) {
            return '<p>Vous devez être connecté pour uploader des pistes.</p>';
        }
        
        if (!current_user_can('upload_suno_tracks')) {
            return '<p>Vous n\'avez pas la permission d\'uploader des pistes.</p>';
        }
        
        $atts = shortcode_atts(array(
            'redirect' => '',
            'max_size' => 100,
            'allowed_types' => 'mp3,m4a,ogg,wav'
        ), $atts);
        
        ob_start();
        include SUNO_PLAYER_PATH . 'templates/upload-form.php';
        return ob_get_clean();
    }
    
    /**
     * Shortcode [suno_gallery]
     */
    public function render_gallery($atts) {
        $atts = shortcode_atts(array(
            'columns' => 3,
            'limit' => 12,
            'user_id' => null,
            'show_title' => true,
            'show_artist' => true,
            'show_play_count' => true,
            'orderby' => 'created_at',
            'order' => 'DESC'
        ), $atts);
        
        $tracks = SunoDatabase::get_tracks(array(
            'user_id' => $atts['user_id'],
            'limit' => $atts['limit'],
            'orderby' => $atts['orderby'],
            'order' => $atts['order']
        ));
        
        ob_start();
        ?>
        <div class="suno-gallery" data-columns="<?php echo esc_attr($atts['columns']); ?>">
            <?php foreach ($tracks as $track): ?>
            <div class="suno-gallery-item">
                <?php if ($track->thumbnail_url): ?>
                <div class="suno-gallery-thumbnail">
                    <img src="<?php echo esc_url($track->thumbnail_url); ?>" alt="<?php echo esc_attr($track->title); ?>">
                    <div class="suno-gallery-overlay">
                        <button class="suno-play-btn" data-track-id="<?php echo $track->id; ?>">
                            <span class="dashicons dashicons-controls-play"></span>
                        </button>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="suno-gallery-info">
                    <?php if ($atts['show_title']): ?>
                    <h4><?php echo esc_html($track->title); ?></h4>
                    <?php endif; ?>
                    
                    <?php if ($atts['show_artist'] && $track->artist): ?>
                    <p class="suno-artist"><?php echo esc_html($track->artist); ?></p>
                    <?php endif; ?>
                    
                    <?php if ($atts['show_play_count']): ?>
                    <p class="suno-play-count">
                        <span class="dashicons dashicons-controls-play"></span>
                        <?php echo number_format($track->play_count); ?>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}