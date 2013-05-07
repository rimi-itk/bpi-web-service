<?php
namespace Bpi\ApiBundle\Tests\SDK\UserStory;

require_once 'PHPUnit/Extensions/Story/TestCase.php';

use Bpi\ApiBundle\Tests\SDK\SDKTestCase;

class ContentOwnershipSpec extends \PHPUnit_Extensions_Story_TestCase
{
    /**
     * @scenario
     */
    public function syndicateByOwner()
    {
        $this->given('Clean install')
            ->when('User pushes new node', $node_id = 100)
            ->and('User gets a node', $node_id)
            ->and('User syndicates a node', $node_id)
            ->then('WS should exit with error', 406)
        ;
    }

    public function runGiven(&$world, $action, $arguments)
    {
        switch($action) {
            case 'Clean install': {
                $world['ct'] = new SDKTestCase();
                $world['ct']->setUp();
                $world['client'] = $world['ct']->createBpi();
            }
            break;

            default: {
                return $this->notImplemented($action);
            }
        }
    }

    public function runWhen(&$world, $action, $arguments)
    {
        switch($action) {
            case 'User pushes new node': {
                $node = $world['client']->push($world['ct']->createDataForPush());
                $data = $node->getProperties();
                $world['nodes'][$arguments[0]] = $data['id'];
            }
            break;

            case 'User gets a node': {
                $node_id = $world['nodes'][$arguments[0]];
                $world['client']->getNode($node_id);
            }
            break;

            case 'User syndicates a node': {
                try
                {
                    $node_id = $world['nodes'][$arguments[0]];
                    $world['client']->syndicateNode($node_id);
                }
                catch (\Bpi\Sdk\Exception\HTTP\ClientError $e)
                {
                    $world['response_code'] = $e->getCode();
                }
            }
            break;

            default: {
                return $this->notImplemented($action);
            }
        }
    }

    public function runThen(&$world, $action, $arguments)
    {
        switch($action) {
            case 'WS should exit with error': {
                $this->assertEquals($arguments[0], $world['response_code']);
            }
            break;

            default: {
                return $this->notImplemented($action);
            }
        }
    }
}
