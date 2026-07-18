=== BG-GAMER Steam Curator Widget ===
Contributors: BG-GAMER
Tags: steam, curator, widget, shortcode, steam games
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 8.1
Stable tag: 1.0.0
License: @dr.necrotix | Nikola Stoyanov

Professional Steam Curator follow widget for BG-GAMER.

== Description ==

Adds a responsive Steam Curator follow widget with:

* Shortcode: [bg_gamer_steam_curator]
* WordPress widget/block-compatible legacy widget
* Settings page under Settings -> BG-GAMER Steam Curator
* Optional automatic placement in posts
* Optional floating compact widget
* Manual follower and recommendation stats
* Vanilla JavaScript click tracking

The widget does not simulate a Steam follow. The main button opens the configured Steam Curator page in a new tab.

== Installation ==

1. Zip the `bg-gamer-steam-curator` folder.
2. Go to WordPress Admin -> Plugins -> Add New -> Upload Plugin.
3. Upload the ZIP file and activate the plugin.
4. Go to Settings -> BG-GAMER Steam Curator.
5. Enter or confirm the Steam Curator URL.

Default Steam Curator URL:

https://store.steampowered.com/curator/5043216-BG-Gamer/

== Shortcode examples ==

Default widget:

`[bg_gamer_steam_curator]`

Override URL and stats:

`[bg_gamer_steam_curator url="https://store.steampowered.com/curator/5043216-BG-Gamer/" followers="125" recommendations="84"]`

Compact sidebar-style widget:

`[bg_gamer_steam_curator layout="compact" placement="sidebar"]`

Floating widget:

`[bg_gamer_steam_curator layout="floating" floating="true" placement="floating"]`

Sticky widget:

`[bg_gamer_steam_curator sticky="true"]`

Supported shortcode attributes:

* url
* title
* description
* followers
* recommendations
* layout
* sticky
* floating
* placement

== Tracking ==

Click links include:

* data-event="steam_curator_click"
* data-placement="shortcode|sidebar|after_post|before_comments|floating|homepage"

The script dispatches:

`window.dispatchEvent(new CustomEvent('bgGamerSteamCuratorClick', { detail: { placement, url } }))`

If `gtag` already exists, it also sends:

`gtag('event', 'steam_curator_click', { placement })`

The plugin does not load Google Analytics by itself.
