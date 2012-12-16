---
layout: default
title: Quickstart
---

# Quickstart

This quickstart will guide you through the steps to deploy a clean Symfony2 application on Windows Azure. This will contain the AcmeDemoBundle that has a very simple hello world page.

Prerequisites:

* Windows Machine or VM
* Windows Azure SDK (Not the PHP one)
* Azure (Test-)Account with SQL Server Database + Storage Account
* PHP with [OpenSSL](http://php.net/manual/en/openssl.installation.php) configured to generate keys

## Using a downloadable Symfony version

1. Go to `https://github.com/beberlei/AzureDistributionBundle/downloads`. Download the latest `symfony-azure-distribution-v*.zip` file. This is a modified Symfony Standard Distribution including all necessary bundles and libraries for Windows Azure and  modified `app\autoload.php` and `app\AppKernel.php` files. Unzip this archive to a directory of your choice.

2. Open up the terminal and go to the project root. Call "php app\console". You should see a list of commands, containing two of the windows azure commands at the bottom:

        windowsazure:init
        windowsazure:package

3. Call `php app\console windowsazure:init`. The result should show that a Webrole was generated and display two passwords for the Remote Desktoping feature. Note down both passwords, you need them during deployment. If an error occurs during the key generation, take a look at the [OpenSSL](http://php.net/manual/en/openssl.installation.php) Installation notes.

4. Configure the database by modifying `app\config\parameters_azure.yml`.

    An example of the parameters_azure.yml looks like:

        # Put Azure Specific configuration parameters into
        # this file. These will overwrite parameters from parameters.yml
        parameters:
            session_type: pdo
            database_driver: pdo_sqlsrv
            database_host: tcp:DBID.database.windows.net
            database_user: USER@DBID
            database_password: PWD
            database_name: DBNAME

    If you have not done it already, you need to create an SQL Server database in the Management console at this step.

5. Configure Security

    Open `app\config\security.yml` and exchange the line:

        - { resource: security.yml }

    with the following line (careful with indention and make sure to use spaces, not tabs):

        - { resource: ../../vendor/azure/WindowsAzure/TaskDemoBundle/Resources/config/security.yml }

6. Register routes in `app\config\routing.yml`:

        WindowsAzureTaskDemoBundle:
            resource: "@WindowsAzureTaskDemoBundle/Controller/"
            type:     annotation
            prefix:   /

    Note: Beware, Yaml only allows spaces no tabs and the correct indention is important.

7. Configure Federation in `app\config\config_azure.yml` after the session configuration:

        windows_azure_distribution:
          session:
            type: %session_type%
            database:
              host: %database_host%
              username: %database_user%
              password: %database_password%
              database: %database_name%
          # NEW: append to existing config, align with session key
          federations:
            default:
              federationName: User_Federation
              distributionKey: user_id
              distributionType: guid

    Note: Beware, Yaml only allows spaces no tabs and the correct indention is important.

8. For the Azure Table features, add the following to your `app\config\config_azure.yml`:

        windows_azure_distribution:
            # previous 'session' and 'federations' config here.
            table:
                account: "accountName"
                key: "accountKey"
                    
    Note: Beware, Yaml only allows spaces no tabs and the correct indention is important.

9. Call `php app\console windowsazure:package` which creates two files into the `build` directory of your project.

10. Deploy the `build\ServiceDefinition.cscfg` and `build\azure-1.cspkg` using the management console. If the Remote Desktop keys were generated successfully, add the certifcate file from `app\azure\Sf2.Web.pfx` in the Certificate Tab.

11. Import the contents of the "schema.sql" from vendor\azure\WindowsAzure\TaskDemoBundle\Resources\schema.sql into your SQL Azure database.

12. Browse to http://appid.cloudapp.net/ - http://appid.cloudapp.net/hello/world or http://appid.cloudapp.net/tasks

## Logging

To get error logging working see the [Logging chapter](10_logging.md) of this documentation.

