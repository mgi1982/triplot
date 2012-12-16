<?php
if (!file_exists($_SERVER['DEPLOYMENT_SOURCE'] . "/composer.phar")) {
    $url = 'https://getcomposer.org/composer.phar';
    file_put_contents($_SERVER['DEPLOYMENT_SOURCE'] . "/composer.phar", file_get_contents($url));
}

register_shutdown_function('copyFiles');

var_dump($_SERVER);
echo var_export($_SERVER);

$_SERVER['argv'][1] = "update";
$_SERVER['argv'][2] = "--prefer-dist";
$_SERVER['argv'][3] = "-v";
require $_SERVER['DEPLOYMENT_SOURCE'] . "/composer.phar";

function copyFiles()
{
    if (!isset($_SERVER['DEPLOYMENT_TARGET'])) {
        echo "Cannot find pyhsical path to application root.\n";
        echo "There should be an 'DEPLOYMENT_TARGET' env variable.\n";
        exit(1);
    }

    echo "Copying code to webroot\n";
    copyDirectory($_SERVER['DEPLOYMENT_SOURCE'], $_SERVER['DEPLOYMENT_TARGET']);
}

function copyDirectory($source, $target)
{
    $it = new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS);
    $ri = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::SELF_FIRST);

    if ( !file_exists($target)) {
        mkdir($target, 0777, true);
    }

    foreach ($ri as $file) {
        $targetPath = $target . DIRECTORY_SEPARATOR . $ri->getSubPathName();
        if ($file->isDir()) {
            if ( ! file_exists($targetPath)) {
                mkdir($targetPath);
            }
        } else if (!file_exists($targetPath) || filemtime($targetPath) < filemtime($file->getPathname())) {
            copy($file->getPathname(), $targetPath);
        }
    }
}
