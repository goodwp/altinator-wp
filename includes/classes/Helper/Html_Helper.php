<?php

namespace GoodWP\Altinator\Helper;

use RuntimeException;
use WP_HTML_Tag_Processor;

abstract class Html_Helper {
    /**
     * Adds the alt attribute to the image in the HTML.
     *
     * @param string $image_html The image HTML.
     * @param string $alt_text  The alt text.
     * @param bool   $overwrite Whether to overwrite the existing alt attribute.
     *
     * @return string The modified block content.
     */
    public static function add_alt_attribute_to_image( string $image_html, string $alt_text, bool $overwrite = false ): string {
        try {
            if ( $overwrite || ! self::has_alt_attribute( $image_html ) ) {
                $html_processor = new WP_HTML_Tag_Processor( $image_html );
                $html_processor->next_tag( [ 'tag_name' => 'img' ] );
                $html_processor->set_attribute( 'alt', $alt_text );
                $image_html = $html_processor->get_updated_html();
            }
        } catch ( RuntimeException ) {
            return $image_html;
        }
        return $image_html;
    }

    /**
     * @param string $html
     * @return bool True if the img tag has an alt attribute, false otherwise.
     * @throws RuntimeException If $html does not contain an img tag.
     */
    public static function has_alt_attribute( string $html ): bool {
        return self::get_alt_attribute_status( $html ) === 1;
    }


    /**
     * @param string $html
     * @return int -1 if no alt attribute found, 0 if the alt attribute is empty, 1 if the alt attribute is not empty.
     * @throws RuntimeException If $html does not contain an img tag.
     */
    public static function get_alt_attribute_status( string $html ): int {
        $html_processor = new WP_HTML_Tag_Processor( $html );
        if ( ! $html_processor->next_tag( [ 'tag_name' => 'img' ] ) ) {
            throw new RuntimeException( 'No img tag found in HTML' );
        }
        $alt_attribute = $html_processor->get_attribute( 'alt' );
        return match ( true ) {
            $alt_attribute === null => -1,
            empty( $alt_attribute ) => 0,
            default => 1,
        };
    }
}
