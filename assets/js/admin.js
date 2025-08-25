/**
 * Scripts admin Suno Music Player
 */
jQuery(document).ready(function($) {
    
    // Bouton Ajouter une piste
    $('#suno-add-track').on('click', function(e) {
        e.preventDefault();
        
        // Créer le modal d'upload
        var modal = $('<div class="suno-modal-overlay">' +
            '<div class="suno-modal">' +
                '<div class="suno-modal-header">' +
                    '<h2>Ajouter une piste</h2>' +
                    '<button class="suno-modal-close">&times;</button>' +
                '</div>' +
                '<div class="suno-modal-content">' +
                    '<form id="suno-upload-form" enctype="multipart/form-data">' +
                        '<div class="form-field">' +
                            '<label>Fichier audio *</label>' +
                            '<input type="file" name="audio_file" accept=".mp3,.m4a,.ogg,.wav" required>' +
                        '</div>' +
                        '<div class="form-field">' +
                            '<label>Titre *</label>' +
                            '<input type="text" name="title" required>' +
                        '</div>' +
                        '<div class="form-field">' +
                            '<label>Artiste</label>' +
                            '<input type="text" name="artist">' +
                        '</div>' +
                        '<div class="form-field">' +
                            '<label>Album</label>' +
                            '<input type="text" name="album">' +
                        '</div>' +
                        '<div class="form-field">' +
                            '<label>Genre</label>' +
                            '<select name="genre">' +
                                '<option value="">-- Sélectionner --</option>' +
                                '<option value="pop">Pop</option>' +
                                '<option value="rock">Rock</option>' +
                                '<option value="electronic">Électronique</option>' +
                                '<option value="jazz">Jazz</option>' +
                                '<option value="classical">Classique</option>' +
                                '<option value="hip-hop">Hip-Hop</option>' +
                                '<option value="other">Autre</option>' +
                            '</select>' +
                        '</div>' +
                        '<div class="form-field">' +
                            '<label>Description</label>' +
                            '<textarea name="description" rows="3"></textarea>' +
                        '</div>' +
                        '<div class="form-actions">' +
                            '<button type="submit" class="button button-primary">Uploader</button>' +
                            '<button type="button" class="button suno-modal-cancel">Annuler</button>' +
                        '</div>' +
                    '</form>' +
                    '<div class="suno-upload-progress" style="display:none;">' +
                        '<div class="progress-bar"><div class="progress-fill"></div></div>' +
                        '<p class="progress-text">Upload en cours...</p>' +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</div>');
        
        $('body').append(modal);
        
        // Fermer le modal
        modal.find('.suno-modal-close, .suno-modal-cancel').on('click', function() {
            modal.remove();
        });
        
        // Soumettre le formulaire
        modal.find('#suno-upload-form').on('submit', function(e) {
            e.preventDefault();
            
            var formData = new FormData(this);
            formData.append('action', 'suno_upload_track');
            formData.append('nonce', suno_admin.nonce);
            
            var progressBar = modal.find('.suno-upload-progress');
            var form = $(this);
            
            form.hide();
            progressBar.show();
            
            $.ajax({
                url: suno_admin.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    var xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener('progress', function(evt) {
                        if (evt.lengthComputable) {
                            var percentComplete = (evt.loaded / evt.total) * 100;
                            progressBar.find('.progress-fill').css('width', percentComplete + '%');
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    if (response.success) {
                        alert('Piste uploadée avec succès !');
                        location.reload();
                    } else {
                        alert('Erreur : ' + response.data);
                        form.show();
                        progressBar.hide();
                    }
                },
                error: function() {
                    alert('Erreur lors de l\'upload');
                    form.show();
                    progressBar.hide();
                }
            });
        });
    });
    
    // Bouton Créer une playlist
    $('#suno-add-playlist').on('click', function(e) {
        e.preventDefault();
        
        var playlistName = prompt('Nom de la nouvelle playlist :');
        if (playlistName) {
            $.post(suno_admin.ajax_url, {
                action: 'suno_create_playlist',
                name: playlistName,
                nonce: suno_admin.nonce
            }, function(response) {
                if (response.success) {
                    alert('Playlist créée !');
                    location.reload();
                } else {
                    alert('Erreur : ' + response.data);
                }
            });
        }
    });
    
    // Supprimer une piste
    $('.suno-delete-track').on('click', function(e) {
        e.preventDefault();
        
        if (!confirm('Voulez-vous vraiment supprimer cette piste ?')) {
            return;
        }
        
        var trackId = $(this).data('id');
        var row = $(this).closest('tr');
        
        $.post(suno_admin.ajax_url, {
            action: 'suno_delete_track',
            track_id: trackId,
            nonce: suno_admin.nonce
        }, function(response) {
            if (response.success) {
                row.fadeOut(400, function() {
                    row.remove();
                });
            } else {
                alert('Erreur : ' + response.data);
            }
        });
    });
    
    // Modifier une piste
    $('.suno-edit-track').on('click', function(e) {
        e.preventDefault();
        
        var trackId = $(this).data('id');
        // TODO: Implémenter l'édition
        alert('Fonctionnalité d\'édition à venir...');
    });
});