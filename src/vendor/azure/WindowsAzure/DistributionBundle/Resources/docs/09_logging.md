---
layout: default
title: Logging
---

# Logging

In Windows Azure Logging is handled by the so-called diagnostics module.
It allows you to synchronize log files from the server to a blob storage account
in configurable time-intervals.

To enable logging open up your `app\azure\ServiceDefinition.csdef` file and
uncomment the following line:

    <!--<Import moduleName="Diagnostics"/>-->
    <Import moduleName="Diagnostics"/>

Open up your `app/config/ServiceConfiguration.cscfg` file and configure
the setting

    <Setting name="Microsoft.WindowsAzure.Plugins.Diagnostics.ConnectionString" value="DefaultEndpointsProtocol=https;AccountName=$name;AccountKey=$key"/>

You can create a Storage account from the Windows Azure Control Panel. Just enter the Account name and key into that setting.

The diagnostics are configured in `app\azure\Sf2.Web\diagnostics.wadcfg`. By default the log files are synchronized to the storage every 10 minutes. You can download the files from there to debug your application.

    <DiagnosticMonitorConfiguration xmlns="http://schemas.microsoft.com/ServiceHosting/2010/10/DiagnosticsConfiguration" configurationChangePollInterval="PT10M" overallQuotaInMB="4096">
        <Directories bufferQuotaInMB="0" scheduledTransferPeriod="PT10M">
            <DirectoryConfiguration container="wad-symfony" directoryQuotaInMB="128">
                <LocalResource name="SymfonyLogFiles" relativePath="logs" />
            </DirectoryConfiguration>
        </Directories>
    </DiagnosticMonitorConfiguration>


Make sure to keep an eye on the size of those files and the synchronization interval, as the synchronization to the storage account can cost you money.

