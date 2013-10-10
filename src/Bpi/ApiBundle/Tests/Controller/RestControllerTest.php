<?php
namespace Bpi\ApiBundle\Tests\Controller;

class RestControllerTest extends ControllerTestCase
{
    public function testAnswerBadRequestOnPostMalformedRequest()
    {
        // empty request
        $client = $this->doRequest('/node.bpi', '');
        $this->assertEquals(422, $client->getResponse()->getStatusCode(), 'Response must be 422 Unprocessable Entity');

        // bad xml request
        $client = $this->doRequest('/node.bpi', '<foo>');
        $this->assertEquals(422, $client->getResponse()->getStatusCode(), 'Response must be 422 Unprocessable Entity');

        // non xml request
        $client = $this->doRequest('/node.bpi', 'foo');
        $this->assertEquals(422, $client->getResponse()->getStatusCode(), 'Response must be 422 Unprocessable Entity');
    }

    public function testSkipAuthorizationForOptionsRequest()
    {
        $client = static::createClient();

        $client->request('GET', '/node/collection');
        $this->assertEquals(401, $client->getResponse()->getStatusCode(), 'Response must be 401 Not authorized');

        $client->request('OPTIONS', '/node/collection');
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), 'Response must be 200 Ok');
    }
}
