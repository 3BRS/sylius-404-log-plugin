<p align="center">
    <a href="https://www.3brs.com" target="_blank">
        <img src="https://3brs1.fra1.cdn.digitaloceanspaces.com/3brs/logo/3BRS-logo-sylius-200.png"/>
    </a>
</p>
<h1 align="center">
404 Log Plugin
<br />
    <a href="https://packagist.org/packages/3brs/sylius-404Log-plugin" title="License" target="_blank">
        <img src="https://img.shields.io/packagist/l/3brs/sylius-404Log-plugin.svg" />
    </a>
    <a href="https://packagist.org/packages/3brs/sylius-404Log-plugin" title="Version" target="_blank">
        <img src="https://img.shields.io/packagist/v/3brs/sylius-404Log-plugin.svg" />
    </a>
    <a href="https://circleci.com/gh/3BRS/sylius-404Log-plugin" title="Build status" target="_blank">
        <img src="https://circleci.com/gh/3BRS/sylius-404Log-plugin.svg?style=shield" />
    </a>
</h1>

## Features

* Logs 404 errors

## Installation

1. Run `composer require 3brs/sylius-404Log-plugin`.
2. Register `\ThreeBRS\Sylius404LogPlugin\ThreeBRSSylius404LogPlugin` in your Kernel. 
3. Import the plugin configuration in your `config/packages/_sylius.yaml`:
   ```yaml
       - { resource: "@ThreeBRSSylius404LogPlugin/Resources/config/config.yaml" }
   ```
4. Import the plugin routes in your `config/routes.yaml`:
   ```yaml
   three_brs_sylius_404_log_plugin:
       resource: "@ThreeBRSSylius404LogPlugin/Resources/config/routes.yaml"
   ```
5. Create and run doctrine database migrations.

## Development

### Usage

- Create symlink from .env.dist to .env or create your own .env file
- Develop your plugin in `/src`
- See `bin/` for useful commands

### Testing

After your changes you must ensure that the tests are still passing.

```bash
$ composer install
$ bin/console doctrine:schema:create -e test
$ bin/phpstan.sh
$ bin/ecs.sh
```

License
-------
This library is under the MIT license.

Credits
-------
Developed by [3BRS](https://3brs.com)
