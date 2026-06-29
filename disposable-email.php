<?php
/**
 * Plugin Name: Disposable Email Guard
 * Description: Blocks disposable email addresses during WordPress user registration.
 * Version: 1.0.0
 * Author: Donchev
 * Text Domain: disposable-email-guard
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

defined('ABSPATH') || exit;

define('DISPOSABLE_EMAIL_GUARD_VERSION', '1.0.0');
define('DISPOSABLE_EMAIL_GUARD_OPTION_NAME', 'disposable_email_guard_settings');
define('DISPOSABLE_EMAIL_GUARD_PLUGIN_FILE', __FILE__);

require_once __DIR__ . '/includes/domain-rules.php';
require_once __DIR__ . '/includes/api-client.php';
require_once __DIR__ . '/includes/settings.php';
require_once __DIR__ . '/includes/registration-validation.php';

add_action('plugins_loaded', 'disposable_email_guard_bootstrap');

function disposable_email_guard_bootstrap(): void
{
    disposable_email_guard_register_settings_hooks();
    disposable_email_guard_register_validation_hooks();
}
