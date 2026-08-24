# WooCommerce Attribute Manager

A modular WordPress/WooCommerce plugin for creating global attributes, generating reusable attribute groups, and quickly applying existing WooCommerce terms to products with an AI-assisted workflow.

**Developer:** [hessamzm](https://github.com/hessamzm)

## Requirements

- PHP 8.2+
- WordPress 7.0+
- WooCommerce 10.0+
- Administrator capability: `manage_woocommerce`

## Features

### Attribute Builder

Create WooCommerce global Attributes in bulk from a simple text structure:

```text
[attribute]
name: Camera Type
slug: camera_type
values: Dome | Bullet | PTZ
```

The plugin creates the global Attribute and its Terms without creating a custom database.

### Attribute Groups

Create reusable groups of existing WooCommerce Attributes:

```text
[group]
name: Camera Details
attributes: Camera Type | Resolution | Lens | Image Sensor
```

Groups can be applied from the WooCommerce Product Data > Attributes panel.

### AI Structure Guide

The guide provides two independent prompts:

1. **Attribute Creation Prompt** — generates an optimized Attribute structure for a business and its products.
2. **Attribute Group Creation Prompt** — automatically includes the store's existing WooCommerce Attributes and asks AI to organize them into reusable groups.

### Product AI Workflow

For a product:

1. Add an Attribute Group.
2. Click **Generate AI Prompt**.
3. The plugin automatically reads the Attributes currently selected for the product.
4. It loads the real WooCommerce Terms for those Attributes.
5. It reads the product title, description and short description.
6. It generates a product-specific AI prompt.
7. Paste the AI response into **AI Terms Input**.
8. Click **Apply Terms to Product**.

Only existing WooCommerce Terms are accepted. The plugin does not create new Terms during AI application.

### Settings

Version 2 includes a Settings page for:

- Interface language
- AI provider
- AI API endpoint
- AI API key
- AI model

The AI API configuration is intentionally preparation for a future automated workflow. Version 2 does not make automatic external AI API requests.

### Localization

The plugin uses the standard WordPress translation system and includes English as the default language plus Persian (`fa_IR`). Plugin language selection is isolated to this plugin's text domain and does not change the global WordPress site language.

To add another language, create the corresponding WordPress translation files in:

```text
languages/
```

The selected interface language can be configured from:

**Attribute Manager → Settings**

## Data and Storage

The plugin does **not create a custom database table**.

WooCommerce Attributes and Terms are stored using WooCommerce's native Attribute/Taxonomy APIs.

Attribute Groups are stored by the plugin for reuse, while deleting the plugin must not remove WooCommerce Attributes or Terms created through WooCommerce.

## Architecture

The plugin is organized into independent modules:

```text
woocommerce-attribute-manager/
├── assets/
├── includes/
│   ├── Admin/
│   ├── Attributes/
│   ├── Groups/
│   ├── Product/
│   ├── REST/
│   ├── Settings/
│   └── Support/
├── languages/
├── readme.txt
└── woocommerce-attribute-manager.php
```

## AI API Roadmap

The Settings page already provides the configuration foundation for future automation:

```text
Product
  ↓
Selected Attribute Groups
  ↓
Selected Attributes
  ↓
Product Information
  ↓
AI API
  ↓
Existing WooCommerce Terms
  ↓
Validation
  ↓
Apply Terms
```

Future versions can use the configured provider, endpoint, API key and model to automate this workflow.

## Development Principles

- PHP 8.2+
- WordPress-native APIs
- WooCommerce-native APIs
- Modular architecture
- No custom database tables
- Translation-ready strings
- English default language
- Persian translation included
- No automatic deletion of WooCommerce Attributes or Terms when the plugin is removed

## Installation

1. Download or clone the repository.
2. Place the plugin directory in:

```text
wp-content/plugins/
```

3. Activate **WooCommerce Attribute Manager**.
4. Make sure WooCommerce is active.
5. Open **Attribute Manager** from the WordPress admin menu.

## License

GPL-2.0-or-later.

## Developer

**hessamzm**

GitHub: https://github.com/hessamzm
