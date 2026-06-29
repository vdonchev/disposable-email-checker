<?php

defined('ABSPATH') || exit;

function disposable_email_guard_register_validation_hooks(): void
{
    add_filter('registration_errors', 'disposable_email_guard_validate_registration_email', 10, 3);
}

function disposable_email_guard_validate_registration_email(WP_Error $errors, string $sanitized_user_login, string $user_email): WP_Error
{
    unset($sanitized_user_login);

    if (!is_email($user_email)) {
        return $errors;
    }

    $settings = disposable_email_guard_get_settings();
    $domain = disposable_email_guard_extract_email_domain($user_email);

    if ($domain === '') {
        return $errors;
    }

    if (disposable_email_guard_domain_is_listed($domain, $settings['whitelist_domains'])) {
        return $errors;
    }

    if (disposable_email_guard_domain_is_listed($domain, $settings['blacklist_domains'])) {
        $errors->add(
            'disposable_email_guard_blacklisted_domain',
            __('This email domain is not allowed.', 'disposable-email-guard')
        );

        return $errors;
    }

    $is_disposable = disposable_email_guard_check_api($user_email, $settings);

    if ($is_disposable === true) {
        $errors->add(
            'disposable_email_guard_disposable_email',
            __('Disposable email addresses are not allowed.', 'disposable-email-guard')
        );
    }

    return $errors;
}
