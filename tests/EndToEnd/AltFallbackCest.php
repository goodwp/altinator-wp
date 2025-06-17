<?php

declare(strict_types=1);

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

final class AltFallbackCest {

    private const ATTACHMENT_ALT = 'Attachment Alt Text From Media Library';
    private const BLOCK_ALT = 'Specific Alt Text In Block';

    public function _before( EndToEndTester $I ): void {
    }

    public function try_core_media_text_block_uses_attachment_alt_when_no_block_alt( EndToEndTester $I ): void {
        $I->wantToTest( 'Core/media-text block: uses attachment alt if image block has no alt.' );
        $imageId = $I->haveTestImageInDatabase( 'dummy-image-1.png', 'Media Text Image 1', self::ATTACHMENT_ALT );

        $blocks = [
            $I->makeMediaTextBlock(
                $imageId,
                [],
                [
					$I->makeParagraphBlock( 'Some text.' ),
				]
            ),
        ];

        $postId = $I->havePostWithBlocksInDatabase( $blocks );
        $I->amOnPostUrl( $postId );
        $I->seeElement( "img[alt='" . self::ATTACHMENT_ALT . "']" );
    }

    public function try_core_media_text_block_uses_block_alt_when_set( EndToEndTester $I ): void {
        $I->wantToTest( 'Core/media-text block: uses block alt if image block has specific alt.' );
        $imageId = $I->haveTestImageInDatabase( 'dummy-image-2.png', 'Media Text Image 2', self::ATTACHMENT_ALT );

        $blocks = [
            $I->makeMediaTextBlock(
                $imageId,
                [ 'alt' => self::BLOCK_ALT ],
                [
					$I->makeParagraphBlock( 'Some text.' ),
				]
            ),
        ];

        $postId = $I->havePostWithBlocksInDatabase( $blocks );
        $I->amOnPostUrl( $postId );
        $I->seeElement( "img[alt='" . self::BLOCK_ALT . "']" );
        $I->dontSeeElement( "img[alt='" . self::ATTACHMENT_ALT . "']" );
    }

    public function try_core_image_block_uses_attachment_alt_when_no_block_alt( EndToEndTester $I ): void {
        $I->wantToTest( 'Core/image block: uses attachment alt if block alt is empty.' );
        $imageId = $I->haveTestImageInDatabase( 'dummy-image-1.png', 'Core Image 1', self::ATTACHMENT_ALT );
        $blocks = [
            $I->makeImageBlock( $imageId, [ 'alt' => '' ] ),
        ];
        $postId = $I->havePostWithBlocksInDatabase( $blocks );
        $I->amOnPostUrl( $postId );
        $I->seeElement( "img[alt='" . self::ATTACHMENT_ALT . "']" );
    }

    public function try_core_image_block_uses_block_alt_when_set( EndToEndTester $I ): void {
        $I->wantToTest( 'Core/image block: uses block alt if specified.' );
        $imageId = $I->haveTestImageInDatabase( 'dummy-image-2.png', 'Core Image 2', self::ATTACHMENT_ALT );
        $blocks = [
            $I->makeImageBlock( $imageId, [ 'alt' => self::BLOCK_ALT ] ),
        ];
        $postId = $I->havePostWithBlocksInDatabase( $blocks );
        $I->amOnPostUrl( $postId );
        $I->seeElement( "img[alt='" . self::BLOCK_ALT . "']" );
        $I->dontSeeElement( "img[alt='" . self::ATTACHMENT_ALT . "']" );
    }

    public function try_core_cover_block_uses_attachment_alt_when_no_block_alt( EndToEndTester $I ): void {
        $I->wantToTest( 'Core/cover block: uses attachment alt if block alt is empty.' );
        $imageId = $I->haveTestImageInDatabase( 'dummy-image-1.png', 'Cover Image', self::ATTACHMENT_ALT );
        $blocks = [
            $I->makeCoverBlock(
                $imageId,
                [ 'alt' => '' ],
                [
					$I->makeParagraphBlock( 'Cover block with an image that should get a fallback alt.' ),
				]
            ),
        ];
        $postId = $I->havePostWithBlocksInDatabase( $blocks );
        $I->amOnPostUrl( $postId );
        $I->seeElement( "img[alt='" . self::ATTACHMENT_ALT . "']" );
    }

    public function try_core_cover_block_uses_block_alt_when_set( EndToEndTester $I ): void {
        $I->wantToTest( 'Core/cover block: uses block alt if specified.' );
        $imageId = $I->haveTestImageInDatabase( 'dummy-image-2.png', 'Cover Image with Alt', self::ATTACHMENT_ALT );
        $blocks = [
            $I->makeCoverBlock(
                $imageId,
                [ 'alt' => self::BLOCK_ALT ],
                [
					$I->makeParagraphBlock( 'Cover block with a specific alt.' ),
				]
            ),
        ];
        $postId = $I->havePostWithBlocksInDatabase( $blocks );
        $I->amOnPostUrl( $postId );
        $I->seeElement( "img[alt='" . self::BLOCK_ALT . "']" );
        $I->dontSeeElement( "img[alt='" . self::ATTACHMENT_ALT . "']" );
    }

    public function try_core_gallery_block_inner_image_uses_attachment_alt_when_no_block_alt( EndToEndTester $I ): void {
        $I->wantToTest( 'Core/gallery block: inner image uses attachment alt when no block alt is set.' );
        $imageId = $I->haveTestImageInDatabase( 'dummy-image-1.png', 'Gallery Image Fallback', self::ATTACHMENT_ALT );

        $blocks = [
            $I->makeGalleryBlock(
                [
					$I->makeImageBlock( $imageId, [ 'alt' => '' ] ), // Should fall back
				]
            ),
        ];

        $postId = $I->havePostWithBlocksInDatabase( $blocks );
        $I->amOnPostUrl( $postId );
        $I->seeElement( "img[alt='" . self::ATTACHMENT_ALT . "']" );
    }

    public function try_core_gallery_block_inner_image_uses_block_alt_when_set( EndToEndTester $I ): void {
        $I->wantToTest( 'Core/gallery block: inner image uses block alt when set.' );
        $imageId = $I->haveTestImageInDatabase( 'dummy-image-2.png', 'Gallery Image Specific Alt', 'Some other attachment alt' );

        $blocks = [
            $I->makeGalleryBlock(
                [
					$I->makeImageBlock( $imageId, [ 'alt' => self::BLOCK_ALT ] ), // Should use block alt
				]
            ),
        ];

        $postId = $I->havePostWithBlocksInDatabase( $blocks );
        $I->amOnPostUrl( $postId );
        $I->seeElement( "img[alt='" . self::BLOCK_ALT . "']" );
    }

    public function try_module_does_not_process_unregistered_core_html_block( EndToEndTester $I ): void {
        $I->wantToTest( 'Module does not process unregistered core/html block images.' );
        $imageId = $I->haveTestImageInDatabase( 'dummy-image-1.png', 'HTML Block Image', self::ATTACHMENT_ALT );
        $imageUrl = $I->grabAttachmentUrl( $imageId );

        $htmlContent = "<img src='{$imageUrl}' alt=''>"; // Empty alt, should remain empty.

        $blocks = [
            [
				'blockName' => 'core/html',
				'attrs' => [],
				'innerHTML' => $htmlContent,
				'innerContent' => [ $htmlContent ],
			],
        ];

        $postId = $I->havePostWithBlocksInDatabase( $blocks );
        $I->amOnPostUrl( $postId );
        $I->seeElement( "img[alt='']" ); // Alt should still be empty
        $I->dontSeeElement( "img[alt='" . self::ATTACHMENT_ALT . "']" );
    }
}
