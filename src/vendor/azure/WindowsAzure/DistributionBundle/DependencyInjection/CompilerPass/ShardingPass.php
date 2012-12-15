<?php
/**
 * WindowsAzure TaskDemoBundle
 *
 * LICENSE
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kontakt@beberlei.de so I can send you a copy immediately.
 */

namespace WindowsAzure\DistributionBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * This will end up in the DoctrineBundle some day, but for now we have to
 * register the sharding manager here in an extra compiler pass.
 */
class ShardingPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ( ! $container->hasParameter('windows_azure_distribution.sharding')) {
            return;
        }

        $shards = $container->getParameter('windows_azure_distribution.sharding');

        foreach ($shards as $connectionName => $options) {
            $this->registerShard($connectionName, $options, $container);
        }
    }

    private function registerShard($connectionName, $options, $container)
    {
        $id      = 'doctrine.dbal.' . $connectionName . '_connection';
        $shardId = 'windows_azure_distribution.' . $connectionName . '_shard_manager';

        if ( ! $container->hasDefinition($id)) {
            throw new \InvalidArgumentException("No connection " . $connectionName . " found for federations.");
        }

        $def  = $container->findDefinition($id);
        $args = $def->getArguments();

        if ( ! isset($args[0]['driver']) || strpos($args[0]['driver'], 'sqlsrv') === false) {
            throw new \InvalidArgumentException("Sharding only possible with sqlsrv driver.");
        }

        $args[0]['sharding'] = array(
            'federationName'   => $options['federationName'],
            'distributionKey'  => $options['distributionKey'],
            'distributionType' => $options['distributionType'],
            'filteringEnabled' => $options['filteringEnabled'],
        );
        $args[0]['MultipleActiveResultSets'] = false;

        $def->setArguments($args);

        $shardDef = new Definition('Doctrine\Shards\DBAL\SQLAzure\SQLAzureShardManager');
        $shardDef->addArgument(new Reference($id));
        $container->setDefinition($shardId, $shardDef);

        if ($connectionName == 'default') {
            $container->setAlias('windows_azure_distribution.shard_manager', $shardId);
        }
    }
}

