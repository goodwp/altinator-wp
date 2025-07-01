<?php

namespace GoodWP\Altinator\Modules\Media_Library;

use GoodWP\Altinator\Plugin;
use GoodWP\Altinator\Vendor\GoodWP\Common\Assets\Asset_Manager_Contract;
use GoodWP\Altinator\Vendor\GoodWP\Common\Contracts\Bootable;
use GoodWP\Altinator\Vendor\GoodWP\Common\Templates\Template_Renderer_Contract;

class Quick_Edit implements Bootable {

    public function __construct(
        protected Asset_Manager_Contract $asset_manager,
        protected Template_Renderer_Contract $template_renderer,
    ) {
    }

    public function boot(): void {
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

        add_filter(
            'manage_media_columns',
            [ $this, 'add_alt_column' ],
        );

        add_action(
            'manage_media_custom_column',
            [ $this, 'render_alt_column' ],
            10,
            2
        );
    }

    public function enqueue_scripts(): void {
        $current_screen = get_current_screen();
        if ( ! $current_screen || $current_screen->id !== 'upload' ) {
            return;
        }

        $quick_edit_asset = $this->asset_manager->parse_asset_file( 'media-library-quick-edit', 'modules/media-library-quick-edit' );
        wp_register_script_module(
            $quick_edit_asset['handle'],
            content_url( ltrim( $quick_edit_asset['relative_path'], '/' ) . '.js' ),
            $quick_edit_asset['dependencies'],
            $quick_edit_asset['version'],
        );

        wp_interactivity_config(
            'altinator/alt-quick-edit',
            []
        );

        wp_enqueue_script_module( $quick_edit_asset['handle'] );
        // See https://core.trac.wordpress.org/ticket/60647.
        // Need to manually enqueue those core scripts.
        wp_enqueue_script( 'wp-notices' );
        wp_enqueue_script( 'wp-data' );
        wp_enqueue_script( 'wp-api-fetch' );
        wp_enqueue_script( 'wp-i18n' );

        // This is a dummy script that is only used to register the translations.
        // Workaround for https://core.trac.wordpress.org/ticket/60234 .
        $dummy_handle = $this->asset_manager->register_script( 'media-library-quick-edit-dummy', 'modules/media-library-quick-edit-dummy' );
        $this->asset_manager->enqueue_script( 'media-library-quick-edit-dummy' );
        wp_set_script_translations( $dummy_handle, 'altinator', Plugin::get_instance()->get_path( 'languages' ) );

        $this->asset_manager->register_style( 'media-library-quick-edit', 'modules/media-library-quick-edit' );
        $this->asset_manager->enqueue_style( 'media-library-quick-edit' );
    }

    public function add_alt_column( $columns ): array {
        $columns['altinator-alt'] = __( 'Alternative Text' ); // phpcs:ignore WordPress.WP.I18n.MissingArgDomain
        return $columns;
    }

    public function render_alt_column( $column, $id ): void {
        if ( $column !== 'altinator-alt' ) {
            return;
        }

        $alt_text = get_post_meta( $id, '_wp_attachment_image_alt', true );

        if ( current_user_can( 'edit_post', $id ) ) {
            $context = [
                'attachmentId' => $id,
                'altText' => $alt_text,
                'isEditing' => false,
                'isSaving' => false,
                'isGenerating' => false,
                'saveResult' => null,
            ];
            $this->template_renderer->render(
                'media-library/quick-edit',
                [
					'attachment_id' => $id,
					'alt_text' => $alt_text,
					'client_context' => $context,
				]
            );
        } else {
            printf( '<p class="altinator-alt__text">%s</p>', esc_html( empty( $alt_text ) ? __( 'No alt-text', 'altinator' ) : $alt_text ) );
        }
    }
}
