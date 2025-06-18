<?php

declare(strict_types=1);


namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

final class QuickEditCest {

    public function _before( EndToEndTester $I ): void {
        // Code here will be executed before each test.
    }

    public function try_to_edit_existing_alt_text( EndToEndTester $I ): void {
        // Setup image with existing alt text
        $original_alt_text = 'Original alt text for testing';
        $new_alt_text = 'Updated alt text after editing';
        $image_id = $I->haveTestImageInDatabase( 'dummy-image-1.png', 'Image with alt text', $original_alt_text );
        $row_selector = 'tr#post-' . $image_id . ' .column-altinator-alt';

        // Go to media library list view
        $I->loginAsAdmin();
        $I->amInMediaLibrary( 'list' );
        $I->wait(1);

        // Verify the original alt text is displayed
        $I->see( $original_alt_text, $row_selector . ' .altinator-alt__text' );

        // Click edit button
        $I->click( '.altinator-alt__toggle-edit', $row_selector );

        // Verify the alt text is in the input field
        $I->seeInField( $row_selector . ' textarea', $original_alt_text );

        // Change the alt text
        $I->fillField( $row_selector . ' textarea', $new_alt_text );

        // Save the changes
        $I->click( 'Save', $row_selector );

        // Wait for the save operation to complete
        $I->waitForText( 'Saved', 5, $row_selector . ' .altinator-alt__notification' );

        // Verify the new alt text is displayed
        $I->see( $new_alt_text, $row_selector . ' .altinator-alt__text' );

        // Verify the alt text is updated in the database
        $I->seeInDatabase(
            'wp_postmeta',
            [
				'post_id' => $image_id,
				'meta_key' => '_wp_attachment_image_alt',
				'meta_value' => $new_alt_text,
			]
        );
    }

    public function try_to_cancel_alt_text_edit( EndToEndTester $I ): void {
        // Setup image with existing alt text
        $original_alt_text = 'Original alt text that should remain';
        $edited_alt_text = 'This text should not be saved';
        $image_id = $I->haveTestImageInDatabase( 'dummy-image-2.png', 'Image for cancel test', $original_alt_text );
        $row_selector = 'tr#post-' . $image_id . ' .column-altinator-alt';

        // Go to media library list view
        $I->loginAsAdmin();
        $I->amInMediaLibrary( 'list' );

        // Verify the original alt text is displayed
        $I->see( $original_alt_text, $row_selector . ' .altinator-alt__text' );

        // Click edit button
        $I->click( '.altinator-alt__toggle-edit', $row_selector );

        // Change the alt text but don't save
        $I->fillField( $row_selector . ' textarea', $edited_alt_text );

        // Click cancel
        $I->click( 'Cancel', $row_selector );

        // Verify the original alt text is still displayed
        $I->see( $original_alt_text, $row_selector . ' .altinator-alt__text' );

        // Verify the alt text in the database is unchanged
        $I->seeInDatabase(
            'wp_postmeta',
            [
				'post_id' => $image_id,
				'meta_key' => '_wp_attachment_image_alt',
				'meta_value' => $original_alt_text,
			]
        );

        // Click edit again
        $I->click( '.altinator-alt__toggle-edit', $row_selector );

        // Verify the edited text is still in the input (not saved but remembered in UI)
        $I->seeInField( 'textarea', $edited_alt_text );
    }

    public function try_to_add_alt_text_to_image_without_alt( EndToEndTester $I ): void {
        // Setup image without alt text
        $new_alt_text = 'Newly added alt text';
        $image_id = $I->haveTestImageInDatabase( 'dummy-image-3.png', 'Image without alt text', null );
        $row_selector = 'tr#post-' . $image_id . ' .column-altinator-alt';

        // Go to media library list view
        $I->loginAsAdmin();
        $I->amInMediaLibrary( 'list' );

        // Verify no alt text is displayed
        $I->see( 'No alt-text', $row_selector );

        // Click edit button
        $I->click( '.altinator-alt__toggle-edit', $row_selector );

        // Add alt text
        $I->fillField( $row_selector . ' textarea', $new_alt_text );

        // Save the changes
        $I->click( 'Save', $row_selector );

        // Wait for the save operation to complete
        $I->waitForText( 'Saved', 5, $row_selector . ' .altinator-alt__notification' );

        // Verify the new alt text is displayed
        $I->see( $new_alt_text, $row_selector .' .altinator-alt__text' );

        // Verify the alt text is added to the database
        $I->seeInDatabase(
            'wp_postmeta',
            [
				'post_id' => $image_id,
				'meta_key' => '_wp_attachment_image_alt',
				'meta_value' => $new_alt_text,
			]
        );
    }

    public function try_to_verify_saved_message_appears( EndToEndTester $I ): void {
        // Setup image
        $original_alt_text = 'Alt text for save message test';
        $new_alt_text = 'Updated text to trigger save message';
        $image_id = $I->haveTestImageInDatabase( 'dummy-image-1.png', 'Image for save message test', $original_alt_text );
        $row_selector = 'tr#post-' . $image_id . ' .column-altinator-alt';

        // Go to media library list view
        $I->loginAsAdmin();
        $I->amInMediaLibrary( 'list' );

        // Click edit button
        $I->click( '.altinator-alt__toggle-edit', $row_selector );

        // Change the alt text
        $I->fillField( $row_selector . ' textarea', $new_alt_text );

        // Save the changes
        $I->click( 'Save', $row_selector );

        // Verify the saved message appears
        $I->waitForText( 'Saved', 5, $row_selector );
        $I->seeElement( '.altinator-alt__notification[role="status"]' );

        $I->wait(1);

        // Verify the message disappears when editing again
        $I->click( '.altinator-alt__toggle-edit', $row_selector );
        $I->dontSee( 'Saved', $row_selector . ' .altinator-alt__notification' );
    }
}
