<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit57cf3f23cc10a18ebb002610773ec2e2
{
    public static $classMap = array (
        'Zebra_Image' => __DIR__ . '/..' . '/stefangabos/zebra_image/Zebra_Image.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInit57cf3f23cc10a18ebb002610773ec2e2::$classMap;

        }, null, ClassLoader::class);
    }
}
