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

use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Nz\CrawlerBundle\Model\LinkManagerInterface;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Form\CallbackTransformer;

class LinkAdmin extends Admin
{

    /**
     * @var LinkManagerInterface
     */
    protected $linkManager;
    protected $maxPerPage = 300;

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
            ->add('url')
            ->add('processed')
            ->add('hasError')
            ->add('skip')
            ->add('notes')
            ->add('crawledAt')
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
                ->add('url', 'url')
                ->add('processed')
                ->add('hasError')
                ->add('skip')
                ->add('crawledAt')
                ->add('notes')
            ->end()
        ;
        
        $formMapper->getFormBuilder()->get('notes')
            ->addModelTransformer(new CallbackTransformer(
                function ($dbNotes) {
                $json = json_encode($dbNotes);
                return $json;
            }, function ($formNotes) {

                if (empty($formNotes)) {
                    return [];
                }

                $array = json_decode($formNotes, true);
                return $array;
            }
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {

        $listMapper
            ->addIdentifier('url', null, array(
                'template' => 'NzCrawlerBundle:CRUD:list__identifier.html.twig'
            ))
            ->add('processed', null)
            ->add('hasError', null, array('editable' => true))
            ->add('skip', null, array('editable' => true))
            /*       custom actions     */
            ->add('_action', 'crawl', array(
                'actions' => array(
                    'Crawl' => array(
                        'template' => 'NzCrawlerBundle:CRUD:list__action_crawl.html.twig'
                    )
                )
            ))

        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {

        $datagridMapper
            ->add('url')
            ->add('processed')
            ->add('hasError')
            ->add('skip')
        ;
    }

    /**
     * @param OptionManagerInterface $optionManager
     */
    public function setLinkManager(LinkManagerInterface $linkManager)
    {
        $this->linkManager = $linkManager;
    }
}
