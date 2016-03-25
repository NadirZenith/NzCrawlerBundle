<?php

namespace Nz\CrawlerBundle\Controller\CRUD;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
//form
use Nz\CrawlerBundle\Entity\Link;
use Nz\CrawlerBundle\Form\Type\UrlsType;

/**
 * Class ProfileController.
 *
 * @author  nz
 */
class ProfileController extends Controller
{

    public function crawlIndexAction($id, Request $request = null)
    {
        $profile = $this->admin->getSubject();
        $persist = $request->get('persist', false);
        $manager = $this->admin->getProfileManager();

        $links = $manager->handleProfileIndex($profile->getId(), $persist);
        $errors = $manager->getLastHandlerErrors();

        $this->addFlash('sonata_flash_success', sprintf('<b>Success:</b> %d <br>', max(count($links) - count($errors), 0)));
        $this->addFlash('sonata_flash_info', sprintf('<b>Links:</b> %d <br>%s', count($links), implode('<br>', $links)));
        $this->addFlash('sonata_flash_error', sprintf('<b>Errors:</b> %d <br>%s', count($errors), implode('<br>->', $errors)));

        return new RedirectResponse($this->admin->generateUrl('edit', array('id' => $id)));
    }

    public function crawlLinksAction($id, Request $request = null)
    {
        $manager = $this->admin->getProfileManager();
        $profile = $this->admin->getSubject();
        $persist = $request->get('persist', false);

        $entities = $manager->handleProfileLinks($profile, $persist);
        $errors = $manager->getLastHandlerErrors();

        $this->addFlash('sonata_flash_success', sprintf('<b>Success:</b> %d <br>', max(count($entities) - count($errors), 0)));
        $this->addFlash('sonata_flash_info', sprintf('<b>Entities:</b> %d <br>%s', count($entities), implode('<br>', $entities)));
        $this->addFlash('sonata_flash_error', sprintf('<b>Errors:</b> %d <br>%s', count($errors), implode('<br>-> ', $errors)));

        return new RedirectResponse($this->admin->generateUrl('nz.crawler.admin.link.list', array('id' => $id)));
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

    public function crawlAllIndexesAction(Request $request = null)
    {
        die('@todo: crawl all profiles indexes');
    }

    public function crawlAllLinksAction(Request $request = null)
    {
        die('@todo: crawl all profiles entities');
    }

    public function cloneAction()
    {
        $object = $this->admin->getSubject();

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        $clonedObject = clone $object;

        $clonedObject->setName($object->getName() . ' (Clone)');

        $this->admin->create($clonedObject);

        $this->addFlash('sonata_flash_success', 'Cloned successfully');

        return new RedirectResponse($this->admin->generateUrl('edit', ['id' => $clonedObject->getId()]));
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
