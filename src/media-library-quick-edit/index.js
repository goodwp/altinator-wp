import {
    getConfig,
    getContext,
    getElement,
    store,
    withSyncEvent,
    withScope,
} from "@wordpress/interactivity";
import "./styles.css";

// see https://core.trac.wordpress.org/ticket/60647
const { notices, data } = window.wp;
const { apiFetch } = window.wp;
const { _x, __ } = window.wp.i18n;

const { state, actions } = store( "altinator/alt-quick-edit", {
    state: {
        get generationEnabled() {
            const { generationEnabled } = getConfig();
            return generationEnabled || false;
        },
        get hasNotification() {
            const { saveResult } = getContext();
            return saveResult !== null;
        },
        get notificationMessage() {
            const { saveResult } = getContext();
            if ( saveResult !== null ) {
                return saveResult
                    ? _x( "Saved", "quick edit", "altinator" )
                    : _x( "Failed to save", "quick edit", "altinator" );
            }
            return "";
        },
        get notificationStatus() {
            const { saveResult } = getContext();
            if ( saveResult !== null ) {
                return saveResult ? "status" : "alert";
            }
            return "";
        },
        get savingEnabled() {
            const { isSaving, isDirty } = getContext();
            return isDirty && ! isSaving;
        },
        get toggleLabel() {
            const { isEditing, isSaving, saveResult } = getContext();
            if ( ! isEditing ) {
                return _x( "Edit", "quick edit", "altinator" );
            }
            return ! isSaving && saveResult
                ? _x( "Close", "quick edit", "altinator" )
                : _x( "Cancel", "quick edit", "altinator" );
        },
        get originalAltText() {
            const { originalAltText } = getContext();
            if ( originalAltText && originalAltText.length > 0 ) {
                return originalAltText;
            }
            return __( "No alt-text", "altinator" );
        },
    },
    actions: {
        handleToggle: withSyncEvent( ( event ) => {
            // TODO: this triggers the warning about not using withSyncEvent although we are using it here.
            event.preventDefault();
            actions.toggleEditing();
        } ),
        handleInputChange: withSyncEvent( ( event ) => {
            actions.updateAltText( event.target.value );
        } ),
        toggleEditing() {
            const context = getContext();
            const wasEditing = context.isEditing;
            context.isEditing = ! wasEditing;
            context.wasEditing = wasEditing;

            if ( ! wasEditing ) {
                // Reset result.
                context.saveResult = null;
            }
        },
        updateAltText( newValue ) {
            const context = getContext();
            if ( context.isSaving ) {
                return false;
            }
            context.isDirty = true;
            context.altText = newValue;
            context.saveResult = null;
        },
        handleSave: withSyncEvent( ( event ) => {
            event.preventDefault();
            actions.save();
        } ),
        *save() {
            const context = getContext();
            if ( context.isSaving ) {
                return false;
            }
            context.isSaving = true;

            try {
                const response = yield apiFetch( {
                    path: `/wp/v2/media/${ context.attachmentId }`,
                    method: "POST",
                    data: {
                        alt_text: context.altText || "",
                    },
                } );
                context.isSaving = false;
                context.isDirty = false;
                context.saveResult = true;
                context.altText = response.alt_text;
                context.originalAltText = response.alt_text;
                actions.toggleEditing();
            } catch ( error ) {
                context.isSaving = false;
                context.saveResult = false;
                yield data
                    .dispatch( notices.store )
                    .createErrorNotice( error.message, {
                        type: "snackbar",
                        context: "altinator",
                        isDismissible: true,
                        explicitDismiss: true,
                    } );
            }
        },
    },
    callbacks: {
        init() {
            const context = getContext();
            const { ref } = getElement();
            ref.removeAttribute( "data-wp-cloak" );
            context.originalAltText = context.altText;
        },
        focusFirstElement() {
            const { isEditing, isSaving, saveResult, wasEditing } =
                getContext();
            const { ref } = getElement();
            if ( isEditing ) {
                ref.querySelector( "textarea" )?.focus();
            } else if ( wasEditing ) {
                // To prevent setting focus on first render.
                ref.querySelector( ".altinator-alt__toggle-edit" )?.focus();
            }
        },
    },
} );
