<?php
/**
 * Template de la playlist
 *
 * @package SunoMusicPlayerDirect
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = get_option('suno_player_settings');
?>

<div class="suno-player-container" id="<?php echo esc_attr($playlist_id); ?>" data-playlist-id="<?php echo esc_attr($playlist_id); ?>">
    
    <!-- Header -->
    <div class="suno-header">
        <h2><?php echo esc_html($atts['title']); ?></h2>
        
        <?php if ($atts['show_upload'] && is_user_logged_in()): ?>
            <button class="suno-upload-btn" data-playlist="<?php echo esc_attr($playlist_id); ?>">
                <span class="dashicons dashicons-upload"></span> Ajouter une chanson
            </button>
        <?php endif; ?>
    </div>
    
    <!-- Player principal -->
    <div class="suno-main-player">
        <div class="player-cover">
            <img src="<?php echo SUNO_PLAYER_URL; ?>assets/images/default-cover.jpg" 
                 alt="Cover" 
                 class="track-cover" />
        </div>
        
        <div class="player-info">
            <h3 class="track-title">Sélectionnez une chanson</h3>
            <p class="track-artist">-</p>
        </div>
        
        <div id="waveform-<?php echo esc_attr($playlist_id); ?>" class="waveform"></div>
        
        <div class="player-controls">
            <button class="btn-prev" title="Précédent">
                <span class="dashicons dashicons-controls-skipback"></span>
            </button>
            <button class="btn-play-pause" title="Play/Pause">
                <span class="dashicons dashicons-controls-play"></span>
            </button>
            <button class="btn-next" title="Suivant">
                <span class="dashicons dashicons-controls-skipforward"></span>
            </button>
            
            <div class="volume-control">
                <span class="dashicons dashicons-controls-volumeon"></span>
                <input type="range" class="volume-slider" min="0" max="100" value="80">
            </div>
            
            <span class="time-display">00:00 / 00:00</span>
        </div>
    </div>
    
    <!-- Playlist -->
    <?php if ($settings['show_playlist']): ?>
    <div class="suno-playlist">
        <?php if (empty($tracks)): ?>
            <p class="no-tracks">Aucune chanson disponible pour le moment.</p>
        <?php else: ?>
            <ul class="track-list">
                <?php foreach ($tracks as $index => $track): ?>
                    <li class="track-item" 
                        data-index="<?php echo $index; ?>"
                        data-id="<?php echo esc_attr($track->id); ?>"
                        data-url="<?php echo esc_url($track->file_url); ?>"
                        data-title="<?php echo esc_attr($track->title); ?>"
                        data-artist="<?php echo esc_attr($track->artist); ?>"
                        data-cover="<?php echo esc_url($track->cover_url ?: SUNO_PLAYER_URL . 'assets/images/default-cover.jpg'); ?>">
                        
                        <span class="track-number"><?php echo $index + 1; ?></span>
                        
                        <img class="track-thumb" 
                             src="<?php echo esc_url($track->cover_url ?: SUNO_PLAYER_URL . 'assets/images/default-cover.jpg'); ?>" 
                             alt="<?php echo esc_attr($track->title); ?>">
                        
                        <div class="track-details">
                            <strong><?php echo esc_html($track->title); ?></strong>
                            <small><?php echo esc_html($track->artist ?: 'Artiste inconnu'); ?></small>
                        </div>
                        
                        <span class="track-stats">
                            <span class="dashicons dashicons-controls-play"></span> <?php echo number_format($track->plays); ?>
                            <?php if ($settings['allow_downloads']): ?>
                                <span class="dashicons dashicons-download"></span> <?php echo number_format($track->downloads); ?>
                            <?php endif; ?>
                        </span>
                        
                        <?php if ($atts['show_download'] && $settings['allow_downloads']): ?>
                            <a href="<?php echo esc_url($track->file_url); ?>" 
                               download 
                               class="btn-download" 
                               data-track-id="<?php echo esc_attr($track->id); ?>"
                               title="Télécharger">
                                <span class="dashicons dashicons-download"></span>
                            </a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
</div>

<!-- Formulaire d'upload modal -->
<?php if ($atts['show_upload'] && is_user_logged_in()): ?>
<div class="suno-upload-modal" id="upload-modal-<?php echo esc_attr($playlist_id); ?>" style="display: none;">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h3>Ajouter une nouvelle chanson</h3>
        
        <form class="suno-upload-form" data-playlist="<?php echo esc_attr($playlist_id); ?>">
            <?php wp_nonce_field('suno_upload', 'suno_upload_nonce'); ?>
            
            <div class="form-group">
                <label>Fichier audio (MP3, OGG, WAV) *</label>
                <input type="file" name="audio_file" accept="audio/*" required>
                <small>Maximum : <?php echo $settings['max_file_size']; ?> MB</small>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Titre *</label>
                    <input type="text" name="title" required>
                </div>
                
                <div class="form-group">
                    <label>Artiste</label>
                    <input type="text" name="artist" placeholder="Créé avec Suno AI">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Album</label>
                    <input type="text" name="album">
                </div>
                
                <div class="form-group">
                    <label>Genre</label>
                    <select name="genre">
                        <option value="">-- Sélectionner --</option>
                        <option value="pop">Pop</option>
                        <option value="rock">Rock</option>
                        <option value="electronic">Électronique</option>
                        <option value="jazz">Jazz</option>
                        <option value="classical">Classique</option>
                        <option value="hip-hop">Hip-Hop</option>
                        <option value="other">Autre</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3" placeholder="Décrivez votre création..."></textarea>
            </div>
            
            <div class="form-group">
                <label>Image de couverture (optionnel)</label>
                <input type="file" name="cover_image" accept="image/*">
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-submit">Uploader</button>
                <button type="button" class="btn-cancel">Annuler</button>
            </div>
            
            <div class="upload-progress" style="display: none;">
                <div class="progress-bar">
                    <div class="progress-fill"></div>
                </div>
                <p class="progress-text">Upload en cours...</p>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>
