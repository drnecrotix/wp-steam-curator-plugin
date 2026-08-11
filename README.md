# BG-GAMER Steam Curator Widget

A lightweight WordPress plugin for displaying a responsive **BG-GAMER Steam Curator** follow widget through shortcodes, WordPress widgets, automatic post placement, or a floating layout.

## Features

- Responsive Steam Curator follow widget
- `[bg_gamer_steam_curator]` shortcode
- WordPress legacy widget support
- Settings page under **Settings → BG-GAMER Steam Curator**
- Optional automatic placement in posts
- Optional compact floating widget
- Manual follower and recommendation statistics
- Vanilla JavaScript click tracking
- Optional integration with an existing Google Analytics `gtag` installation

> The plugin does not simulate or perform a Steam follow. The primary action opens the configured Steam Curator page in a new browser tab.

## Requirements

- WordPress 6.0 or newer
- PHP 8.1 or newer
- Tested up to WordPress 6.8

## Installation

1. Download or clone this repository.
2. Place the plugin files inside a `bg-gamer-steam-curator` directory.
3. Zip the directory if installing through the WordPress dashboard.
4. Go to **WordPress Admin → Plugins → Add New → Upload Plugin**.
5. Upload the ZIP file and activate the plugin.
6. Open **Settings → BG-GAMER Steam Curator**.
7. Enter or confirm your Steam Curator URL and configure the widget options.

## Usage

### Default widget

```text
[bg_gamer_steam_curator]
```

### Custom URL and statistics

```text
[bg_gamer_steam_curator url="https://store.steampowered.com/curator/5043216-BG-Gamer/" followers="125" recommendations="84"]
```

### Compact sidebar layout

```text
[bg_gamer_steam_curator layout="compact" placement="sidebar"]
```

### Floating widget

```text
[bg_gamer_steam_curator layout="floating" floating="true" placement="floating"]
```

### Sticky widget

```text
[bg_gamer_steam_curator sticky="true"]
```

## Shortcode Attributes

The shortcode supports the following attributes:

| Attribute | Purpose |
| --- | --- |
| `url` | Steam Curator destination URL |
| `title` | Widget heading |
| `description` | Widget description |
| `followers` | Manual follower statistic |
| `recommendations` | Manual recommendation statistic |
| `layout` | Widget layout |
| `sticky` | Enables sticky presentation |
| `floating` | Enables floating presentation |
| `placement` | Tracking placement identifier |

## Click Tracking

Curator links expose tracking data such as:

```text
data-event="steam_curator_click"
data-placement="shortcode|sidebar|after_post|before_comments|floating|homepage"
```

The JavaScript component dispatches a browser event:

```javascript
window.dispatchEvent(
  new CustomEvent('bgGamerSteamCuratorClick', {
    detail: { placement, url }
  })
);
```

When `gtag` is already available on the page, the plugin can also send:

```javascript
gtag('event', 'steam_curator_click', { placement });
```

The plugin does **not** load Google Analytics itself.

## Default Curator

The plugin is configured for the BG-GAMER Steam Curator by default. The destination can be changed from the plugin settings or overridden through the shortcode.

## Development

The plugin is intentionally lightweight and uses standard WordPress APIs together with vanilla JavaScript for its front-end behavior. Contributions should keep compatibility, security, accessibility, and performance in mind.

When proposing changes, create a dedicated branch and submit a pull request rather than committing directly to `main`.

## Contributing

Bug reports, documentation improvements, and feature proposals are welcome. Keep pull requests focused on a single logical change and clearly describe what was changed and why.

## Version

Current stable version: **1.0.0**

## License

Copyright © Nikola Stoyanov / BG-GAMER.
