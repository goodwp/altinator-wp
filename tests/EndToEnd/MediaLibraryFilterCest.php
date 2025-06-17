<?php

declare(strict_types=1);


namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

final class MediaLibraryFilterCest {

    public function _before( EndToEndTester $I ): void {
        // Code here will be executed before each test.
    }

    public function try_to_use_alt_filter_in_grid_view( EndToEndTester $I ): void {
        // Setup images
        $dummy_image_1 = $I->haveTestImageInDatabase( 'dummy-image-1.png', 'image without alt', null );
        $dummy_image_2 = $I->haveTestImageInDatabase( 'dummy-image-2.png', 'image with alt', 'Image showing the text 1024x768 and come circle patterns' );

        // Go to media library
        $I->loginAsAdmin();
        $I->amInMediaLibrary( 'grid' );

        // Check see alt filter
        $I->seeElement( '#media-attachment-altinator-alt-filter' );
        $I->see( 'Alt Text: All' );

        // Check base view with both images.
        $I->see( 'Showing 2 of 2 media items' );

        // Filter for images without alt text
        $I->selectOption( '#media-attachment-altinator-alt-filter', 'No Alt Text' );
        $I->wait( 1 ); // Ajax request
        $I->see( 'Showing 1 of 1 media items' );
        $I->seeElement( 'li.attachment[data-id="' . $dummy_image_1 . '"]' );

        // Filter for images with alt text
        $I->selectOption( '#media-attachment-altinator-alt-filter', 'Has Alt Text' );
        $I->wait( 1 ); // Ajax request
        $I->see( 'Showing 1 of 1 media items' );
        $I->seeElement( 'li.attachment[data-id="' . $dummy_image_2 . '"]' );
    }

    public function try_to_use_alt_filter_in_list_view( EndToEndTester $I ): void {
        // Setup images
        $dummy_image_1 = $I->haveTestImageInDatabase( 'dummy-image-1.png', 'image without alt', null );
        $dummy_image_2 = $I->haveTestImageInDatabase( 'dummy-image-2.png', 'image with alt', 'Image showing the text 1024x768 and come circle patterns' );

        // Go to media library
        $I->loginAsAdmin();
        $I->amInMediaLibrary( 'list' );

        // Check see alt filter
        $I->seeElement( '#altinator-alt-filter-bar' );
        $I->see( 'Alt Text: All' );

        // Check base view with both images.
        $I->see( '2 items' );

        // Filter for images without alt text
        $I->selectOption( '#altinator-alt-filter-bar', 'No Alt Text' );
        $I->click( 'Filter' );
        $I->see( '1 item' );
        $I->see( 'image without alt' );
        $I->seeElement( 'tr#post-' . $dummy_image_1 );
        $I->dontSee( 'image with alt' );
        $I->dontSeeElement( 'tr#post-' . $dummy_image_2 );

        // Filter for images with alt text
        $I->selectOption( '#altinator-alt-filter-bar', 'Has Alt Text' );
        $I->click( 'Filter' );
        $I->see( '1 item' );
        $I->see( 'image with alt' );
        $I->seeElement( 'tr#post-' . $dummy_image_2 );
        $I->dontSee( 'image without alt' );
        $I->dontSeeElement( 'tr#post-' . $dummy_image_1 );
    }
}
