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

namespace WindowsAzure\DistributionBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use WindowsAzure\DistributionBundle\DependencyInjection\CompilerPass\ShardingPass;

class WindowsAzureDistributionBundle extends Bundle
{
    public function boot()
    {
        parent::boot();

        if ( ! $this->container->has('windows_azure_distribution.storage_registry')) {
            return;
        }

        // instantiate storage registry, will lead to registration of stream wrappers.
        $storageRegistry = $this->container->get('windows_azure_distribution.storage_registry');
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ShardingPass());
    }

}

