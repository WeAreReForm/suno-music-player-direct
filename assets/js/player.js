/**
 * Player public Suno Music
 */
jQuery(document).ready(function($) {
    
    // Variables globales
    var currentTrack = 0;
    var playlist = [];
    var wavesurfer = null;
    var isPlaying = false;
    
    // Initialiser le player
    function initPlayer() {
        // Charger la playlist si elle existe
        if ($('.suno-playlist').length > 0) {
            loadPlaylist();
        }
        
        // Initialiser WaveSurfer si présent
        if ($('#waveform').length > 0) {
            initWaveform();
        }
        
        // Event listeners
        bindEvents();
    }
    
    // Charger la playlist
    function loadPlaylist() {
        $.post(suno_player.ajax_url, {
            action: 'suno_get_playlist',
            nonce: suno_player.nonce
        }, function(response) {
            if (response.success) {
                playlist = response.data;
                renderPlaylist();
                if (playlist.length > 0) {
                    loadTrack(0);
                }
            }
        });
    }
    
    // Afficher la playlist
    function renderPlaylist() {
        var html = '';
        $.each(playlist, function(index, track) {
            html += '<div class="suno-track-item" data-index="' + index + '">' +
                '<span class="suno-track-number">' + (index + 1) + '</span>' +
                '<div class="suno-track-info">' +
                    '<div class="suno-track-title">' + track.title + '</div>' +
                    (track.artist ? '<div class="suno-track-artist">' + track.artist + '</div>' : '') +
                '</div>' +
                '<span class="suno-track-duration">' + formatTime(track.duration) + '</span>' +
            '</div>';
        });
        
        $('.suno-track-list').html(html);
    }
    
    // Initialiser WaveSurfer
    function initWaveform() {
        wavesurfer = WaveSurfer.create({
            container: '#waveform',
            waveColor: 'rgba(255, 255, 255, 0.3)',
            progressColor: '#fff',
            cursorColor: '#fff',
            barWidth: 2,
            barRadius: 3,
            responsive: true,
            height: 60,
            normalize: true,
            backend: 'MediaElement'
        });
        
        // Events WaveSurfer
        wavesurfer.on('ready', function() {
            updateDuration();
        });
        
        wavesurfer.on('audioprocess', function() {
            updateProgress();
        });
        
        wavesurfer.on('finish', function() {
            playNext();
        });
    }
    
    // Charger une piste
    function loadTrack(index) {
        if (!playlist[index]) return;
        
        currentTrack = index;
        var track = playlist[index];
        
        // Mettre à jour l'interface
        $('.suno-now-playing-title').text(track.title);
        $('.suno-now-playing-artist').text(track.artist || 'Artiste inconnu');
        
        // Marquer la piste active
        $('.suno-track-item').removeClass('active');
        $('.suno-track-item[data-index="' + index + '"]').addClass('active');
        
        // Charger dans WaveSurfer
        if (wavesurfer) {
            wavesurfer.load(track.url);
        } else {
            // Fallback sur audio HTML5
            var audio = $('#suno-audio')[0];
            if (audio) {
                audio.src = track.url;
                audio.load();
            }
        }
        
        // Mettre à jour le compteur de lectures
        $.post(suno_player.ajax_url, {
            action: 'suno_update_play_count',
            track_id: track.id,
            nonce: suno_player.nonce
        });
    }
    
    // Play/Pause
    function togglePlay() {
        if (wavesurfer) {
            wavesurfer.playPause();
            isPlaying = !isPlaying;
        } else {
            var audio = $('#suno-audio')[0];
            if (audio) {
                if (isPlaying) {
                    audio.pause();
                } else {
                    audio.play();
                }
                isPlaying = !isPlaying;
            }
        }
        
        updatePlayButton();
    }
    
    // Piste suivante
    function playNext() {
        if (currentTrack < playlist.length - 1) {
            loadTrack(currentTrack + 1);
            if (isPlaying || suno_player.settings.autoplay) {
                setTimeout(function() {
                    togglePlay();
                }, 500);
            }
        }
    }
    
    // Piste précédente
    function playPrevious() {
        if (currentTrack > 0) {
            loadTrack(currentTrack - 1);
            if (isPlaying) {
                setTimeout(function() {
                    togglePlay();
                }, 500);
            }
        }
    }
    
    // Mettre à jour le bouton play/pause
    function updatePlayButton() {
        var $btn = $('.suno-control-btn.play-pause');
        var $icon = $btn.find('.dashicons');
        
        if (isPlaying) {
            $icon.removeClass('dashicons-controls-play').addClass('dashicons-controls-pause');
        } else {
            $icon.removeClass('dashicons-controls-pause').addClass('dashicons-controls-play');
        }
    }
    
    // Mettre à jour la progression
    function updateProgress() {
        if (wavesurfer) {
            var current = wavesurfer.getCurrentTime();
            var total = wavesurfer.getDuration();
            
            $('.suno-time-current').text(formatTime(current));
            $('.suno-time-total').text(formatTime(total));
            
            var percent = (current / total) * 100;
            $('.suno-progress-fill').css('width', percent + '%');
        }
    }
    
    // Mettre à jour la durée
    function updateDuration() {
        if (wavesurfer) {
            var duration = wavesurfer.getDuration();
            $('.suno-time-total').text(formatTime(duration));
        }
    }
    
    // Formater le temps
    function formatTime(seconds) {
        if (!seconds || isNaN(seconds)) return '0:00';
        
        var minutes = Math.floor(seconds / 60);
        seconds = Math.floor(seconds % 60);
        return minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
    }
    
    // Bind des événements
    function bindEvents() {
        // Play/Pause
        $(document).on('click', '.suno-control-btn.play-pause', function() {
            togglePlay();
        });
        
        // Suivant
        $(document).on('click', '.suno-control-btn.next', function() {
            playNext();
        });
        
        // Précédent
        $(document).on('click', '.suno-control-btn.previous', function() {
            playPrevious();
        });
        
        // Clic sur une piste
        $(document).on('click', '.suno-track-item', function() {
            var index = $(this).data('index');
            loadTrack(index);
            togglePlay();
        });
        
        // Volume
        $(document).on('input', '.suno-volume-slider', function() {
            var volume = $(this).val() / 100;
            if (wavesurfer) {
                wavesurfer.setVolume(volume);
            } else {
                var audio = $('#suno-audio')[0];
                if (audio) {
                    audio.volume = volume;
                }
            }
        });
        
        // Progress bar click
        $(document).on('click', '.suno-progress-bar', function(e) {
            var $bar = $(this);
            var percent = (e.pageX - $bar.offset().left) / $bar.width();
            
            if (wavesurfer && wavesurfer.getDuration()) {
                wavesurfer.seekTo(percent);
            } else {
                var audio = $('#suno-audio')[0];
                if (audio && audio.duration) {
                    audio.currentTime = audio.duration * percent;
                }
            }
        });
        
        // Téléchargement
        $(document).on('click', '.suno-download-btn', function(e) {
            e.preventDefault();
            var url = playlist[currentTrack].url;
            var title = playlist[currentTrack].title;
            
            var a = document.createElement('a');
            a.href = url;
            a.download = title + '.mp3';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        });
    }
    
    // Initialisation
    initPlayer();
});