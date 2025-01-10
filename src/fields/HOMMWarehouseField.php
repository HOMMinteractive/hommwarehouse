<?php

/**
 * HOMM warehouse plugin for Craft CMS
 *
 * Craft CMS warehouse field
 *
 * @link      https://github.com/HOMMinteractive
 * @copyright Copyright (c) 2025 HOMM interactive
 */

namespace homm\hommwarehouse\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\PreviewableFieldInterface;
use craft\helpers\Json;
use homm\hommwarehouse\assetbundles\hommwarehouse\HOMMWarehouseAsset;
use homm\hommwarehouse\HOMMWarehouse;
use yii\db\Schema;

/**
 * Class HOMMWarehouseField
 *
 * @author    Benjamin Ammann
 * @package   HOMMWarehouse
 * @since     0.0.1
 */
class HOMMWarehouseField extends Field implements PreviewableFieldInterface
{
    // Static Methods
    // =========================================================================

    /**
     * Returns the display name of this class.
     *
     * @return string The display name of this class.
     */
    public static function displayName(): string
    {
        return Craft::t('hommwarehouse', 'Warehouse stock');
    }

    /**
     * Return an array with the supported translation methods
     */
    public static function supportedTranslationMethods(): array
    {
        return [
            self::TRANSLATION_METHOD_NONE,
        ];
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getContentColumnType(): array|string
    {
        return Schema::TYPE_TEXT;
    }

    /**
     * @inheritdoc
     */
    public function normalizeValue(mixed $value, ?\craft\base\ElementInterface $element = null): mixed
    {
        if (gettype($value) == 'string') {
            $json = array_filter(json_decode($value, true));

            if (empty($json)) {
                $json = [
                    'updatedAt' => null,
                    'stock' => 0,
                ];
            }

            return $json;
        } elseif ($value === null) {
            return $value;
        } else {
            $value['updatedAt'] = date('Y-m-d H:i:s');
            $value['stock'] = $value['stock'] ?? 0;
            return json_encode($value);
        }
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml(mixed $value, ?\craft\base\ElementInterface $element = null): string
    {
        if (!HOMMWarehouse::$plugin->getSettings()->enabled) {
            return '';
        }

        // Register our asset bundle
        Craft::$app->getView()->registerAssetBundle(HOMMWarehouseAsset::class);

        // Get our id and namespace
        $id = Craft::$app->getView()->formatInputId($this->handle);
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);

        // Variables to pass down to our field JavaScript to let it namespace properly
        $jsonVars = [
            'id' => $id,
            'name' => $this->handle,
            'namespace' => $namespacedId,
            'prefix' => Craft::$app->getView()->namespaceInputId(''),
        ];
        $jsonVars = Json::encode($jsonVars);

        // Render the input template
        return Craft::$app->getView()->renderTemplate(
            'hommwarehouse/_components/fields/HOMMWarehouseField_input',
            [
                'name' => $this->handle,
                'value' => $value,
                'field' => $this,
                'id' => $id,
                'namespacedId' => $namespacedId,
            ]
        );
    }

    public function getTableAttributeHtml(mixed $value, ElementInterface $element): string
    {
        if (!HOMMWarehouse::$plugin->getSettings()->enabled) {
            return '';
        }

        // Register our asset bundle
        Craft::$app->getView()->registerAssetBundle(HOMMWarehouseAsset::class);

        // Render the attribute html template
        return Craft::$app->getView()->renderTemplate(
            'hommwarehouse/_components/HOMMWarehouseField_attribute',
            [
                'name' => $this->handle,
                'value' => $value,
                'field' => $this,
            ]
        );
    }
}
