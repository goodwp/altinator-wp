<?php
/**
 * Template for rendering the quick-edit column in the media library list view.
 *
 * @package Altinator
 */

/**
 * @var $data array{
 *   attachment_id: int,
 *   alt_text: string,
 *   client_context: array<string, mixed>
 *  }
 */

defined( 'ABSPATH' ) || exit;

$attachment_id = $data['attachment_id'];
$alt_text = $data['alt_text'];
$client_context = $data['client_context'];

$id = sprintf( 'AltinatorAltQuickEditForm-%s', $attachment_id );
$input_id = sprintf( 'AltinatorAltQuickEditFormInput-%s', $attachment_id );
$status_id = sprintf( 'AltinatorAltQuickEditFormInput-%s-Status', $attachment_id );

$edit_toggle_label = sprintf(
    /* translators: Attachment post title */
    __( 'Edit the alternative text for the file "%s"', 'altinator' ),
    get_the_title( $attachment_id )
);

?>
<div class="altinator-alt__quick-edit"
     data-wp-interactive="altinator/alt-quick-edit"
     data-wp-context="<?php echo esc_attr( wp_json_encode( $client_context ) ); ?>"
     data-wp-watch="callbacks.focusFirstElement">
    <p class="altinator-alt__text" data-wp-text="state.originalAltText" data-wp-bind--hidden="context.isEditing">
        <?php echo esc_html( empty( $alt_text ) ? __( 'No alt-text', 'altinator' ) : $alt_text ); ?>
    </p>
    <div class="altinator-alt__edit-form"
         id="<?php echo esc_attr( $id ); ?>"
         data-wp-bind--hidden="!context.isEditing"
         data-wp-cloak
         data-wp-init="callbacks.init">
        <label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>">
            <?php
                echo esc_html( __( 'Alternative Text' ) ); // phpcs:ignore WordPress.WP.I18n.MissingArgDomain
            ?>
        </label>
        <textarea
                rows="2"
               form=""
               name="<?php echo esc_attr( $input_id ); ?>"
               id="<?php echo esc_attr( $input_id ); ?>"
               aria-describedby="<?php echo esc_attr( $status_id ); ?>"
               data-wp-bind--value="context.altText"
               data-wp-on--input="actions.handleInputChange"
                data-wp-bind--disabled="context.isSaving" ><?php echo esc_html( $alt_text ); ?></textarea>
        <button class="button button-primary"
                data-wp-on--click="actions.handleSave"
                data-wp-bind--disabled="!state.savingEnabled"
                data-wp-bind--aria-busy="context.isSaving"
                data-wp-bind--hidden="!context.isDirty">
            <span><?php echo esc_html( __( 'Save', 'altinator' ) ); ?></span>
            <span class="spinner" data-wp-bind--hidden="!context.isSaving" data-wp-class--is-active="context.isSaving"></span>
        </button>
        <button class="button"
                aria-expanded="false"
                aria-controls="<?php echo esc_attr( $id ); ?>"
                data-wp-bind--aria-expanded="context.isEditing"
                data-wp-on--click="actions.handleToggle"
                data-wp-bind--disabled="state.isSaving"
                data-wp-text="state.toggleLabel"
        ><?php echo esc_html( __( 'Cancel', 'altinator' ) ); ?>
        </button>
    </div>
    <a class="altinator-alt__toggle-edit"
       href="<?php echo esc_url( get_edit_post_link( $attachment_id ) . '#attachment_alt' ); ?>"
       aria-label="<?php echo esc_attr( $edit_toggle_label ); ?>"
       title="<?php echo esc_attr( __( 'Edit the alternative text', 'altinator' ) ); ?>"
       aria-expanded="false"
       aria-controls="<?php echo esc_attr( $id ); ?>"
       data-wp-on--click="actions.handleToggle"
       data-wp-bind--aria-expanded="context.isEditing"
       data-wp-text="state.toggleLabel"
       data-wp-bind--hidden="context.isEditing"
    >
        <?php echo esc_html( __( 'Edit', 'altinator' ) ); ?>
    </a>
    <p class="altinator-alt__notification"
       id="<?php echo esc_attr( $status_id ); ?>"
       aria-live="polite"
       data-wp-bind--aria-hidden="!state.hasNotification"
       data-wp-text="state.notificationMessage"
       data-wp-bind--role="state.notificationStatus"></p>
</div>
