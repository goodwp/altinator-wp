# Altinator

A WordPress plugin to help you optimize your image alternative texts and make your site more accessible.

## Features
- Media library filters for images with/without alt text
- Help tab in the media library about alt texts
- Quick-edit alt texts directly in the media library list view
- Frontend inspector module to highlight images missing alt text on your site
- Settings page to enable/disable features and configure modules
- Extensible via WordPress hooks and filters

## Installation
1. Upload the plugin files to the `/wp-content/plugins/altinator` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. (Optional) Configure the plugin via the "Altinator" settings under Settings > Altinator.

## Altinator Plugin Hooks

### Actions
- [**altinator/boot**](includes/classes/Plugin.php): Fires after the plugin and all its services are booted. Use this to boot your own service providers/services after Altinator is ready.
- [**altinator/init_container**](includes/classes/Plugin.php): Fires after the DI container is initialized. Allows adding/changing services or providers.
- [**altinator/migration**](includes/classes/Migration/Migration.php): Fires after plugin migrations are run. Params: new version, old version.
- [**altinator/activation**](includes/classes/Migration/Activation.php): Fires after plugin activation code. Param: main plugin instance.
- [**altinator/deactivation**](includes/classes/Migration/Deactivation.php): Fires after plugin deactivation code. Param: main plugin instance.

### Filters
- [**altinator/media_library/enable**](includes/classes/Modules/Media_Library/Media_Library_Module.php): Filter whether the Media Library module (filter, quick edit, help tab) is enabled.
- [**altinator/frontend_inspector/enable**](includes/classes/Modules/Frontend_Inspector/Frontend_Inspector_Module.php): Filter whether the Frontend Inspector module is enabled.
- [**altinator/frontend_inspector/capability**](includes/classes/Modules/Frontend_Inspector/Frontend_Inspector_Module.php): Filter the capability required to activate the Frontend Inspector module. Defaults to 'upload_files'.
- [**altinator/frontend_inspector/active**](includes/classes/Modules/Frontend_Inspector/Frontend_Inspector_Module.php): Filter whether the Frontend Inspector module is active on this request (base value is set via frontend inspector URL param).
- [**altinator/frontend_inspector/tooltip**](includes/classes/Modules/Frontend_Inspector/Frontend_Inspector_Module.php): Filter the HTML for the frontend inspector tooltip.
- [**altinator/alt_fallback/enable**](includes/classes/Modules/Alt_Fallback/Alt_Fallback_Module.php): Filter whether the Alt Fallback module is enabled.
- [**altinator/alt_fallback/blocks**](includes/classes/Modules/Alt_Fallback/Alt_Fallback_Module.php): Filter the blocks that should have the alt fallback applied.
- [**altinator/ai_alt_generation/enable**](includes/classes/Modules/Ai_Alt_Generation/Ai_Alt_Generation_Module.php): Filter whether the AI Alt Generation module is enabled.
- [**altinator/ai_alt_generation/capability**](includes/classes/Modules/Ai_Alt_Generation/Rest_Api.php): Filter the capability required to generate alt text. Default: upload_files.
- [**altinator/ai_alt_generation/api_key**](includes/classes/Modules/Ai_Alt_Generation/Ai_Api_Service.php): Filter the API key used for AI alt generation.
- [**altinator/ai_alt_generation/api_base**](includes/classes/Modules/Ai_Alt_Generation/Ai_Api_Service.php): Filter the API base URL for AI alt generation.
- [**altinator/ai_alt_generation/context**](includes/classes/Modules/Ai_Alt_Generation/Ai_Api_Service.php): Filter the context array used for AI generation (attachment id, language, image src, etc).

## Local Development Environment

The local development environment is based on [DDEV](https://ddev.readthedocs.io/en/latest/).

After the first setup, remove the `##ddev-generated` header from the wp-config.php file and change the ddev-include code
to

```php
$ddev_settings = __DIR__ . '/wp-config-ddev.php';
if ( getenv( 'IS_DDEV_PROJECT' ) == 'true' && is_readable( $ddev_settings ) ) {
	require_once( $ddev_settings );
}
```

It is recommended to run all tasks inside the ddev container.

### Development Tasks/Scripts

- PHP code style: `composer run phpcs` / `composer run phpcs:fix`
- PHPStan: `composer run phpstan`
- Code quality: `composer run code-quality`
- Tests: WP-Browser and Codeception
- JS/CSS formatting: `npm run format`
- JS/CSS build: `npm run build`
- Translations: `composer run i18n`
- Build plugin zip: `composer run build`

## Releasing

1. Run linting, phpcs, phpcs:fix + phpstan
2. Update version
    - in main plugin file headers (`/altinator.php`)
    - in `src/Plugin.php`
    - in `package.json`
    - in `composer.json` + run `ddev composer update -- --lock`
    - stable tag in `readme.txt`
3. Add changelog entry in `readme.txt` and `CHANGELOG.md`
4. Update "Tested up to" as needed
5. Run translation updates
6. Create PR/merge into main (triggers CI jobs)
7. Create a new release with a new tag in main `vX.Y.Z` with changelog

## License
GPL-3.0-or-later

## See Also
- [WordPress.org plugin page](https://wordpress.org/plugins/altinator/)
- [Screenshots and user documentation](./readme.txt)

## Security

If you believe you have found a security vulnerability, please do not create a public issue or post it publicly anywhere else. You can responsibly disclose the problem directly via GitHubs security reporting feature via email to mail at fabiantodt.at
