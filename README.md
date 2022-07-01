<p align="center">
    <a href="https://medoo.in" target="_blank"><img src="https://cloud.githubusercontent.com/assets/1467904/19835326/ca62bc36-9ebd-11e6-8b37-7240d76319cd.png"></a>
</p>

<p align="center">
    <a href="https://github.com/catfan/Medoo/actions"><img alt="Build Status" src="https://github.com/lorddeveloper/Medoo/actions/workflows/php.yml/badge.svg"></a>
    <a href="https://packagist.org/packages/catfan/medoo"><img alt="Total Downloads" src="https://poser.pugx.org/lorddeveloper/medoo/downloads"></a>
    <a href="https://packagist.org/packages/catfan/medoo"><img alt="Latest Stable Version" src="https://poser.pugx.org/lorddeveloper/medoo/v/stable"></a>
    <a href="https://packagist.org/packages/catfan/medoo"><img alt="License" src="https://poser.pugx.org/lorddeveloper/medoo/license"></a>
    <a href="https://opencollective.com/medoo"><img alt="Backers on Open Collective" src="https://opencollective.com/Medoo/backers/badge.svg"></a>
    <a href="https://opencollective.com/medoo"><img alt="Sponsors on Open Collective" src="https://opencollective.com/Medoo/sponsors/badge.svg"> </a>
</p>

> The lightweight PHP database framework to accelerate development

## Features

* **Lightweight** - Portable with only one file.

* **Easy** - Easy to learn and use, friendly construction.

* **Powerful** - Supports various common and complex SQL queries, data mapping and prevents SQL injection.

* **Compatible** - Supports MySQL, MSSQL, SQLite, MariaDB, PostgreSQL, Sybase, Oracle, and more.

* **Friendly** - Works well with every PHP framework, like Laravel, Codeigniter, Yii, Slim, and frameworks that are supporting singleton extension or composer.

* **Free** - Under the MIT license, you can use it anywhere, whatever you want.

## Requirement

PHP 8.0+ and installed PDO extension.

## Get Started

### Install via composer

Add Medoo to the composer.json configuration file.
```
$ composer require jove/medoo
```

And update the composer
```
$ composer update
```

```php
// Require Composer's autoloader.
require __DIR__ .'/vendor/autoload.php';

use Amp\Loop;
// Using Medoo namespace.
use Medoo;

// Running the event loop
Loop::run(function () {
    // Connect the database.
    $database = yield Medoo\connect(Medoo\Drivers\MySQL::class, [
        'host' => 'localhost',
        'database' => 'name',
        'username' => 'your_username',
        'password' => 'your_password'
    ]);
    
    // Enjoy
    yield $database->insert('account', [
        'user_name' => 'foo',
        'email' => 'foo@bar.com'
    ]);
    
    $data = yield $database->select('account', [
        'user_name',
        'email'
    ], [
        'user_id' => 50
    ]);
    
    echo json_encode($data);

    // [{
    //    "user_name" : "foo",
    //    "email" : "foo@bar.com",
    // }]
});
```

## Contribution Guides

For starting a new pull request, please make sure it's compatible with other databases and write a unit test as possible.

Run `phpunit tests` for unit testing and `php-cs-fixer fix` for fixing code style.

Each commit is started with `[fix]`, `[feature]` or `[update]` tag to indicate the change.

Please keep it simple and keep it clear.

## License

Medoo is under the MIT license.

## Links

* Official website: [https://medoo.in](https://medoo.in)

* Documentation: [https://medoo.in/doc](https://medoo.in/doc)

* Twitter: [https://twitter.com/MedooPHP](https://twitter.com/MedooPHP)