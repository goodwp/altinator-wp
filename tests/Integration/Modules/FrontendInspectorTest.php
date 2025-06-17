<?php

namespace Tests\Integration\Modules;

use GoodWP\Altinator\Modules\Frontend_Inspector\Frontend_Inspector_Module;
use GoodWP\Altinator\Settings\Settings;
use GoodWP\Altinator\Vendor\GoodWP\Common\Assets\Asset_Manager_Contract;
use lucatume\WPBrowser\TestCase\WPTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\Support\IntegrationTester;

class FrontendInspectorTest extends WPTestCase {

    /**
     * @var IntegrationTester
     */
    protected $tester;

    /**
     * @var Frontend_Inspector_Module
     */
    private Frontend_Inspector_Module $module;
    private Settings&MockObject $settingsMock;
    private Asset_Manager_Contract&MockObject $assetManagerMock;

    public function setUp(): void {
        parent::setUp();
        $this->settingsMock = $this->createMock( Settings::class );
        $this->settingsMock->method( 'get_setting' )
            ->with( 'frontend_inspector_enabled' )
            ->willReturn( true );

        $this->assetManagerMock = $this->createMock( Asset_Manager_Contract::class );

        $this->module = new Frontend_Inspector_Module(
            $this->assetManagerMock,
            $this->settingsMock
        );
    }

    public function tearDown(): void {
        parent::tearDown();
        $this->module->shutdown();
    }

    public function test_filter_not_applied_when_module_not_active(): void {
        // Setup.
        add_filter( 'altinator/frontend_inspector/active', '__return_false' );
        $this->module->boot();

        $this->assertFalse( has_filter( 'wp_content_img_tag', [ $this->module, 'content_img_tag_callback' ] ) );

        $img_tag = '<img src="test.jpg" />';
        $filtered_img = apply_filters( 'wp_content_img_tag', $img_tag, 'test', 0 );

        // The image should remain unchanged.
        $this->assertEquals( $img_tag, $filtered_img );

        // Cleanup.
        remove_filter( 'altinator/frontend_inspector/active', '__return_false' );
    }

    public function test_filter_applied_when_module_active(): void {
        // Setup.
        $user = static::factory()->user->create( [ 'role' => 'administrator' ] );
        wp_set_current_user( $user );
        add_filter( 'altinator/frontend_inspector/active', '__return_true' );
        $this->module->boot();

        $this->assertTrue( (bool) has_filter( 'wp_content_img_tag', [ $this->module, 'content_img_tag_callback' ] ) );

        $img_tag = '<img src="test.jpg" />';
        $filtered_img = apply_filters( 'wp_content_img_tag', $img_tag, 'test', 0 );

        // The image should be changed.
        $this->assertNotEquals( $img_tag, $filtered_img );

        // Cleanup.
        remove_filter( 'altinator/frontend_inspector/active', '__return_true' );
        wp_set_current_user( 0 );
    }

    public function test_add_missing_alt_attribute_with_no_alt(): void {
        $img_tag = '<img src="test.jpg" />';
        $result = $this->module->add_missing_alt_attribute( $img_tag );

        // Should add data-altinator attribute with missing-alt value.
        $this->assertStringContainsString( 'data-altinator="missing-alt"', $result );

        // Should add role="img" attribute.
        $this->assertStringContainsString( 'role="img"', $result );

        // Should add aria-describedby attribute.
        $this->assertStringContainsString( 'aria-describedby="altinator-desc-', $result );

        // Should add tooltip.
        $this->assertStringContainsString( '<span id="altinator-desc-', $result );
        $this->assertStringContainsString( 'class="altinator-frontend-inspector-status"', $result );
    }

    public function test_add_missing_alt_attribute_with_empty_alt(): void {
        $img_tag = '<img src="test.jpg" alt="" />';
        $result = $this->module->add_missing_alt_attribute( $img_tag );

        // Should add data-altinator attribute with empty-alt value.
        $this->assertStringContainsString( 'data-altinator="empty-alt"', $result );

        // Should add role="img" attribute.
        $this->assertStringContainsString( 'role="img"', $result );

        // Should add aria-describedby attribute.
        $this->assertStringContainsString( 'aria-describedby="altinator-desc-', $result );

        // Should add tooltip.
        $this->assertStringContainsString( '<span id="altinator-desc-', $result );
        $this->assertStringContainsString( 'class="altinator-frontend-inspector-status"', $result );
    }

    public function test_add_missing_alt_attribute_with_existing_alt(): void {
        $img_tag = '<img src="test.jpg" alt="Existing alt text" />';
        $result = $this->module->add_missing_alt_attribute( $img_tag );

        // Should not modify the image tag.
        $this->assertEquals( $img_tag, $result );

        // Should not add data-altinator attribute.
        $this->assertStringNotContainsString( 'data-altinator', $result );

        // Should not add tooltip.
        $this->assertStringNotContainsString( 'class="altinator-frontend-inspector-status"', $result );
    }

    public function test_add_missing_alt_attribute_with_already_processed_image(): void {
        $img_tag = '<img src="test.jpg" data-altinator="missing-alt" />';
        $result = $this->module->add_missing_alt_attribute( $img_tag );

        // Should not modify the image tag.
        $this->assertEquals( $img_tag, $result );
    }

    public function test_add_missing_alt_attribute_with_non_image_html(): void {
        $html = '<p>This is a paragraph</p>';
        $result = $this->module->add_missing_alt_attribute( $html );

        // Should not modify the HTML.
        $this->assertEquals( $html, $result );
    }
}
