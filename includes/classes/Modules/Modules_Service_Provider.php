<?php

namespace GoodWP\Altinator\Modules;

use GoodWP\Altinator\Modules\Alt_Fallback\Alt_Fallback_Module;
use GoodWP\Altinator\Modules\Frontend_Inspector\Frontend_Inspector_Module;
use GoodWP\Altinator\Modules\Media_Library\Filter;
use GoodWP\Altinator\Modules\Media_Library\Media_Library_Module;
use GoodWP\Altinator\Modules\Media_Library\Quick_Edit;
use GoodWP\Altinator\Vendor\GoodWP\Common\DI\Service_Provider;

class Modules_Service_Provider extends Service_Provider {

    /**
     * @var class-string<Module>[]
     */
    protected array $modules = [
        Frontend_Inspector_Module::class,
        Alt_Fallback_Module::class,
        Media_Library_Module::class,
    ];

    public function register(): void {
        foreach ( $this->modules as $module ) {
            $this->container->singleton( $module );
        }

        $this->container->singleton( Quick_Edit::class );
        $this->container->singleton( Filter::class );
    }

    public function boot(): void {
        foreach ( $this->modules as $module_class ) {
            $module = $this->container->get( $module_class );
            if ( $module->is_enabled() ) {
                $module->boot();
            }
        }
    }
}
