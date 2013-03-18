<?php
namespace Bpi\Sdk;

use Symfony\Component\DomCrawler\Crawler;

class Link
{
    /**
     *
     * @var \Symfony\Component\DomCrawler\Crawler
     */
    protected $crawler;
    
    /**
     * 
     * @param \Symfony\Component\DomCrawler\Crawler $crawler
     */
    public function __construct(Crawler $crawler)
    {
        $this->crawler = $crawler;
    }
    
    /**
     * 
     * @param \Bpi\Sdk\Document $document
     */
    public function follow(Document $document)
    {
        $document->request('GET', $this->crawler->attr('href'));
    }

    /**
     * 
     * @return array properties
     */
    public function toArray()
    {
        $properties = array();
        foreach($this->crawler as $node)
        {
            foreach ($node->attributes as $attr_name => $attr)
            {
                $properties[$attr_name] = $attr->value;
            }
        }
        return $properties;
    }
}
