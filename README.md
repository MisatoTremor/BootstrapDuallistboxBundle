# BootstrapDuallistboxBundle

This bundle eases integration of bootstrap-duallistbundle into your Symfony2 project.

## Installation

### Installation by Composer

Add BootstrapDuallistbox bundle as a dependency to the composer.json of your application

```json
{
    "require": {
        ...
        "misato/bootstrap-duallistbox-bundle": "dev-master"
        ...
    }
}
```

Activate automatic symlinking after composer update/install

```json
{
    "scripts": {
        "post-install-cmd": [
            "Misato\\BootstrapDuallistboxBundle\\Composer\\ScriptHandler::postInstallSymlinkBootstrapDuallistbox"
        ],
        "post-update-cmd": [
            "Misato\\BootstrapDuallistboxBundle\\Composer\\ScriptHandler::postInstallSymlinkBootstrapDuallistbox"
        ]
    }
}
```

There is also a console command to check and / or install this symlink:

for less:

```bash
    php app/console misato:bootstrap-duallistbox:symlink
```

### Add BootstrapDuallistboxBundle to your application kernel.

```php
// app/AppKernel.php
<?php
    // ...
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Misato\BootstrapDuallistbox\MisatoBootstrapDuallistboxBundle(),
        );
    }
```

### Add bootstrap-duallistbox resources to your web folder

To copy the bootstrap-duallistbox css and javascript files to the web folder you can use the command below:

```bash
    php app/console assets:install web/
```
