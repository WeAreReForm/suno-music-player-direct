<?php
/**
 * Plugin Name: Suno Music Player Direct
 * Plugin URI: https://github.com/WeAreReForm/suno-music-player-direct
 * Description: Hébergez et affichez vos créations Suno directement sur WordPress avec un player moderne
 * Version: 2.0
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

// Définir les constantes
define('SUNO_PLAYER_VERSION', '2.0');
define('SUNO_PLAYER_URL', plugin_dir_url(__FILE__));
define('SUNO_PLAYER_PATH', plugin_dir_path(__FILE__));

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