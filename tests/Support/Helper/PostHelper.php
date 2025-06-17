<?php

declare(strict_types=1);

namespace Tests\Support\Helper;

use lucatume\WPBrowser\Module\WPDb;
use lucatume\WPBrowser\Module\WPWebDriver as WebDriver;

class PostHelper extends \Codeception\Module {

    /**
     * Creates a post with the given blocks and returns its permalink.
     *
     * @param array[] $overrides Array of block definitions.
     * @param array   $overrides Optional. Additional arguments for post creation.
     * @return int The post ID.
     */
    public function havePostWithBlocksInDatabase( array $blocks, array $overrides = [] ): int {
        $I = $this->getModule( WPDb::class );

        $content = $this->getSerializedBlockOutput( $blocks );
        $defaultArgs = [
            'post_title' => 'Test Post ' . uniqid(),
            'post_content' => $content,
            'post_status' => 'publish',
            'post_type' => 'post',
        ];
        $postArray = array_merge( $defaultArgs, $overrides );

        $postId = $I->havePostInDatabase( $postArray );
        return $postId;
    }

    public function amOnPostUrl( int $postId, array $urlArgs = [] ): void {
        $I = $this->getModule( WebDriver::class );
        $url = '/?p=' . $postId;
        if (!empty($urlArgs)) {
            $url .= '&' . http_build_query($urlArgs);
        }
        $I->amOnPage( $url );
    }

    /**
     * Serialize an array of blocks into a string.
     *
     * @param array[] $blocksArray Array of block definitions.
     * @return string The serialized block string.
     */
    public function getSerializedBlockOutput( array $blocksArray ): string {
        return serialize_blocks( $blocksArray );
    }

    public function makeImageBlock( int $attachmentId, array $attrs = [] ): array {
        return [
            'blockName' => 'core/image',
            'attrs' => [
                'id' => $attachmentId,
                ...$attrs,
            ],
            'innerBlocks' => [],
            'innerHTML' =>
                sprintf(
                    '\n<figure class="wp-block-image size-large"><img src="%1$s" %3$s class="wp-image-%2$s"/></figure>\n',
                    $this->getModule( AttachmentHelper::class )->grabAttachmentUrl( $attachmentId ),
                    $attachmentId,
                    isset( $attrs['alt'] ) ? sprintf( 'alt="%s"', esc_attr( $attrs['alt'] ) ) : ''
                ),
            'innerContent' => [
                sprintf(
                    '\n<figure class="wp-block-image size-large"><img src="%1$s" %3$s class="wp-image-%2$s"/></figure>\n',
                    $this->getModule( AttachmentHelper::class )->grabAttachmentUrl( $attachmentId ),
                    $attachmentId,
                    isset( $attrs['alt'] ) ? sprintf( 'alt="%s"', esc_attr( $attrs['alt'] ) ) : ''
                ),
            ],
        ];
    }

    public function makeMediaTextBlock( int $attachmentId, array $attrs = [], array $innerBlocks = [] ): array {
        $attachmentUrl = $this->getModule( AttachmentHelper::class )->grabAttachmentUrl( $attachmentId );
        return [
            'blockName' => 'core/media-text',
            'attrs' => [
                'mediaId' => $attachmentId,
                'mediaLink' => $attachmentUrl,
                'mediaType' => 'image',
                ...$attrs,
            ],
            'innerBlocks' => $innerBlocks,
            'innerHTML' => sprintf(
                '\n<div class="wp-block-media-text is-stacked-on-mobile"><figure class="wp-block-media-text__media"><img src="%1$s" %3$s class="wp-image-%2$s size-full"/></figure><div class="wp-block-media-text__content"></div></div>\n',
                $attachmentUrl,
                $attachmentId,
                isset( $attrs['alt'] ) ? sprintf( 'alt="%s"', esc_attr( $attrs['alt'] ) ) : ''
            ),
            'innerContent' => [
                sprintf(
                    '\n<div class="wp-block-media-text is-stacked-on-mobile"><figure class="wp-block-media-text__media"><img src="%1$s" %3$s class="wp-image-%2$s size-full"/></figure><div class="wp-block-media-text__content">',
                    $attachmentUrl,
                    $attachmentId,
                    isset( $attrs['alt'] ) ? sprintf( 'alt="%s"', esc_attr( $attrs['alt'] ) ) : ''
                ),
                null,
                '</div></div>\n',
            ],
        ];
    }

    public function makeCoverBlock( int $attachmentId, array $attrs = [], array $innerBlocks = [] ): array {
        $attachmentUrl = $this->getModule( AttachmentHelper::class )->grabAttachmentUrl( $attachmentId );
        return [
            'blockName' => 'core/cover',
            'attrs' => [
                'url' => $attachmentUrl,
                'id' => $attachmentId,
                'customOverlayColor' => '#ededed',
                ...$attrs,
            ],
            'innerBlocks' => $innerBlocks,
            'innerHTML' => sprintf(
                '\n<div class="wp-block-cover is-light"><img class="wp-block-cover__image-background wp-image-%1$s size-large" %3$s src="%2$s" data-object-fit="cover"/><span aria-hidden="true" class="wp-block-cover__background has-background-dim" style="background-color:#ededed"></span><div class="wp-block-cover__inner-container"></div></div>\n',
                $attachmentId,
                $attachmentUrl,
                isset( $attrs['alt'] ) ? sprintf( 'alt="%s"', esc_attr( $attrs['alt'] ) ) : ''
            ),
            'innerContent' => [
                sprintf(
                    '\n<div class="wp-block-cover is-light"><img class="wp-block-cover__image-background wp-image-%1$s size-large" %3$s src="%2$s" data-object-fit="cover"/><span aria-hidden="true" class="wp-block-cover__background has-background-dim" style="background-color:#ededed"></span><div class="wp-block-cover__inner-container">\n',
                    $attachmentId,
                    $attachmentUrl,
                    isset( $attrs['alt'] ) ? sprintf( 'alt="%s"', esc_attr( $attrs['alt'] ) ) : ''
                ),
                null,
                '</div></div>\n',
            ],
        ];
    }

    public function makeGalleryBlock( array $imageBlocks ): array {
        return [
            'blockName' => 'core/gallery',
            'attrs' => [],
            'innerBlocks' => $imageBlocks,
            'innerHTML' => '\n<figure class="wp-block-gallery has-nested-images columns-default is-cropped"></figure>\n',
            'innerContent' => [
                '\n<figure class="wp-block-gallery has-nested-images columns-default is-cropped">',
                null,
                '</figure>\n',
            ],
        ];
    }

    public function makeParagraphBlock( string $text, array $attrs = [] ): array {
        return [
            'blockName' => 'core/paragraph',
            'attrs' => $attrs,
            'innerHTML' => sprintf( '\n<p>%s</p>\n', $text ),
            'innerContent' => [
                sprintf( '\n<p>%s</p>\n', $text ),
            ],
            'innerBlocks' => [],
        ];
    }
}
