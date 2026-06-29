# AGENTS.md

- This is a small WordPress plugin; the plugin header and bootstrap live in `disposable-email.php`.
- Composer is used only for runtime dependencies; there is no build config, test config, or CI workflow, so do not invent lint, test, typecheck, or build commands until those files exist.
- Runtime target from the plugin header is WordPress 6.0+ and PHP 7.4+, so avoid newer PHP syntax/features.
- Files under `includes/` are loaded manually from `disposable-email.php`; add new runtime files there only if they are explicitly required by the bootstrap.
- Registration validation order is whitelist allow, blacklist block, then API check; API failures return `null` and registration is allowed.
- `.idea/` is intentionally ignored in `.gitignore` and should not be committed.

## Git Workflow

- Commit messages use `type (context): description message`.
- When the user says `commit`, decide how many logical commits to create.
- Each commit must bump the plugin version in both the `Version:` header and `DISPOSABLE_EMAIL_GUARD_VERSION` constant in `disposable-email.php`; choose the SemVer component to bump based on the change: patch for fixes/docs/internal maintenance, minor for backward-compatible features, major for breaking changes.
- When a commit changes the plugin version, create a matching Git tag in the form `vX.Y.Z` after the commit.
- When the user says `push`, commit first, tag any version bump commit, then push commits and tags.
