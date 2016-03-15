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
use Sonata\AdminBundle\Route\RouteCollection;

class ProfileAdmin extends Admin
{

    /**
     * @var LinkManagerInterface
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
        '_per_page' => 320,
        '_sort_order' => 'DESC',
    );

    protected function configureRoutes(RouteCollection $collection)
    {
        // on top
        $collection->add('crawl-indexes', 'crawl-indexes');
        $collection->add('crawl-links', 'crawl-links');
        $collection->add('crawl-url', 'crawl-url');
        // on list
        $collection->add('crawl-link', $this->getRouterIdParameter() . '/crawl');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('name')
            ->add('config')
            ->add('processed')
            ->add('enabled')
            ->add('lastProcessedAt')
            ->add('lastProcessedStatus')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Option', array(
                'class' => 'col-md-8',
            ))
                ->add('name')
                ->add('config','textarea')
                ->add('processed')
                ->add('enabled')
                ->add('lastProcessedAt')
                ->add('lastProcessedStatus')
            ->end()
        ;
        
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {

        $listMapper
            ->addIdentifier('name', null, array(
                /*'template' => 'NzCrawlerBundle:CRUD:list__identifier.html.twig'*/
            ))
            ->add('enabled', null, array('editable' => true))
            ->add('processed', null, array('editable' => false))
            ->add('lastProcessedAt')
            ->add('lastProcessedStatus')
            
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {

        $datagridMapper
            ->add('name')
            ->add('enabled')
            ->add('processed')
            ->add('lastProcessedAt')
            ->add('lastProcessedStatus')
        ;
    }

    /**
     */
    public function setProfileManager(ProfileManagerInterface $profileManager)
    {
        $this->profileManager= $profileManager;
    }
}
