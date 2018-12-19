<?php

namespace Bpi\ApiBundle\DataFixtures\MongoDB;

use Bpi\ApiBundle\Domain\Entity\Audience;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AudienceFixtures extends Fixture implements RandomFixtureReferenceInterface
{
    const AUDIENCE_STUDERENDE = 'audience-Studerende';
    const AUDIENCE_VOKSNE = 'audience-Voksne';

    /**
     * Audience fixtures references.
     *
     * @var array
     */
    private static $audienceReferences;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $audiences = [
            self::AUDIENCE_STUDERENDE =>'Studerende',
            self::AUDIENCE_VOKSNE => 'Voksne',
        ];

        foreach ($audiences as $reference => $audience) {
            $audienceFixture = new Audience();
            $audienceFixture->setAudience($audience);

            $manager->persist($audienceFixture);

            $this->addReference($reference, $audienceFixture);
        }

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function addReference($name, $object)
    {
        parent::addReference($name, $object);

        self::$audienceReferences[] = $name;
    }

    /**
     * {@inheritdoc}
     */
    public static function getRandomFixtureReference()
    {
        $randomReferenceName = self::$audienceReferences[mt_rand(0, count(self::$audienceReferences) - 1)];

        return $randomReferenceName;
    }
}
