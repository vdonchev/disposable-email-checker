<?php

defined('ABSPATH') || exit;

function disposable_email_guard_register_settings_hooks(): void
{
    add_action('admin_menu', 'disposable_email_guard_add_settings_page');
    add_action('admin_init', 'disposable_email_guard_register_settings');
}

function disposable_email_guard_default_settings(): array
{
    return [
        'enabled_on_registration' => '0',
        'api_endpoint' => '',
        'api_key' => '',
        'whitelist_domains' => '',
        'blacklist_domains' => '',
        'disposable_message' => disposable_email_guard_default_disposable_message(),
    ];
}

function disposable_email_guard_default_disposable_message(): string
{
    return __('Disposable email addresses are not allowed.', 'disposable-email-guard');
}

function disposable_email_guard_get_settings(): array
{
    $settings = get_option(DISPOSABLE_EMAIL_GUARD_OPTION_NAME, []);

    if (!is_array($settings)) {
        $settings = [];
    }

    return wp_parse_args($settings, disposable_email_guard_default_settings());
}

function disposable_email_guard_add_settings_page(): void
{
    add_options_page(
        __('Disposable Email Guard', 'disposable-email-guard'),
        __('Disposable Email Guard', 'disposable-email-guard'),
        'manage_options',
        'disposable-email-guard',
        'disposable_email_guard_render_settings_page'
    );
}

function disposable_email_guard_register_settings(): void
{
    register_setting(
        'disposable_email_guard_settings',
        DISPOSABLE_EMAIL_GUARD_OPTION_NAME,
        [
            'type' => 'array',
            'sanitize_callback' => 'disposable_email_guard_sanitize_settings',
            'default' => disposable_email_guard_default_settings(),
        ]
    );

    add_settings_section(
        'disposable_email_guard_api_section',
        __('API Settings', 'disposable-email-guard'),
        'disposable_email_guard_render_api_section',
        'disposable-email-guard'
    );

    add_settings_field(
        'enabled_on_registration',
        __('Enable on Registration', 'disposable-email-guard'),
        'disposable_email_guard_render_enabled_on_registration_field',
        'disposable-email-guard',
        'disposable_email_guard_api_section'
    );

    add_settings_field(
        'api_endpoint',
        __('API Endpoint URL', 'disposable-email-guard'),
        'disposable_email_guard_render_api_endpoint_field',
        'disposable-email-guard',
        'disposable_email_guard_api_section'
    );

    add_settings_field(
        'api_key',
        __('API Key', 'disposable-email-guard'),
        'disposable_email_guard_render_api_key_field',
        'disposable-email-guard',
        'disposable_email_guard_api_section'
    );

    add_settings_section(
        'disposable_email_guard_domain_section',
        __('Domain Rules', 'disposable-email-guard'),
        'disposable_email_guard_render_domain_section',
        'disposable-email-guard'
    );

    add_settings_field(
        'whitelist_domains',
        __('Whitelist Domains', 'disposable-email-guard'),
        'disposable_email_guard_render_whitelist_field',
        'disposable-email-guard',
        'disposable_email_guard_domain_section'
    );

    add_settings_field(
        'blacklist_domains',
        __('Blacklist Domains', 'disposable-email-guard'),
        'disposable_email_guard_render_blacklist_field',
        'disposable-email-guard',
        'disposable_email_guard_domain_section'
    );

    add_settings_field(
        'disposable_message',
        __('Disposable Email Message', 'disposable-email-guard'),
        'disposable_email_guard_render_disposable_message_field',
        'disposable-email-guard',
        'disposable_email_guard_domain_section'
    );
}

function disposable_email_guard_sanitize_settings($input): array
{
    if (!is_array($input)) {
        $input = [];
    }

    $disposable_message = isset($input['disposable_message']) ? sanitize_text_field((string) $input['disposable_message']) : '';

    if ($disposable_message === '') {
        $disposable_message = disposable_email_guard_default_disposable_message();
    }

    return [
        'enabled_on_registration' => !empty($input['enabled_on_registration']) ? '1' : '0',
        'api_endpoint' => isset($input['api_endpoint']) ? esc_url_raw((string) $input['api_endpoint']) : '',
        'api_key' => isset($input['api_key']) ? sanitize_text_field((string) $input['api_key']) : '',
        'whitelist_domains' => isset($input['whitelist_domains']) ? disposable_email_guard_normalize_domain_list((string) $input['whitelist_domains']) : '',
        'blacklist_domains' => isset($input['blacklist_domains']) ? disposable_email_guard_normalize_domain_list((string) $input['blacklist_domains']) : '',
        'disposable_message' => $disposable_message,
    ];
}

function disposable_email_guard_render_api_section(): void
{
    echo '<p>' . esc_html__('Configure the API used to check whether an email address is disposable. If the API is unavailable, registration is allowed.', 'disposable-email-guard') . '</p>';
}

function disposable_email_guard_render_domain_section(): void
{
    echo '<p>' . esc_html__('Enter one domain per line. Whitelisted domains are always allowed. Blacklisted domains are always blocked.', 'disposable-email-guard') . '</p>';
}

function disposable_email_guard_render_enabled_on_registration_field(): void
{
    $settings = disposable_email_guard_get_settings();

    printf(
        '<label><input type="checkbox" name="%1$s[enabled_on_registration]" value="1" %2$s /> %3$s</label>',
        esc_attr(DISPOSABLE_EMAIL_GUARD_OPTION_NAME),
        checked('1', $settings['enabled_on_registration'], false),
        esc_html__('Block disposable emails during user registration.', 'disposable-email-guard')
    );
}

function disposable_email_guard_render_api_endpoint_field(): void
{
    $settings = disposable_email_guard_get_settings();

    printf(
        '<input type="url" class="regular-text" name="%1$s[api_endpoint]" value="%2$s" placeholder="https://api.example.com/check" />',
        esc_attr(DISPOSABLE_EMAIL_GUARD_OPTION_NAME),
        esc_attr($settings['api_endpoint'])
    );
}

function disposable_email_guard_render_api_key_field(): void
{
    $settings = disposable_email_guard_get_settings();

    printf(
        '<input type="password" class="regular-text" name="%1$s[api_key]" value="%2$s" autocomplete="off" />',
        esc_attr(DISPOSABLE_EMAIL_GUARD_OPTION_NAME),
        esc_attr($settings['api_key'])
    );
}

function disposable_email_guard_render_whitelist_field(): void
{
    $settings = disposable_email_guard_get_settings();

    disposable_email_guard_render_domain_textarea('whitelist_domains', $settings['whitelist_domains']);
}

function disposable_email_guard_render_blacklist_field(): void
{
    $settings = disposable_email_guard_get_settings();

    disposable_email_guard_render_domain_textarea('blacklist_domains', $settings['blacklist_domains']);
}

function disposable_email_guard_render_disposable_message_field(): void
{
    $settings = disposable_email_guard_get_settings();

    printf(
        '<input type="text" class="regular-text" name="%1$s[disposable_message]" value="%2$s" />',
        esc_attr(DISPOSABLE_EMAIL_GUARD_OPTION_NAME),
        esc_attr($settings['disposable_message'])
    );

    echo '<p class="description">' . esc_html__('Shown when the API identifies an email address as disposable.', 'disposable-email-guard') . '</p>';
}

function disposable_email_guard_render_domain_textarea(string $field, string $value): void
{
    printf(
        '<textarea class="large-text code" rows="8" name="%1$s[%2$s]">%3$s</textarea>',
        esc_attr(DISPOSABLE_EMAIL_GUARD_OPTION_NAME),
        esc_attr($field),
        esc_textarea($value)
    );
}

function disposable_email_guard_render_settings_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    echo '<div class="wrap">';
    echo '<h1>' . esc_html__('Disposable Email Guard', 'disposable-email-guard') . '</h1>';
    echo '<form action="options.php" method="post">';

    settings_fields('disposable_email_guard_settings');
    do_settings_sections('disposable-email-guard');
    submit_button();

    echo '</form>';
    echo '</div>';
}
