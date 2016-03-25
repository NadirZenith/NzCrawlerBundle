<?php

namespace Nz\CrawlerBundle\Client;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('client');
        
        $this->addIndexSection($rootNode);
        $this->addEntitySection($rootNode);
        
        return $treeBuilder;
        
    }
    
    /**
     *  Add user config section
     */
    private function addIndexSection($rootNode){
        $rootNode
            ->children()
                /*->arrayNode('index')*/
                    /*->children()*/
                        ->scalarNode('service')->info('...')->cannotBeEmpty()->defaultValue('nz.migration.wp.user_default')->end()
                        ->scalarNode('baseurl')->info('Src Entity')->cannotBeEmpty()->defaultValue('\Nz\WordpressBundle\Entity\User')->end()
                        ->scalarNode('base_domain')->info('Base domain to prepend to relative paths')->cannotBeEmpty()->end()
                        ->scalarNode('link_filter_selector')->info('..')->cannotBeEmpty()->end()
                        ->scalarNode('logo_url')->info('profile logo url')->defaultValue(false)->end()
                        ->scalarNode('logo_selector')->info('profile logo url selector')->defaultValue(false)->end()
                        ->scalarNode('next_page_selector')->info('next page css selector')->defaultValue(false)->end()
                        ->scalarNode('start_page')->info('..')->cannotBeEmpty()->end()
                        ->scalarNode('limit_pages')->info('..')->cannotBeEmpty()->end()
                        ->scalarNode('next_page_mask')->info('..')->cannotBeEmpty()->end()
                        ->scalarNode('next_page_mask')->info('..')->defaultValue(false)->end()
                        //->scalarNode()->info('..')->cannotBeEmpty()->end()
                        //->append($this->addFieldsMappingNode('fields'))
                        ->arrayNode('strings_to_filter')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('regexes_to_filter')
                            ->prototype('scalar')->end()
                        ->end()
                    /*->end()*/
                /*->end()*/
            ->end()
        ;
    }
    /**
     *  Add user config section
     */
    private function addEntitySection($rootNode){
        $rootNode
            ->children()
                /*->arrayNode('entity')*/
                    /*->children()*/
                        ->scalarNode('service')->info('...')->cannotBeEmpty()->defaultValue('nz.migration.wp.user_default')->end()
                        ->scalarNode('target_class')->info('...')->cannotBeEmpty()->defaultValue('AppBundle/Entity/News/Post')->end()
                        ->scalarNode('base_host')->info('...')->cannotBeEmpty()->defaultValue('nz.migration.wp.user_default')->end()
                        ->scalarNode('article_base_filter')->info('Src Entity')->cannotBeEmpty()->defaultValue('\Nz\WordpressBundle\Entity\User')->end()
                        ->scalarNode('link_filter_selector')->info('..')->cannotBeEmpty()->end()
                        ->scalarNode('start_page')->info('..')->cannotBeEmpty()->end()
                        ->scalarNode('limit_pages')->info('..')->cannotBeEmpty()->end()
                        ->scalarNode('next_page_mask')->info('..')->cannotBeEmpty()->end()
                        //->scalarNode()->info('..')->cannotBeEmpty()->end()
                        ->arrayNode('strings_to_filter')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('regexes_to_filter')
                            ->prototype('scalar')->end()
                        ->end()
                        /*->append($this->addItemsMappingNode())*/
                        ->append($this->addFieldsMappingNode('items'))
                        ->append($this->addFieldsMappingNode('filters'))
                        ->append($this->addFieldsMappingNode('defaults'))
                        ->append($this->addFieldsMappingNode('entity'))
                    /*->end()*/
                /*->end()*/
            ->end()
        ;
    }
    
    
    /**
     * @param string $field_type fields or metas
     */
    private function addFieldsMappingNode($field_type ){
        $builder = new TreeBuilder();
        $node = $builder->root($field_type);
        
        $node
            ->prototype('array')
                ->beforeNormalization()
                    ->ifString()->then(function ($v) {return [0 => $v];})
                    /*->ifTrue()->then(function ($v) {return [0 => $v];})*/
                ->end()
                ->children()
                    ->scalarNode(0)->cannotBeEmpty()->end()
                    ->scalarNode(1)->cannotBeEmpty()->defaultValue('bypass')->end()
                    ->variableNode(2)->cannotBeEmpty()->defaultValue([])
                        ->beforeNormalization()
                            ->ifArray()
                            ->then(function ($a) {
                                if(count($a[0])===1){
                                    //normal
                                    return $this->fixOptions($a);
                                }else{
                                    //stack
                                    $result = [];
                                    foreach ($a as $stack ) {
                                        $result[] = [
                                            $stack[0],
                                            $stack[1],
                                            $this->fixOptions($stack[2])
                                        ];
                                    }
                                    return $result;
                                }
                               
                            })
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
                            
        return $node;

    }
    
    private function fixOptions($a)
    {
        $result = [];
        foreach ($a as $option ) {
            $result = array_merge($result,$option);
        }
        
        return $result;

    }
}
