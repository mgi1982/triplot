---
title: Installation
layout: default
---

# Installation

Prerequisites for this bundle are a Windows development machine with the Windows Azure SDK installed. 

You can either install the SDK through [Web Platform Installer](http://azurephp.interoperabilitybridges.com/articles/setup-the-windows-azure-development-environment-automatically-with-the-microsoft-web-platform-installer) or all [dependencies manually](http://azurephp.interoperabilitybridges.com/articles/setup-the-windows-azure-development-environment-manually).

## Composer

The most simple way to use Azure Distribution Bundle is with [Composer](http://www.packagist.org)-based applications. Add this package to your composer.json and run `composer update`:

    {
        "require": {
            "beberlei/azure-distribution-bundle": "*"
        }
    }

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
