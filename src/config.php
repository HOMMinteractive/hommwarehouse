<?php
/**
 * HOMM warehouse plugin for Craft CMS
 *
 * Craft CMS warehouse field
 *
 * @link      https://github.com/HOMMinteractive
 * @copyright Copyright (c) 2025 HOMM interactive
 */

use craft\helpers\App;

/**
 * HOMMWarehouse config.php
 *
 * This file exists only as a template for the HOMMWarehouse settings.
 * It does nothing on its own.
 *
 * Don't edit this file, instead copy it to 'craft/config' as 'hommwarehouse.php'
 * and make your changes there to override the settings from the Control Panel.
 * Settings that are not set here keep their Control Panel value.
 */

$config = [
    // Handles of further fields that belong to the warehouse (e.g. a stock total field),
    // hidden in edit forms together with the warehouse fields when the plugin is disabled.
    'hiddenFieldHandles' => [],
];

// Enable / disable the plugin per environment, e.g. HOMMWAREHOUSE_ENABLED=false in the .env.
// Only set if the variable is filled, otherwise the Control Panel setting applies.
$enabled = App::env('HOMMWAREHOUSE_ENABLED');

if ($enabled !== null && $enabled !== '') {
    $config['enabled'] = App::parseBooleanEnv('$HOMMWAREHOUSE_ENABLED') ?? true;
}

return $config;
