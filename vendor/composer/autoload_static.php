<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitbbe5273800b52278cdc48a3f101fe2f6
{
    public static $files = array (
        '0e6d7bf4a5811bfa5cf40c5ccd6fae6a' => __DIR__ . '/..' . '/symfony/polyfill-mbstring/bootstrap.php',
        '6e3fae29631ef280660b3cdad06f25a8' => __DIR__ . '/..' . '/symfony/deprecation-contracts/function.php',
        'a1105708a18b76903365ca1c4aa61b02' => __DIR__ . '/..' . '/symfony/translation/Resources/functions.php',
        'a4a119a56e50fbb293281d9a48007e0e' => __DIR__ . '/..' . '/symfony/polyfill-php80/bootstrap.php',
        '60799491728b879e74601d83e38b2cad' => __DIR__ . '/..' . '/illuminate/collections/helpers.php',
        '72579e7bd17821bb1321b87411366eae' => __DIR__ . '/..' . '/illuminate/support/helpers.php',
    );

    public static $prefixLengthsPsr4 = array (
        'v' => 
        array (
            'voku\\' => 5,
        ),
        'S' => 
        array (
            'Symfony\\Polyfill\\Php80\\' => 23,
            'Symfony\\Polyfill\\Mbstring\\' => 26,
            'Symfony\\Contracts\\Translation\\' => 30,
            'Symfony\\Component\\Translation\\' => 30,
        ),
        'P' => 
        array (
            'Psr\\SimpleCache\\' => 16,
            'Psr\\Container\\' => 14,
            'Psr\\Clock\\' => 10,
        ),
        'I' => 
        array (
            'Illuminate\\Support\\' => 19,
            'Illuminate\\Database\\' => 20,
            'Illuminate\\Contracts\\' => 21,
            'Illuminate\\Container\\' => 21,
        ),
        'D' => 
        array (
            'Doctrine\\Inflector\\' => 19,
        ),
        'C' => 
        array (
            'Carbon\\Doctrine\\' => 16,
            'Carbon\\' => 7,
        ),
        'B' => 
        array (
            'Brick\\Math\\' => 11,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'voku\\' => 
        array (
            0 => __DIR__ . '/..' . '/voku/portable-ascii/src/voku',
        ),
        'Symfony\\Polyfill\\Php80\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-php80',
        ),
        'Symfony\\Polyfill\\Mbstring\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-mbstring',
        ),
        'Symfony\\Contracts\\Translation\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/translation-contracts',
        ),
        'Symfony\\Component\\Translation\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/translation',
        ),
        'Psr\\SimpleCache\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/simple-cache/src',
        ),
        'Psr\\Container\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/container/src',
        ),
        'Psr\\Clock\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/clock/src',
        ),
        'Illuminate\\Support\\' => 
        array (
            0 => __DIR__ . '/..' . '/illuminate/macroable',
            1 => __DIR__ . '/..' . '/illuminate/conditionable',
            2 => __DIR__ . '/..' . '/illuminate/collections',
            3 => __DIR__ . '/..' . '/illuminate/support',
        ),
        'Illuminate\\Database\\' => 
        array (
            0 => __DIR__ . '/..' . '/illuminate/database',
        ),
        'Illuminate\\Contracts\\' => 
        array (
            0 => __DIR__ . '/..' . '/illuminate/contracts',
        ),
        'Illuminate\\Container\\' => 
        array (
            0 => __DIR__ . '/..' . '/illuminate/container',
        ),
        'Doctrine\\Inflector\\' => 
        array (
            0 => __DIR__ . '/..' . '/doctrine/inflector/lib/Doctrine/Inflector',
        ),
        'Carbon\\Doctrine\\' => 
        array (
            0 => __DIR__ . '/..' . '/carbonphp/carbon-doctrine-types/src/Carbon/Doctrine',
        ),
        'Carbon\\' => 
        array (
            0 => __DIR__ . '/..' . '/nesbot/carbon/src/Carbon',
        ),
        'Brick\\Math\\' => 
        array (
            0 => __DIR__ . '/..' . '/brick/math/src',
        ),
    );

    public static $classMap = array (
        'Abram\\Odbc\\ODBCBuilder' => __DIR__ . '/../..' . '/src/ODBCBuilder.php',
        'Abram\\Odbc\\ODBCConnection' => __DIR__ . '/../..' . '/src/ODBCConnection.php',
        'Abram\\Odbc\\ODBCConnector' => __DIR__ . '/../..' . '/src/ODBCConnector.php',
        'Abram\\Odbc\\ODBCPdo' => __DIR__ . '/../..' . '/src/ODBCPdo.php',
        'Abram\\Odbc\\ODBCPdoStatement' => __DIR__ . '/../..' . '/src/ODBCPdoStatement.php',
        'Abram\\Odbc\\ODBCProcessor' => __DIR__ . '/../..' . '/src/ODBCProcessor.php',
        'Abram\\Odbc\\ODBCServiceProvider' => __DIR__ . '/../..' . '/src/ODBCServiceProvider.php',
        'Attribute' => __DIR__ . '/..' . '/symfony/polyfill-php80/Resources/stubs/Attribute.php',
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'PhpToken' => __DIR__ . '/..' . '/symfony/polyfill-php80/Resources/stubs/PhpToken.php',
        'Stringable' => __DIR__ . '/..' . '/symfony/polyfill-php80/Resources/stubs/Stringable.php',
        'UnhandledMatchError' => __DIR__ . '/..' . '/symfony/polyfill-php80/Resources/stubs/UnhandledMatchError.php',
        'ValueError' => __DIR__ . '/..' . '/symfony/polyfill-php80/Resources/stubs/ValueError.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitbbe5273800b52278cdc48a3f101fe2f6::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitbbe5273800b52278cdc48a3f101fe2f6::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitbbe5273800b52278cdc48a3f101fe2f6::$classMap;

        }, null, ClassLoader::class);
    }
}
