<?php

namespace GoodWP\Altinator\Migration;

use GoodWP\Altinator\Vendor\GoodWP\Common\Plugin\Migration as Base_Migration;

/**
 * {@inheritDoc}
 */
class Migration extends Base_Migration {

    /**
     * Creates a new Migration service instance
     *
     * @param string $plugin_version Current installed (code) plugin version.
     * @param string $plugin_slug The slug of the plugin to use for the db version.
     */
    public function __construct( string $plugin_version, string $plugin_slug ) {
        parent::__construct( $plugin_version, $plugin_slug );
    }


    /**
     * {@inheritDoc}
     */
    public function run_migration_steps( string $new_version, string $old_version ): void {
        // TODO: Implement run_migration_steps() method.

        /**
         * Allows doing something after the plugins migrations are run.
         *
         * @param string $new_version New plugin version.
         * @param string $old_version Old/previous plugin version.
         */
        do_action( 'altinator/migration', $new_version, $old_version );
    }
}
