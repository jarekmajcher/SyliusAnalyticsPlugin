<?php

declare(strict_types=1);

namespace Setono\SyliusAnalyticsPlugin\DependencyInjection;

use Setono\SyliusAnalyticsPlugin\Form\Type\ContainerType;
use Setono\SyliusAnalyticsPlugin\Form\Type\PropertyType;
use Setono\SyliusAnalyticsPlugin\Model\Container;
use Setono\SyliusAnalyticsPlugin\Model\Property;
use Setono\SyliusAnalyticsPlugin\Repository\ContainerRepository;
use Setono\SyliusAnalyticsPlugin\Repository\PropertyRepository;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Sylius\Component\Resource\Factory\Factory;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('setono_sylius_analytics');
        $rootNode = $treeBuilder->getRootNode();

        /** @psalm-suppress MixedMethodCall,PossiblyNullReference,UndefinedInterfaceMethod */
        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('events')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('add_payment_info')->defaultTrue()->end()
                        ->booleanNode('add_shipping_info')->defaultTrue()->end()
                        ->booleanNode('add_to_cart')->defaultTrue()->end()
                        ->booleanNode('begin_checkout')->defaultTrue()->end()
                        ->booleanNode('purchase')->defaultTrue()->end()
                        ->booleanNode('view_cart')->defaultTrue()->end()
                        ->booleanNode('view_item_list')->defaultTrue()->end()
                        ->booleanNode('view_item')->defaultTrue()->end()
        ;

        $this->addResourcesSection($rootNode);

        return $treeBuilder;
    }

    private function addResourcesSection(ArrayNodeDefinition $node): void
    {
        /** @psalm-suppress MixedMethodCall,PossiblyNullReference,UndefinedInterfaceMethod */
        $node
            ->children()
                ->arrayNode('resources')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('container')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->defaultValue(Container::class)->cannotBeEmpty()->end()
                                        ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                        ->scalarNode('repository')->defaultValue(ContainerRepository::class)->cannotBeEmpty()->end()
                                        ->scalarNode('factory')->defaultValue(Factory::class)->end()
                                        ->scalarNode('form')->defaultValue(ContainerType::class)->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('property')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->defaultValue(Property::class)->cannotBeEmpty()->end()
                                        ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                        ->scalarNode('repository')->defaultValue(PropertyRepository::class)->cannotBeEmpty()->end()
                                        ->scalarNode('factory')->defaultValue(Factory::class)->end()
                                        ->scalarNode('form')->defaultValue(PropertyType::class)->cannotBeEmpty()->end()
        ;
    }
}
