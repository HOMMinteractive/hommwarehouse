<?php
/**
 * HOMM warehouse plugin for Craft CMS
 *
 * Craft CMS warehouse field
 *
 * @link      https://github.com/HOMMinteractive
 * @copyright Copyright (c) 2025 HOMM interactive
 */

namespace homm\hommwarehouse\assetbundles\hommwarehouse;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * Class HOMMWarehouseAsset
 *
 * @author    Benjamin Ammann
 * @package   HOMMWarehouse
 * @since     0.0.1
 */
class HOMMWarehouseAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * Initializes the bundle.
     */
    public function init()
    {
        // define the path that your publishable resources live
        $this->sourcePath = "@homm/hommwarehouse/assetbundles/hommwarehouse/dist";

        // define the dependencies
        $this->depends = [
            CpAsset::class,
        ];

        // define the relative path to CSS/JS files that should be registered with the page
        // when this asset bundle is registered
        $this->js = [
            'js/HOMMWarehouseField.js',
        ];

        $this->css = [
            'css/HOMMWarehouseField.css',
        ];

        parent::init();
    }
}
