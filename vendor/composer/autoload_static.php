<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit03af49956f7d47d5411e4e607f8dbb7a
{
    public static $files = array (
        '897632f4a070213431597053c607ac41' => __DIR__ . '/../..' . '/Model/GcrWebHook.php',
    );

    public static $prefixLengthsPsr4 = array (
        'G' => 
        array (
            'Gcr\\' => 4,
        ),
        'C' => 
        array (
            'Curl\\' => 5,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Gcr\\' => 
        array (
            0 => __DIR__ . '/../..' . '/classes',
        ),
        'Curl\\' => 
        array (
            0 => __DIR__ . '/..' . '/php-curl-class/php-curl-class/src/Curl',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit03af49956f7d47d5411e4e607f8dbb7a::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit03af49956f7d47d5411e4e607f8dbb7a::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit03af49956f7d47d5411e4e607f8dbb7a::$classMap;

        }, null, ClassLoader::class);
    }
}
