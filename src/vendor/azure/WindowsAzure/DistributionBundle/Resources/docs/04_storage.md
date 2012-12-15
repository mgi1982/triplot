---
title: Blob Storage
layout: default
---

# Blob Storage

The Windows Azure Distribution Bundle comes with support for accessing Windows Blob Storage.
This can be done using the [Azure Blob Storage](http://github.com/beberlei/aure-blob-storage) library.
This library is a dependency that you have install.

You can configure blob storages from the config.yml (or config_azure.yml):

    windows_azure_distribution:
        blob_storage:
            test:
                accountName: myacc
                accountKey: key
            test_with_stream:
                accountName: myacc2
                accountKey: key2
                stream: azure

There is not yet a mechanism to fallback to a local API if you dont want to hit
blob storage during development, so you need to put the details into config.yml
so it works in development.

With this configuration you can access the blob storage accounts from your
controllers (or any services) using the storage registry service:

    <?php
    namespace Vendor\AppBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    class MyController extends Controller
    {
        public function indexAction()
        {
            $registry = $this->container->get('windows_azure_distribution.storage_registry');
            $client   = $registry->get('test');
            $client2  = $registry->get('test2');

            $streamFile = 'azure://container/test.jpg';
            $data = file_get_contents($streamFile);
        }
    }

# Filesystem

Symfony ships with a filesystem API that is very convenient to use for some mass-operations.
Because some parts of it are incompatible with Azure Streams you can use a subclass of it
provided by this bundle:

    <?php

    $filesystem = new \WindowsAzure\DistributionBundle\Filesystem\AzureFilesystem;
    $filesystem->mirror("dir/", "azure://dir");

