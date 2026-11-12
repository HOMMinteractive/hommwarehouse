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
use craft\events\CreateFieldLayoutFormEvent;
use craft\events\RegisterElementExportersEvent;
use craft\fieldlayoutelements\CustomField;
use craft\models\FieldLayout;
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

        if (HOMMWarehouse::$plugin->getSettings()->enabled) {
            Event::on(
                Entry::class,
                Element::EVENT_REGISTER_EXPORTERS,
                function(RegisterElementExportersEvent $event) {
                    $event->exporters[] = WarehouseExporter::class;
                }
            );
        } else {
            // The fields stay in the layouts (no data is lost), they are only hidden in edit forms
            Event::on(
                FieldLayout::class,
                FieldLayout::EVENT_CREATE_FORM,
                function(CreateFieldLayoutFormEvent $event) {
                    $this->hideWarehouseFields($event);
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
     * Removes warehouse fields and the configured further fields from element edit forms.
     */
    protected function hideWarehouseFields(CreateFieldLayoutFormEvent $event): void
    {
        $hiddenHandles = $this->getSettings()->hiddenFieldHandles;
        $tabs = [];

        foreach ($event->tabs as $tab) {
            $elements = array_values(array_filter(
                $tab->getElements(),
                fn($element) => !(
                    $element instanceof CustomField
                    && (
                        $element->getField() instanceof HOMMWarehouseField
                        || in_array($element->getField()->handle, $hiddenHandles, true)
                    )
                )
            ));

            if (count($elements) !== count($tab->getElements())) {
                // Clone so the cached field layout keeps all its elements
                $tab = clone $tab;
                $tab->setElements($elements);
            }

            $tabs[] = $tab;
        }

        $event->tabs = $tabs;
    }

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
