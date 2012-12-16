<?php

if ( !isset($argv[1])) {
    echo "php patch_kernel.php <kernelFile>\n";
    exit(1);
}

$autoloadFile = $argv[1];
if ( ! file_exists($autoloadFile)) {
    echo "Autoload File $autoloadFile does not exist.\n";
    exit(2);
}

$autoloadContents = file_get_contents($autoloadFile);

$addAutoloads = array(
    'WindowsAzure\\DistributionBundle'  => "__DIR__ . '/../vendor/azure/',",
    'WindowsAzure\\TaskDemoBundle'      => "__DIR__ . '/../vendor/azure/',",
    'Beberlei\\AzureBlobStorage'        => "__DIR__ . '/../vendor/azure/azure-blob-storage/lib/',",
    'Doctrine\\Shards'                  => "__DIR__ . '/../vendor/azure/doctrine-shards/lib/',",
    'Doctrine\\KeyValueStore'           => "__DIR__ . '/../vendor/azure/doctrine-keyvaluestore/lib/',",
    'Assert'                            => "__DIR__ . '/../vendor/azure/assert/lib/',",
);

$lines = explode("\n", $autoloadContents);
$newLines = array();
$universalLoader = false;
$registerNamespaces = false;

for ($i = 0; $i < count($lines); $i++) {
    if (strpos($lines[$i], "UniversalClassLoader") !== false) {
        $universalClassLoader = true;
    }

    if (strpos($lines[$i], "registerNamespaces") !== false) {
        $registerNamespaces = true;
    }

    if (strpos($lines[$i], "));") !== false) {
        foreach ($addAutoloads as $namespace => $dir) {
            $newLines[] = str_repeat(" ", 4) . "'$namespace' => $dir";
        }
        $addAutoloads = array();
    }
    $newLines[] = $lines[$i];
}

file_put_contents($autoloadFile, implode("\n", $newLines));

