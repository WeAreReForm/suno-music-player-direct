<?php
/**
 * Template formulaire d'upload
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="suno-upload-form">
    <form id="suno-upload-form" enctype="multipart/form-data">
        <?php wp_nonce_field('suno_upload_nonce', 'suno_upload_nonce'); ?>
        
        <div class="suno-upload-dropzone">
            <div class="suno-upload-icon">
                <span class="dashicons dashicons-upload"></span>
            </div>
            <h3>Glissez vos fichiers ici</h3>
            <p>ou cliquez pour sélectionner</p>
            <input type="file" id="suno-file-input" name="audio_file" accept=".mp3,.m4a,.ogg,.wav" style="display: none;">
        </div>
        
        <div class="suno-upload-fields" style="display: none;">
            <div class="form-field">
                <label for="track-title">Titre</label>
                <input type="text" id="track-title" name="title" required>
            </div>
            
            <div class="form-field">
                <label for="track-artist">Artiste</label>
                <input type="text" id="track-artist" name="artist">
            </div>
            
            <div class="form-field">
                <label for="track-album">Album</label>
                <input type="text" id="track-album" name="album">
            </div>
            
            <div class="form-field">
                <label for="track-genre">Genre</label>
                <select id="track-genre" name="genre">
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
            
            <div class="form-field">
                <label for="track-description">Description</label>
                <textarea id="track-description" name="description" rows="3"></textarea>
            </div>
        </div>
        
        <div class="suno-upload-result" style="display: none;"></div>
    </form>
</div>