---
title: Installation
layout: default
---

# Installation

Prerequisites for this bundle are a Windows development machine with the Windows Azure SDK installed. You don't need the PHP SDK to run this bundle.

You can either install the SDK through [Web Platform Installer](http://azurephp.interoperabilitybridges.com/articles/setup-the-windows-azure-development-environment-automatically-with-the-microsoft-web-platform-installer) or all [dependencies manually](http://azurephp.interoperabilitybridges.com/articles/setup-the-windows-azure-development-environment-manually).

## Composer

The most simple way to use Azure Distribution Bundle is with [Composer](http://www.packagist.org)-based applications. Add this package to your composer.json and run `composer update`:

    {
        "require": {
            "beberlei/azure-distribution-bundle": "*"
        }
    }

## bin\vendors and deps

For a 'bin\vendors' based application add the Git path to your 'deps' file.

    [AzureDistributionBundle]
    git=https://github.com/beberlei/AzureDistributionBundle.git
    target=/azure/WindowsAzure/DistributionBundle

    [Assert]
    git=https://github.com/beberlei/assert.git
    target=/azure/assert

    [AzureBlobStorage]
    git=https://github.com/beberlei/azure-blob-storage.git
    target=/azure/azure-blob-storage

    [Shards]
    git=https://github.com/doctrine/shards.git
    target=/azure/doctrine-shards

    [KeyValueStore]
    git=https://github.com/doctrine/KeyValueStore.git
    target=/azure/doctrine-keyvaluestore

Then call "php bin\vendors install" or "php bin\vendors update" to install this package. Proceed with section "Autoloading"

## Download

Go to https://github.com/beberlei/AzureDistributionBundle/downloads. Download
the `azure-distribution-bundle-v*.zip` file into the `vendor/azure` directory.

## Autoloading

If you are using Download or Deps files you have to manually register autoloading in 'app/autoload.php':

    'WindowsAzure\\DistributionBundle'  => __DIR__ . '/../vendor/azure/',
    'WindowsAzure\\TaskDemoBundle'      => __DIR__ . '/../vendor/azure/',
    'Beberlei\\AzureBlobStorage'        => __DIR__ . '/../vendor/azure/azure-blob-storage/lib/',
    'Assert\\'                          => __DIR__ . '/../vendor/azure/assert/lib/',
    'Doctrine\\Shards'                  => __DIR__ . '/../vendor/azure/doctrine-shards/lib/',
    'Doctrine\\KeyValueStore'           => __DIR__ . '/../vendor/azure/doctrine-keyvaluestore/lib/',

Also you have to add the bundle in your kernel, see the next section on this.

## Azure Kernel

The Azure kernel can be used to set the temporary and cache directories to `sys_get_tempdir()` on production. These are the only writable directories for the webserver on Azure.

    <?php

    use Symfony\Component\HttpKernel\Kernel;
    use Symfony\Component\Config\Loader\LoaderInterface;
    use WindowsAzure\DistributionBundle\HttpKernel\AzureKernel; // change use

    class AppKernel extends AzureKernel // change kernel here
    {
        $bundles = array(
            // ...
            new WindowsAzure\DistributionBundle\WindowsAzureDistributionBundle();
            // ...
        );

        // keep the old code here.

        return $bundles;
    }
