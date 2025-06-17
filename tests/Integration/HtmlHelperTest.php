<?php

namespace Tests\Integration;

use GoodWP\Altinator\Helper\Html_Helper;
use lucatume\WPBrowser\TestCase\WPTestCase;
use Tests\Support\IntegrationTester;

class HtmlHelperTest extends WPTestCase {

    /**
     * @var IntegrationTester
     */
    protected $tester;

    public function test_get_alt_attribute_status_returns_1_for_alt_attribute() {
        $string = '<img src="https://example.com/image.jpg" alt="This is an alt attribute" title="test title" class="block" />';
        $this->assertEquals( 1, Html_Helper::get_alt_attribute_status( $string ) );
        $this->assertTrue( Html_Helper::has_alt_attribute( $string ) );
    }

    public function test_get_alt_attribute_status_returns_0_for_empty_alt_attribute() {
        $string = '<img src="https://example.com/image.jpg" alt="" title="test title" class="block" />';
        $this->assertEquals( 0, Html_Helper::get_alt_attribute_status( $string ) );
        $this->assertFalse( Html_Helper::has_alt_attribute( $string ) );
    }

    public function test_get_alt_attribute_status_returns_negative_1_for_empty_alt_attribute() {
        $string = '<img src="https://example.com/image.jpg" title="test title" class="block" />';
        $this->assertEquals( -1, Html_Helper::get_alt_attribute_status( $string ) );
        $this->assertFalse( Html_Helper::has_alt_attribute( $string ) );
    }

    public function test_get_alt_attribute_status_returns_1_for_alt_attribute_in_figure() {
        $string = '<figure class="figure">
                <img src="https://example.com/image.jpg" alt="This is an alt attribute" title="test title" class="block" />
            <figcaption>This is a caption</figcaption>
            </figure>';
        $this->assertEquals( 1, Html_Helper::get_alt_attribute_status( $string ) );
        $this->assertTrue( Html_Helper::has_alt_attribute( $string ) );
    }

    public function test_get_alt_attribute_status_returns_0_for_empty_alt_attribute_in_figure() {
        $string = '<figure class="figure">
                <img src="https://example.com/image.jpg" alt="" title="test title" class="block" />
            <figcaption>This is a caption</figcaption>
            </figure>';
        $this->assertEquals( 0, Html_Helper::get_alt_attribute_status( $string ) );
        $this->assertFalse( Html_Helper::has_alt_attribute( $string ) );
    }

    public function test_get_alt_attribute_status_returns_negative_1_for_empty_alt_attribute_in_figure() {
        $string = '<figure class="figure">
                <img src="https://example.com/image.jpg" title="test title" class="block" />
            <figcaption>This is a caption</figcaption>
            </figure>';
        $this->assertEquals( -1, Html_Helper::get_alt_attribute_status( $string ) );
        $this->assertFalse( Html_Helper::has_alt_attribute( $string ) );
    }
}
