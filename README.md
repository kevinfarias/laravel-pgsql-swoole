## PgSqlSwoole integration for Laravel Framework
This integration allows the use of <b>PgSqlSwoole</b> php driver function with Laravel framework instead of PDO.<br>
It emulates PDO class used by Laravel.<br>
The advantage is to use non-blocking PostgreSQL connection in Laravel Octane.<br>
Very experimental. Do not use in production.<br>
Heavily inspired in: https://github.com/andreossido/laravel-odbc<br>
<br><br>
### # How to install
> `composer require kevinfarias/laravel-pgsql-swoole` To add source in your project

### # Usage Instructions
It's very simple to configure:

**1) Add database to database.php file**
```PHP
'pgsql-swoole' => [
    'driver' => 'pgsql-swoole',
    'database' => 'DatabaseName',
    'host' => '127.0.0.1',
    'username' => 'username',
    'password' => 'password'
]
```

**2) Add service provider in app.php file**
```PHP
'providers' => [
  ...
  KevinFarias\PgSqlSwoole\PgSqlSwooleServiceProvider::class
]
```

### # Eloquen ORM
You can use Laravel, Eloquent ORM and other Illuminate's components as usual.
```PHP
# Facade
$books = DB::connection('pgsql-swoole')->table('books')->where('Author', 'Kevin Farias')->get();

# ORM
$books = Book::where('Author', 'Kevin Farias')->get();
```

### # Custom getLastInsertId() function
If you want to provide a custom <b>getLastInsertId()</b> function, you can extends *PgSqlSwooleProcessor* class and override function.<br>
```PHP
class CustomProcessor extends PgSqlSwooleProcessor
{
    /**
     * @param Builder $query
     * @param null $sequence
     * @return mixed
     */
    public function getLastInsertId(Builder $query, $sequence = null)
    {
        return $query->getConnection()->table($query->from)->latest('id')->first()->getAttribute($sequence);
    }
}
```

### # Custom Processor / QueryGrammar / SchemaGrammar
To use another class instead default one you can update your connection in:
```PHP
'pgsql-swoole' => [
    'driver' => 'pgsql-swoole',
    'database' => 'DatabaseName',
    'host' => '127.0.0.1',
    'username' => 'username',
    'password' => 'password',
    'options' => [
        'processor' => Illuminate\Database\Query\Processors\Processor::class,   //default
        'grammar' => [
            'query' => Illuminate\Database\Query\Grammars\Grammar::class,       //default
            'schema' => Illuminate\Database\Schema\Grammars\Grammar::class      //default
        ]
    ]
]
```
