<?php

namespace GoodWP\Altinator\Modules\Alt_Fallback;

use GoodWP\Altinator\Helper\Attachment_Helper;
use GoodWP\Altinator\Helper\Html_Helper;
use GoodWP\Altinator\Modules\Module;
use GoodWP\Altinator\Settings\Settings;

/**
 * @template T_Block_Name as string
 * @template T_Attachment_ID_Attribute as string
 */
class Alt_Fallback_Module extends Module {
    protected array $blocks = [];

    public function __construct(
        protected Settings $settings
    ) {
    }

    public function is_enabled(): bool {
        /**
         * Filter whether the Alt Fallback module is enabled.
         *
         * @param bool $enabled Whether the Alt Fallback module is enabled.
         */
        return apply_filters(
            'altinator/alt_fallback/enable',
            $this->settings->get_setting( 'alt_fallback_enabled', true )
        );
    }

    public function boot(): void {
        /**
         * Allow adding more (custom, 3rd party) blocks that should have the alt fallback applied.
         *
         * @phpstan-param array<T_Block_Name, T_Attachment_ID_Attribute> $blocks
         * @param array<string,string> $blocks
         */
        $this->blocks = apply_filters(
            'altinator/alt_fallback/blocks',
            static::default_blocks()
        );

        foreach ( $this->blocks as $block_name => $attachment_id_attribute ) {
            add_filter(
                'render_block_' . $block_name,
                [ $this, 'block_render_callback' ],
                999,
                2
            );
        }
    }

    /**
     * @phpstan-return array<T_Block_Name, T_Attachment_ID_Attribute>
     * @return array<string,string>
     */
    protected static function default_blocks(): array {
        return [
            'core/image' => 'id',
            'core/media-text' => 'mediaId',
            'core/cover' => 'id',
        ];
    }

    public function shutdown(): void {
        foreach ( $this->blocks as $block_name => $attachment_id_attribute ) {
            remove_filter(
                'render_block_' . $block_name,
                [ $this, 'block_render_callback' ],
                999
            );
        }
    }

    /**
     * Callback for render_block filter.
     *
     * @param string                                                  $html
     * @param array{blockName:string,attrs:array}&array<string,mixed> $block
     * @return string
     */
    public function block_render_callback( string $html, array $block ): string {
        $block_name = $block['blockName'];
        if ( ! array_key_exists( $block_name, $this->blocks ) ) {
            return $html;
        }

        $attachment_id_attribute = $this->blocks[ $block_name ];
        if ( ! array_key_exists( $attachment_id_attribute, $block['attrs'] ) ) {
            return $html;
        }

        $html = $this->add_block_fallback_alt( $html, $block['attrs'][ $attachment_id_attribute ] );

        return $html;
    }

    /**
     * Adds the alt attribute based on the attachments alt text
     * to the image tag if it doesn't exist.
     *
     * @param string $block_content The block content.
     * @param int    $attachment_id The attachment ID.
     *
     * @return string The modified block content.
     */
    public function add_block_fallback_alt( string $block_content, int $attachment_id ): string {
        $alt_text = Attachment_Helper::get_alt_text( $attachment_id );
        if ( ! empty( $alt_text ) ) {
            $block_content = Html_Helper::add_alt_attribute_to_image( $block_content, $alt_text );
        }
        return $block_content;
    }
}
