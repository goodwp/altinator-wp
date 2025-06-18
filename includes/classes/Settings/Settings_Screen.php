<?php

namespace GoodWP\Altinator\Settings;

use GoodWP\Altinator\Vendor\GoodWP\Admin\Screens\Admin_Screen;
use GoodWP\Altinator\Vendor\GoodWP\Common\Assets\Asset_Manager_Contract;
use GoodWP\Altinator\Vendor\GoodWP\Common\Assets\Has_Assets;
use GoodWP\Altinator\Vendor\GoodWP\Common\Contracts\Bootable;

/**
 * Admin screen showing settings (especially API credentials).
 * Rendered via React.
 */
class Settings_Screen extends Admin_Screen implements Bootable {
    use Has_Assets;

    /**
     * {@inheritDoc}
     *
     * @var string
     */
    protected string $screen_id = 'altinator-settings';

    public function __construct(
        Asset_Manager_Contract $asset_manager
    ) {
        $this->asset_manager = $asset_manager;
    }

    /**
     * {@inheritDoc}
     */
    public function boot(): void {
        add_action( 'admin_menu', [ $this, 'register' ] );
    }

    /**
     * Registers the settings page with WordPress.
     *
     * @return void
     */
    public function register(): void {
        $screen_id = add_options_page(
            _x( 'Altinator', 'Settings Page', 'altinator' ),
            _x( 'Altinator', 'Settings Page', 'altinator' ),
            'manage_options',
            $this->screen_id,
            [ $this, 'render' ]
        );
        if ( ! $screen_id ) {
            return;
        }
        $this->screen_id = $screen_id;
        add_action( "load-{$this->screen_id}", [ $this, 'load' ] );
    }

    /**
     * {@inheritDoc}
     */
    public function load(): void {
        if ( $this->is_current_screen() ) {
            add_action( 'admin_enqueue_scripts', [ $this, 'register_assets' ] );
        }
    }

    /**
     * Register scripts and styles.
     *
     * @return void
     */
    public function register_assets(): void {
        $script_asset_handle = $this->asset_manager->register_script( 'settings', 'settings' );
        wp_set_script_translations( $script_asset_handle, 'altinator' );
        $this->asset_manager->enqueue_script( 'settings' );
        $this->asset_manager->register_style( 'settings', 'settings', [ 'wp-components', 'wp-editor', 'wp-block-editor', 'wp-block-editor-content' ] );
        $this->asset_manager->enqueue_style( 'settings' );
    }

    /**
     * Renders just the container into which React renders the components.
     *
     * @return void
     */
    public function render(): void {
		echo '<div id="altinator-settings-page"></div>';
    }
}
