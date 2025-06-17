<?php

namespace GoodWP\Altinator\Modules\Frontend_Inspector;

use GoodWP\Altinator\Helper\Attachment_Helper;
use GoodWP\Altinator\Modules\Module;
use GoodWP\Altinator\Settings\Settings;
use GoodWP\Altinator\Vendor\GoodWP\Common\Assets\Asset_Manager_Contract;
use WP_Admin_Bar;
use WP_HTML_Tag_Processor;

class Frontend_Inspector_Module extends Module {
    public const ACTIVE_QUERY_VAR = 'altinator-inspector';

    protected string $capability = 'upload_files';

    public function __construct(
        protected Asset_Manager_Contract $asset_manager,
        protected Settings $settings
    ) {
    }

    public function is_enabled(): bool {
        /**
         * Filter whether the Frontend Inspector module is enabled.
         * Enabled means the menu item is added to the admin bar and it can be started/activated.
         *
         * @param bool $enabled Whether the Frontend Inspector module is enabled.
         */
        return apply_filters(
            'altinator/frontend_inspector/enable',
            $this->settings->get_setting( 'frontend_inspector_enabled', true )
        );
    }

    public function boot(): void {
        /**
         * Filter the capability required to activate the Frontend Inspector module.
         *
         * @param string $capability Capability required to activate the Frontend Inspector module.
         */
        $this->capability = apply_filters( 'altinator/frontend_inspector/capability', $this->capability );
        if ( ! current_user_can( $this->capability ) ) {
            return;
        }

        add_action( 'admin_bar_menu', [ $this, 'add_admin_bar_menu' ], 999 );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

        if ( $this->is_active() ) {
            /**
             * Use hook WP core provides for manipulating img tags (sizes/srcset, autoloading, width/height)
             */
            add_filter( 'wp_content_img_tag', [ $this, 'content_img_tag_callback' ], 10, 3 );
        }
    }

    public function shutdown(): void {
        remove_action( 'admin_bar_menu', [ $this, 'add_admin_bar_menu' ], 999 );
        remove_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
        remove_filter( 'wp_content_img_tag', [ $this, 'content_img_tag_callback' ], 10 );
    }

    protected function is_active(): bool {
        $active_on_load = false;

        if ( isset( $_REQUEST[ self::ACTIVE_QUERY_VAR ] ) && isset( $_REQUEST[ self::ACTIVE_QUERY_VAR . '_nonce' ] ) ) {
            if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST[ self::ACTIVE_QUERY_VAR . '_nonce' ] ) ), 'altinator-frontend-inspector' ) ) {
                $active_on_load = filter_var( wp_unslash( $_REQUEST[ self::ACTIVE_QUERY_VAR ] ), FILTER_VALIDATE_BOOLEAN );
            }
        }

        /**
         * Filter whether the Frontend Inspector module is active.
         *
         * @param bool $active Whether the Frontend Inspector module is active.
         */
        return apply_filters( 'altinator/frontend_inspector/active', $active_on_load );
    }

    /**
     * @param string $filtered_image Full img tag with attributes that will replace the source img tag.
     * @param string $context Additional context, like the current filter name or the function name from where this was called.
     * @param int    $attachment_id The image attachment ID. May be 0 in case the image is not an attachment.
     * @return string
     */
    public function content_img_tag_callback( string $filtered_image, string $context, int $attachment_id ): string {
        return $this->add_missing_alt_attribute( $filtered_image );
    }

    public function add_missing_alt_attribute( string $html ): string {
        $html_processor = new WP_HTML_Tag_Processor( $html );
        if ( ! $html_processor->next_tag( [ 'tag_name' => 'img' ] ) ) {
            return $html;
        }
        // Already processed.
        if ( $html_processor->get_attribute( 'data-altinator' ) !== null ) {
            return $html;
        }
        $alt_attribute = $html_processor->get_attribute( 'alt' );
        if ( empty( $alt_attribute ) ) {
            $state = $alt_attribute === null ? 'missing-alt' : 'empty-alt';
            $html_processor->set_attribute( 'data-altinator', $state );

            // Add role="img" to ensure screen readers recognize it as an image.
            $html_processor->set_attribute( 'role', 'img' );

            // Add aria-describedby pointing to a hidden description.
            $unique_id = 'altinator-desc-' . uniqid();
            $html_processor->set_attribute( 'aria-describedby', $unique_id );

            // Get updated HTML with our new attributes.
            $html = $html_processor->get_updated_html();

            $image_src = $html_processor->get_attribute( 'src' );
            $html .= $this->build_inspector_tooltip( $image_src, $unique_id, $state );
        }

        return $html;
    }

    public function enqueue_scripts(): void {
        $script_handle = $this->asset_manager->register_script( 'frontend-inspector', 'modules/frontend-inspector' );
        $this->asset_manager->register_style( 'frontend-inspector', 'modules/frontend-inspector' );

        $frontend_args = [
            'active' => $this->is_active(),
        ];
        wp_add_inline_script(
            $script_handle,
            '
                    window.__ALTINATOR__ = {config: ' . json_encode( $frontend_args ) . '};
                ',
            'before'
        );

        $this->asset_manager->enqueue_script( 'frontend-inspector' );
        $this->asset_manager->enqueue_style( 'frontend-inspector' );
    }

    public function add_admin_bar_menu( WP_Admin_Bar $wp_admin_bar ): void {
        // Only show in the frontend.
        if ( is_admin() ) {
            return;
        }

        $wp_admin_bar->add_node(
            [
                'id' => 'altinator-inspector',
                'title' => sprintf(
                    '<span class="ab-icon" aria-hidden="true"></span><span class="ab-label">%s</span>',
                    __( 'Alt-Text Inspector', 'altinator' )
                ),
                // Reload the page with get parameter to enable img parsing.
                'href' => home_url(
                    add_query_arg(
                        [
                            self::ACTIVE_QUERY_VAR => 1,
                            self::ACTIVE_QUERY_VAR . '_nonce' => wp_create_nonce( 'altinator-frontend-inspector' ),
                        ]
                    )
                ),
                'meta' => [
                    'class' => 'altinator-inspector-toggle',
                    'onclick' => 'if(window.__ALTINATOR__.config.active){window.__ALTINATOR__.api.toggle(); return false;}',
                ],
            ]
        );
    }

    /**
     * Add a visually hidden description that screen readers will announce.
     *
     * @param string|null               $image_src The current HTML tag processor processing an image.
     * @param string                    $unique_id
     * @param 'missing-alt'|'empty-alt' $state
     * @return string
     */
    protected function build_inspector_tooltip( ?string $image_src, string $unique_id, string $state ): string {
        $description = $state === 'missing-alt' ?
            /* translators: %s: image filename */
            __( 'The image %s is missing alt text. Please add a descriptive alt text for accessibility.', 'altinator' ) :
            /* translators: %s: image filename */
            __( 'The image %s has an empty alt text. Please make sure that is correct.', 'altinator' );
        $description = sprintf( $description, basename( $image_src ?? '' ) );

        // Add a link to the image edit page if possible.
        $image_edit_button = '';
        if ( ! empty( $image_src ) ) {
            $attachment_id = Attachment_Helper::get_attachment_id_from_url( $image_src );
            if ( $attachment_id ) {
                // $image_edit_button = sprintf(
                // ' <a href="%1$s">%2$s</a>',
                // get_edit_post_link( $attachment_id ),
                // _x( 'Edit here.', 'frontend inspector tooltip', 'altinator' )
                // );
                // The image could be inside an a-tag already, which would output this link outside the span element.
                // Therefore we're using another span element and make it mouse + keyboard accessible.
                // TODO: This is really not perfect. We should build  a more extensive frontend inspector UI.
                $image_edit_button = sprintf(
                    ' <span onclick="%1$s" tabindex="0" role="link" class="altinator-frontend-inspector-edit-link" aria-label="%3$s">%2$s</span>',
                    esc_attr( 'window.open("' . get_edit_post_link( $attachment_id ) . '", "_blank");return false;' ),
                    _x( 'Edit here.', 'frontend inspector tooltip', 'altinator' ),
                    esc_attr_x( 'Edit the image', 'frontend inspector tooltip', 'altinator' )
                );
            }
        }

        $html = sprintf(
        // Use inline-element, because img could be inside another inline-element (e.g., a-tag).
            '<span id="%1$s" class="altinator-frontend-inspector-status">%2$s%3$s</span>',
            $unique_id,
            $description,
            $image_edit_button
        );

        /**
         * Filter the HTML for the frontend inspector tooltip.
         *
         * @param string $html
         * @param string $image_src
         * @param string $unique_id
         * @param 'missing-alt'|'empty-alt' $state
         */
        return apply_filters( 'altinator/frontend_inspector/tooltip', $html, $image_src, $unique_id, $state );
    }
}
