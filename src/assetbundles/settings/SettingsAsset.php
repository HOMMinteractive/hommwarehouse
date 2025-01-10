<?php
/**
 * HOMM warehouse plugin for Craft CMS
 *
 * Craft CMS warehouse field
 *
 * @link      https://github.com/HOMMinteractive
 * @copyright Copyright (c) 2025 HOMM interactive
 */

namespace homm\hommwarehouse\assetbundles\settings;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    Benjamin Ammann
 * @package   HOMMWarehouse
 * @since     0.0.1
 */
class SettingsAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@homm/hommwarehouse/assetbundles/settings/dist";

        $this->depends = [
            CpAsset::class,
        ];

        parent::init();
    }
}
