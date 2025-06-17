<?php

namespace GoodWP\Altinator;

use GoodWP\Altinator\Migration\Activation;
use GoodWP\Altinator\Migration\Deactivation;
use GoodWP\Altinator\Migration\Migration;
use GoodWP\Altinator\Settings\Settings;
use GoodWP\Altinator\Settings\Settings_Screen;
use GoodWP\Altinator\Vendor\GoodWP\Common\Assets\Asset_Manager;
use GoodWP\Altinator\Vendor\GoodWP\Common\Assets\Asset_Manager_Contract;
use GoodWP\Altinator\Vendor\GoodWP\Common\DI\Container;
use GoodWP\Altinator\Vendor\GoodWP\Common\DI\Service_Provider;
use GoodWP\Altinator\Vendor\GoodWP\Common\Events\Event_Manager;
use GoodWP\Altinator\Vendor\GoodWP\Common\Events\Event_Manager_Contract;
use GoodWP\Altinator\Vendor\GoodWP\Common\Templates\Template_Renderer;
use GoodWP\Altinator\Vendor\GoodWP\Common\Templates\Template_Renderer_Contract;
use GoodWP\Altinator\Vendor\lucatume\DI52\ContainerException;

/**
 * Main plugin service provider, which provides base services (like assets and settings).
 */
class Plugin_Service_Provider extends Service_Provider {
    /**
     * {@inheritDoc}
     */
    public function register(): void {
        $this->register_core_services();
        $this->register_migration_services();
    }

    /**
     * Registers all core/shared services like templates, events and assets.
     *
     * @return void
     * @throws ContainerException If the binding fails.
     */
    protected function register_core_services(): void {
        $this->container->singleton(
            Asset_Manager_Contract::class,
            function ( Container $container ) {
                $module = $container->get( Plugin::class );
                return new Asset_Manager(
                    'build',
                    $module->get_relative_path(),
                    $module::VERSION,
                    'alt-'
                );
            }
        );
        $this->container->alias( 'assets', Asset_Manager_Contract::class );

        $this->container->singleton(
            Event_Manager_Contract::class,
            function ( Container $container ) {
                // Set prefix to "altinator/".
                return new Event_Manager( "{$container->get('plugin.slug')}/" );
            }
        );

        $this->container->alias( 'events', Event_Manager_Contract::class );

        $this->container->singleton(
            Template_Renderer_Contract::class,
            function ( Container $container ) {
                return new Template_Renderer(
                    $container->get( 'plugin' )->get_path( 'templates' ),
                    null, // Do not allow overwriting.
                    $container->get( 'events' )
                );
            }
        );
        $this->container->alias( 'templates', Template_Renderer_Contract::class );

        $this->container->singleton( Settings::class );
        $this->container->alias( 'settings', Settings::class );
        $this->container->singleton( Settings_Screen::class );
    }

    /**
     * Registers all migration related services.
     *
     * @return void
     * @throws ContainerException If the binding fails.
     */
    protected function register_migration_services(): void {
        $this->container->singleton( Migration::class );
        $this->container->singleton( Activation::class );
        $this->container->singleton( Deactivation::class );
    }

    /**
     * {@inheritDoc}
     */
    public function boot(): void {
        add_filter( 'admin_init', $this->container->callback( Migration::class, 'run' ) );
        $this->container->get( Settings::class )->boot();

        if ( is_admin() ) {
            $this->container->get( Settings_Screen::class )->boot();
        }
    }
}
