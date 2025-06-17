<?php

namespace GoodWP\Altinator\Migration;

use GoodWP\Altinator\Plugin;

/**
 * {@inheritDoc}
 */
class Activation extends \GoodWP\Altinator\Vendor\GoodWP\Common\Plugin\Activation {

    /**
     * Creates a new activation instance.
     *
     * @param string $plugin_file The main plugin file. Required for activation hook.
     * @param Plugin $plugin The main plugin instance which is being activated.
     */
    public function __construct( string $plugin_file, protected Plugin $plugin ) {
        parent::__construct( $plugin_file );
    }

    /**
     * {@inheritDoc}
     */
    public function run(): void {
        /**
         * Allows doing something when the plugin is being activated, and after the plugins activation code ran.
         *
         * @param Plugin $plugin The main plugin instance.
         */
        do_action( 'altinator/activation', $this->plugin );
    }
}
