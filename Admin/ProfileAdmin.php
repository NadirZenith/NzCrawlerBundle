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
            ->with('Main', array(
                'class' => 'col-md-10',
            ))
                ->add('name')
                ->add('config', 'sonata_simple_formatter_type', array(
                    'format' => 'markdown',
                    'attr' => array(
                        'style' => 'min-height:350px'
                    )
                ))
            ->end()
            ->with('Option', array(
                'class' => 'col-md-2',
            ))
                ->add('processed')
                ->add('enabled')
            ->end()
            ->with('Status', array(
                'class' => 'col-md-10',
            ))
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
                /* 'template' => 'NzCrawlerBundle:CRUD:list__identifier.html.twig' */
            ))
            ->add('enabled', null, array('editable' => true))
            ->add('processed', null, array('editable' => false))
            ->add('lastProcessedAt')
            ->add('lastProcessedStatus')
            /*       custom actions     */
            ->add('_action', 'crawl', array(
                'actions' => array(
                    'Crawl Index' => array(
                        'template' => 'NzCrawlerBundle:CRUD:list__action_crawl_index.html.twig'
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

    /**
     * {@inheritdoc}
     */
    public function validate(ErrorElement $errorElement, $profile)
    {
        try {
            $parser = new Parser();
            $config = $parser->parse($profile->getConfig());

            // Use a Symfony ConfigurationInterface object to specify the *.yml format
            $yamlConfiguration = new Configuration();
            // Process the configuration files (merge one-or-more *.yml files)
            $processor = new Processor();
            $processor->processConfiguration(
                $yamlConfiguration, array($config) // As many *.yml files as required
            );
        } catch (ParseException $ex) {

            $errorElement->addViolation(sprintf('Invalid YML: %s ', $ex->getMessage()));
        } catch (InvalidConfigurationException $ex) {
            $errorElement->addViolation(sprintf('Invalid Configuration: %s', $ex->getMessage()));
        }
    }

    /**
     */
    public function setProfileManager(ProfileManagerInterface $profileManager)
    {
        $this->profileManager = $profileManager;
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
        return $this->linkManager ;
    }
}
