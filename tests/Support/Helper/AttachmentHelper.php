<?php

declare(strict_types=1);

namespace Tests\Support\Helper;

use Codeception\Module;
use lucatume\WPBrowser\Module\WPDb;

class AttachmentHelper extends Module
{

    public function haveTestImageInDatabase(string $filename, ?string $title = null, ?string $altText = null): int {
        $overrides = [];
        if ($title) {
            $overrides['post_title'] = $title;
        }
        $meta = [];
        if ($altText) {
            $meta['_wp_attachment_image_alt'] = $altText;
        }
        if(!empty($meta)) {
            $overrides['meta'] = $meta;
        }
        $I = $this->getModule(WPDb::class);
        return $I->haveAttachmentInDatabase(
            codecept_data_dir('images/' . $filename),
            'now',
            $overrides
        );
    }

    /**
     * Gets the URL of an attachment by its ID.
     *
     * @param int $attachmentId The ID of the attachment.
     * @return string The URL of the attachment.
     */
    public function grabAttachmentUrl(int $attachmentId): string
    {
        $I = $this->getModule(WPDb::class);
        $attachmentFile = $I->grabAttachmentAttachedFile($attachmentId);
        return $I->grabSiteUrl() . '/wp-content/uploads/' . $attachmentFile;
    }

}
