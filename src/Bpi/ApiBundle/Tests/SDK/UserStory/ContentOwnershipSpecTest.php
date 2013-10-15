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
        $this->given('Exists clients', array('200100'))
            ->when('Client pushes a node', '200100', 'alpha', 'alpha_remote')
            ->and('Client fetches a node', '200100', 'alpha_remote', 'alpha_fetched')
            ->and('Client syndicates a node', '200100', 'alpha_fetched', 'alpha_local')
            ->then('WS should exit with error', 406)
        ;
    }

    /**
     * @scenario
     */
    public function pushSameNodeManyTimesByOwner()
    {
        $this->given('Exists clients', array('200100'))
            ->when('Client pushes a node', '200100', 'alpha', 'alpha_pushed')
            ->and('Client pushes a node', '200100', 'alpha', 'alpha_pushed_2')
            ->then('WS should exit with error', 422)
        ;
    }

    /**
     * @scenario
     */
    public function agencyIdReplacements()
    {
        $this->given('Exists clients', array('200100', '200200'))
            ->when('Client has a node',
                '200100',
                'alpha',
                array('related_materials' => array(
                    '200100:123456',
                    '200100:234567',
                    '150010:444555',
                    '123123:asdfg',
                    '000102:23Gas4',
                ))
            )
            ->and('Client pushes a node', '200100', 'alpha', 'alpha_remote')
            ->and('Client fetches a node', '200200', 'alpha_remote', 'alpha_fetched')
            ->and('Client syndicates a node', '200200', 'alpha_fetched', 'alpha_local')
            ->then('Client should have a node with these values',
                '200200',
                'alpha_local',
                array('material' => array(
                    '200200:123456',
                    '200200:234567',
                    '150010:444555',
                    '123123:asdfg',
                    '000102:23Gas4',
                ))
            )
        ;
    }

    public function runGiven(&$world, $action, $arguments)
    {
        switch($action) {
            case 'Exists clients': {
                $world['ct'] = new SDKTestCase();
                $world['ct']->setUp();

                foreach ($arguments[0] as $client) {
                    if ($client == '200100') {
                        $world['200100']['client'] = $world['ct']->createBpi();
                    } elseif ($client == '200200') {
                        $world['200200']['client'] = $world['ct']->createBpiBravo();
                    } else {
                        return $this->notImplemented($action);
                    }

                    $world[$client]['nodes']['alpha'] = $world['ct']->getPredefinedLocalNode('alpha', $client);
                }
            }
            break;
        }
    }

    public function runWhen(&$world, $action, $arguments)
    {
        if (preg_match('~^Client ~', $action)) {
            $agency_id = $arguments[0];
            $node_key = $arguments[1];
        }

        switch($action) {
            case 'Client pushes a node': {
                $pushed_node_key = $arguments[2];
                try
                {
                    $local_node = $world[$agency_id]['nodes'][$node_key];
                    $local_node = $world[$agency_id]['client']->push($local_node);
                    $data = $local_node->getProperties();
                    $world['pushes'][$pushed_node_key]['id'] = $data['id'];
                    $world['pushes'][$pushed_node_key]['status'] = $world[$agency_id]['client']->_getCurrentDocument()->status();
                }
                catch (\Bpi\Sdk\Exception\SDKException $e)
                {
                    $world['status'] = $world[$agency_id]['client']->_getCurrentDocument()->status();
                }
            }
            break;

            case 'Client fetches a node': {
                $remote_node_key = $arguments[1];
                $local_node_key = $arguments[2];
                $remote_id = $world['pushes'][$remote_node_key]['id'];

                $world[$agency_id]['fetches'][$local_node_key] = $world[$agency_id]['client']->getNode($remote_id);
                $world['status'] = $world[$agency_id]['client']->_getCurrentDocument()->status();
            }
            break;

            case 'Client syndicates a node': {
                $fetched_node_key = $arguments[1];
                $local_node_key = $arguments[2];

                try
                {
                    $data = $world[$agency_id]['fetches'][$fetched_node_key]->getProperties();
                    $world[$agency_id]['client']->syndicateNode($data['id']);
                    // Move node from fetched list to local
                    $world[$agency_id]['nodes'][$local_node_key] = $world[$agency_id]['fetches'][$fetched_node_key];
                    unset($world[$agency_id]['fetches'][$fetched_node_key]);
                }
                catch (\Bpi\Sdk\Exception\SDKException $e)
                {
                    $world['status'] = $world[$agency_id]['client']->_getCurrentDocument()->status();
                }
            }
            break;

            case 'Client has a node': {
                $extra_data = $arguments[2];

                foreach ($arguments[2] as $key => $value) {
                    $world[$agency_id]['nodes'][$node_key][$key] = $value;
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

            case 'Client should have a node with these values': {
                $agency_id = $arguments[0];
                $local_node_key = $arguments[1];
                $needle = $arguments[2];

                $data = $world[$agency_id]['nodes'][$local_node_key]->getProperties();
                var_dump($data);
                foreach ($needle as $key => $value) {
                    if (!isset($data[$key]) || $data[$key] != $value) {
                        $this->assertEquals($value, $data[$key]);
                    }
                }
            }
            break;

            default: {
                return $this->notImplemented($action);
            }
        }
    }
}
