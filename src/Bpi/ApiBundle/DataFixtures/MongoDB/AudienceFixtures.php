<?php

namespace Bpi\ApiBundle\DataFixtures\MongoDB;

use Bpi\ApiBundle\Domain\Entity\Audience;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AudienceFixtures extends Fixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $audiences = [
            'Studerende',
            'Voksne',
        ];

        foreach ($audiences as $audience) {
            $audienceFixture = new Audience();
            $audienceFixture->setAudience($audience);

            $manager->persist($audienceFixture);

            $this->addReference('audience-'.$audience, $audienceFixture);
        }

        $manager->flush();
    }
}
