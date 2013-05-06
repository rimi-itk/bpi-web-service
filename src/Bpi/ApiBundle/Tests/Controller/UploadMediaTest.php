<?php
namespace Bpi\ApiBundle\Tests\Controller;

class UploadMediaTest extends ControllerTestCase
{
    public function testSendLargeRequest()
    {
        try{
            $client = $this->doRequest('/node.bpi', $this->loadFixture('Assets/EmbededOverflow'));
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(413, $e->getStatusCode());
        }
    }

    /* @todo implement or remove
    public function testPostAssetRequest()
    {
        // find first node
        $client = $this->doRequest('/node/collection.bpi', $this->loadFixture('NodesQuery/FindOne'));
        $xml = simplexml_load_string($client->getResponse()->getContent());

        $links = $xml->xpath('//entity[@name="node"]/links/link[@rel="assets"]'); // assets relations
        $this->assertNotEmpty($links[0]['href']);

        // push revision
        $image = $this->loadFixture('Assets/img', 'gif');
        $image_name = mt_rand().'.gif';
        $client = $this->doRequest($links[0]['href'].'/'.$image_name, $image, 'PUT');
        $this->assertEquals(204, $client->getResponse()->getStatusCode());
        $this->assertEmpty($client->getResponse()->getContent());

        // @todo try to get resource back from server
    }
    */

    /**
     * @todo test replacement of file / uniqueness
     */
}
