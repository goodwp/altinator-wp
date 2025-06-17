<?php

namespace Tests\Integration\Modules;

use GoodWP\Altinator\Modules\Alt_Fallback\Alt_Fallback_Module;
use GoodWP\Altinator\Settings\Settings;
use lucatume\WPBrowser\TestCase\WPTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionObject;
use Tests\Support\IntegrationTester;
use function GoodWP\Altinator\altinator;

class AltFallbackTest extends WPTestCase {

    /**
     * @var IntegrationTester
     */
    protected $tester;

    /**
     * @var Alt_Fallback_Module
     */
    private Alt_Fallback_Module $module;
    private Settings&MockObject $settingsMock;

    public function setUp(): void {
        parent::setUp();
        $this->settingsMock = $this->createMock( Settings::class );
        $this->settingsMock->method( 'get_setting' )->with( 'alt_fallback' )->willReturn( true );

        $this->module = new Alt_Fallback_Module(
            $this->settingsMock
        );
    }


    public function tearDown(): void {
        parent::tearDown();
        $this->module->shutdown();
    }

    public function test_core_image_block_with_empty_alt_gets_fallback_alt(): void {
        $this->module->boot();
        $attachment_id = self::factory()->attachment->create_object( __DIR__ . '/../../_data/images/dummy-image-1.png' );
        update_post_meta( $attachment_id, '_wp_attachment_image_alt', 'Test Alt Text' );

        $block_content = '<figure class="wp-block-image size-large"><img src="..." alt="" class="wp-image-' . $attachment_id . '"/></figure>';
        $block = [
            'blockName' => 'core/image',
            'attrs' => [ 'id' => $attachment_id ],
        ];

        $filtered_content = $this->module->block_render_callback( $block_content, $block );

        $this->assertStringContainsString( 'alt="Test Alt Text"', $filtered_content );
    }

    public function test_core_image_block_with_existing_alt_is_not_changed(): void {
        $this->module->boot();
        $attachment_id = self::factory()->attachment->create_object( __DIR__ . '/../../_data/images/dummy-image-1.png' );
        update_post_meta( $attachment_id, '_wp_attachment_image_alt', 'Should not be used' );

        $block_content = '<figure class="wp-block-image size-large"><img src="..." alt="Existing Alt" class="wp-image-' . $attachment_id . '"/></figure>';
        $block = [
            'blockName' => 'core/image',
            'attrs' => [
				'id' => $attachment_id,
				'alt' => 'Existing Alt',
			],
        ];

        $filtered_content = $this->module->block_render_callback( $block_content, $block );

        $this->assertStringContainsString( 'alt="Existing Alt"', $filtered_content );
        $this->assertStringNotContainsString( 'alt="Should not be used"', $filtered_content );
    }

    public function test_core_media_text_block_with_empty_alt_gets_fallback_alt(): void {
        $this->module->boot();
        $attachment_id = self::factory()->attachment->create_object( __DIR__ . '/../../_data/images/dummy-image-1.png' );
        update_post_meta( $attachment_id, '_wp_attachment_image_alt', 'Media Text Fallback' );

        $block_content = '<div class="wp-block-media-text"><figure><img src="..." alt="" class="wp-image-' . $attachment_id . '"/></figure><div></div></div>';
        $block = [
            'blockName' => 'core/media-text',
            'attrs' => [ 'mediaId' => $attachment_id ],
        ];

        $filtered_content = $this->module->block_render_callback( $block_content, $block );

        $this->assertStringContainsString( 'alt="Media Text Fallback"', $filtered_content );
    }

    public function test_unsupported_block_is_not_processed(): void {
        $this->module->boot();
        $block_content = '<p>Some text</p>';
        $block = [ 'blockName' => 'core/paragraph' ];

        $filtered_content = $this->module->block_render_callback( $block_content, $block );

        $this->assertEquals( $block_content, $filtered_content );
    }

    public function test_add_block_fallback_alt_adds_alt_when_missing(): void {
        $this->module->boot();
        $attachment_id = self::factory()->attachment->create_object( __DIR__ . '/../../_data/images/dummy-image-1.png' );
        update_post_meta( $attachment_id, '_wp_attachment_image_alt', 'Fallback Alt' );

        $html = '<img src="..." alt="">';
        $result = $this->module->add_block_fallback_alt( $html, $attachment_id );

        $this->assertStringContainsString( 'alt="Fallback Alt"', $result );
    }

    public function test_add_block_fallback_alt_does_not_overwrite_existing_alt(): void {
        $this->module->boot();
        $attachment_id = self::factory()->attachment->create_object( __DIR__ . '/../../_data/images/dummy-image-1.png' );
        update_post_meta( $attachment_id, '_wp_attachment_image_alt', 'Should Not Be Used' );

        $html = '<img src="..." alt="Existing Alt">';
        $result = $this->module->add_block_fallback_alt( $html, $attachment_id );

        $this->assertStringContainsString( 'alt="Existing Alt"', $result );
        $this->assertStringNotContainsString( 'alt="Should Not Be Used"', $result );
    }

    public function test_add_block_fallback_alt_does_nothing_if_no_attachment_alt(): void {
        $this->module->boot();
        $attachment_id = self::factory()->attachment->create_object( __DIR__ . '/../../_data/images/dummy-image-1.png' );
        // No alt text is set for the attachment.

        $html = '<img src="..." alt="">';
        $result = $this->module->add_block_fallback_alt( $html, $attachment_id );

        $this->assertEquals( $html, $result );
    }

    public function test_add_block_fallback_alt_handles_no_image_tag(): void {
        $this->module->boot();
        $attachment_id = self::factory()->attachment->create_object( __DIR__ . '/../../_data/images/dummy-image-1.png' );
        update_post_meta( $attachment_id, '_wp_attachment_image_alt', 'Some Alt' );

        $html = '<p>This is a paragraph without an image.</p>';
        $result = $this->module->add_block_fallback_alt( $html, $attachment_id );

        $this->assertEquals( $html, $result );
    }

    public function test_boot_registers_render_callback_for_default_blocks(): void {
        $default_blocks = [
            'core/image'      => 'id',
            'core/media-text' => 'mediaId',
            'core/cover'      => 'id',
        ];

        $this->module->boot();

        foreach ( array_keys( $default_blocks ) as $block_name ) {
            $this->assertTrue( has_filter( 'render_block_' . $block_name ) );
        }
    }

    public function test_allows_adding_blocks_via_filter(): void {
        $filter_callback = function ( $blocks ) {
            $blocks['custom/image-block'] = 'customId';
            return $blocks;
        };
        add_filter( 'altinator/alt_fallback/blocks', $filter_callback, 10, 1 );

        $this->module->boot();

        $reflector = new ReflectionObject( $this->module );
        $property = $reflector->getProperty( 'blocks' );
        $property->setAccessible( true );
        $final_blocks = $property->getValue( $this->module );

        $this->assertArrayHasKey( 'custom/image-block', $final_blocks );
        $this->assertEquals( 'customId', $final_blocks['custom/image-block'] );
        $this->assertArrayHasKey( 'core/image', $final_blocks );

        remove_filter( 'altinator/alt_fallback/blocks', $filter_callback, 10 );

        $blocks = [
            'core/image'      => 'id',
            'core/media-text' => 'mediaId',
            'core/cover'      => 'id',
            'custom/image-block' => 'customId',
        ];
        foreach ( array_keys( $blocks ) as $block_name ) {
            $this->assertTrue( has_filter( 'render_block_' . $block_name ) );
        }
    }
}
