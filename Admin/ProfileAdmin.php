<?php

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

class ProfileAdmin extends Admin
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
        //crawl index config
        $collection->add('crawl-index', $this->getRouterIdParameter() . '/crawl-index');
        $collection->add('crawl-indexes');
        //crawl entity config
        $collection->add('crawl-entity', $this->getRouterIdParameter() . '/crawl-entity');
        $collection->add('crawl-entities');
        // crawl url(s)
        $collection->add('crawl-urls', $this->getRouterIdParameter() . '/crawl-urls');
        $collection->add('crawl-links', $this->getRouterIdParameter() . '/crawl-links');
        //clone profile
        $collection->add('clone', $this->getRouterIdParameter() . '/clone');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureSideMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
        /* d($childAdmin); */
        $admin = $childAdmin ? $childAdmin : $this;

        $request = $this->getRequest();
        $persist = $this->getRequest()->get('persist', false);

        $uri = $admin->generateUrl($action, array_merge($request->attributes->get('_route_params'), array('persist' => !$persist)));
        $style = 'background-color:%s';
        $menu->addChild($persist ? $this->trans('sidemenu.link_persisting') : $this->trans('sidemenu.link_testing'), [
            'uri' => $uri,
            'attributes' => array(
                'style' => sprintf($style, $persist ? 'orangered' : 'greenyellow')
            )
        ]);
        $id = $this->getSubject() ? $this->getSubject()->getId() : false;

        /* d('profileAdmin', $action); */
        if (in_array($action, ['edit'])) {

            $menu->addChild($this->trans('sidemenu.link_crawl_index'), array(
                'uri' => $this->generateUrl('crawl-index', array('id' => $id))
            ));

            $menu->addChild($this->trans('sidemenu.link_crawl_urls'), array(
                'uri' => $this->generateUrl('crawl-urls', array('id' => $id))
            ));

            $menu->addChild($this->trans('sidemenu.link_view_links'), array(
                'uri' => $this->generateUrl('nz.crawler.admin.link.list', array('id' => $id))
            ));
        }
        if (in_array($action, ['list'])) {
            if ($id) {
                $menu->addChild($this->trans('sidemenu.link_edit'), array(
                    'uri' => $this->generateUrl('edit', array('id' => $id))
                ));
                $menu->addChild($this->trans('sidemenu.link_crawl_links'), array(
                    'uri' => $this->generateUrl('crawl-links', array('id' => $id))
                ));
            } else {
                $menu->addChild($this->trans('sidemenu.link_crawl_indexes'), array(
                    'uri' => $this->generateUrl('crawl-indexes')
                ));
                $menu->addChild($this->trans('sidemenu.link_crawl_entities'), array(
                    'uri' => $this->generateUrl('crawl-entities')
                ));
                //crawl indexes
                //crawl entities
            }
        }
        if (in_array($action, ['crawl-urls'])) {
            $menu->addChild($this->trans('sidemenu.link_edit'), array(
                'uri' => $this->generateUrl('edit', array('id' => $id))
            ));
        }
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
            ->tab('Profile')
            ->with('Main', array(
                'class' => 'col-md-12',
            ))
            ->add('name')
            ->add('config', 'sonata_simple_formatter_type', array(
                'format' => 'markdown',
                'attr' => array(
                    'style' => 'min-height:350px'
                )
            ))
            ->end()
            ->with('Status', array(
                'class' => 'col-md-10',
            ))
            ->add('lastProcessedAt')
            ->add('lastProcessedStatus')
            ->end()
            ->with('Option', array(
                'class' => 'col-md-2',
            ))
            ->add('processed')
            ->add('enabled')
            ->end()
            ->end()
            ->tab('crawl')
            ->with('Status', array(
                'class' => 'col-md-10',
            ))
            /* ->add('links', 'sonata_type_collection') */
            /* ->add('links', 'textarea', ['mapped' => false]) */
            /* ->add('links', 'sonata_type_collection') */
            ->end()
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
                'template' => 'NzCrawlerBundle:CRUD:list__profile_identifier.html.twig'
            ))
            ->add('enabled', null, array('editable' => true))
            ->add('processed', null, array('editable' => false))
            /*       custom actions     */
            ->add('_action', 'crawl', array(
                'actions' => array(
                    'Crawl Index' => array(
                        'template' => 'NzCrawlerBundle:CRUD:list__profile_action.html.twig'
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
            ->add('name')
            ->add('enabled')
            ->add('processed')
            ->add('lastProcessedAt')
            ->add('lastProcessedStatus')
        ;
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
     * {@inheritdoc}
     */
    public function validate(ErrorElement $errorElement, $profile)
    {
        try {
            $this->parseConfig($profile->getConfig());
        } catch (ParseException $ex) {

            $errorElement->addViolation(sprintf('Invalid YML: %s ', $ex->getMessage()));
        } catch (InvalidConfigurationException $ex) {
            $errorElement->addViolation(sprintf('Invalid Configuration: %s', $ex->getMessage()));
        }
    }

    public function parseConfig($config)
    {

        $parser = new Parser();
        // Use a Symfony ConfigurationInterface object to specify the *.yml format
        $yamlConfiguration = new Configuration();

        // Process the configuration files (merge one-or-more *.yml files)
        $processor = new Processor();
        return $processor->processConfiguration(
                $yamlConfiguration, array($parser->parse($config)) // As many *.yml files as required
        );
    }

    /**
     */
    public function setProfileManager(ProfileManagerInterface $profileManager)
    {
        $this->profileManager = $profileManager;
    }

    /**
     */
    public function getProfileManager()
    {
        return $this->profileManager;
    }

    /**
     * @param LinkManagerInterface $linkManager
     */
    public function setLinkManager(LinkManagerInterface $linkManager)
    {
        $this->linkManager = $linkManager;
    }

    /**
     * @param LinkManagerInterface $linkManager
     */
    public function getLinkManager()
    {
        return $this->linkManager;
    }
}
