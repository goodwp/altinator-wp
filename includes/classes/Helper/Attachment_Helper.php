<?php

namespace GoodWP\Altinator\Helper;

abstract class Attachment_Helper {

    /**
     * Get the alt text of an attachment.
     *
     * @param int $attachment_id
     * @return string
     */
    public static function get_alt_text( int $attachment_id ): string {
        $alt_text = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
        if ( ! empty( $alt_text ) ) {
            return $alt_text;
        }
        return '';
    }

    /**
     * Gets the attachment ID from an image URL.
     * Better than attachment_url_to_postid() because it works with cropped/sized image urls.
     *
     * Based on https://wordpress.stackexchange.com/a/7094/168171
     *
     * @param string $image_url
     * @return int|null
     */
    public static function get_attachment_id_from_url( string $image_url ): ?int {
        $dir = wp_upload_dir();

        // Baseurl never has a trailing slash.
        if ( ! str_contains( $image_url, $dir['baseurl'] . '/' ) ) {
            // URL points to a place outside the upload directory.
            return null;
        }

        // $file_name = str_replace( $dir['baseurl'] . '/', '', $image_url );
        $file_name = basename( $image_url );

        // 1. Query attachments based on the main file name.
        $query = [
            'post_type'  => 'attachment',
            'post_status' => 'any',
            'posts_per_page' => 10,
            'fields'     => 'ids',
            'suppress_filters' => false, // WPML/Polylang should hook into this.
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Only runs when frontend inspector is active.
            'meta_query' => [
                [
                    'key'     => '_wp_attached_file',
                    'value'   => $file_name,
                    'compare' => 'LIKE',
                ],
            ],
        ];

        $attachment_ids = get_posts( $query );

        if ( ! empty( $attachment_ids ) ) {
            foreach ( $attachment_ids as $id ) {
                $image_src = wp_get_attachment_image_src( $id, 'full' );
                if ( $image_url === $image_src[0] ) {
                    return $id;
                }
            }
        }

        $query['meta_query'][0]['key'] = '_wp_attachment_metadata';

        // 2. Query attachments based on generated sizes file names.
        $attachment_ids = get_posts( $query );

        if ( empty( $attachment_ids ) ) {
            return null;
        }

        foreach ( $attachment_ids as $id ) {
            $meta = wp_get_attachment_metadata( $id );

            foreach ( $meta['sizes'] as $size => $values ) {
                $image_src = wp_get_attachment_image_src( $id, $size );
                if ( $values['file'] === $file_name && $image_url === $image_src[0] ) {
                    return $id;
                }
            }
        }

        return null;
    }
}
