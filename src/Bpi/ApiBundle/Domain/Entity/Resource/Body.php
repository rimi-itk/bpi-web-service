<?php
namespace Bpi\ApiBundle\Domain\Entity\Resource;

use Bpi\ApiBundle\Domain\Entity\Asset;
use Gaufrette\File;

class Body
{
    const BASE_URL_STUB = '__embedded_asset_base_url__';

    /**
     *
     * @var \DOMDocument
     */
    protected $dom;

    protected $linked_list;

    /**
     *
     * @param string $content
     * @throws \RuntimeException
     */
    public function __construct($content)
    {
        $this->dom = new \DOMDocument();
        $this->dom->strictErrorChecking = false;

        libxml_use_internal_errors(true);
        $result = @$this->dom->loadHTML($content);
        libxml_clear_errors();

        if (false === $result) {
            /** @todo write details in log **/
            throw new \RuntimeException('Unable to import content into DOMDocument');
        }

        $this->linked_list = new \SplObjectStorage();
    }

    /**
     * Consider file as attached asset if content has no elements with corresponding id
     *
     * @param \Gaufrette\File $file
     * @return Asset
     */
    public function allocateFile(File $file)
    {
        $element = $this->dom->getElementById($file->getKey());
        $asset = is_null($element) ? new Asset($file, Asset::ATTACHED) : new Asset($file, Asset::EMBEDDED);
        $this->linked_list->attach($asset, $element);
        return $asset;
    }

    public function replaceAssetLink(Asset $asset, $link)
    {
        if (!$asset->isEmbedded())
            return;

        $element = $this->linked_list->offsetGet($asset);
        $element->setAttribute('src', $link);
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
        $content = '';
        $xpath = new \DOMXPath($this->dom);
        foreach($xpath->query('//html/body/*') as $node)
            $content .= $this->dom->saveHTML($node);

        return $content;
    }
}
