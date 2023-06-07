# How to build the project in LOCAL environment

## Install the composer

```shell
composer update
```

## Install all NPM packages

```shell
npm instal
```

## Create link to stored files folder

```shell
php artisan storage:link
```

# How to build the project in PRODUCTION environment

## Delete ignored files, folders and more. (OPTIONAL)
>
> **Note:** This command is used to force the deletion of some project files in order to clear some caches or even to renew them after a new builder.

```shell
rm -r -Force .\vendor\, .\node_modules\, .\composer.lock, .\package-lock.json
```

## Install the production-optimized composer
>
> **Note:** For an installation with files and dependencies for development just remove the flags *"--optimize-autoloader --no-dev"*.

```shell
composer update --optimize-autoloader --no-dev
```

## Install all NPM packages

```shell
npm instal
```

## Create link to stored files folder

```shell
php artisan storage:link
```

## Completely clear cache of Laravel app

```shell
php artisan cache:clear;
php artisan config:clear;
php artisan event:clear;
php artisan optimize:clear;
php artisan route:clear;
php artisan view:clear;
```

## Create project cache for production

```shell
php artisan config:cache;
php artisan event:cache;
php artisan route:cache;
php artisan view:cache;
```
