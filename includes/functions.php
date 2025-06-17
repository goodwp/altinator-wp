<?php

namespace GoodWP\Altinator;

use GoodWP\Altinator\Helper\Attachment_Helper;
use GoodWP\Altinator\Helper\Html_Helper;
use GoodWP\Altinator\Modules\Alt_Fallback\Alt_Fallback_Module;
use RuntimeException;

/**
 * Whether the image has an alt attribute.
 *
 * @param string $image_html
 * @return bool
 */
function does_image_have_alt_attribute( string $image_html ): bool {
    try {
        return Html_Helper::has_alt_attribute( $image_html );
    } catch ( RuntimeException $exception ) {
        return false;
    }
}

/**
 * Get the alt text of an attachment.
 *
 * @param int $attachment_id
 * @return string
 */
function get_attachment_alt_text( int $attachment_id ): string {
    return Attachment_Helper::get_alt_text( $attachment_id );
}

/**
 * Adds the alt attribute based on the attachments alt text
 * to the image tag if it doesn't exist.
 *
 * @param string $image_html
 * @param int    $attachment_id
 * @return string
 */
function add_alt_from_attachment_to_html( string $image_html, int $attachment_id ): string {
    return altinator()->get( Alt_Fallback_Module::class )
        ->add_block_fallback_alt( $image_html, $attachment_id );
}
