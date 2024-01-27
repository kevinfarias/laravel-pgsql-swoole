<?php

namespace KevinFarias\PgSqlSwoole;

use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class PgSqlSwooleServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('db', function ($app) {
            return new SwooleDatabaseManager($app, $app['db.factory']);
        });
        $this->app->resolving('db', function ($db) {
            /** @var DatabaseManager $db */
            $db->extend('pgsql-swoole', function ($config, $name) {
                $pdoConnection = (new PgSqlSwooleConnector())->connect($config);
                $connection = new PgSqlSwooleConnection($pdoConnection, $config['database'], isset($config['prefix']) ? $config['prefix'] : '', $config);
                return $connection;
            });
        });
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        Model::setConnectionResolver($this->app['db']);
        Model::setEventDispatcher($this->app['events']);
    }
}
