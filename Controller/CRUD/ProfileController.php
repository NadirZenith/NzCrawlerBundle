<?php

namespace Nz\CrawlerBundle\Controller\CRUD;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Nz\CrawlerBundle\Clients;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Config\Definition\Processor;
use Nz\CrawlerBundle\Client\Configuration;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * Class CRUDController.
 *
 * @author  nz
 */
class ProfileController extends Controller
{

    /**
     * crawl all indexes into new links
     */
    public function crawlIndexAction($id, Request $request = null)
    {

        $profile = $this->admin->getSubject();
        $config = $this->getProfileConfig($profile);

        if (!is_array($config)) {
            return $config;
        }

        $persist = $request->get('persist', false);

        $handler = $this->getHandler();
        $clientPool = $this->getClientPool();

        $client = $clientPool->getIndexClient('dynamic');

        $client->useConfig($config['index']);

        $links = $handler->handleIndexClient($client, $persist);
        $info = '';
        foreach ($links as $link) {
            $info .= sprintf('<p>%s</p>', $link->getUrl());
        }

        $errors = $handler->getErrors();
        $error = '';
        foreach ($errors as $err) {
            $notes = $err->getNotes();
            $error .= sprintf('<p>%s</p>', end($notes));
        }

        $this->addFlash('sonata_flash_success', sprintf('New Links: %s <br> %s', count($links), $info));
        $this->addFlash('sonata_flash_error', sprintf('Errors: %s <br> %s', count($errors), $error));

        return new RedirectResponse($this->admin->generateUrl('list'));
    }

    public function crawlEntityAction($id, Request $request = null)
    {
        include 'nzdebug.php';
        $profile = $this->admin->getSubject();
        $config = $this->getProfileConfig($profile);
        if (!is_array($config)) {
            return $config;
        }
        $persist = $request->get('persist', true);

        $handler = $this->getHandler();
        $clientPool = $this->getClientPool();
        $client = $clientPool->getEntityClient('dynamic');
        $client->useConfig($config['entity']);

        $linkManager = $this->getLinkManager();
        $links = $linkManager->findFromHost($config['entity']['base_host']);
        $links = array_splice($links, -5);

        $handler->setEntityClass($config['entity']['target_entity']);

        $errors = [];
        $entities = [];
        ini_set('max_execution_time', 0);
        foreach ($links as $link) {
            $client->setLink($link);
            $entity = $handler->handleEntityClient($client, $persist);
            if (!$entity) {
                $notes = $link->getNotes();
                $errors[] = substr(end($notes), 0, 200);
            } else {
                $entities[] = $entity->getTitle();
            }
        }

        d($entity);
        d($errors);
        d($config);
        d($links);
        dd($entities);
    }

    public function getProfileConfig($profile)
    {
        try {
            $parser = new Parser();
            $config = $parser->parse($profile->getConfig());
        } catch (ParseException $ex) {

            $this->addFlash('sonata_flash_error', sprintf('Invalid YML: %s ', $ex->getMessage()));
            return new RedirectResponse($this->admin->generateUrl('list'));
        }


        // Use a Symfony ConfigurationInterface object to specify the *.yml format
        $yamlConfiguration = new Configuration();
        /* d($yamlConfiguration->getConfigTreeBuilder()->buildTree()); */

        // Process the configuration files (merge one-or-more *.yml files)
        $processor = new Processor();
        try {
            $configuration = $processor->processConfiguration(
                $yamlConfiguration, array($config) // As many *.yml files as required
            );
        } catch (InvalidConfigurationException $ex) {

            $this->addFlash('sonata_flash_error', sprintf('Invalid Configuration: %s ', $ex->getMessage()));
            return new RedirectResponse($this->admin->generateUrl('list'));
        }

        return $configuration;
    }

    /**
     * Get Crawler handler
     * 
     * @return \Nz\CrawlerBundle\Crawler\Handler
     */
    private function getHandler()
    {
        return $this->get('nz.crawler.handler');
    }

    /**
     * Get Link Manager
     * 
     * @return \Nz\CrawlerBundle\Client\ClientPool
     */
    private function getClientPool()
    {
        return $this->get('nz.crawler.client.pool');
    }

    /**
     * Get Link Manager
     * 
     * @return \Nz\CrawlerBundle\Entity\LinkManager
     */
    private function getLinkManager()
    {
        return $this->get('nz.crawler.link.manager');
    }
}
