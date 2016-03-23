<?php

namespace Nz\CrawlerBundle\Controller\CRUD;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
//form
use Nz\CrawlerBundle\Entity\Link;
use Nz\CrawlerBundle\Form\Type\UrlsType;

/**
 * Class CRUDController.
 *
 * @author  nz
 */
class ProfileController extends Controller
{

    /**
     * Crawl Profile Index Action
     */
    public function crawlIndexAction($id, Request $request = null)
    {
        $profile = $this->admin->getSubject();
        $persist = $request->get('persist', false);
        $manager = $this->admin->getProfileManager();

        $links = $manager->handleProfileIndex($profile->getId(), $persist);

        $errors = $manager->getLastHandlerErrors();
        $this->addFlashMessage(['Success' => $links], ['Errors' => $errors]);

        return new RedirectResponse($this->admin->generateUrl('edit', array('id' => $id)));
    }

    public function crawlLinksAction($id, Request $request = null)
    {
        $manager = $this->admin->getProfileManager();
        $profile = $this->admin->getSubject();
        $persist = $request->get('persist', false);

        $entities = $manager->handleProfileLinks($profile->getId(), $persist);
        $errors = $manager->getLastHandlerErrors();
        $this->addFlashMessage(['success' => $entities], ['errors' => $errors]);

        return new RedirectResponse($this->admin->generateUrl('nz.crawler.admin.link.list', array('id' => $id)));
    }

    /**
     * Crawl Profile Index Action
     */
    public function crawlIndexesAction(Request $request = null)
    {
        die('crawl all profiles indexes');
    }

    public function crawlEntitiesAction(Request $request = null)
    {
        die('crawl all profiles entities');
    }

    public function addFlashMessage($success, $info = array(), $errors = array())
    {
        $key = is_null(key($success)) ? 'Success' : key($success);
        $success = is_int(key($success)) ? $success : $success[$key];
        $this->addFlash('sonata_flash_success', sprintf('<b>%s:</b> %d <br>%s', $key, count($success), implode('<br>', $success)));

        if (!empty($info)) {
            $key = is_int(key($info)) ? 'Info' : key($info);
            $info = is_int(key($info)) ? $info : $info[$key];
            $this->addFlash('sonata_flash_info', sprintf('<b>%s:</b> %d <br>%s', $key, count($info), implode('<br>->', $info)));
        }

        if (!empty($errors)) {
            /* dd($errors); */
            $key = is_null(key($errors)) ? 'Errors' : key($errors);
            $values = is_null(key($errors)) ? $errors : $errors[$key];
            $e = [];
            foreach ($values as $error) {
                $e[] = $error->getMessage();
            }
            $this->addFlash('sonata_flash_error', sprintf('<b>%s:</b> %d <br>%s', $key, count($values), implode('<br>->', $e)));
        }
    }

    public function crawlUrlsAction($id, Request $request = null)
    {

        $id = $request->get($this->admin->getIdParameter());
        $handler = $this->getHandler();
        $client = $this->getClientPool()->getClient('config');
        $profile = $this->admin->getSubject();
        $persist = $request->get('persist', false);

        if (!$profile) {
            throw $this->createNotFoundException(sprintf('unable to find the object with id : %s', $id));
        }
        $client->configure($profile->getParsedConfig());

        $form = $this->createForm(UrlsType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $urls = explode(',', $form->get('urls')->getData());
            $links = $handler->urlsToLinks($urls, $persist);
            $errors = $handler->getErrors();
            if ($persist) {
                $profile->setLinks($links);
                $this->getDoctrine()->getManager()->flush();
            }

            $entities = $handler->handleLinks($client, $links, $persist);
            $errors = array_merge($errors, $handler->getErrors());
            /* dd($links, $entities, $errors); */
            $this->addFlashMessage(['Links' => $links], ['Entities' => $entities], ['errors' => $errors]);

            return new RedirectResponse(
                $this->admin->generateUrl('crawl-urls', array('id' => $id))
            );
        }

        return $this->render($this->admin->getTemplate('crawl_urls'), array(
                'action' => 'crawl-urls',
                'elements' => $this->admin->getShow(),
                'object' => $profile,
                'form' => $form->createView(),
                ), null, $request);
    }

    public function cloneAction()
    {
        $object = $this->admin->getSubject();

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        // Be careful, you may need to overload the __clone method of your object
        // to set its id to null !
        $clonedObject = clone $object;

        $clonedObject->setName($object->getName() . ' (Clone)');

        $this->admin->create($clonedObject);

        $this->addFlash('sonata_flash_success', 'Cloned successfully');

        return new RedirectResponse($this->admin->generateUrl('edit', ['id' => $clonedObject->getId()]));

        // if you have a filtered list and want to keep your filters after the redirect
        // return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));
    }

    public function crawlEntityAction($id, Request $request = null)
    {
        $handler = $this->getHandler();
        $clientPool = $this->getClientPool();
        $client = $clientPool->getEntityClient('dynamic');
        $linkManager = $this->getLinkManager();
        $profile = $this->admin->getSubject();
        $config = $this->admin->parseConfig($profile->getConfig());
        $persist = $request->get('persist', false);
        if (!is_array($config)) {
            return new RedirectResponse($this->admin->generateUrl('list'));
        }

        /* $handler->setEntityClass($config['entity']['target_entity']); */
        /* $client->useConfig($config['entity']); */
        $links = $linkManager->findFromHost($config['entity']['base_host']);

        /* $links = array_splice($links, -1); */
        $errors = [];
        $entities = [];
        ini_set('max_execution_time', 0);
        foreach ($links as $link) {
            $client->setLink($link);
            $entity = $handler->handleLink($client, $persist);
            dd($entity);
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
