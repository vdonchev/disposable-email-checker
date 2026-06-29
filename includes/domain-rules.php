<?php

defined('ABSPATH') || exit;

function disposable_email_guard_normalize_domain(string $domain): string
{
    $domain = strtolower(trim($domain));
    $domain = ltrim($domain, '@');

    return sanitize_text_field($domain);
}

function disposable_email_guard_normalize_domain_list(string $domains): string
{
    $normalized = [];
    $lines = preg_split('/\r\n|\r|\n/', $domains);

    if (!is_array($lines)) {
        return '';
    }

    foreach ($lines as $line) {
        $domain = disposable_email_guard_normalize_domain($line);

        if ($domain !== '') {
            $normalized[$domain] = $domain;
        }
    }

    return implode("\n", array_values($normalized));
}

function disposable_email_guard_parse_domain_list(string $domains): array
{
    $normalized = disposable_email_guard_normalize_domain_list($domains);

    if ($normalized === '') {
        return [];
    }

    return explode("\n", $normalized);
}

function disposable_email_guard_extract_email_domain(string $email): string
{
    if (!is_email($email)) {
        return '';
    }

    $parts = explode('@', $email);

    if (count($parts) !== 2) {
        return '';
    }

    return disposable_email_guard_normalize_domain($parts[1]);
}

function disposable_email_guard_domain_is_listed(string $domain, string $domains): bool
{
    $domain = disposable_email_guard_normalize_domain($domain);

    if ($domain === '') {
        return false;
    }

    return in_array($domain, disposable_email_guard_parse_domain_list($domains), true);
}
