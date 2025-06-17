<?php

declare(strict_types=1);

namespace Tests\Support;

use Codeception\Actor;

/**
 * Inherited Methods
 * @method void wantTo($text)
 * @method void wantToTest($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause($vars = [])
 *
 * @SuppressWarnings(PHPMD)
*/
class EndToEndTester extends Actor
{
    use _generated\EndToEndTesterActions;

    /**
     * Define custom actions here
     */

    public function amOnAltinatorSettingsPage(): void {
        $this->amOnPage('/wp-admin/options-general.php?page=altinator-settings');
    }

    public function amInMediaLibrary(string $view = 'grid'): void {
        $view = in_array($view, ['grid', 'list']) ? $view : 'grid';
        $this->amOnPage('/wp-admin/upload.php?mode=' . $view);
    }
}
