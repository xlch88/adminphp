<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInite75f47a56781e6934475492ef14d6150
{
    public static $prefixLengthsPsr4 = array (
        'A' => 
        array (
            'App\\Model\\' => 10,
            'App\\Lib\\' => 8,
            'App\\' => 4,
            'AdminPHP\\' => 9,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'App\\Model\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app/Common/Model',
        ),
        'App\\Lib\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app/Common/Lib',
        ),
        'App\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app',
        ),
        'AdminPHP\\' => 
        array (
            0 => __DIR__ . '/../..' . '/adminphp',
        ),
    );

    public static $fallbackDirsPsr0 = array (
        0 => __DIR__ . '/../..' . '/app/Extend',
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInite75f47a56781e6934475492ef14d6150::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInite75f47a56781e6934475492ef14d6150::$prefixDirsPsr4;
            $loader->fallbackDirsPsr0 = ComposerStaticInite75f47a56781e6934475492ef14d6150::$fallbackDirsPsr0;
            $loader->classMap = ComposerStaticInite75f47a56781e6934475492ef14d6150::$classMap;

        }, null, ClassLoader::class);
    }
}
