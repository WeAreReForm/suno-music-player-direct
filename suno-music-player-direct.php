<?php
/**
 * Plugin Name: Suno Music Player Direct
 * Plugin URI: https://github.com/WeAreReForm/suno-music-player-direct
 * Description: Hébergez et affichez vos créations Suno directement sur WordPress avec un player moderne
 * Version: 2.1
 * Author: WeAreReForm
 * Author URI: https://github.com/WeAreReForm
 * License: GPL-2.0
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: suno-music-player
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

// Définir les constantes avec détection automatique du nom du dossier
define('SUNO_PLAYER_VERSION', '2.1');
define('SUNO_PLAYER_FILE', __FILE__);
define('SUNO_PLAYER_PATH', plugin_dir_path(__FILE__));
define('SUNO_PLAYER_URL', plugin_dir_url(__FILE__));
define('SUNO_PLAYER_BASENAME', plugin_basename(__FILE__));

// Debug : afficher le chemin si en mode debug
if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log('Suno Player Path: ' . SUNO_PLAYER_PATH);
    error_log('Suno Player URL: ' . SUNO_PLAYER_URL);
}

// Charger la classe principale
require_once SUNO_PLAYER_PATH . 'includes/class-suno-player.php';

// Activation/Désactivation
register_activation_hook(__FILE__, array('SunoMusicPlayerDirect', 'activate'));
register_deactivation_hook(__FILE__, array('SunoMusicPlayerDirect', 'deactivate'));

// Initialiser le plugin
function suno_player_init() {
    $plugin = new SunoMusicPlayerDirect();
    $plugin->run();
}
add_action('plugins_loaded', 'suno_player_init');

// Ajouter un lien vers les réglages dans la page des plugins
function suno_player_action_links($links) {
    $settings_link = '<a href="' . admin_url('admin.php?page=suno-music') . '">Réglages</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . SUNO_PLAYER_BASENAME, 'suno_player_action_links');