<?php
/**
 * WindowsAzure DistributionBundle
 *
 * LICENSE
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kontakt@beberlei.de so I can send you a copy immediately.
 */

namespace WindowsAzure\DistributionBundle\Deployment\Assets;

use Beberlei\AzureBlobStorage\BlobClient;
use WindowsAzure\DistributionBundle\Filesystem\AzureFilesystem;

/**
 * Serve assets from blob storage
 */
class BlobStrategy extends AssetStrategy
{
    public function deploy($documentRoot, $buildNumber)
    {
        $client = new BlobClient(
            sprintf('http://%s.blob.core.windows.net', $this->container->getParameter('windows_azure_distribution.assets.account_name')),
            $this->container->getParameter('windows_azure_distribution.assets.account_name'),
            $this->container->getParameter('windows_azure_distribution.assets.account_key')
        );
        $client->registerStreamWrapper('azureassets');

        $this->moveTo('azureassets://v' . $buildNumber);
    }

    protected function getFilesystem()
    {
        return new AzureFilesystem();
    }
}

