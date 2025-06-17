<?php

namespace Tests\Integration;

use GoodWP\Altinator\Helper\Attachment_Helper;
use GoodWP\Altinator\Helper\Utils;
use lucatume\WPBrowser\TestCase\WPTestCase;
use Tests\Support\IntegrationTester;

class AttachmentHelperTest extends WPTestCase {

    /**
     * @var IntegrationTester
     */
    protected $tester;

    public function test_get_alt_text_returns_alt_when_present() {
        $attachment_id = self::factory()->attachment->create_object( __DIR__ . '/../_data/images/dummy-image-1.png' );
        $expected_alt = 'Test Alt Text';
        update_post_meta( $attachment_id, '_wp_attachment_image_alt', $expected_alt );

        $this->assertEquals( $expected_alt, Attachment_Helper::get_alt_text( $attachment_id ) );
    }

    public function test_get_alt_text_returns_empty_string_when_not_present() {
        $attachment_id = self::factory()->attachment->create_object( __DIR__ . '/../_data/images/dummy-image-1.png' );
        // Ensure the meta is not set
        delete_post_meta( $attachment_id, '_wp_attachment_image_alt' );

        $this->assertEquals( '', Attachment_Helper::get_alt_text( $attachment_id ) );
    }

    public function test_get_alt_text_returns_empty_string_when_meta_is_empty() {
        $attachment_id = self::factory()->attachment->create_object( __DIR__ . '/../_data/images/dummy-image-1.png' );
        update_post_meta( $attachment_id, '_wp_attachment_image_alt', '' );

        $this->assertEquals( '', Attachment_Helper::get_alt_text( $attachment_id ) );
    }
}
