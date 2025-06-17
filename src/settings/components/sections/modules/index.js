import {
    __experimentalText as Text,
    Panel,
    PanelBody,
    PanelRow,
    ToggleControl,
} from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import { useSettings } from "../../../hooks/use-setting";

const ModuleSettings = () => {
    const { values, setValues, isLoading } = useSettings(
        [ "frontend_inspector_enabled", "alt_fallback_enabled" ],
        false
    );

    return (
        <Panel header={ __( "Modules", "altinator" ) }>
            <PanelBody title={ __( "Frontend Inspector", "altinator" ) }>
                <PanelRow>
                    <ToggleControl
                        className="altinator-frontend-inspector-enabled"
                        checked={ !! values.frontend_inspector_enabled }
                        onChange={ ( enable ) => {
                            setValues( { frontend_inspector_enabled: enable } );
                        } }
                        label={ __( "Enable Frontend Inspector", "altinator" ) }
                        help={ __(
                            "If the frontend inspector feature should be available from the WP Admin Bar.",
                            "altinator"
                        ) }
                    />
                </PanelRow>
            </PanelBody>
            <PanelBody title={ __( "Alt Fallbacks", "altinator" ) }>
                <PanelRow>
                    <Text>
                        { __(
                            "Some blocks (like the core image block) provide a field to set the alt attribute. If this field is empty in the block editor it will not output any alt-text, even if you later add an alt-text to the attachment in the media library.",
                            "altinator"
                        ) }
                    </Text>
                </PanelRow>
                <PanelRow>
                    <ToggleControl
                        className="altinator-alt-fallback-enabled"
                        checked={ !! values.alt_fallback_enabled }
                        onChange={ ( enable ) => {
                            setValues( { alt_fallback_enabled: enable } );
                        } }
                        label={ __( "Enable Alt Fallbacks", "altinator" ) }
                        help={ __(
                            "This setting will add the alt attribute to certain blocks if the alt text of the blocks is empty and the alt text of the media attachments is not empty.",
                            "altinator"
                        ) }
                    />
                </PanelRow>
                <PanelRow>
                    <Text>
                        { __(
                            "By default it will work for the core image, gallery, media-text and cover block. But you can add third-party and custom blocks via the `altinator/alt_fallback/blocks` filter.",
                            "altinator"
                        ) }
                    </Text>
                </PanelRow>
            </PanelBody>
        </Panel>
    );
};

export default ModuleSettings;
