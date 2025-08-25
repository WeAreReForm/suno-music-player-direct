/**
 * Suno Music Player Direct - JavaScript
 * Version: 2.0
 */

(function($) {
    'use strict';

    // Player Class
    class SunoPlayer {
        constructor(container) {
            this.container = $(container);
            this.audio = new Audio();
            this.currentTrack = null;
            this.playlist = [];
            this.isPlaying = false;
            this.currentIndex = 0;
            
            this.init();
        }

        init() {
            this.loadPlaylist();
            this.bindEvents();
            this.initUploadForm();
        }

        loadPlaylist() {
            // Load tracks from DOM
            const tracks = this.container.find('.suno-track');
            tracks.each((index, track) => {
                const $track = $(track);
                this.playlist.push({
                    id: $track.data('track-id'),
                    title: $track.find('.suno-track-title').text(),
                    artist: $track.find('.suno-track-artist').text(),
                    url: $track.data('audio-url'),
                    duration: $track.data('duration')
                });
            });
        }

        bindEvents() {
            // Track click
            this.container.on('click', '.suno-track', (e) => {
                e.preventDefault();
                const index = $(e.currentTarget).index();
                this.playTrack(index);
            });

            // Play/Pause button
            this.container.on('click', '.suno-play-pause', (e) => {
                e.preventDefault();
                this.togglePlay();
            });

            // Previous button
            this.container.on('click', '.suno-prev', (e) => {
                e.preventDefault();
                this.playPrevious();
            });

            // Next button
            this.container.on('click', '.suno-next', (e) => {
                e.preventDefault();
                this.playNext();
            });

            // Volume control
            this.container.on('input', '.suno-volume-slider', (e) => {
                this.setVolume(e.target.value / 100);
            });

            // Progress bar click
            this.container.on('click', '.suno-progress-bar', (e) => {
                const $bar = $(e.currentTarget);
                const clickX = e.pageX - $bar.offset().left;
                const width = $bar.width();
                const percentage = clickX / width;
                this.seek(percentage);
            });

            // Audio events
            this.audio.addEventListener('timeupdate', () => this.updateProgress());
            this.audio.addEventListener('ended', () => this.playNext());
            this.audio.addEventListener('loadedmetadata', () => this.onTrackLoaded());
        }

        playTrack(index) {
            if (index < 0 || index >= this.playlist.length) return;

            this.currentIndex = index;
            this.currentTrack = this.playlist[index];
            
            // Update UI
            this.container.find('.suno-track').removeClass('active');
            this.container.find('.suno-track').eq(index).addClass('active');
            
            // Update now playing
            this.updateNowPlaying();
            
            // Load and play audio
            this.audio.src = this.currentTrack.url;
            this.audio.play();
            this.isPlaying = true;
            this.updatePlayButton();
        }

        togglePlay() {
            if (!this.currentTrack) {
                this.playTrack(0);
                return;
            }

            if (this.isPlaying) {
                this.audio.pause();
                this.isPlaying = false;
            } else {
                this.audio.play();
                this.isPlaying = true;
            }
            
            this.updatePlayButton();
        }

        playPrevious() {
            const newIndex = this.currentIndex > 0 ? this.currentIndex - 1 : this.playlist.length - 1;
            this.playTrack(newIndex);
        }

        playNext() {
            const newIndex = this.currentIndex < this.playlist.length - 1 ? this.currentIndex + 1 : 0;
            this.playTrack(newIndex);
        }

        setVolume(value) {
            this.audio.volume = value;
        }

        seek(percentage) {
            if (!this.audio.duration) return;
            this.audio.currentTime = this.audio.duration * percentage;
        }

        updateProgress() {
            if (!this.audio.duration) return;
            
            const percentage = (this.audio.currentTime / this.audio.duration) * 100;
            this.container.find('.suno-progress-fill').css('width', percentage + '%');
            
            // Update time display
            const currentTime = this.formatTime(this.audio.currentTime);
            const totalTime = this.formatTime(this.audio.duration);
            this.container.find('.suno-current-time').text(currentTime);
            this.container.find('.suno-total-time').text(totalTime);
        }

        updateNowPlaying() {
            if (!this.currentTrack) return;
            
            this.container.find('.suno-now-playing-title').text(this.currentTrack.title);
            this.container.find('.suno-now-playing-artist').text(this.currentTrack.artist);
        }

        updatePlayButton() {
            const $btn = this.container.find('.suno-play-pause');
            if (this.isPlaying) {
                $btn.html('<span class="dashicons dashicons-controls-pause"></span>');
            } else {
                $btn.html('<span class="dashicons dashicons-controls-play"></span>');
            }
        }

        onTrackLoaded() {
            // Track loaded, update duration display
            const totalTime = this.formatTime(this.audio.duration);
            this.container.find('.suno-total-time').text(totalTime);
        }

        formatTime(seconds) {
            if (isNaN(seconds)) return '0:00';
            
            const minutes = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return minutes + ':' + (secs < 10 ? '0' : '') + secs;
        }

        // Upload Form Handling
        initUploadForm() {
            const $dropzone = $('.suno-upload-dropzone');
            const $fileInput = $('#suno-file-input');
            
            if (!$dropzone.length) return;

            // Click to upload
            $dropzone.on('click', function() {
                $fileInput.trigger('click');
            });

            // Drag and drop
            $dropzone.on('dragover', function(e) {
                e.preventDefault();
                $(this).addClass('dragover');
            });

            $dropzone.on('dragleave', function() {
                $(this).removeClass('dragover');
            });

            $dropzone.on('drop', function(e) {
                e.preventDefault();
                $(this).removeClass('dragover');
                
                const files = e.originalEvent.dataTransfer.files;
                if (files.length > 0) {
                    handleFileUpload(files[0]);
                }
            });

            // File input change
            $fileInput.on('change', function() {
                if (this.files.length > 0) {
                    handleFileUpload(this.files[0]);
                }
            });
        }
    }

    // File Upload Handler
    function handleFileUpload(file) {
        // Validate file type
        const validTypes = ['audio/mpeg', 'audio/mp3', 'audio/wav'];
        if (!validTypes.includes(file.type)) {
            showMessage('Veuillez sélectionner un fichier audio valide (MP3, WAV)', 'error');
            return;
        }

        // Validate file size (max 50MB)
        const maxSize = 50 * 1024 * 1024;
        if (file.size > maxSize) {
            showMessage('Le fichier est trop volumineux (max 50MB)', 'error');
            return;
        }

        // Show upload progress
        showUploadProgress();

        // Create FormData
        const formData = new FormData();
        formData.append('action', 'suno_upload_track');
        formData.append('nonce', suno_player_ajax.nonce);
        formData.append('file', file);
        formData.append('title', $('#suno-track-title').val() || file.name);
        formData.append('artist', $('#suno-track-artist').val() || 'Unknown Artist');
        formData.append('description', $('#suno-track-description').val() || '');

        // Upload via AJAX
        $.ajax({
            url: suno_player_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                const xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        const percentComplete = (e.loaded / e.total) * 100;
                        updateUploadProgress(percentComplete);
                    }
                }, false);
                return xhr;
            },
            success: function(response) {
                if (response.success) {
                    showMessage('Chanson uploadée avec succès !', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showMessage(response.data || 'Erreur lors de l\'upload', 'error');
                }
                hideUploadProgress();
            },
            error: function() {
                showMessage('Erreur de connexion', 'error');
                hideUploadProgress();
            }
        });
    }

    // Upload Progress Functions
    function showUploadProgress() {
        const html = `
            <div class="suno-upload-progress">
                <div class="suno-spinner"></div>
                <p>Upload en cours... <span class="progress-percent">0%</span></p>
                <div class="progress-bar">
                    <div class="progress-fill"></div>
                </div>
            </div>
        `;
        $('.suno-upload-form').append(html);
    }

    function updateUploadProgress(percent) {
        $('.progress-percent').text(Math.round(percent) + '%');
        $('.progress-fill').css('width', percent + '%');
    }

    function hideUploadProgress() {
        $('.suno-upload-progress').remove();
    }

    // Message Display
    function showMessage(message, type = 'info') {
        const html = `
            <div class="suno-message suno-message-${type}">
                <span class="dashicons dashicons-${type === 'success' ? 'yes' : 'warning'}"></span>
                ${message}
            </div>
        `;
        
        $('.suno-messages').html(html);
        
        setTimeout(() => {
            $('.suno-message').fadeOut();
        }, 5000);
    }

    // Initialize on document ready
    $(document).ready(function() {
        // Initialize all players on the page
        $('.suno-player-container').each(function() {
            new SunoPlayer(this);
        });

        // Handle like button clicks
        $(document).on('click', '.suno-like-btn', function(e) {
            e.stopPropagation();
            const $btn = $(this);
            const trackId = $btn.data('track-id');
            
            $.post(suno_player_ajax.ajax_url, {
                action: 'suno_like_track',
                nonce: suno_player_ajax.nonce,
                track_id: trackId
            }, function(response) {
                if (response.success) {
                    $btn.find('.like-count').text(response.data.likes);
                    $btn.toggleClass('liked');
                }
            });
        });

        // Handle download button clicks
        $(document).on('click', '.suno-download-btn', function(e) {
            e.stopPropagation();
            const trackId = $(this).data('track-id');
            
            // Track download count
            $.post(suno_player_ajax.ajax_url, {
                action: 'suno_track_download',
                nonce: suno_player_ajax.nonce,
                track_id: trackId
            });
        });
    });

})(jQuery);