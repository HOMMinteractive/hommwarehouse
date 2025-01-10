<?php
/**
 * HOMM warehouse plugin for Craft CMS
 *
 * Craft CMS warehouse field
 *
 * @link      https://github.com/HOMMinteractive
 * @copyright Copyright (c) 2025 HOMM interactive
 */

namespace homm\hommwarehouse;

use Craft;
use craft\base\Element;
use craft\base\Plugin;
use craft\elements\Entry;
use craft\services\Fields;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterElementExportersEvent;
use homm\hommwarehouse\assetbundles\settings\SettingsAsset;
use homm\hommwarehouse\exporters\WarehouseExporter;
use homm\hommwarehouse\fields\HOMMWarehouseField;
use homm\hommwarehouse\models\Settings;
use yii\base\Event;

/**
 * Class HOMMWarehouse
 *
 * @author    Benjamin Ammann
 * @package   HOMMWarehouse
 * @since     0.0.1
 */
class HOMMWarehouse extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * HOMMWarehouse::$plugin
     *
     * @var HOMMWarehouse
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * To execute your plugin’s migrations, you’ll need to increase its schema version.
     *
     * @var string
     */
    public string $schemaVersion = '1.0.0';

    /**
     * @var bool
     */
    public bool $hasCpSettings = true;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = HOMMWarehouseField::class;
            }
        );

        if (!HOMMWarehouse::$plugin->getSettings()->enabled) {
            Event::on(
                Entry::class,
                Element::EVENT_REGISTER_EXPORTERS,
                function(RegisterElementExportersEvent $event) {
                    $event->exporters[] = WarehouseExporter::class;
                }
            );
        }

        Craft::info(
            Craft::t(
                'hommwarehouse',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel(): ?\craft\base\Model
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): ?string
    {
        Craft::$app->getView()->registerAssetBundle(SettingsAsset::class);

        return Craft::$app->view->renderTemplate(
            'hommwarehouse/settings',
            [
                'settings' => $this->getSettings(),
            ]
        );
    }
}
