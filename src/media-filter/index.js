import { __, _x } from "@wordpress/i18n";

/**
 * This is only for the grid view.
 */

( function () {
    const media = wp.media;
    media.view.AttachmentFilters.AltinatorAltFilter =
        media.view.AttachmentFilters.extend( {
            id: "media-attachment-altinator-alt-filter",

            createFilters: function () {
                this.filters = {
                    all: {
                        text: _x(
                            "Alt Text: All",
                            "media library filter",
                            "altinator"
                        ),
                        props: {
                            altinator_alt: null,
                        },
                        priority: 10,
                    },
                    withAlt: {
                        text: _x(
                            "Has Alt Text",
                            "media library filter",
                            "altinator"
                        ),
                        props: {
                            altinator_alt: true,
                        },
                        priority: 10,
                    },
                    withoutAlt: {
                        text: _x(
                            "No Alt Text",
                            "media library filter",
                            "altinator"
                        ),
                        props: {
                            altinator_alt: false,
                        },
                    },
                };
            },
        } );

    const originalAttachmentsBrowser = media.view.AttachmentsBrowser;
    media.view.AttachmentsBrowser = media.view.AttachmentsBrowser.extend( {
        createToolbar: function () {
            // original toolbar
            originalAttachmentsBrowser.prototype.createToolbar.apply(
                this,
                arguments
            );

            // a visually hidden label element needs to be rendered before.
            this.toolbar.set(
                "AltinatorAltFilterLabel",
                new wp.media.view.Label( {
                    value: __( "Filter by alt text", "altinator" ),
                    attributes: {
                        for: "media-attachment-altinator-alt-filter",
                    },
                    priority: -75,
                } ).render()
            );

            this.toolbar.set(
                "AltinatorAltFilter",
                new media.view.AttachmentFilters.AltinatorAltFilter( {
                    controller: this.controller,
                    model: this.collection.props,
                    priority: -75,
                } ).render()
            );
        },
    } );
} )();
