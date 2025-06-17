<?php

namespace Tests\Integration;

use lucatume\WPBrowser\TestCase\WPTestCase;
use Tests\Support\IntegrationTester;
use function GoodWP\Altinator\does_image_have_alt_attribute;
use function GoodWP\Altinator\get_attachment_alt_text;
use function GoodWP\Altinator\add_alt_from_attachment_to_html;

class GlobalFunctionsTest extends WPTestCase {

    /**
     * @var IntegrationTester
     */
    protected $tester;

    // Tests for does_image_have_alt_attribute()
    public function test_does_image_have_alt_attribute_returns_true_for_alt_attribute() {
        $html = '<img src="image.jpg" alt="Test Alt">';
        $this->assertTrue( does_image_have_alt_attribute( $html ) );
    }

    public function test_does_image_have_alt_attribute_returns_false_for_empty_alt_attribute() {
        $html = '<img src="image.jpg" alt="">';
        $this->assertFalse( does_image_have_alt_attribute( $html ) );
    }

    public function test_does_image_have_alt_attribute_returns_false_for_missing_alt_attribute() {
        $html = '<img src="image.jpg">';
        $this->assertFalse( does_image_have_alt_attribute( $html ) );
    }

    public function test_does_image_have_alt_attribute_returns_false_for_no_image_tag() {
        $html = '<p>This is not an image.</p>';
        $this->assertFalse( does_image_have_alt_attribute( $html ) );
    }

    // Tests for get_alt_text()
    public function test_get_alt_text_global_returns_alt_when_present() {
        $attachment_id = self::factory()->attachment->create_object( __DIR__ . '/../_data/images/dummy-image-1.png' );
        $expected_alt = 'Global Test Alt';
        update_post_meta( $attachment_id, '_wp_attachment_image_alt', $expected_alt );

        $this->assertEquals( $expected_alt, get_attachment_alt_text( $attachment_id ) );
    }

    public function test_get_alt_text_global_returns_empty_string_when_not_present() {
        $attachment_id = self::factory()->attachment->create_object( __DIR__ . '/../_data/images/dummy-image-1.png' );
        delete_post_meta( $attachment_id, '_wp_attachment_image_alt' );

        $this->assertEquals( '', get_attachment_alt_text( $attachment_id ) );
    }

    public function test_get_alt_text_global_returns_empty_string_when_meta_is_empty() {
        $attachment_id = self::factory()->attachment->create_object( __DIR__ . '/../_data/images/dummy-image-1.png' );
        update_post_meta( $attachment_id, '_wp_attachment_image_alt', '' );

        $this->assertEquals( '', get_attachment_alt_text( $attachment_id ) );
    }

    // Tests for add_alt_from_attachment_to_html()
    public function test_add_alt_from_attachment_to_html_adds_alt_when_missing() {
        $attachment_id = self::factory()->attachment->create_object( __DIR__ . '/../_data/images/dummy-image-1.png' );
        update_post_meta( $attachment_id, '_wp_attachment_image_alt', 'Attachment Alt Text' );

        $html = '<img src="image.jpg">';
        $result_html = add_alt_from_attachment_to_html( $html, $attachment_id );
        $this->assertStringContainsString( 'alt="Attachment Alt Text"', $result_html );
        $this->assertStringContainsString( 'src="image.jpg"', $result_html );
    }

    public function test_add_alt_from_attachment_to_html_adds_alt_when_empty() {
        $attachment_id = self::factory()->attachment->create_object( __DIR__ . '/../_data/images/dummy-image-1.png' );
        update_post_meta( $attachment_id, '_wp_attachment_image_alt', 'Attachment Alt Text For Empty' );

        $html = '<img src="image.jpg" alt="">';
        // The Alt_Fallback_Module->add_block_fallback_alt checks if alt is *missing* (Utils::has_alt_attribute is false)
        // An empty alt attribute means Utils::has_alt_attribute is false.
        $result_html = add_alt_from_attachment_to_html( $html, $attachment_id );
        $this->assertStringContainsString( 'alt="Attachment Alt Text For Empty"', $result_html );
        $this->assertStringContainsString( 'src="image.jpg"', $result_html );
    }

    public function test_add_alt_from_attachment_to_html_does_not_overwrite_existing_alt() {
        $attachment_id = self::factory()->attachment->create_object( __DIR__ . '/../_data/images/dummy-image-1.png' );
        update_post_meta( $attachment_id, '_wp_attachment_image_alt', 'Should Not Be Used' );

        $html = '<img src="image.jpg" alt="Existing Alt">';
        $result_html = add_alt_from_attachment_to_html( $html, $attachment_id );
        $this->assertStringContainsString( 'alt="Existing Alt"', $result_html );
        $this->assertStringContainsString( 'src="image.jpg"', $result_html );
    }

    public function test_add_alt_from_attachment_to_html_does_nothing_if_no_attachment_alt() {
        $attachment_id = self::factory()->attachment->create_object( __DIR__ . '/../_data/images/dummy-image-1.png' );
        delete_post_meta( $attachment_id, '_wp_attachment_image_alt' ); // No alt text for attachment

        $html = '<img src="image.jpg">';
        $result_html = add_alt_from_attachment_to_html( $html, $attachment_id );
        $this->assertStringContainsString( 'src="image.jpg"', $result_html );
    }

    public function test_add_alt_from_attachment_to_html_handles_no_image_tag() {
        $attachment_id = self::factory()->attachment->create_object( __DIR__ . '/../_data/images/dummy-image-1.png' );
        update_post_meta( $attachment_id, '_wp_attachment_image_alt', 'Some Alt' );

        $html = '<p>No image here.</p>';
        $result_html = add_alt_from_attachment_to_html( $html, $attachment_id );
        $this->assertEquals( $html, $result_html );
    }
}
