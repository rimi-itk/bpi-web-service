<?php
namespace Bpi\ApiBundle\Tests\SDK\UserStory;

require_once 'PHPUnit/Extensions/Story/TestCase.php';

use Bpi\ApiBundle\Tests\SDK\SDKTestCase;

class ContentOwnershipSpecTest extends \PHPUnit_Extensions_Story_TestCase
{
    /**
     * @scenario
     */
    public function syndicateByOwner()
    {
        $this->given('Clean install')
            ->when('User pushes a node', 'alpha')
            ->and('User gets a pushed node', 'alpha')
            ->and('User syndicates a pushed node', 'alpha')
            ->then('WS should exit with error', 406)
        ;
    }

    /**
     * @scenario
     */
    public function pushSameNodeManyTimesByOwner()
    {
        $this->given('Clean install')
            ->when('User pushes a node', 'alpha')
            ->and('User pushes a node', 'alpha')
            ->then('WS should exit with error', 422)
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
            case 'User pushes a node': {
                try
                {
                    $local_node = $world['ct']->getPredefinedLocalNode($arguments[0]);
                    $node = $world['client']->push($local_node);
                    $world['pushes'] = $node;
                    $world['status'] = $world['client']->_getCurrentDocument()->status();
                }
                catch (\Bpi\Sdk\Exception\SDKException $e)
                {
                    $world['status'] = $world['client']->_getCurrentDocument()->status();
                }
            }
            break;

            case 'User gets a pushed node': {
                $data = $world['pushes']->getProperties();
                $world['client']->getNode($data['id']);
                $world['status'] = $world['client']->_getCurrentDocument()->status();
            }
            break;

            case 'User syndicates a pushed node': {
                try
                {
                    $data = $world['pushes']->getProperties();
                    $world['client']->syndicateNode($data['id']);
                }
                catch (\Bpi\Sdk\Exception\SDKException $e)
                {
                    $world['status'] = $world['client']->_getCurrentDocument()->status();
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
                $this->assertEquals($arguments[0], $world['status']->getCode());
            }
            break;

            default: {
                return $this->notImplemented($action);
            }
        }
    }
}
