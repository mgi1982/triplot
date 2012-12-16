---
layout: default
title: Overview
---

# Features

Cloud-Services put constraints on how an application is allowed to run on their hosted services. This is done for security and scalability reasons. The default Symfony Sandbox won't run on Azure without some slight changes. The Azure deployment related tasks will be solved by this bundle:

* Command for packaging applications for Windows Azure (Done)
* Startup tasks for cache:clear/cache:warmup are invoked for new instances. (Done)
* Writing cache and log-files into a writable directory. (Done)
* Distributed sessions
   * PDO (Done)
   * Azure Table
* Specific 'azure' environment that is inherits from prod. (Done)
* Deploying assets to Azure Blob Storage (Done)
* Aid for generation remote desktop usables instances and other common configuration options for ServiceDefinition.csdef and ServiceConfiguration.cscfg
* Management of dev-fabric startup/cleanup during development
* Wrapper API for access to Azure Globals such as RoleId etc.
* Logging to a central server/blob storage.
* Diagnostics

Why is this Symfony specific? Generic deployment of PHP applications on Azure requires alot more work, because you can't rely on the conventions of the framework. This bundle makes the Azure experience very smooth, no details about Azure deployment are necessary to get started.


Using this kernel is totally optional. You can make the necessary modifications yourself to get the application running on Azure by pointing the log and cache directory to `sys_get_temp_dir()`.

## Azure Roles and Symfony applications

Windows Azure ships with a concept of roles. You can have different Web- or Worker Roles and each of them can come in one or many instances. Web- and Worker roles don't easily match to a Symfony2 application.

Symfony applications encourage code-reuse while azure roles enforce complete code seperation. You can have a Multi Kernel application in Symfony, but that can still contain both commands (Worker) and controllers (Web).

Dividing the code that is used on a worker or a web role for Azure will amount to considerable work. However package size has to be taken into account for faster boot operations. This is why the most simple approach to role building with Symfony2 is to ship all the code no matter what the role is. This is how this bundle works by default. If you want to keep the packages smaller you can optionally instruct the packaging to lazily fetch vendors using the Composer library.

