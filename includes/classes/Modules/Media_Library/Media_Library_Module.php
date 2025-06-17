<?php

namespace GoodWP\Altinator\Modules\Media_Library;

use GoodWP\Altinator\Modules\Module;
use GoodWP\Altinator\Vendor\GoodWP\Common\Assets\Asset_Manager_Contract;

class Media_Library_Module extends Module {
    public function __construct(
        protected Asset_Manager_Contract $asset_manager,
        protected Filter $filter_submodule,
        protected Quick_Edit $quick_edit_submodule,
    ) {
    }

    /**
     * Since this feature is a core feature of the plugin, no setting to enable/disable is provided.
     * Instead, it can still be disabled with a filter hook.
     *
     * @return bool
     */
    public function is_enabled(): bool {
        /**
         * Filter whether the Media Library module is enabled.
         *
         * @param bool $enabled Whether the Media Library module is enabled.
         */
        return apply_filters( 'altinator/media_library/enable', true );
    }

    public function boot(): void {
        add_action(
            'load-upload.php',
            [ $this, 'load_media_library' ]
        );

        $this->filter_submodule->boot();
        $this->quick_edit_submodule->boot();

        // Always register those, since other submodules may depend on them.
        add_action( 'admin_enqueue_scripts', [ $this, 'register_scripts' ] );
    }

    public function load_media_library(): void {
        $this->add_help_tab();

        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
    }

    protected function add_help_tab(): void {
        $screen = get_current_screen();
        $screen->add_help_tab(
            [
                'id' => 'altinator_alt_text',
                'title' => __( 'Alternative Text' ), // phpcs:ignore WordPress.WP.I18n.MissingArgDomain
                'priority' => 100,
                // The help text is taken from wp-admin/includes/media.php.
                'content' => '
				<p>' .
                    sprintf(
                    // phpcs:disable WordPress.WP.I18n.MissingArgDomain
                    /* translators: 1: Link to tutorial, 2: Additional link attributes, 3: Accessibility text. */
                        __( '<a href="%1$s" %2$s>Learn how to describe the purpose of the image%3$s</a>. Leave empty if the image is purely decorative.' ),
                        /* translators: Localized tutorial, if one exists. W3C Web Accessibility Initiative link has list of existing translations. */
                        esc_url( __( 'https://www.w3.org/WAI/tutorials/images/decision-tree/' ) ),
                        'target="_blank"',
                        sprintf(
                            '<span class="screen-reader-text"> %s</span>',
                            /* translators: Hidden accessibility text. */
                            __( '(opens in a new tab)' )
                        )
                    // phpcs:enable WordPress.WP.I18n.MissingArgDomain
                    ) . '
				</p>
				<p>' .
                    esc_html__( 'You can edit the alternative text of a file directly in the list view, by clicking "Edit" in the "Alternative Text" column.', 'altinator' )
                    . ' </p>
		',
            ]
        );
    }

    public function register_scripts(): void {
        // Always register those, since other submodules may depend on them.
        $this->asset_manager->register_script(
            'media-library-notices',
            'modules/media-library-notices'
        );
        $this->asset_manager->register_style(
            'media-library-notices',
            'modules/media-library-notices',
            [ 'wp-components' ]
        );
    }

    public function enqueue_scripts(): void {
        $this->asset_manager->enqueue_script( 'media-library-notices' );
        $this->asset_manager->enqueue_style( 'media-library-notices' );
    }
}
