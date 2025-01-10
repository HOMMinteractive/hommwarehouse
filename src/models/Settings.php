<?php
/**
 * HOMM warehouse plugin for Craft CMS
 *
 * Craft CMS warehouse field
 *
 * @link      https://github.com/HOMMinteractive
 * @copyright Copyright (c) 2025 HOMM interactive
 */

namespace homm\hommwarehouse\models;

use craft\base\Model;

/**
 * Class Settings
 *
 * @author    Benjamin Ammann
 * @package   HOMMWarehouse
 * @since     0.0.1
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var bool
     */
    public bool $enabled = true;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['enabled'], 'boolean'],
        ];
    }
}
