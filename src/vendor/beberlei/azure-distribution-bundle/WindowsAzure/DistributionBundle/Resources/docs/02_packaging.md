---
title: Packaging
layout: default
---

# Packaging

Before you start generating Azure packages you have to create the basic infrastructure. This bundle will create a folder `app/azure` with a bunch of configuration and resource files that are necessary for deployment. To initialize this infrastructure call:

    php app\console windowsazure:init

The main configuration files are `app\azure\ServiceDefinition.csdef` and `app\azure\ServiceConfiguration.cscfg`. They will be created with one Azure role "Sf2.Web", which will is configured as an HTTP application. For the Web-role the IIS Web.config is copied to `app\azure\Sf2.Web.Web.config`. During deployment it will be copied to the right location.

To generate an Azure package just invoke:

    php app\console windowsazure:package

The command will then try and generate an Azure package for you given the current azure Configuration files. You can re-run this script whenever you want to generate a new packaged version of your application. The result will be generated into the 'build' directory of your application.

The deployment process (windowsazure:package) executes the following steps:

1. For every role a list of all files to be deployed is generated into the file %RoleName%.roleFiles.txt. These files will be used with the `/roleFiles` parameter of [cspack.exe](http://msdn.microsoft.com/en-us/library/windowsazure/gg432988.aspx). By default directories with the name 'tests', 'Tests', 'build', 'logs', 'cache', 'docs' and 'test-suite' are excluded from the deployment.

    For configuration of this process see the "Configure Packaging" section of the docs.

    Because compiling the roles file can take some time you can optimize this process. See the "Optimizing Packaging" section in the docs for this.

2. cspack.exe is called with the appropriate parameters.

3. If the --dev-fabric flag isset after package generation the Azure dev-fabric will be started.

## Optimize Packaging speed

Packaging always compiles a list of files to deploy before the actual packaging takes place and cspack.exe is called. This list of files includes the vendor directory by default. The vendor directory contains the largest amount of code in your application and takes longest to search through. However vendor code is rather static. The only time files are updated here is when you call:

1. php bin/vendors in Symfony 2.0.x
2. composer update/install in Symfony starting 2.1

You can hook in both vendor updating processes to directly generate a list of files once and re-use it whenever you call 'app\console windowsazure:package'. This can speedup this step of deployment by roughly 90%.

To get this working under Symfony 2.0.x put the following code into your 'bin/vendors' php script at the end:

    <?php
    //...
    // Remove the cache
    system(sprintf('%s %s cache:clear --no-warmup', $interpreter, escapeshellarg($rootDir.'/app/console')));

    // This line is new:
    \WindowsAzure\DistributionBundle\Deployment\VendorRoleFilesListener::generateVendorRolesFile(__DIR__ . "/../vendor");

In Symfony 2.1 and higher put the following block into your applications composer.json:

    "scripts": {
        "post-update-cmd": "WindowsAzure\\DistributionBundle\\Deployment\\VendorRoleFilesListener::listenPostInstallUpdate",
        "post-install-cmd": "WindowsAzure\\DistributionBundle\\Deployment\\VendorRoleFilesListener::listenPostInstallUpdate"
    }
