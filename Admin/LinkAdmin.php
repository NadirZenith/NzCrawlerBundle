<?php

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
    protected $parentAssociationMapping = 'profile';

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
            ->add('error')
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
            ->add('name')
            ->add('url', 'url')
            ->add('processed')
            ->add('error')
            ->add('skip')
            ->add('crawledAt')
            ->add('notes')
            ->add('profile')
            ->end()
        ;
        /*
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
         */
    }

    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);

        if ($this->getParent()) {
            return $query;
        }

        if ($this->getRequest()->get('filter')) {
            return $query;
        }

        $query->andWhere(
            $query->getRootAliases()[0] . '.profile is NULL'
        );
        return $query;
        /* d($this->getRequest()); */
        /* dd($query); */
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {

        $listMapper
            ->addIdentifier('url', null, array(
                'template' => 'NzCrawlerBundle:CRUD:list__link_identifier.html.twig'
            ))
            ->add('processed', null)
            ->add('error', null, array('editable' => true))
            ->add('skip', null, array('editable' => true))
            /*       custom actions     */
            ->add('_action', 'crawl', array(
                'actions' => array(
                    'Crawl' => array(
                        'template' => 'NzCrawlerBundle:CRUD:list__link_action.html.twig'
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
            ->add('error')
            ->add('skip')
        ;

        if ($this->getParent()) {
            return;
        }
        //if not in parent add by profile filter
        $datagridMapper
            /*
             */
            ->add('profile', null, array(), 'entity', array(
                'class' => 'Nz\CrawlerBundle\Entity\Profile',
                'property' => 'name',
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureSideMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
        $request = $this->getRequest();

        $persist = $this->getRequest()->get('persist', false);
        $uri = $this->generateUrl($action, array_merge($request->attributes->get('_route_params'), array('persist' => !$persist)));
        $style = 'background-color:%s';
        $menu->addChild($persist ? $this->trans('sidemenu.link_persisting') : $this->trans('sidemenu.link_testing'), [
            'uri' => $uri,
            'attributes' => array(
                'style' => sprintf($style, $persist ? 'orangered' : 'greenyellow')
            )
        ]);

        if ('list' === $action) {
            $menu->addChild($this->trans('sidemenu.link_crawl_indexes'), [
                'uri' => $this->generateUrl('crawl-indexes'),
            ]);
            /* $admin = $this->isChild() ? $this->getParent() : $this; */

            $menu->addChild($this->trans('sidemenu.link_crawl_links'), [
                'uri' => $this->generateUrl('crawl-links'),
            ]);
        }
    }

    public function getPersistentParameters()
    {
        if (!$this->getRequest()) {
            return array();
        }
        return array(
            'persist' => $this->getRequest()->get('persist', false)
        );
    }

    /**
     */
    public function setLinkManager(LinkManagerInterface $linkManager)
    {
        $this->linkManager = $linkManager;
    }
}
