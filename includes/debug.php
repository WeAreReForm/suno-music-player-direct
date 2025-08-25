<?php
/**
 * Page de débogage pour vérifier l'installation
 */

if (!defined('ABSPATH')) {
    exit;
}

class SunoDebug {
    
    public static function check_installation() {
        $results = array();
        
        // Vérifier les fichiers
        $required_files = array(
            'includes/class-suno-player.php',
            'includes/class-suno-loader.php',
            'includes/class-suno-database.php',
            'includes/class-suno-admin.php',
            'includes/class-suno-public.php',
            'includes/class-suno-shortcodes.php',
            'includes/class-suno-ajax.php',
            'includes/class-suno-upload.php',
            'assets/js/admin.js',
            'assets/js/player.js',
            'assets/js/upload.js',
            'assets/css/admin.css',
            'assets/css/player.css',
            'templates/playlist.php',
            'templates/upload-form.php',
            'templates/single-player.php'
        );
        
        foreach ($required_files as $file) {
            $path = SUNO_PLAYER_PATH . $file;
            $results['files'][$file] = file_exists($path);
        }
        
        // Vérifier les tables
        global $wpdb;
        $tables = array(
            $wpdb->prefix . 'suno_tracks',
            $wpdb->prefix . 'suno_playlists',
            $wpdb->prefix . 'suno_stats'
        );
        
        foreach ($tables as $table) {
            $results['tables'][$table] = $wpdb->get_var("SHOW TABLES LIKE '$table'") === $table;
        }
        
        // Vérifier les dossiers d'upload
        $upload_dir = wp_upload_dir();
        $suno_dir = $upload_dir['basedir'] . '/suno-music';
        $results['upload_dir'] = is_dir($suno_dir) && is_writable($suno_dir);
        
        // Vérifier les capacités
        $results['capabilities'] = array(
            'manage_suno_music' => current_user_can('manage_suno_music'),
            'upload_suno_tracks' => current_user_can('upload_suno_tracks'),
            'delete_suno_tracks' => current_user_can('delete_suno_tracks')
        );
        
        // Vérifier les options
        $results['options'] = array(
            'suno_player_db_version' => get_option('suno_player_db_version'),
            'suno_player_settings' => get_option('suno_player_settings')
        );
        
        return $results;
    }
    
    public static function display_debug_page() {
        $results = self::check_installation();
        ?>
        <div class="wrap">
            <h1>Débogage Suno Music Player</h1>
            
            <h2>Fichiers requis</h2>
            <table class="wp-list-table widefat">
                <thead>
                    <tr>
                        <th>Fichier</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results['files'] as $file => $exists): ?>
                    <tr>
                        <td><?php echo $file; ?></td>
                        <td>
                            <?php if ($exists): ?>
                            <span style="color: green;">✓ Présent</span>
                            <?php else: ?>
                            <span style="color: red;">✗ Manquant</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <h2>Tables de base de données</h2>
            <table class="wp-list-table widefat">
                <thead>
                    <tr>
                        <th>Table</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results['tables'] as $table => $exists): ?>
                    <tr>
                        <td><?php echo $table; ?></td>
                        <td>
                            <?php if ($exists): ?>
                            <span style="color: green;">✓ Existe</span>
                            <?php else: ?>
                            <span style="color: red;">✗ Manquante</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <h2>Dossier d'upload</h2>
            <p>
                <?php if ($results['upload_dir']): ?>
                <span style="color: green;">✓ Le dossier d'upload existe et est accessible en écriture</span>
                <?php else: ?>
                <span style="color: red;">✗ Problème avec le dossier d'upload</span>
                <?php endif; ?>
            </p>
            
            <h2>Capacités utilisateur</h2>
            <ul>
                <?php foreach ($results['capabilities'] as $cap => $has): ?>
                <li>
                    <?php echo $cap; ?>: 
                    <?php if ($has): ?>
                    <span style="color: green;">✓ Oui</span>
                    <?php else: ?>
                    <span style="color: red;">✗ Non</span>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
            
            <h2>Scripts chargés</h2>
            <div id="suno-loaded-scripts">
                <p>Vérification en cours...</p>
            </div>
            
            <script>
            jQuery(document).ready(function($) {
                var scriptsHtml = '<ul>';
                
                // Vérifier jQuery
                scriptsHtml += '<li>jQuery: ' + (typeof jQuery !== 'undefined' ? '<span style="color:green;">✓ Chargé</span>' : '<span style="color:red;">✗ Non chargé</span>') + '</li>';
                
                // Vérifier les variables globales
                scriptsHtml += '<li>suno_admin: ' + (typeof suno_admin !== 'undefined' ? '<span style="color:green;">✓ Disponible</span>' : '<span style="color:red;">✗ Non disponible</span>') + '</li>';
                scriptsHtml += '<li>suno_player: ' + (typeof suno_player !== 'undefined' ? '<span style="color:green;">✓ Disponible</span>' : '<span style="color:red;">✗ Non disponible</span>') + '</li>';
                
                // Vérifier WaveSurfer
                scriptsHtml += '<li>WaveSurfer: ' + (typeof WaveSurfer !== 'undefined' ? '<span style="color:green;">✓ Chargé</span>' : '<span style="color:red;">✗ Non chargé</span>') + '</li>';
                
                scriptsHtml += '</ul>';
                
                $('#suno-loaded-scripts').html(scriptsHtml);
                
                // Test de fonctionnement du bouton
                scriptsHtml += '<h3>Test du JavaScript</h3>';
                scriptsHtml += '<button id="suno-test-btn" class="button button-primary">Cliquez pour tester</button>';
                scriptsHtml += '<div id="suno-test-result"></div>';
                
                $('#suno-loaded-scripts').append(scriptsHtml);
                
                $('#suno-test-btn').on('click', function() {
                    $('#suno-test-result').html('<p style="color:green;">✓ JavaScript fonctionne correctement !</p>');
                });
            });
            </script>
        </div>
        <?php
    }
}