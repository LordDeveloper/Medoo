## Requirement

PHP 8.0+ and installed PDO extension.

## Get Started

### Install via composer

Add Medoo to the composer.json configuration file.
```
$ composer require jove/medoo:dev-master
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