/**
 * Upload de fichiers Suno
 */
jQuery(document).ready(function($) {
    
    // Zone de drop
    var $dropzone = $('.suno-upload-dropzone');
    var $fileInput = $('#suno-file-input');
    var $uploadForm = $('#suno-upload-form');
    
    // Clic sur la dropzone
    $dropzone.on('click', function() {
        $fileInput.click();
    });
    
    // Sélection de fichier
    $fileInput.on('change', function() {
        var file = this.files[0];
        if (file) {
            handleFile(file);
        }
    });
    
    // Drag & Drop
    $dropzone.on('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('dragover');
    });
    
    $dropzone.on('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('dragover');
    });
    
    $dropzone.on('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('dragover');
        
        var files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            handleFile(files[0]);
        }
    });
    
    // Gérer le fichier
    function handleFile(file) {
        // Vérifier le type
        var validTypes = ['audio/mpeg', 'audio/mp3', 'audio/mp4', 'audio/m4a', 'audio/ogg', 'audio/wav'];
        if (!validTypes.includes(file.type)) {
            alert('Type de fichier non supporté. Utilisez MP3, M4A, OGG ou WAV.');
            return;
        }
        
        // Vérifier la taille (100 MB max)
        if (file.size > 104857600) {
            alert('Le fichier est trop volumineux (max 100 MB)');
            return;
        }
        
        // Afficher les infos du fichier
        showFileInfo(file);
        
        // Préparer l'upload
        uploadFile(file);
    }
    
    // Afficher les infos du fichier
    function showFileInfo(file) {
        var info = '<div class="suno-file-info">' +
            '<p><strong>Fichier:</strong> ' + file.name + '</p>' +
            '<p><strong>Taille:</strong> ' + formatFileSize(file.size) + '</p>' +
            '<p><strong>Type:</strong> ' + file.type + '</p>' +
        '</div>';
        
        $dropzone.html(info);
    }
    
    // Upload du fichier
    function uploadFile(file) {
        var formData = new FormData();
        formData.append('action', 'suno_upload_track');
        formData.append('nonce', suno_player.upload_nonce);
        formData.append('audio_file', file);
        
        // Récupérer les métadonnées du formulaire si elles existent
        formData.append('title', $('#track-title').val() || file.name.replace(/\.[^/.]+$/, ''));
        formData.append('artist', $('#track-artist').val() || '');
        formData.append('album', $('#track-album').val() || '');
        formData.append('genre', $('#track-genre').val() || '');
        formData.append('description', $('#track-description').val() || '');
        
        // Afficher la progression
        showProgress();
        
        $.ajax({
            url: suno_player.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                
                // Progression de l'upload
                xhr.upload.addEventListener('progress', function(evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = (evt.loaded / evt.total) * 100;
                        updateProgress(percentComplete);
                    }
                }, false);
                
                return xhr;
            },
            success: function(response) {
                if (response.success) {
                    showSuccess(response.data.message);
                    
                    // Recharger la page après 2 secondes
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    showError(response.data);
                }
            },
            error: function(xhr, status, error) {
                showError('Erreur lors de l\'upload: ' + error);
            }
        });
    }
    
    // Afficher la progression
    function showProgress() {
        var html = '<div class="suno-upload-progress">' +
            '<div class="progress-bar">' +
                '<div class="progress-fill" style="width: 0%"></div>' +
            '</div>' +
            '<p class="progress-text">Upload en cours... <span class="progress-percent">0%</span></p>' +
        '</div>';
        
        $('.suno-upload-result').html(html).show();
    }
    
    // Mettre à jour la progression
    function updateProgress(percent) {
        $('.progress-fill').css('width', percent + '%');
        $('.progress-percent').text(Math.round(percent) + '%');
    }
    
    // Afficher le succès
    function showSuccess(message) {
        var html = '<div class="suno-upload-success">' +
            '<span class="dashicons dashicons-yes-alt"></span>' +
            '<p>' + message + '</p>' +
        '</div>';
        
        $('.suno-upload-result').html(html);
    }
    
    // Afficher l'erreur
    function showError(message) {
        var html = '<div class="suno-upload-error">' +
            '<span class="dashicons dashicons-warning"></span>' +
            '<p>' + message + '</p>' +
            '<button class="button" onclick="location.reload()">Réessayer</button>' +
        '</div>';
        
        $('.suno-upload-result').html(html);
    }
    
    // Formater la taille du fichier
    function formatFileSize(bytes) {
        if (bytes < 1024) {
            return bytes + ' B';
        } else if (bytes < 1048576) {
            return Math.round(bytes / 1024) + ' KB';
        } else {
            return Math.round(bytes / 1048576 * 10) / 10 + ' MB';
        }
    }
});