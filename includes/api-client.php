<?php

defined('ABSPATH') || exit;

/**
 * Returns true when disposable, false when allowed, and null when the API cannot decide.
 */
function disposable_email_guard_check_api(string $email, array $settings): ?bool
{
    $endpoint = isset($settings['api_endpoint']) ? esc_url_raw((string) $settings['api_endpoint']) : '';
    $api_key = isset($settings['api_key']) ? sanitize_text_field((string) $settings['api_key']) : '';

    if ($endpoint === '' || $api_key === '' || !is_email($email)) {
        return null;
    }

    $response = wp_remote_post($endpoint, [
        'timeout' => 5,
        'headers' => [
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ],
        'body' => wp_json_encode([
            'email' => $email,
        ]),
    ]);

    if (is_wp_error($response)) {
        return null;
    }

    $status_code = (int) wp_remote_retrieve_response_code($response);

    if ($status_code < 200 || $status_code >= 300) {
        return null;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (!is_array($data) || !array_key_exists('disposable', $data)) {
        return null;
    }

    return (bool) $data['disposable'];
}
