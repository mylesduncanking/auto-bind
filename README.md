# Install via composer

`composer require mylesduncanking/laravel-auto-bind`

# Usage

In each controller you want to auto-bind properties, add the `#[AutoBind]` attribute to each property.

```php
use MylesDuncanKing\AutoBind\AutoBindProperty;

#[AutoBindProperty]
public Client $client;
```

**Note:** You can specify an alternative to the ID column being used by default by defining a column value. (`#[AutoBindProperty(column: 'your_alternative_column')] `)

In your controller's construct method add the call to bind the properties.

```php
public function __construct()
{
    AutoBind::bind($this);
}
```

Then just ensure that your route file contains the same name as your controller property as normal Laravel route-model binding requires.

You can then access the auto-bound properties via the class properties.
```php
public function read()
{
    echo $this->client->id;
}
```

Or add those bound properties directly into your view via the `bound` method.
```php
public function read()
{
    $foo = 'bar';
    return view('clients.read', array_merge(AutoBind::bound(), compact(['foo'])));
}
```

# Full example
### Route file: app/routes/web.php
```php
<?php

Route::get('clients', [ClientsController::class, 'index'])->name('clients');
Route::post('clients', [ClientsController::class, 'create'])->name('client.create');
Route::get('clients/{client}/{tab?}', [ClientsController::class, 'read'])->name('client.read');
Route::patch('clients/{client}', [ClientsController::class, 'update'])->name('client.update');
Route::delete('clients/{client}', [ClientsController::class, 'delete'])->name('client.delete');
```

### Controller file: app/Http/Controllers/ClientsController.php
```php
<?php

namespace App\Http\Controllers;

use App\Models\Client;
use MylesDuncanKing\AutoBind\AutoBind;
use MylesDuncanKing\AutoBind\AutoBindProperty as AutoBind;

class ClientsController extends \Illuminate\Routing\Controller
{
    #[AutoBind]
    public Client $client;

    public function __construct()
    {
        AutoBind::bind($this);
    }

    public function create()
    {
        request()->validate(['name' => ['required', 'string', 'max:40']]);

        $client = new Client();
        $client->name = request('name');
        $client->save();

        return redirect()->route('client.read', $this->client);
    }

    public function read($tab = 'index')
    {
        return view('clients.read', AutoBind::bound($this, compact(['tab'])));
    }

    public function update()
    {
        request()->validate(['name' => ['required', 'string', 'max:40']]);

        $this->client->name = request('name');
        $this->client->save();

        return redirect()->back();
    }

    public function delete()
    {
        $this->client->delete();
        return redirect()->route('clients');
    }
}
```
