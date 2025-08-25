<?php
/**
 * Template player simple
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="suno-single-player" data-track-id="<?php echo $track->id; ?>">
    <div class="suno-player-info">
        <?php if ($track->thumbnail_url): ?>
        <div class="suno-player-thumbnail">
            <img src="<?php echo esc_url($track->thumbnail_url); ?>" alt="<?php echo esc_attr($track->title); ?>">
        </div>
        <?php endif; ?>
        
        <div class="suno-player-details">
            <h3><?php echo esc_html($track->title); ?></h3>
            <?php if ($track->artist): ?>
            <p class="suno-player-artist"><?php echo esc_html($track->artist); ?></p>
            <?php endif; ?>
        </div>
    </div>
    
    <audio id="suno-audio-<?php echo $track->id; ?>" controls <?php echo $atts['autoplay'] ? 'autoplay' : ''; ?> <?php echo $atts['loop'] ? 'loop' : ''; ?>>
        <source src="<?php echo esc_url($track->file_url); ?>" type="audio/mpeg">
        Votre navigateur ne supporte pas l'élément audio.
    </audio>
    
    <div class="suno-player-stats">
        <span class="suno-play-count">
            <span class="dashicons dashicons-controls-play"></span>
            <?php echo number_format($track->play_count); ?> lectures
        </span>
    </div>
</div>