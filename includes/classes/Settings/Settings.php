<?php

namespace GoodWP\Altinator\Settings;

/**
 * Main settings class for the plugin.
 * Registers settings and provides APIs to get, set and validate values.
 */
class Settings extends \GoodWP\Altinator\Vendor\GoodWP\Common\WordPress\Settings {

    /**
     * The option group to which register settings to.
     *
     * @var string
     */
    protected const OPTION_GROUP = 'altinator';

    /**
     * {@inheritDoc}
     */
    public function boot(): void {
        parent::boot();

        assert( ! empty( static::OPTION_GROUP ), 'Settings class must declare OPTION_GROUP const with option group' );

        foreach ( $this->build_settings() as $key => $args ) {
            $full_key = static::OPTION_GROUP . '_' . $key;
            $this->setting_keys[ $key ] = $full_key;
        }
    }

    /**
     * {@inheritDoc}
     *
     * Builds the array of settings in format to pass to register_setting
     *
     * @return array
     */
    public function build_settings(): array {
        return [
            'frontend_inspector_enabled' => [
                'type' => 'boolean',
                'default' => true,
                'show_in_rest' => true,
                'sanitize_callback' => 'rest_sanitize_boolean',
                'label' => __( 'Enable Frontend Inspector', 'altinator' ),
                'description' => __( 'Enables the frontend inspector to show images without or with an empty alt attribute.', 'altinator' ),
            ],
            'alt_fallback_enabled' => [
                'type' => 'boolean',
                'default' => true,
                'show_in_rest' => true,
                'sanitize_callback' => 'rest_sanitize_boolean',
                'label' => __( 'Enable Alt Fallback', 'altinator' ),
                'description' => __( 'Outputs the media attachments alt text on certain blocks that have their own alt attribute setting. By default works on the core image, media-text and cover block.', 'altinator' ),
            ],
        ];
    }
}
