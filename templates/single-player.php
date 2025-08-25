<?php
/**
 * Template d'un player unique
 *
 * @package SunoMusicPlayerDirect
 */

if (!defined('ABSPATH')) {
    exit;
}

$player_id = 'suno-player-' . wp_rand(1000, 9999);
?>

<div class="suno-single-player" id="<?php echo esc_attr($player_id); ?>">
    <div class="player-wrapper">
        
        <?php if (!empty($atts['title']) || !empty($atts['artist'])): ?>
        <div class="player-info">
            <?php if (!empty($atts['title'])): ?>
                <h4 class="player-title"><?php echo esc_html($atts['title']); ?></h4>
            <?php endif; ?>
            
            <?php if (!empty($atts['artist'])): ?>
                <p class="player-artist"><?php echo esc_html($atts['artist']); ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <div class="player-waveform" id="waveform-<?php echo esc_attr($player_id); ?>"></div>
        
        <div class="player-controls">
            <button class="btn-play-pause" title="Play/Pause">
                <span class="dashicons dashicons-controls-play"></span>
            </button>
            
            <div class="time-info">
                <span class="current-time">00:00</span>
                <span class="separator">/</span>
                <span class="total-time">00:00</span>
            </div>
            
            <div class="volume-wrapper">
                <button class="btn-mute" title="Mute">
                    <span class="dashicons dashicons-controls-volumeon"></span>
                </button>
                <input type="range" class="volume-slider" min="0" max="100" value="80">
            </div>
            
            <?php if ($atts['loop']): ?>
            <button class="btn-loop active" title="Loop">
                <span class="dashicons dashicons-controls-repeat"></span>
            </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    const playerId = '<?php echo esc_js($player_id); ?>';
    const audioUrl = '<?php echo esc_js($atts['url']); ?>';
    const autoplay = <?php echo $atts['autoplay'] ? 'true' : 'false'; ?>;
    const loop = <?php echo $atts['loop'] ? 'true' : 'false'; ?>;
    
    // Initialiser WaveSurfer pour ce player
    const wavesurfer = WaveSurfer.create({
        container: '#waveform-' + playerId,
        waveColor: '#94a3b8',
        progressColor: '<?php echo get_option('suno_player_settings')['primary_color']; ?>',
        cursorColor: '#fff',
        barWidth: 2,
        barRadius: 3,
        responsive: true,
        height: 40,
        normalize: true
    });
    
    wavesurfer.load(audioUrl);
    
    // Auto-play si demandé
    if (autoplay) {
        wavesurfer.on('ready', function() {
            wavesurfer.play();
        });
    }
    
    // Contrôles
    $('#' + playerId + ' .btn-play-pause').on('click', function() {
        wavesurfer.playPause();
        $(this).find('.dashicons')
            .toggleClass('dashicons-controls-play')
            .toggleClass('dashicons-controls-pause');
    });
    
    // Volume
    $('#' + playerId + ' .volume-slider').on('input', function() {
        wavesurfer.setVolume($(this).val() / 100);
    });
    
    // Mute
    $('#' + playerId + ' .btn-mute').on('click', function() {
        wavesurfer.toggleMute();
        $(this).find('.dashicons')
            .toggleClass('dashicons-controls-volumeon')
            .toggleClass('dashicons-controls-volumeoff');
    });
    
    // Mise à jour du temps
    wavesurfer.on('audioprocess', function() {
        const current = wavesurfer.getCurrentTime();
        const total = wavesurfer.getDuration();
        
        $('#' + playerId + ' .current-time').text(formatTime(current));
        $('#' + playerId + ' .total-time').text(formatTime(total));
    });
    
    // Loop
    if (loop) {
        wavesurfer.on('finish', function() {
            wavesurfer.play();
        });
    }
    
    function formatTime(seconds) {
        const minutes = Math.floor(seconds / 60);
        seconds = Math.floor(seconds % 60);
        return minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
    }
});
</script>
