---
title: Table Service
layout: default
---

# Table Service

Note: Only works with Doctrine Common 2.2

The Windows Azure Distribution bundle comes with support for accessing Windows Table Storage.
This is done in combination with the [Doctrine KeyValueStore](http://github.com/doctrine/KeyValueStore)
project.

You can configure the table manager with:

    windows_azure_distribution:
        table:
            account: "acc"
            key: "key"

If you configured the manager and installed the Doctrine Key Value Store project you can access the
table manager with:

    <?php
    namespace Vendor\AppBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    class MyController extends Controller
    {
        public function indexAction()
        {
            $manager = $this->container->get('windows_azure_distribution.table.manager');
        }
    }

Table entities can be any objects in your project, no additional configuration is necessary. Just 
