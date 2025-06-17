<?php

declare(strict_types=1);


namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

final class SettingsCest {


    public function test_see_settings_page( EndToEndTester $I ): void {
        $I->loginAsAdmin();
        $I->amOnAltinatorSettingsPage();
        $I->waitForElement( '.altinator-settings' );
        $I->see( 'Altinator Settings', 'h1' );
    }

    public function test_cant_access_settings_page_as_non_admin( EndToEndTester $I ): void {
        $user = $I->haveUserInDatabase( 'test-user', 'editor', [ 'user_pass' => 'password' ] );
        $I->loginAs( 'test-user', 'password' );
        $I->amOnAltinatorSettingsPage();
        $I->see( 'Sorry, you are not allowed to access this page.' );
        $I->dontSee( 'Altinator Settings', 'h1' );
    }

    public function test_frontend_inspector_module_settings( EndToEndTester $I ): void {
        $I->loginAsAdmin();
        $I->amOnAltinatorSettingsPage();
        $I->waitForElement( '.altinator-settings' );

        // Frontend Module
        $I->see( 'Enable Frontend Inspector' );
        // Default value
        $I->seeCheckboxIsChecked( '.altinator-frontend-inspector-enabled input' );
        $I->dontSeeOptionInDatabase( 'altinator_frontend_inspector_enabled' );

        // Disable
        $I->uncheckOption( '.altinator-frontend-inspector-enabled input' );
        $I->dontSeeCheckboxIsChecked( '.altinator-frontend-inspector-enabled input' );
        $I->click( 'Save' );
        $I->wait( 1 );
        $I->seeOptionInDatabase( 'altinator_frontend_inspector_enabled', 0 );

        // Enable
        $I->checkOption( '.altinator-frontend-inspector-enabled input' );
        $I->seeCheckboxIsChecked( '.altinator-frontend-inspector-enabled input' );
        $I->click( 'Save' );
        $I->wait( 1 );
        $I->seeOptionInDatabase( 'altinator_frontend_inspector_enabled', 1 );
    }

    public function test_alt_fallback_module_settings( EndToEndTester $I ): void {
        $I->loginAsAdmin();
        $I->amOnAltinatorSettingsPage();
        $I->waitForElement( '.altinator-settings' );

        // Alt Fallback
        $I->see( 'Enable Alt Fallbacks' );
        // Default value
        $I->seeCheckboxIsChecked( '.altinator-alt-fallback-enabled input' );
        $I->dontSeeOptionInDatabase( 'altinator_alt_fallback_enabled' );

        // Disable
        $I->uncheckOption( '.altinator-alt-fallback-enabled input' );
        $I->dontSeeCheckboxIsChecked( '.altinator-alt-fallback-enabled input' );
        $I->click( 'Save' );
        $I->wait( 1 );
        $I->seeOptionInDatabase( 'altinator_alt_fallback_enabled', 0 );

        // Enable
        $I->checkOption( '.altinator-alt-fallback-enabled input' );
        $I->seeCheckboxIsChecked( '.altinator-alt-fallback-enabled input' );
        $I->click( 'Save' );
        $I->wait( 1 );
        $I->seeOptionInDatabase( 'altinator_alt_fallback_enabled', 1 );
    }
}
