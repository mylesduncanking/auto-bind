# Install via composer

`composer require mylesduncanking/laravel-auto-bind`

# Getting started

In your controller's construct method add the call to bind the properties.

```php
public function __construct()
{
    AutoBind::bind($this);
}
```

In each controller you want to auto-bind properties, add the `#[AutoBind]` attribute to each property.
```php
...

use MylesDuncanKing\AutoBind\Attribute as AutoBindAttr;

class ClientsController extends \Illuminate\Routing\Controller
{
    #[AutoBindAttr]
    public Client $client;

    ...
```

Then just ensure that your route file contains the same name as your controller property.
```php
...

Route::post('clients', [ClientsController::class, 'create']);
Route::get('clients/{client}', [ClientsController::class, 'read']);
Route::patch('clients/{client}', [ClientsController::class, 'update']);
Route::delete('clients/{client}', [ClientsController::class, 'delete']);

...
```

# Usage
You can then access the auto-bound properties via the class properties.
```php
public function read()
{
    echo $this->client->id;
}
```

Or add those bound properties directly into your view via the `bound` method.
```php
...

use MylesDuncanKing\AutoBind;

...

public function read()
{
    $foo = 'bar';
    return view('clients.read', array_merge(AutoBind::bound(), compact(['foo'])));
}
```