<?php

if ( !isset($argv[1])) {
    echo "php patch_kernel.php <kernelFile>\n";
    exit(1);
}

$kernelFile = $argv[1];
if ( ! file_exists($kernelFile)) {
    echo "Kernel File $kernelFile does not exist.\n";
    exit(2);
}

$kernelContents = file_get_contents($kernelFile);
$kernelContents = str_replace('use Symfony\Component\HttpKernel\Kernel;', 'use WindowsAzure\DistributionBundle\HttpKernel\AzureKernel;', $kernelContents);
$kernelContents = str_replace('extends Kernel', 'extends AzureKernel', $kernelContents);

$addBundles = array(
    'WindowsAzure\DistributionBundle\WindowsAzureDistributionBundle',
    'WindowsAzure\TaskDemoBundle\WindowsAzureTaskDemoBundle',
);

$lines = explode("\n", $kernelContents);
$newLines = array();
$bundles = array();

for ($i = 0; $i < count($lines); $i++) {
    if (strpos($lines[$i], "Bundle(") !== false) {
        $bundles[] = trim(str_replace(array("new", "(),"), "", $lines[$i])); // not perfect, but works for our bundles
    }

    if (trim($lines[$i]) == ");") {
        foreach ($addBundles as $bundleName) {
            if (in_array($bundleName, $bundles)) {
                continue;
            }

            $newLines[] = str_repeat(" ", 12) . "new " . $bundleName . "(),";
        }
    }
    $newLines[] = $lines[$i];
}

file_put_contents($kernelFile, implode("\n", $newLines));

