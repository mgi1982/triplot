---
title: Deploying assets on Azure
layout: default
---

# Assets

By default the packaging process is configured to serve assets (such as images,
stylesheets, javascript) from the local webservers. If you configure your Azure
Blob Storage Account you can change this to automatically deploy to Azure Blob.

This offers much better performance (CDN) for your assets and much better conditions
for traffic.

To use Azure Blog storage add the following configuration to your `config.yml`:

    windows_azure_distribution:
        assets:
            type: blob
            accountName: acc
            accountKey: pw1

During packaging the Azure Distribution bundle will copy all assets onto Azure Blob
storage, versioned by the current build number. This way different assets between different
versions in staging/production will never affect each other.

Make sure to clean up your storage account and delete those old version containers
if you don't need them anymore.
