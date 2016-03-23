<?php

namespace Nz\CrawlerBundle\Crawler;

/**
 * Description of MediaMatcher
 *
 * @author nz
 */
class MediaMatcher
{

    protected $mediaClass;
    protected $galleryClass;
    protected $galleryHasMediaClass;
    protected $categoryManager;
    protected $temp_files = [];

    public function __construct($mediaClass, $galleryClass, $galleryHasMediaClass, $categoryManager)
    {
        $this->mediaClass = $mediaClass;
        $this->galleryClass = $galleryClass;
        $this->galleryHasMediaClass = $galleryHasMediaClass;
        $this->categoryManager = $categoryManager;
    }

    private function getNewMedia()
    {
        return new $this->mediaClass();
    }

    private function getNewGallery()
    {
        return new $this->galleryClass();
    }

    private function getNewGalleryHasMedia()
    {
        return new $this->galleryHasMediaClass();
    }

    private function getCategory($name)
    {
        return $this->categoryManager->findOneBy(array('name' => $name));
    }

    public function cleanUp()
    {

        foreach ($this->temp_files as $ref) {
            if (is_file($ref)) {
                unlink($ref);
            }
        }

        $this->temp_files = [];
    }

    public function normalizeMedias(array $medias, $context = 'crawl')
    {
        $Medias = array();
        foreach ($medias as $med) {
            $media = $this->getNewMedia();
            $media->setCategory($this->getCategory($context));
            $media->setContext($context);
            $media->setEnabled(true);
            $media->setProviderName($med['provider']);
            $temp_file = false;

            if ($med['provider'] === 'sonata.media.provider.image') {

                $ext = pathinfo($med['url'], PATHINFO_EXTENSION);
                $temp_file = rtrim(sys_get_temp_dir(), '/') . '/' . uniqid() . '.' . $ext;
                $file_content = file_get_contents($med['url']);
                file_put_contents($temp_file, $file_content);
                $media->setBinaryContent($temp_file);
            } else {

                $media->setBinaryContent($med['id']);
            }

            $this->temp_files[] = $temp_file;
            $Medias[] = $media;
        }

        return $Medias;
    }

    public function normalizeGallery(array $medias, $context = 'crawl', $format = 'admin')
    {
        $gallery = $this->getNewGallery();
        $gallery->setContext($context);
        $gallery->setEnabled(true);
        $gallery->setDefaultFormat('admin');

        $galleryHasMedias = [];
        foreach ($medias as $media) {
            $galleryHasMedia = $this->getNewGalleryHasMedia();
            $galleryHasMedia->setEnabled(true);
            $galleryHasMedia->setGallery($gallery);
            $galleryHasMedia->setMedia($media);
            $galleryHasMedias[] = $galleryHasMedia;
        }

        if (!empty($galleryHasMedias)) {
            $gallery->setGalleryHasMedias($galleryHasMedias);
            return $gallery;
        }
    }

    public function matchProviders(array $urls)
    {

        $result = [];
        foreach ($urls as $url) {

            if ($youtube = $this->matchYoutubeVideo($url)) {
                //YOUTUBE
                $result[] = [
                    'url' => $url,
                    'id' => $youtube,
                    'provider' => 'sonata.media.provider.youtube',
                ];
            } else if ($sapo = $this->matchSapoVideo($url)) {
                //SAPO
                $result[] = [
                    'url' => $url,
                    'id' => $sapo,
                    'provider' => 'sonata.media.provider.sapo',
                ];
            } else if ($dailymotion = $this->matchDailymotionVideo($url)) {
                //DAILYMOTION
                $result[] = [
                    'url' => $url,
                    'id' => $dailymotion,
                    'provider' => 'sonata.media.provider.dailymotion',
                ];
            } else if ($vimeo = $this->matchVimeoVideo($url)) {
                //VIMEO
                $result[] = [
                    'url' => $url,
                    'id' => $vimeo,
                    'provider' => 'sonata.media.provider.vimeo',
                ];
            } else if ($playwire = $this->matchPlaywireVideo($url)) {
                //PLAYWIRE
                $result[] = [
                    'url' => $url,
                    'id' => $playwire,
                    'provider' => 'sonata.media.provider.playwire',
                ];
            } else if ($image = $this->matchImageMedia($url)) {

                //IMAGE
                $result[] = [
                    'url' => $image,
                    'provider' => 'sonata.media.provider.image',
                ];
            }
        }

        return $result;
    }

    protected function matchYoutubeVideo($url)
    {
        if (preg_match('/youtube\.com\/watch\?v=([^\&\?\/]+)/', $url, $id)) {
            $values = $id[1];
        } else if (preg_match('/youtube\.com\/embed\/([^\&\?\/]+)/', $url, $id)) {
            $values = $id[1];
        } else if (preg_match('/youtube\.com\/v\/([^\&\?\/]+)/', $url, $id)) {
            $values = $id[1];
        } else if (preg_match('/youtu\.be\/([^\&\?\/]+)/', $url, $id)) {
            $values = $id[1];
        } else if (preg_match('/youtube\.com\/verify_age\?next_url=\/watch%3Fv%3D([^\&\?\/]+)/', $url, $id)) {
            $values = $id[1];
        } else {
            // not an youtube video
            return false;
        }
        return $values;
    }

    protected function matchSapoVideo($url)
    {
        if (preg_match('/videos\.sapo\.pt\/([A-Za-z0-9]+)\/mov\//', $url, $id)) {
            $values = $id[1];
        } else {
            // not an sapo video
            return false;
        }

        return $values;
    }

    protected function matchDailymotionVideo($url)
    {
        if (preg_match('/dailymotion\.com\/embed\/video\/([^\&\?\/]+)/', $url, $id)) {
            $values = $id[1];
        } else {
            // not an dailymotion video
            return false;
        }

        return $values;
    }

    protected function matchVimeoVideo($url)
    {
        if (preg_match('/https?:\/\/(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)/', $url, $id)) {
            $values = $id[3];
        } else {
            // not an vimeo video
            return false;
        }

        return $values;
    }

    protected function matchPlaywireVideo($url)
    {
        ////config.playwire.com/1000748/videos/v2/3959845/zeus.json
        if (preg_match('/(?:config\.|player\.)?playwire.com\/(\d+)\/videos\/v2\/(\d+)\//', $url, $id)) {
            return $url;
        }
        return false;
    }

    protected function matchImageMedia($url)
    {
        if (preg_match('/\.(jpe?g|png|gif|bmp)$/i', $url)) {

            return $url;
        }
        // not an image
        return false;
    }
}
