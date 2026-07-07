<?php

/**
 * Plugin consts.
 *
 * @since      1.2.9
 * @package    shortcodes-finder
 * @author     Scribit <wordpress@scribit.it>
 */

if (! defined('ABSPATH')) exit;

define('SHORTCODES_FINDER_VERSION', '1.6.2');

define('SHORTCODES_FINDER_OPTION_VERSION', 'sf_version');    // From version 1.3.0
define('SHORTCODES_FINDER_OPTION_DISABLE_UNUSED', 'sf_disable_unused');    // From version 1.3.0
define('SHORTCODES_FINDER_OPTION_DISABLED_SHORTCODES', 'sf_disabled_shortcodes');    // From version 1.3.0

if (!defined('SHORTCODES_FINDER_PLUGIN_SLUG')) {
    define('SHORTCODES_FINDER_PLUGIN_SLUG', 'shortcodes_finder');
}

if (!defined('SHORTCODES_FINDER_NONCE_ACTION')) {
    define('SHORTCODES_FINDER_NONCE_ACTION', 'shortcodes_finder_search');    // From version 1.6.2
}
