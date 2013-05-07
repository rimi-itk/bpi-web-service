<?php
namespace Bpi\ApiBundle\Domain\Entity\Resource;

use Bpi\ApiBundle\Domain\Entity\Asset;
use Gaufrette\File;

class Body
{
    const BASE_URL_STUB = '__embedded_asset_base_url__';
    const WELLFORM_INDICATOR = '__wellform__';

    /**
     *
     * @var \DOMDocument
     */
    protected $dom;

    protected $filesystem;
    protected $router;

    /**
     *
     * @param string $content
     * @throws \RuntimeException
     */
    public function __construct($content, $filesystem=null, $router=null)
    {
        $this->dom = new \DOMDocument();
        $this->dom->strictErrorChecking = false;

        libxml_use_internal_errors(true);

        // DOMDocument detects encoding from meta tag
        if (false === stristr($content, 'id="' . self::WELLFORM_INDICATOR . '"'))
        {
            $wellformed_content = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" id="' . self::WELLFORM_INDICATOR . '" /></head><body>';
            $wellformed_content .= $content;
            $wellformed_content .= '</body></html>';
        }
        else
        {
            $wellformed_content = $content;
        }

        $result = @$this->dom->loadHTML($wellformed_content);
        libxml_clear_errors();

        if (false === $result) {
            /** @todo write details in log **/
            throw new \RuntimeException('Unable to import content into DOMDocument');
        }

        $this->filesystem = $filesystem;
        $this->router = $router;
        $this->rebuildInlineAssets();
    }

    /**
     * Handy way to present object as string for persistence
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getFlattenContent();
    }

    /**
     * Convert into flat string
     *
     * @return string
     */
    public function getFlattenContent()
    {
        // Fixed length strings must work faster that regexp
        $replaces = array(
            '<html>', '</html>',
            '<head>', '</head>',
            '<body>', '</body>',
            '<meta content="text/html; charset=utf-8" http-equiv="Content-Type" id="__wellform__"></meta>',
        );
        return str_ireplace($replaces, '', $this->dom->C14N());
    }

    protected function rebuildInlineAssets()
    {
        // Rebuild images
        $images = $this->dom->getElementsByTagName('img');
        foreach ($images as $img) {
            $src = $img->getAttributeNode('src')->value;
            $ext = pathinfo($src, PATHINFO_EXTENSION);

            // Download file and save to db.
            $filename = md5($src.microtime());
            $file = $this->filesystem->createFile($filename);
            // @todo Download files in a proper way.
            $file->setContent(file_get_contents($src));

            // Build URL for new image and replace img src.
            $url = $this->router->generate('get_asset', array('filename'=>$file->getKey(), 'extension' => $ext), true);
            $img->setAttribute('src', $url);
        }
    }
}
