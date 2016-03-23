<?php

namespace Nz\CrawlerBundle\Block;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Block\BaseBlockService;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\CoreBundle\Model\Metadata;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Nz\CrawlerBundle\Form\Type\UrlsType;

/**
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class PreviewProfileBlock extends BaseBlockService
{

    protected $formFactory;

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        return $this->renderResponse($blockContext->getTemplate(), array(
                'block' => $blockContext->getBlock(),
                'settings' => $blockContext->getSettings(),
                ), $response);
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        $formMapper->add('settings', 'sonata_type_immutable_array', array(
            'keys' => array(
                array('content', 'textarea', array()),
            ),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'admin' => null,
            'object' => null,
            'urls_form' => $this->getUrlsForm(),
            'view_type' => 'preview',
            'template' => 'NzCrawlerBundle:Block:block_preview_profile.html.twig',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockMetadata($code = null)
    {
        return new Metadata($this->getName(), (!is_null($code) ? $code : $this->getName()), false, 'SonataBlockBundle', array(
            'class' => 'fa fa-file-text-o',
        ));
    }

    public function setFormBuilder(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function getUrlsForm()
    {

        return $this->formFactory->create(UrlsType::class)->createView();

        return $this->formFactory->createBuilder()
                ->add('urls', TextareaType::class, array('label' => false))
                ->add('save', SubmitType::class, array('label' => 'Crawl Urls'))
                ->getForm()->createView();
    }
}
