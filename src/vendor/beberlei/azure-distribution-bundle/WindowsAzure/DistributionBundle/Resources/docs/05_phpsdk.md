---
layout: default
title: PHP Azure SDK Services
---

# Registering PHP Azure SDK Services

The new PHP SDK provides access to all service APIs of Windows
Azure. The Windows Azure Distribution Bundle allows to
conveniently register them with the Symfony Dependency Injection
Container:

    windows_azure_distribution:
        services:
            blob:
                default: UseDevelopmentStorage=true
                test2: DefaultEndpointsProtocol=[http|https];AccountName=[yourAccount];AccountKey=[yourKey]
            table:
                default: UseDevelopmentStorage=true
                test2: DefaultEndpointsProtocol=[http|https];AccountName=[yourAccount];AccountKey=[yourKey]
            queue:
                default: UseDevelopmentStorage=true
                test2: DefaultEndpointsProtocol=[http|https];AccountName=[yourAccount];AccountKey=[yourKey]
            service_bus:
                default: Endpoint=[yourEndpoint];SharedSecretIssuer=[yourWrapAuthenticationName];SharedSecretValue=[yourWrapPassword]
            management:
                default: SubscriptionID=[yourSubscriptionId];CertificatePath=[filePathToYourCertificate]

The connection strings for all services are using the exact same
syntax as the PHP Azure SDK. See their [README](https://github.com/WindowsAzure/azure-sdk-for-php#getting-started-1)
for more information.

Defining the services like above creates services in the DIC with the naming pattern ``windows_azure.type.name``.
In our case:

* ``windows_azure.blob.default`` and ``windows_azure.blob.test2``.
* ``windows_azure.table.default`` and ``windows_azure.table.test2``.
* ``windows_azure.queue.default`` and ``windows_azure.queue.test2``.
* ``windows_azure.service_bus.default``
* ``windows_azure.management.default``

