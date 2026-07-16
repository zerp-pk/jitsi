# zerp/jitsi

Jitsi Meet module for the [Zerp](https://github.com/zerp-pk) ERP platform. Schedule and manage Jitsi Meet video conferences for the Zerp ERP platform, no account or API key required by default

## Requirements

- PHP 8.2+
- A Laravel application with package auto-discovery enabled (built for Zerp, Laravel 12)

## Installation

```bash
composer require zerp/jitsi
```

The package auto-registers via Laravel's package discovery - no manual service provider registration needed.

## What it provides

- `Zerp\Jitsi\Providers\JitsiServiceProvider` - boots this module's routes, migrations, and settings
- Frontend pages/components under `src/Resources/js`

## License

MIT - see [LICENSE](LICENSE).
