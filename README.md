# HOMM warehouse plugin for Craft CMS

Craft CMS warehouse field type

> This plugin adds a new warehouse field type

![Screenshot](resources/img/plugin-logo.svg)

## Requirements

This plugin requires Craft CMS 5.x.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require homm/hommwarehouse

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for HOMM warehouse.

## HOMM warehouse Overview

This plugin adds a new warehouse field type

## Configuring HOMM warehouse

Go to _Settings > HOMM Warehouse_:

Here you can set if the plugin should be enabled/disabled.

When disabled, the warehouse fields stay in the field layouts (no data is lost), but they are hidden in the edit forms and the export is not available.

### Configuration file

The settings can be overridden per environment in `config/hommwarehouse.php`. Values set there take precedence over the Control Panel setting, which stays the fallback for everything not set in the file.

Copy [`src/config.php`](src/config.php) to `config/hommwarehouse.php` as a starting point. It reads `HOMMWAREHOUSE_ENABLED` from the `.env` (only if filled) and lets you list further fields in `hiddenFieldHandles`, which are hidden together with the warehouse fields when the plugin is disabled.

## Using HOMM warehouse (not done yet)

1. Go to _Settings > Fields_ and create a new field.
2. Within the _Field Type_ choose _HOMM warehouse_
3. Assign the field to a section
4. Now you can set an amount within your entries warehouse field

Basic usage in the template (Twig):

```html
<!-- Retrieve the stock -->
<span>{{ entry.warehouseField.stock }}</span>

<!-- Retrieve the date on which the stock was last changed -->
<span>{{ entry.warehouseField.updatedAt }}</span>
```

## HOMM warehouse Roadmap

Some things to do, and ideas for potential features:

* Add twig variables

Brought to you by [HOMM interactive](https://github.com/HOMMinteractive)
