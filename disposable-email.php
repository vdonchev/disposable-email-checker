<?php
/**
 * Plugin Name: Disposable Email Guard
 * Description: Blocks disposable email addresses during WordPress user registration.
 * Version: 1.1.1
 * Author: Donchev
 * Text Domain: disposable-email-guard
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

defined('ABSPATH') || exit;

define('DISPOSABLE_EMAIL_GUARD_VERSION', '1.1.1');
define('DISPOSABLE_EMAIL_GUARD_OPTION_NAME', 'disposable_email_guard_settings');
define('DISPOSABLE_EMAIL_GUARD_PLUGIN_FILE', __FILE__);

$disposable_email_guard_autoload = __DIR__ . '/vendor/autoload.php';

if (file_exists($disposable_email_guard_autoload)) {
    require_once $disposable_email_guard_autoload;
}

require_once __DIR__ . '/includes/domain-rules.php';
require_once __DIR__ . '/includes/api-client.php';
require_once __DIR__ . '/includes/settings.php';
require_once __DIR__ . '/includes/registration-validation.php';

add_action('plugins_loaded', 'disposable_email_guard_bootstrap');

function disposable_email_guard_bootstrap(): void
{
    disposable_email_guard_register_update_checker();
    disposable_email_guard_register_settings_hooks();
    disposable_email_guard_register_validation_hooks();
}

function disposable_email_guard_register_update_checker(): void
{
    if (!class_exists('YahnisElsts\\PluginUpdateChecker\\v5\\PucFactory')) {
        return;
    }

    $update_checker = YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
        'https://github.com/vdonchev/disposable-email-checker/',
        DISPOSABLE_EMAIL_GUARD_PLUGIN_FILE,
        'disposable-email'
    );

    $update_checker->setBranch('master');
}
