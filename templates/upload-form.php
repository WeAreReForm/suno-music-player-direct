<?php
/**
 * Template du formulaire d'upload
 *
 * @package SunoMusicPlayerDirect
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = get_option('suno_player_settings');
?>

<div class="suno-upload-standalone">
    <form class="suno-upload-form" enctype="multipart/form-data">
        <?php wp_nonce_field('suno_upload', 'suno_upload_nonce'); ?>
        
        <h3>🎵 Uploader une nouvelle chanson</h3>
        
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
                    <option value="reggae">Reggae</option>
                    <option value="blues">Blues</option>
                    <option value="country">Country</option>
                    <option value="folk">Folk</option>
                    <option value="metal">Metal</option>
                    <option value="indie">Indie</option>
                    <option value="other">Autre</option>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="4" placeholder="Décrivez votre création, le prompt utilisé dans Suno..."></textarea>
        </div>
        
        <?php if ($atts['categories']): ?>
        <div class="form-group">
            <label>Tags (séparés par des virgules)</label>
            <input type="text" name="tags" placeholder="suno, ai, génératif, ambient">
        </div>
        <?php endif; ?>
        
        <div class="form-group">
            <label>Image de couverture (optionnel)</label>
            <input type="file" name="cover_image" accept="image/*">
            <small>JPEG, PNG, GIF - Max 2MB</small>
        </div>
        
        <div class="form-group">
            <label>
                <input type="checkbox" name="terms" required>
                J'accepte les conditions d'utilisation et certifie que j'ai les droits sur cette musique
            </label>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-submit">
                <span class="dashicons dashicons-upload"></span> Uploader la chanson
            </button>
            
            <?php if (!empty($atts['redirect'])): ?>
                <input type="hidden" name="redirect_url" value="<?php echo esc_url($atts['redirect']); ?>">
            <?php endif; ?>
        </div>
        
        <div class="upload-progress" style="display: none;">
            <div class="progress-bar">
                <div class="progress-fill"></div>
            </div>
            <p class="progress-text">Upload en cours... <span class="progress-percent">0%</span></p>
        </div>
        
        <div class="upload-success" style="display: none;">
            <div class="success-message">
                <span class="dashicons dashicons-yes-alt"></span>
                <h4>Upload réussi !</h4>
                <p>Votre chanson a été uploadée avec succès.</p>
                <button type="button" class="btn-new-upload">Uploader une autre chanson</button>
            </div>
        </div>
    </form>
</div>
