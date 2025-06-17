<?php

namespace GoodWP\Altinator\Modules\Media_Library;

use GoodWP\Altinator\Vendor\GoodWP\Common\Assets\Asset_Manager_Contract;
use GoodWP\Altinator\Vendor\GoodWP\Common\Contracts\Bootable;
use WP_Query;

class Filter implements Bootable {

    public const QUERY_VAR = 'altinator_alt';

    public function __construct(
        protected Asset_Manager_Contract $asset_manager,
    ) {
    }

    public function boot(): void {
        add_filter(
            'query_vars',
            [ $this, 'register_query_var' ],
        );

        add_action(
            'pre_get_posts',
            [ $this, 'filter_pre_get_posts' ]
        );

        add_action(
            'restrict_manage_posts',
            [ $this, 'add_filter_by_alt_text_to_media_library' ],
            10,
            2
        );

        add_filter(
            'ajax_query_attachments_args',
            [ $this, 'filter_ajax_attachment_query' ]
        );

        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
    }


    public function register_query_var( $query_vars ): array {
        $query_vars[] = self::QUERY_VAR;
        return $query_vars;
    }


    public function filter_ajax_attachment_query( $args ): array {
        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- Hook is triggered by core after parsing the core query variables - assume to be safe.
        if ( ! array_key_exists( self::QUERY_VAR, $_REQUEST['query'] ) || $_REQUEST['query'][ self::QUERY_VAR ] === null || $_REQUEST['query'][ self::QUERY_VAR ] === '' ) {
            return $args;
        }

        $request_value = filter_var( wp_unslash( $_REQUEST['query'][ self::QUERY_VAR ] ), FILTER_VALIDATE_BOOLEAN );
        $args = $this->modify_query( $args, $request_value );

        // phpcs:enable WordPress.Security.NonceVerification.Recommended

        return $args;
    }

    public function modify_query( WP_Query|array $query, $filter ): WP_Query|array {
        if ( $filter === null || $filter === '' ) {
            return $query;
        }

        if ( filter_var( $filter, FILTER_VALIDATE_BOOLEAN ) ) {
            $meta_query = [
                'relation' => 'AND',
                [
                    'key' => '_wp_attachment_image_alt',
                    'compare' => 'EXISTS',
                ],
                [
                    'key' => '_wp_attachment_image_alt',
                    'compare' => '!=',
                    'value' => '',
                ],
            ];
        } else {
            $meta_query = [
                'relation' => 'OR',
                [
                    'key' => '_wp_attachment_image_alt',
                    'compare' => 'NOT EXISTS',
                ],
                [
                    'key' => '_wp_attachment_image_alt',
                    'compare' => '=',
                    'value' => '',
                ],
            ];
        }

        if ( is_array( $query ) ) {
            if ( ! empty( $query['meta_query'] ) ) {
                // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- No alternative, alt text is stored as meta field.
                $query['meta_query'] = [
                    'relation' => 'AND',
                    $query['meta_query'],
                    $meta_query,
                ];
            } else {
                // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- No alternative, alt text is stored as meta field.
                $query['meta_query'] = $meta_query;
            }
        } elseif ( ! empty( $query->get( 'meta_query' ) ) ) {
            $query->set(
                'meta_query',
                [
                    'relation' => 'AND',
                    $query->get( 'meta_query' ),
                    $meta_query,
                ]
            );
        } else {
            $query->set( 'meta_query', $meta_query );
        }

        return $query;
    }

    public function add_filter_by_alt_text_to_media_library( $post_type, $which ): void {
        if ( $post_type !== 'attachment' ) {
            return;
        }

        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- Hook is triggered by core after parsing the core query variables - assume to be safe.
        // phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Compared against filter_var in match below.
        $selected_filter_raw = isset( $_GET[ self::QUERY_VAR ] ) ? wp_unslash( $_GET[ self::QUERY_VAR ] ) : null;
        $selected_filter = match ( true ) {
            $selected_filter_raw === null || $selected_filter_raw === '' => '',
            filter_var( $selected_filter_raw, FILTER_VALIDATE_BOOLEAN ) => '1',
            ! filter_var( $selected_filter_raw, FILTER_VALIDATE_BOOLEAN ) => '0',
            default => null,
        };

        // phpcs:enable WordPress.Security.NonceVerification.Recommended
        // phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

        $id = 'altinator-alt-filter-' . $which;

        ?>
        <label for="<?php echo esc_attr( $id ); ?>"
               class="screen-reader-text"><?php esc_html_e( 'Filter by alt text', 'altinator' ); ?></label>
        <select name="<?php echo esc_attr( self::QUERY_VAR ); ?>" id="<?php echo esc_attr( $id ); ?>">
            <option <?php selected( $selected_filter, '' ); ?> value="">
                <?php echo esc_html_x( 'Alt Text: All', 'media library filter', 'altinator' ); ?>
            </option>
            <option <?php selected( $selected_filter, '1' ); ?> value="1">
                <?php
                echo esc_html_x(
                    'Has Alt Text',
                    'media library filter',
                    'altinator'
                );
                ?>
            </option>
            <option <?php selected( $selected_filter, '0' ); ?> value="0">
                <?php
                echo esc_html_x(
                    'No Alt Text',
                    'media library filter',
                    'altinator'
                );
                ?>
            </option>
        </select>

        <?php
    }

    public function filter_pre_get_posts( $query ): void {
        if ( ! is_admin() || ! $query->is_main_query() || $query->get( 'post_type' ) !== 'attachment' ) {
            return;
        }
        $filter = $query->get( self::QUERY_VAR, null );
        if ( $filter !== null && $filter !== '' ) {
            $this->modify_query( $query, $query->get( self::QUERY_VAR ) );
        }
        $query->set( self::QUERY_VAR, null ); // Unset.
    }

    public function enqueue_scripts(): void {
        $current_screen = get_current_screen();
        if ( ! $current_screen || $current_screen->id !== 'upload' ) {
            return;
        }
        $this->asset_manager->register_script( 'media-filter', 'modules/media-filter', [ 'media-views' ] );
        $this->asset_manager->enqueue_script( 'media-filter' );
    }
}
