<?php

namespace GoodWP\Altinator\Migration;

use GoodWP\Altinator\Plugin;

/**
 * {@inheritDoc}
 */
class Deactivation extends \GoodWP\Altinator\Vendor\GoodWP\Common\Plugin\Deactivation {

    /**
     * Creates a new activation instance.
     *
     * @param string $plugin_file The main plugin file. Required for activation hook.
     * @param Plugin $plugin The main plugin instance which is being deactivated.
     */
    public function __construct( string $plugin_file, protected Plugin $plugin ) {
        parent::__construct( $plugin_file );
    }

    /**
     * {@inheritDoc}
     */
    public function run(): void {
        /**
         * Allows doing something when the plugin is being deactivated, and after the plugins deactivation code ran.
         *
         * @param Plugin $plugin The main plugin instance.
         */
        do_action( 'altinator/deactivation', $this->plugin );
    }
}
