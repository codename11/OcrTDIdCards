<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit801b88ad65d9929a0127a8218c6c0216
{
    public static $prefixLengthsPsr4 = array (
        't' => 
        array (
            'thiagoalessio\\TesseractOCR\\' => 27,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'thiagoalessio\\TesseractOCR\\' => 
        array (
            0 => __DIR__ . '/..' . '/thiagoalessio/tesseract_ocr/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit801b88ad65d9929a0127a8218c6c0216::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit801b88ad65d9929a0127a8218c6c0216::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
