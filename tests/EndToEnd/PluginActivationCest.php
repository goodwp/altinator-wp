<?php

declare(strict_types=1);

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

class PluginActivationCest {

    public function try_activating_plugin( EndToEndTester $I ): void {
        $I->loginAsAdmin();
        $I->amOnPluginsPage();

        $I->seePluginActivated( 'altinator' );

        $I->deactivatePlugin( 'altinator' );

        $I->seePluginDeactivated( 'altinator' );

        $I->activatePlugin( 'altinator' );

        $I->seePluginActivated( 'altinator' );
    }
}
