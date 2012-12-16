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

namespace WindowsAzure\DistributionBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Windows Azure Configuration
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('windows_azure');

        $rootNode
            ->children()
                ->arrayNode('services')
                    ->children()
                        ->arrayNode('blob')
                            ->useAttributeAsKey('name')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('table')
                            ->useAttributeAsKey('name')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('queue')
                            ->useAttributeAsKey('name')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('service_bus')
                            ->useAttributeAsKey('name')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('management')
                            ->useAttributeAsKey('name')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('deployment')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('roleFiles')
                           ->children()
                                ->scalarNode('ignoreVCS')->defaultValue(true)->end()
                                ->variableNode('include')->defaultValue(array())->end()
                                ->variableNode('exclude')->defaultValue(array())->end()
                                ->variableNode('ignorePatterns')->defaultValue(array())->end()
                           ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('session')
                    ->children()
                        ->scalarNode('type')
                            ->validate()
                                ->ifNotInArray(array('pdo'))
                                ->thenInvalid('Only pdo allowed here.')
                            ->end()
                        ->end()
                        ->arrayNode('database')
                            ->children()
                                ->scalarNode('host')->isRequired()->end()
                                ->scalarNode('username')->isRequired()->end()
                                ->scalarNode('password')->isRequired()->end()
                                ->scalarNode('database')->isRequired()->end()
                                ->scalarNode('table')->defaultValue('azure_sessions')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('assets')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('type')
                            ->defaultValue('webrole')
                            ->validate()
                                ->ifNotInArray(array('webrole', 'blob', 'service'))
                                ->thenInvalid('Assets can either deployed on local "webrole" or in "blob" storage.')
                            ->end()
                        ->end()
                        ->scalarNode('id')->end()
                        ->scalarNode('accountName')->end()
                        ->scalarNode('accountKey')->end()
                    ->end()
                ->end()
                ->arrayNode('federations')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('federationName')->isRequired()->end()
                            ->scalarNode('distributionKey')->isRequired()->end()
                            ->scalarNode('distributionType')->isRequired()->end()
                            ->scalarNode('filteringEnabled')->defaultValue(false)->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}

