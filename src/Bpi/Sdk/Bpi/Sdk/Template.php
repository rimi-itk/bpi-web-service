<?php
namespace Bpi\Sdk;

use Symfony\Component\DomCrawler\Crawler;

class Template
{
    /**
     *
     * @var Symfony\Component\DomCrawler\Crawler
     */
    protected $crawler;

    /**
     *
     * @var array
     */
    protected $fields = array();

    /**
     * 
     * @param \Symfony\Component\DomCrawler\Crawler $crawler
     */
    public function __construct(Crawler $crawler)
    {
        $this->crawler = $crawler;
    }

    /**
     * Iterate over fields and prepare flat array with data.
     *
     * @return array
     */
    protected function render() {
        $data = array();
        foreach($this->fields as $field) {
            $field->assignToList($data);
        }
        return $data;
    }

    /**
     *
     * @param \Bpi\Sdk\Document $document
     */
    public function post(Document $document)
    {
        $document->request('POST', $this->crawler->attr('href'), $this->render());
    }

    /**
     * Walk for each field in template.
     *
     * @param callback $callback
     * @return \Bpi\Sdk\Template
     */
    public function eachField($callback) {
        foreach ($this->crawler->filter('field') as $node) {
            $callback($this->fields[] = new Template\Field($node));
        }

        return $this;
    }
}
