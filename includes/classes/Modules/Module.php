<?php

namespace GoodWP\Altinator\Modules;

use GoodWP\Altinator\Vendor\GoodWP\Common\Contracts\Bootable;

abstract class Module implements Bootable {
    /**
     * Whether the module is enabled.
     * A module can use a setting, a filter hook or a default value to set this.
     *
     * @return bool
     */
    abstract public function is_enabled(): bool;
}
