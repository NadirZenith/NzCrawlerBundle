<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nz\CrawlerBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Nz\CrawlerBundle\Model\ProfileManagerInterface;
use Nz\CrawlerBundle\Model\LinkManagerInterface;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\CoreBundle\Validator\ErrorElement;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Config\Definition\Processor;
use Nz\CrawlerBundle\Client\Configuration;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Sonata\AdminBundle\Admin\AdminInterface;
use Knp\Menu\ItemInterface as MenuItemInterface;

class CrawledAdmin extends Admin
{

    /**
     * @var LinkManagerInterface
     */
    protected $linkManager;

    /**
     * @var ProfileManagerInterface
     */
    protected $profileManager;
    protected $maxPerPage = 30;

    /**
     * Default values to the datagrid.
     *
     * @var array
     */
    protected $datagridValues = array(
        '_page' => 1,
        '_per_page' => 28,
        '_sort_order' => 'DESC',
    );

    protected function configureRoutes(RouteCollection $collection)
    {
        // on list
        $collection->add('crawl-index', $this->getRouterIdParameter() . '/crawl-index');
        $collection->add('crawl-entity', $this->getRouterIdParameter() . '/crawl-entity');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('title')
            ->add('date')
            ->add('content')
            ->add('excerpt')
            ->add('image')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Main', array(
                'class' => 'col-md-8',
            ))
            ->add('title')
            ->add('source')
            ->add('excerpt')
            ->add('content', 'sonata_simple_formatter_type', array(
                'format' => 'markdown',
                'attr' => array(
                    'style' => 'min-height:350px'
                )
            ))
            ->end()
            ->with('Option', array(
                'class' => 'col-md-4',
            ))
            ->add('enabled')
            ->add('image', 'sonata_type_model_list', array('required' => true), array(
                'link_parameters' => array(
                    'context' => 'crawl',
                    'hide_context' => true,
                ),
            ))
            ->add('gallery', 'sonata_type_model_list', array(
                'required' => false,
                ), array(
                'link_parameters' => array(
                    'context' => 'crawl',
                    'filter' => array('context' => array('value' => 'crawl')),
                    'provider' => '',
                ),
            ))
            ->add('date', 'sonata_type_datetime_picker', array('dp_side_by_side' => true))
            ->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {

        $listMapper
            ->add('custom', 'string', array(
                'template' => 'NzCrawlerBundle:CRUD:list__crawled_identifier.html.twig',
            ))
            /* ->addIdentifier('title', null, array()) */
            ->add('enabled', null, array('editable' => true))
            ->add('date')
        /*       custom actions     
          ->add('_action', 'crawl', array(
          'actions' => array(
          'Crawl Index' => array(
          'template' => 'NzCrawlerBundle:CRUD:list__action_crawl_index.html.twig'
          )
          )
          ))
         */

        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {

        $datagridMapper
            ->add('title')
            ->add('enabled')
            ->add('date')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureSideMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
        /*
          $persist = $this->getRequest()->get('persist', false);
          $style = 'background-color:%s';
          $menu->addChild($persist ? 'Persisting' : 'Testing', [
          'uri' => $this->generateUrl('list', array('persist' => !$persist)),
          'attributes' => array(
          'style' => sprintf($style, $persist ? 'orangered' : 'greenyellow')
          )
          ]);
         */
    }
}
