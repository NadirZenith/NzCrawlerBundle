<?php

namespace Nz\CrawlerBundle\Controller\CRUD;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
//form
use Nz\CrawlerBundle\Entity\Link;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

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
        $config = $this->admin->getProfileConfig($profile);

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

        return new RedirectResponse($this->admin->generateUrl('edit', array('id' => $id)));
    }

    public function crawlLinksAction($id, Request $request = null)
    {

        $id = $request->get($this->admin->getIdParameter());

        $object = $this->admin->getObject($id);
        $persist = $request->get('persist', false);

        if (!$object) {
            throw $this->createNotFoundException(sprintf('unable to find the object with id : %s', $id));
        }

        $this->admin->checkAccess('show', $object);

        $preResponse = $this->preShow($request, $object);
        if ($preResponse !== null) {
            return $preResponse;
        }

        $this->admin->setSubject($object);

        /*        */

        // create a task and give it some dummy data for this example
        $link = new Link();
        /* $link->setUrl('url here'); */
        $link->getCrawledAt(new \DateTime('now'));

        $form = $this->createFormBuilder($link)
            ->add('url', TextType::class)
            /* ->add('dueDate', DateType::class) */
            ->add('save', SubmitType::class, array('label' => 'Crawl Link'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $config = $this->admin->getProfileConfig($object);
            /*dd($persist);*/
            $this->crawlLinkWithConfig($link, $config, $persist);
            /* d($object); */
            /* dd($link); */
            // ... perform some action, such as saving the task to the database

            /* return $this->redirectToRoute('task_success'); */
            return new RedirectResponse(
                $this->admin->generateUrl('crawl-links', array('id' => $id))
            );
        }
        /* dd($this->admin->getTemplates()); */
        /* $datagrid = $this->admin->getDatagrid(); */
        /* $formView = $datagrid->getForm()->createView(); */

        return $this->render($this->admin->getTemplate('crawl_links'), array(
                'action' => 'crawl-links',
                'elements' => $this->admin->getShow(),
                'object' => $object,
                'form' => $form->createView(),
                /* 'form' => $formView, */
                /* 'datagrid' => $datagrid, */
                /* 'csrf_token' => $this->getCsrfToken('sonata.batch'), */
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
        $profile = $this->admin->getSubject();
        $config = $this->admin->getProfileConfig($profile);
        if (!is_array($config)) {
            return new RedirectResponse($this->admin->generateUrl('list'));
        }


        $persist = $request->get('persist', false);
        $handler = $this->getHandler();
        $clientPool = $this->getClientPool();
        $client = $clientPool->getEntityClient('dynamic');
        $client->useConfig($config['entity']);

        $linkManager = $this->getLinkManager();
        $links = $linkManager->findFromHost($config['entity']['base_host']);
        $links = array_splice($links, -1);

        $handler->setEntityClass($config['entity']['target_entity']);

        $errors = [];
        $entities = [];
        ini_set('max_execution_time', 0);
        foreach ($links as $link) {
            $client->setLink($link);
            $entity = $handler->handleEntityClient($client, $persist);
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

    private function crawlLinkWithConfig($link, $config, $persist = false)
    {
        $handler = $this->getHandler();
        $client = $this->getClientPool()->getEntityClient('dynamic');
        $client->useConfig($config['entity']);

        $handler->setEntityClass($config['entity']['target_entity']);

        $client->setLink($link);
        $entity = $handler->handleEntityClient($client, $persist);
        if (!$entity) {
            $notes = $link->getNotes();
            $error = substr(end($notes), 0, 200);
            $this->addFlash('sonata_flash_error', sprintf('Error: %s ', $error));
        } else {
            $success = method_exists($entity, '__toString') ? $entity : $entity->getId();
            $this->addFlash('sonata_flash_success', sprintf('Success: %s ', $success));
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
        return $this->get('nz.crawler.link.manager');
    }
}
