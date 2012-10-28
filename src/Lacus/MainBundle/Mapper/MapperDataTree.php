<?php

namespace Lacus\MainBundle\Mapper;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class MapperDataTree
{
    /**
     * @return \Symfony\Component\Config\Definition\NodeInterface
     */
    public static function getTree()
    {
        $treeBuilder = new TreeBuilder();
        $options = $treeBuilder->root('data');
        $options
            ->children()
                ->arrayNode('fields')
                    ->cannotBeEmpty()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->arrayNode('segment')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('name')->defaultNull()->end()
                                    ->variableNode('options')
                                        ->defaultValue(array())
                                        ->beforeNormalization()
                                            ->ifTrue(function($v){ return !is_array($v); })
                                            ->then(function($v){ return array($v); })
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('field')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('value')->defaultNull()->end()
                                    ->scalarNode('rows')->defaultValue(1)->end()
                                    ->scalarNode('default')->defaultNull()->end()
                                    ->booleanNode('show_alternatives')->defaultFalse()->end()
                                    ->booleanNode('wysiwyg')->defaultFalse()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->children()
                ->arrayNode('files')
                    ->canBeUnset()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->arrayNode('segment')
                                ->children()
                                    ->scalarNode('name')->isRequired()->end()
                                    ->variableNode('options')->defaultValue(array())->end()
                                ->end()
                            ->end()
                            ->arrayNode('field')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->booleanNode('multiple')->defaultFalse()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder->buildTree();
    }

    /**
     * @param string $rawData
     * @return array
     */
    public static function filter($rawData)
    {
        $tree = self::getTree();
        return $tree->finalize($tree->normalize($rawData));
    }
}