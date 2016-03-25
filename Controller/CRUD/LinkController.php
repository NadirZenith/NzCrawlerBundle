<?php

namespace Nz\CrawlerBundle\Controller\CRUD;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Nz\CrawlerBundle\Clients;

/**
 * Class CRUDController.
 *
 * @author  nz
 */
class LinkController extends Controller
{

    /**
     * crawl all indexes into new links
     */
    public function crawlIndexesAction(Request $request = null)
    {

        $persist = $request->get('persist', false);

        $handler = $this->getHandler();
        $clientPool = $this->getClientPool();
        $clients = $clientPool->getClients();
        $links = [];
        $errors = [];
        foreach ($clients as $client) {
            $l = $handler->handleIndex($client, $persist);

            $links = array_merge($links, $l);

            $e = $handler->getErrors();
            $errors = array_merge($errors, $e);
        }

        $this->addFlashMessage(['success' => $links], [ 'Errors' => $errors]);

        return new RedirectResponse($this->admin->generateUrl('list'));
    }

    /**
     * Crawl link
     */
    public function crawlLinkAction($id, Request $request = null)
    {

        $link = $this->admin->getSubject();
        $handler = $this->getHandler();
        $persist = $request->get('persist', false);

        if (!$link) {
            throw new NotFoundHttpException(sprintf('unable to find the link with id : %s', $id));
        }

        $clientPool = $this->getClientPool();
        if ($this->admin->getParent()) {
            //dynamic client from profile
            $profile = $this->admin->getParent()->getSubject();
            $client = $clientPool->getClient('config');
            $client->configure($link, $profile->getParsedConfig());
        } else {
            //system client
            $client = $clientPool->getClientForLink($link);
        }

        if (!$client) {
            $this->addFlash('sonata_flash_error', sprintf('No client for url: %s', $link->getUrl()));
            return new RedirectResponse($this->admin->generateUrl('list'));
        }

        $entity = $handler->handleLink($client, $persist);
        if ($entity) {

            $this->addFlash('sonata_flash_success', sprintf('Success: %s:%d', $entity, $entity->getId()));
        } else {
            $this->addFlash('sonata_flash_error', sprintf('Error: %s', implode('', $handler->getErrors())));
        }
        
        return new RedirectResponse($this->admin->generateUrl('list'));
    }

    /**
     * Crawl not processed links
     */
    public function crawlLinksAction(Request $request = null)
    {
        $persist = $request->get('persist', false);
        $limit = $request->get('limit', 5);

        $linkManager = $this->getLinkManager();

        $handler = $this->getHandler();
        $links = $linkManager->findLinksForProcess($limit);
        $clientPool = $this->getClientPool();

        $errors = [];
        $entities = [];
        ini_set('max_execution_time', 0);
        foreach ($links as $link) {
            $client = $clientPool->getEntityClientForLink($link);

            if ($client) {
                $entity = $handler->handleLink($client, $persist);

                if (!$entity) {
                    $notes = $link->getNotes();
                    $errors[] = substr(end($notes), 0, 200);
                } else {
                    $entities[] = $entity->getTitle();
                }
            }
        }

        $flash = sprintf('<b>Links:</b> %s<br> <b>Success:</b> %s<br>%s <br><b>Errors:</b>%s<br>%s', count($links), count($entities), implode('<br>', $entities), count($errors), implode('<br> -', $errors));

        $this->addFlash('sonata_flash_info', $flash);

        return new RedirectResponse($this->admin->generateUrl('list'));
    }

    /**
     * Crawl link
     */
    public function crawlUrlAction(Request $request)
    {

        $url = $request->get('url');
        $persist = $request->get('persist', false);

        if (!$url) {
            throw new NotFoundHttpException('No url supplied');
        }

        $link = new \Nz\CrawlerBundle\Entity\Link();
        $link->setUrl($url);

        $clientPool = $this->getClientPool();
        $handler = $this->getHandler();

        $client = $clientPool->getEntityClientForLink($link);
        if (!$client) {
            $this->addFlash('sonata_flash_error', sprintf('No client for url: %s', $link->getUrl()));
            return new RedirectResponse($this->admin->generateUrl('list'));
        }
        $entity = $handler->handleLink($client, $persist);

        if (!$entity) {
            $notes = $link->getNotes();
            $error = end($notes);

            $this->addFlash('sonata_flash_error', sprintf('Error creating entity: %s', $error));
        } else {
            $this->addFlash('sonata_flash_success', sprintf('Created entity with id %s, title %s', $entity->getId(), $entity->getTitle()));
        }

        return new RedirectResponse($this->admin->generateUrl('list'));
    }

    public function addFlashMessage($success, $info = array(), $errors = array())
    {
        $key = is_int(key($success)) ? 'Success' : key($success);
        $success = is_int(key($success)) ? $success : $success[$key];
        $this->addFlash('sonata_flash_success', sprintf('<b>%s:</b> %d <br>%s', $key, count($success), implode('<br>', $success)));

        if (!empty($info)) {
            $key = is_int(key($info)) ? 'Info' : key($info);
            $info = is_int(key($info)) ? $info : $info[$key];
            $this->addFlash('sonata_flash_info', sprintf('<b>%s:</b> %d <br>%s', $key, count($info), implode('<br>', $info)));
        }

        if (!empty($errors)) {
            $e = [];
            foreach ($errors as $link) {

                $notes = $link->getNotes();
                $e[] = substr(end($notes), 0, 200);
            }
            $this->addFlash('sonata_flash_error', sprintf('<b>Errors:</b> %d <br>%s', count($errors), implode('<br>', $e)));
        }
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
        return $this->get('nz.crawler.manager.link');
    }
}
