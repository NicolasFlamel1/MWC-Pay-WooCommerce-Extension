<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit233def9630f62d50c949a092f4d8e511
{
    public static $prefixLengthsPsr4 = array (
        'N' => 
        array (
            'Nicolasflamel\\MwcPay\\' => 21,
        ),
        'B' => 
        array (
            'Brick\\Math\\' => 11,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Nicolasflamel\\MwcPay\\' => 
        array (
            0 => __DIR__ . '/..' . '/nicolasflamel/mwc-pay/src',
        ),
        'Brick\\Math\\' => 
        array (
            0 => __DIR__ . '/..' . '/brick/math/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit233def9630f62d50c949a092f4d8e511::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit233def9630f62d50c949a092f4d8e511::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit233def9630f62d50c949a092f4d8e511::$classMap;

        }, null, ClassLoader::class);
    }
}
