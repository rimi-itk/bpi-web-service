<?php
/**
 * @file
 *  Functional test of user entity.
 */

namespace Bpi\ApiBundle\Tests\Service\User;

use Bpi\ApiBundle\Tests\Service\BpiTest;
use Guzzle\Http\Client;

use Bpi\ApiBundle\Tests\Service\Fixtures\User\LoadUsers;
use Bpi\ApiBundle\Domain\Entity\File as BpiFile;

class User extends BpiTest
{
    protected $guzzle;

    public function setUp()
    {
        parent::setUp();

        $this->guzzle = new Client($this->domain);
    }

    protected function tearDown()
    {
        $this->em->createQueryBuilder('Bpi\ApiBundle\Domain\Entity\User')
            ->remove()
            ->field('externalId')->equals('999')
            ->field('email')->equals('testUser@email.com')
            ->field('userFirstName')->equals('Test')
            ->field('userLastName')->equals('User')
            ->getQuery()
            ->execute()
        ;
        $this->em->flush();

        parent::tearDown();
    }

    private function createUser()
    {
        $testUser = array(
            'externalId' => '999',
            'email' => 'testUser@email.com',
            'userFirstName' => 'Test',
            'userLastName' => 'User'
        );

        $headers = array('Auth' => 'BPI agency="200400", token="$1$GynHO0zr$zHwvyDYeQ83iNruX.pati."');

        $request = $this->guzzle->post('app_dev.php/user/', $headers, $testUser);
        $response  = $request->send();

        return $response;
    }

    public function testUserCreateAction()
    {
        $response = $this->createUser();
        $responseStatusCode = $response->getStatusCode();

        // Check user data in response.
        $this->assertEquals('200', $responseStatusCode, "Create user failed. Status code wrong");
        if (200 === $responseStatusCode) {
            $responseBody = $response->xml();
            $this->assertInstanceOf('SimpleXMLElement', $responseBody->user, 'User data not and XML structure.');
            $this->assertEquals(1, $responseBody->user->count(), 'User not found in response xml.');
            $this->assertEquals('TestUser', $responseBody->user->internal_user_name[0]->__toString(), "Internal user name don't match in response.");
            $this->assertEquals('testUser@email.com', $responseBody->user->email[0]->__toString(), "Email don't match in response.");
            $this->assertEquals('Test', $responseBody->user->user_first_name[0]->__toString(), "User first name don't match in response.");
            $this->assertEquals('User', $responseBody->user->user_last_name[0]->__toString(), "User last name don't match in response.");
            $this->assertEquals('200400', $responseBody->user->agency_id[0]->__toString(), "User agency id don't match in response.");
        }

        // Check user saved in database.
        $user = $this->em->createQueryBuilder('Bpi\ApiBundle\Domain\Entity\User')
            ->field('externalId')->equals('999')
            ->field('email')->equals('testUser@email.com')
            ->field('userFirstName')->equals('Test')
            ->field('userLastName')->equals('User')
            ->getQuery()
            ->getSingleResult()
        ;

        $this->assertNotNull($user, "User not found in database");
    }

    public function testUserSubscribe()
    {
        $this->createUser();

        $user = $this->em->createQueryBuilder('Bpi\ApiBundle\Domain\Entity\User')
            ->field('externalId')->equals('999')
            ->field('email')->equals('testUser@email.com')
            ->field('userFirstName')->equals('Test')
            ->field('userLastName')->equals('User')
            ->getQuery()
            ->getSingleResult()
        ;

        $this->assertNotNull($user, "User not found in database");

        $testSubscription = array(
            'title' => 'Subscription',
            'filter' => '{category: {cat1, cat5}, tag: {tag20, tag}}',
            'userId' => $user->getId()
        );

        $headers = array('Auth' => 'BPI agency="200400", token="$1$GynHO0zr$zHwvyDYeQ83iNruX.pati."');

        $request = $this->guzzle->post('app_dev.php/user/subscription', $headers, $testSubscription);
        $response  = $request->send();
        $responseStatusCode = $response->getStatusCode();

        // Check user subscription response.
        $this->assertEquals('200', $responseStatusCode, "Create user failed. Status code wrong");
        if (200 === $responseStatusCode) {
            $responseBody = $response->xml();
            $this->assertLessThan(1, $responseBody->error->count(), 'Some error in response.');
            $this->assertInstanceOf('SimpleXMLElement', $responseBody->user, 'User data not and XML structure.');
            $this->assertEquals(1, $responseBody->user->subscriptions->count(), "Should be only one subscription for this user.");
            $this->assertEquals('Subscription', $responseBody->user->subscriptions->entry->title->__toString(), "Subscription title don't match.");
            $this->assertEquals('{category: {cat1, cat5}, tag: {tag20, tag}}', $responseBody->user->subscriptions->entry->filter->__toString(), "Subscription filter don't match.");
        }
    }

    public function testUserDataAction()
    {
        $user = $this->createUser();
        $user = $user->xml();

        $testSubscription = array(
            'title' => 'Subscription',
            'filter' => '{category: {cat1, cat5}, tag: {tag20, tag}}',
            'userId' => $user->user->id->__toString()
        );

        $headers = array('Auth' => 'BPI agency="200400", token="$1$GynHO0zr$zHwvyDYeQ83iNruX.pati."');

        $this->guzzle->post('app_dev.php/user/subscription', $headers, $testSubscription)->send();

        $request = $this->guzzle->get('app_dev.php/user/', $headers);
        $response  = $request->send();
        $responseStatusCode = $response->getStatusCode();

        // Check user data in response.
        $this->assertEquals('200', $responseStatusCode, "Create user failed. Status code wrong");
        if (200 === $responseStatusCode) {
            $responseBody = $response->xml();
            $this->assertLessThan(1, $responseBody->error->count(), 'Some error in response.');
            $this->assertInstanceOf('SimpleXMLElement', $responseBody->user, 'User data not and XML structure.');
            $this->assertEquals(1, $responseBody->user->count(), 'User not found in response xml.');
            $this->assertEquals('TestUser', $responseBody->user->internal_user_name[0]->__toString(), "Internal user name don't match in response.");
            $this->assertEquals('testUser@email.com', $responseBody->user->email[0]->__toString(), "Email don't match in response.");
            $this->assertEquals('Test', $responseBody->user->user_first_name[0]->__toString(), "User first name don't match in response.");
            $this->assertEquals('User', $responseBody->user->user_last_name[0]->__toString(), "User last name don't match in response.");
            $this->assertEquals('200400', $responseBody->user->agency_id[0]->__toString(), "User agency id don't match in response.");

            $this->assertLessThan(1, $responseBody->error->count(), 'Some error in response.');
            $this->assertInstanceOf('SimpleXMLElement', $responseBody->user, 'User data not and XML structure.');
            $this->assertEquals(1, $responseBody->user->subscriptions->count(), 'Should be only one subscription for this user.');
            $this->assertEquals('Subscription', $responseBody->user->subscriptions->entry->title->__toString(), "Subscription title don't match.");
            $this->assertEquals('{category: {cat1, cat5}, tag: {tag20, tag}}', $responseBody->user->subscriptions->entry->filter->__toString(), "Subscription filter don't match.");
        }
    }
}
