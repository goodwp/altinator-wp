<?php

namespace GoodWP\Altinator;

use GoodWP\Altinator\Modules\Modules_Service_Provider;
use GoodWP\Altinator\Vendor\GoodWP\Common\DI\Container;
use GoodWP\Altinator\Vendor\GoodWP\Common\Plugin\Plugin as Base_Plugin;

/**
 * {@inheritDoc}
 */
class Plugin extends Base_Plugin {
    public const VERSION = '1.0.0-alpha.2';
    public const SLUG = 'altinator';
    public readonly string $path;
    public readonly string $relative_path;
    public readonly string $file;
    protected array $service_providers = [
        Plugin_Service_Provider::class,
        Modules_Service_Provider::class,
    ];

    public function __construct( string $plugin_file ) {
        assert( ! empty( static::VERSION ), 'VERSION constant in Plugin class must be defined' );
        assert( ! empty( static::SLUG ), 'SLUG constant in Plugin class must be defined' );

        // TODO: this is a workaround for symlinking.
        $full_path = wp_normalize_path( WP_PLUGIN_DIR ) . DIRECTORY_SEPARATOR . plugin_basename( $plugin_file );
        $this->file          = $full_path;
        $this->path          = dirname( $full_path );
        // $this->path          = dirname( $plugin_file );
        $this->relative_path = str_replace( wp_normalize_path( WP_CONTENT_DIR ), '', $this->path );
    }

    /**
     * {@inheritDoc}
     */
    public function init_container( ?Container $base_container = null ): Container {
        $container = parent::init_container( $base_container );
        /**
         * Allows adding your services or service providers to the container or changing bindings
         * after all core service providers and services were registered.
         *
         * @param Container $container The container instance after it's initialized/registered.
         */
        do_action( 'altinator/init_container', $container );
        return $container;
    }

    /**
     * {@inheritDoc}
     */
    public function boot(): void {
        parent::boot();
        /**
         * Allows booting your own service providers/services after the plugin and all its services were booted.
         *
         * @param self $plugin The plugin instance booting.
         */
        do_action( 'altinator/boot', $this );
    }
    public function get_relative_path( string $relative_path = '' ): string {
        if ( ! empty( $relative_path ) ) {
            return $this->relative_path . DIRECTORY_SEPARATOR . $relative_path;
        }

        return $this->relative_path;
    }
}
