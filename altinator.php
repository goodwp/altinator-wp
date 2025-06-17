<?php
// phpcs:ignoreFile PSR1.Files.SideEffects

/**
 * Plugin Name:       Altinator
 * Description:       Helps you optimize your image alternative texts and make your site more accessible.
 * Requires at least: 6.8
 * Requires PHP:      8.2
 * Version:           1.0.0
 * Author:            GoodWP
 * Author URI:        https://goodwp.io
 * License:           GNU General Public License v3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       altinator
 * Domain Path:       /languages
 *
 * @package           GoodWP\Altinator
 */

namespace GoodWP\Altinator;

use GoodWP\Altinator\Migration\Activation;
use GoodWP\Altinator\Migration\Deactivation;
use Exception;

defined('ABSPATH') || exit;

require_once plugin_dir_path(__FILE__) . 'vendor/vendor-prefixed/autoload.php';
require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

require_once plugin_dir_path(__FILE__) . 'includes/functions.php';

define('ALTINATOR_PLUGIN_FILE', __FILE__);

function altinator(): Plugin
{
    /** @var ?Plugin $plugin */
    $plugin = null;
    try {
        /**
         * try to get an already initialized instance which was loaded on activation/elsewhere
         * so ->init does not trigger two times (e.g. event manager adds events a second time)
         * because activating the plugin runs before plugins_loaded.
         */
        $plugin = Plugin::get_instance();
    } catch (Exception) {
        // if there's no existing instance, create a new one and init it
        $plugin = Plugin::create(__FILE__);
        $plugin->boot();
    }
    return $plugin;
}
add_action('plugins_loaded', function () {
    altinator();
    // Should not be required after WordPress 6.7/6.8
    // load_plugin_textdomain('plugin', false, basename(__DIR__) . '/languages');
});

/**
 * When activation/deactivation happens, plugins_loaded is never triggered
 * therefore our plugin is never initialized.
 * So hook a function here which boots the plugin and gets the activation service.
 */
register_activation_hook(__FILE__, function () {
    $plugin = altinator();
    $plugin->get(Activation::class)->run();
});
register_deactivation_hook(__FILE__, function () {
    $plugin = altinator();
    $plugin->get(Deactivation::class)->run();
});
