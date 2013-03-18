<?php
namespace Bpi\Sdk;

use Symfony\Component\DomCrawler\Crawler;

class Query
{
    /**
     *
     * @var Symfony\Component\DomCrawler\Crawler
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
     * @param array $params
     * @throws Exception\InvalidQueryParameter
     */
    protected function validate(array $params)
    {
        foreach ($params as $user_param => $value)
        {
            if ($this->crawler->filter("param[name='{$user_param}']")->count() <= 0)
            {
                throw new Exception\InvalidQueryParameter(sprintf('The API has no such query parameter [%s] on page [%s]', $user_param, $this->crawler->attr('href')));
            }
        }
    }
    
    /**
     * Transform query to array
     * 
     * @return array
     */
    public function toArray()
    {
        $result = array();
        foreach($this->crawler as $node)
        {
            foreach ($node->attributes as $attr_name => $attr)
            {
                $result[$attr_name] = $attr->value;
            }
        }
        
        foreach ($this->crawler->filter('param') as $node)
        {
            foreach ($node->attributes as $attr_name => $attr)
            {
                $result['params'][$attr_name] = $attr->value;
            }
        }
        
        return $result;
    }

    /**
     * 
     * @param \Bpi\Sdk\Document $document
     * @param array $params
     */
    public function send(Document $document, array $params)
    {
        $this->validate($params);
        $document->request('GET', $this->crawler->attr('href'), $params);
    }
}
