<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitcefa44e43dc00c5b89e2170a33e646fd
{
    public static $prefixLengthsPsr4 = array (
        't' => 
        array (
            'think\\' => 6,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'think\\' => 
        array (
            0 => __DIR__ . '/..' . '/topthink/think-image/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitcefa44e43dc00c5b89e2170a33e646fd::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitcefa44e43dc00c5b89e2170a33e646fd::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
