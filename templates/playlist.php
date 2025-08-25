<?php
/**
 * Template de playlist
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="suno-player-container theme-<?php echo esc_attr($atts['theme']); ?>">
    <div class="suno-playlist">
        <div class="suno-playlist-header">
            <h2 class="suno-playlist-title"><?php echo esc_html($atts['title']); ?></h2>
            
            <div class="suno-playlist-actions">
                <?php if ($atts['show_upload'] && is_user_logged_in()): ?>
                <button class="suno-btn suno-upload-btn">
                    <span class="dashicons dashicons-upload"></span>
                    Ajouter
                </button>
                <?php endif; ?>
                
                <?php if ($atts['show_download'] && !empty($tracks)): ?>
                <button class="suno-btn suno-download-btn">
                    <span class="dashicons dashicons-download"></span>
                    Télécharger
                </button>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($tracks)): ?>
        
        <!-- Player principal -->
        <div class="suno-main-player">
            <div class="suno-now-playing">
                <h3 class="suno-now-playing-title">Sélectionnez une piste</h3>
                <p class="suno-now-playing-artist">-</p>
            </div>
            
            <!-- Waveform -->
            <div id="waveform" class="suno-waveform"></div>
            
            <!-- Contrôles -->
            <div class="suno-controls">
                <button class="suno-control-btn previous">
                    <span class="dashicons dashicons-controls-skipback"></span>
                </button>
                
                <button class="suno-control-btn play-pause">
                    <span class="dashicons dashicons-controls-play"></span>
                </button>
                
                <button class="suno-control-btn next">
                    <span class="dashicons dashicons-controls-skipforward"></span>
                </button>
            </div>
            
            <!-- Progress bar -->
            <div class="suno-progress">
                <span class="suno-time suno-time-current">0:00</span>
                <div class="suno-progress-bar">
                    <div class="suno-progress-fill"></div>
                </div>
                <span class="suno-time suno-time-total">0:00</span>
            </div>
            
            <!-- Volume -->
            <div class="suno-volume">
                <span class="dashicons dashicons-controls-volumeon"></span>
                <input type="range" class="suno-volume-slider" min="0" max="100" value="70">
            </div>
        </div>
        
        <!-- Liste des pistes -->
        <div class="suno-track-list">
            <!-- Les pistes seront chargées ici par JavaScript -->
        </div>
        
        <!-- Audio HTML5 fallback -->
        <audio id="suno-audio" preload="metadata"></audio>
        
        <?php else: ?>
        
        <div class="suno-empty-playlist">
            <p>Aucune piste disponible pour le moment.</p>
            <?php if ($atts['show_upload'] && is_user_logged_in()): ?>
            <p>Commencez par uploader votre première piste !</p>
            <?php endif; ?>
        </div>
        
        <?php endif; ?>
    </div>
</div>