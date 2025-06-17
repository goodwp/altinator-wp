<?php

declare(strict_types=1);

namespace Tests\EndToEnd;

use Facebook\WebDriver\WebDriverKeys;
use Tests\Support\EndToEndTester;

final class FrontendInspectorCest {

    public function _before( EndToEndTester $I ): void {
        // Login as admin
        $I->loginAsAdmin();
    }

    public function test_inspector_can_be_activated_via_admin_bar( EndToEndTester $I ): void {
        $I->wantToTest( 'Frontend Inspector can be activated via admin bar' );

        // Create a post with an image that has no alt text
        $imageId = $I->haveTestImageInDatabase( 'dummy-image-1.png', 'Test Image', '' );
        $blocks = [
            $I->makeImageBlock( $imageId, [ 'alt' => '' ] ),
        ];
        $postId = $I->havePostWithBlocksInDatabase( $blocks );

        // Activate the Frontend Inspector
        $this->activateFrontendInspector( $I, $postId );

        // Check that the image has the data-altinator attribute
        $I->seeElement( 'img[data-altinator="empty-alt"]' );
    }

    /**
     * Helper method to activate the Frontend Inspector for a post
     */
    private function activateFrontendInspector( EndToEndTester $I, int $postId ): void {
        // Go to the post
        $I->amOnPostUrl( $postId );

        // Check that the admin bar item exists
        $I->seeElement( '.altinator-inspector-toggle' );

        // Click the admin bar item to activate the inspector
        $I->click( '.altinator-inspector-toggle' );

        // Wait for page to reload with the inspector active
        $I->waitForElement( 'img[data-altinator]' );
    }

    public function test_inspector_cannot_be_activated_when_module_disabled( EndToEndTester $I ): void {
        $I->wantToTest( 'Frontend Inspector cannot be activated when module is disabled' );
        $I->haveOptionInDatabase( 'altinator_frontend_inspector_enabled', 0 );

        // Create a post with an image that has no alt text
        $imageId = $I->haveTestImageInDatabase( 'dummy-image-1.png', 'Test Image', '' );
        $blocks = [
            $I->makeImageBlock( $imageId, [ 'alt' => '' ] ),
        ];
        $postId = $I->havePostWithBlocksInDatabase( $blocks );

        // Go to the post
        $I->amOnPostUrl( $postId );

        // Check that the admin bar item does not exist
        $I->dontSeeElement( '.altinator-inspector-toggle' );

        // Re-enable the module for other tests
        $I->haveOptionInDatabase( 'altinator_frontend_inspector_enabled', 1 );
    }

    public function test_image_outlines_and_classes_based_on_alt_status( EndToEndTester $I ): void {
        $I->wantToTest( 'Images have correct outlines and classes based on alt attribute status' );

        // Create images with different alt statuses
        $imageWithAlt = $I->haveTestImageInDatabase( 'dummy-image-1.png', 'Image With Alt', 'This is an alt text' );
        $imageWithEmptyAlt = $I->haveTestImageInDatabase( 'dummy-image-2.png', 'Image With Empty Alt', '' );
        $imageWithoutAlt = $I->haveTestImageInDatabase( 'dummy-image-3.png', 'Image Without Alt', null );

        // Create a post with these images
        $blocks = [
            $I->makeImageBlock( $imageWithAlt, [ 'alt' => 'This is an alt text' ] ),
            $I->makeImageBlock( $imageWithEmptyAlt, [ 'alt' => '' ] ),
            $I->makeImageBlock( $imageWithoutAlt ),
        ];
        $postId = $I->havePostWithBlocksInDatabase( $blocks );

        // Activate the Frontend Inspector
        $this->activateFrontendInspector( $I, $postId );

        // Check that images have correct data-altinator attributes
        $I->dontSeeElement( 'img[alt="This is an alt text"][data-altinator]' ); // Image with alt should not have data-altinator
        $I->seeElement( 'img[alt=""][data-altinator="empty-alt"]' ); // Image with empty alt should have data-altinator="empty-alt"
        $I->seeElement( 'img[data-altinator="missing-alt"]' ); // Image without alt should have data-altinator="missing-alt"
    }

    public function test_tooltip_messages_and_edit_links( EndToEndTester $I ): void {
        $I->wantToTest( 'Tooltip messages and edit links are correct' );

        // Create an image with empty alt
        $imageId = $I->haveTestImageInDatabase( 'dummy-image-1.png', 'Test Image', '' );
        $blocks = [
            $I->makeImageBlock( $imageId, [ 'alt' => '' ] ),
        ];
        $postId = $I->havePostWithBlocksInDatabase( $blocks );

        // Activate the Frontend Inspector
        $this->activateFrontendInspector( $I, $postId );

        // Check that the tooltip exists
        $I->seeElement( '.altinator-frontend-inspector-status' );

        // Check that the tooltip contains the correct message for empty alt
        $I->see( 'has an empty alt text', '.altinator-frontend-inspector-status' );

        // Hover over the image to show the tooltip
        $I->moveMouseOver( 'img[data-altinator="empty-alt"]' );
        // Check that the edit link exists
        $I->seeElement( '.altinator-frontend-inspector-edit-link' );
        $I->see( 'Edit here', '.altinator-frontend-inspector-edit-link' );
    }

    public function test_with_different_block_types( EndToEndTester $I ): void {
        $I->wantToTest( 'Frontend Inspector works with different block types' );

        // Create images for different blocks
        $imageBlockId = $I->haveTestImageInDatabase( 'dummy-image-1.png', 'Image Block', '' );
        $mediaTextBlockId = $I->haveTestImageInDatabase( 'dummy-image-2.png', 'Media Text Block', '' );
        $coverBlockId = $I->haveTestImageInDatabase( 'dummy-image-3.png', 'Cover Block', '' );

        // Create a post with different block types
        $blocks = [
            $I->makeImageBlock( $imageBlockId, [ 'alt' => '' ] ),
            $I->makeMediaTextBlock(
                $mediaTextBlockId,
                [ 'alt' => '' ],
                [ $I->makeParagraphBlock( 'Media Text Block content' ) ]
            ),
            $I->makeCoverBlock(
                $coverBlockId,
                [ 'alt' => '' ],
                [ $I->makeParagraphBlock( 'Cover Block content' ) ]
            ),
        ];
        $postId = $I->havePostWithBlocksInDatabase( $blocks );

        // Activate the Frontend Inspector
        $this->activateFrontendInspector( $I, $postId );

        // Check that all images have data-altinator attribute
        $I->seeNumberOfElements( 'img[data-altinator="empty-alt"]', 3 );

        // Check that all images have tooltips
        $I->seeNumberOfElements( '.altinator-frontend-inspector-status', 3 );
    }

    public function test_focusing_hovering_image_shows_tooltip( EndToEndTester $I ): void {
        $I->wantToTest( 'Focusing or hovering an image shows the tooltip' );

        // Create an image with empty alt
        $imageId = $I->haveTestImageInDatabase( 'dummy-image-1.png', 'Test Image', '' );
        $blocks = [
            $I->makeImageBlock( $imageId, [ 'alt' => '' ] ),
        ];
        $postId = $I->havePostWithBlocksInDatabase( $blocks );

        // Activate the Frontend Inspector
        $this->activateFrontendInspector( $I, $postId );

        // Check that the tooltip exists but might be hidden by CSS
        $I->seeElement( '.altinator-frontend-inspector-status' );

        // Hover over the image to show the tooltip
        $I->moveMouseOver( 'img[data-altinator="empty-alt"]' );

        // Check that the tooltip is visible
        // Note: This assumes that the CSS makes the tooltip visible on hover
        // We might need to check specific CSS properties or classes that indicate visibility
        $I->waitForElementVisible( '.altinator-frontend-inspector-status' );

        // Test keyboard focus as well
        $I->click( 'body' ); // Click elsewhere to remove focus
        $I->pressKey( 'img[data-altinator="empty-alt"]', WebDriverKeys::TAB );

        // Check that the tooltip is visible when focused
        $I->waitForElementVisible( '.altinator-frontend-inspector-status' );
    }

    public function test_inspector_can_be_deactivated_via_admin_bar( EndToEndTester $I ): void {
        $I->wantToTest( 'Frontend Inspector can be deactivated via admin bar' );

        // Create a post with an image that has an empty alt attribute
        $imageId = $I->haveTestImageInDatabase( 'dummy-image-1.png', 'Test Image', '' );
        $blocks = [
            $I->makeImageBlock( $imageId, [ 'alt' => '' ] ),
        ];
        $postId = $I->havePostWithBlocksInDatabase( $blocks );

        // Activate the Frontend Inspector
        $this->activateFrontendInspector( $I, $postId );

        // Verify that the inspector is active
        $I->seeElement( 'img[data-altinator="empty-alt"]' );
        $I->seeElement( '.altinator-frontend-inspector-status' );
        $I->seeElement( 'body.has-altinator-frontend-inspector' );

        // Click the admin bar item again to deactivate the inspector
        $I->click( '.altinator-inspector-toggle' );

        // Wait for page to reload with the inspector inactive
        $I->waitForElement( 'img' );

        // Check that the body doesn't have the "has-altinator-frontend-inspector" class
        $I->dontSeeElement( 'body.has-altinator-frontend-inspector' );

        // Hover over the image and check that the tooltip is not visible
        $I->moveMouseOver( 'img' );
        $I->dontSeeElement( '.altinator-frontend-inspector-status' );
    }
}
