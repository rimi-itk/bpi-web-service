<?php

namespace Bpi\ApiBundle\DataFixtures\MongoDB;

use Bpi\ApiBundle\Domain\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class TagFixtures.
 */
class TagFixtures extends Fixture implements RandomFixtureReferenceInterface
{
    const TAG_ALPHA = 'tag-alpha';
    const TAG_BETA = 'tag-beta';
    const TAG_GAMMA = 'tag-gamma';
    const TAG_DELTA = 'tag-delta';
    const TAG_EPSILON = 'tag-epsilon';
    const TAG_ZETA = 'tag-zeta';
    const TAG_ETA = 'tag-eta';
    const TAG_THETA = 'tag-theta';
    const TAG_IOTA = 'tag-iota';
    const TAG_KAPPA = 'tag-kappa';
    const TAG_LAMBDA = 'tag-lambda';
    const TAG_MU = 'tag-mu';
    const TAG_NU = 'tag-nu';
    const TAG_XI = 'tag-xi';
    const TAG_OMICRON = 'tag-omicron';
    const TAG_PI = 'tag-pi';
    const TAG_RHO = 'tag-rho';
    const TAG_SIGMA = 'tag-sigma';
    const TAG_TAU = 'tag-tau';
    const TAG_UPSILON = 'tag-upsilon';
    const TAG_PHI = 'tag-phi';
    const TAG_CHI = 'tag-chi';
    const TAG_PSI = 'tag-psi';
    const TAG_OMEGA = 'tag-omega';

    /**
     * Tag fixtures references.
     *
     * @var array
     */
    private static $tagReferences;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $tags = [
            self::TAG_ALPHA => 'alpha',
            self::TAG_BETA => 'beta',
            self::TAG_GAMMA => 'gamma',
            self::TAG_DELTA => 'delta',
            self::TAG_EPSILON => 'epsilon',
            self::TAG_ZETA => 'zeta',
            self::TAG_ETA => 'eta',
            self::TAG_THETA => 'theta',
            self::TAG_IOTA => 'iota',
            self::TAG_KAPPA => 'kappa',
            self::TAG_LAMBDA => 'lambda',
            self::TAG_MU => 'mu',
            self::TAG_NU => 'nu',
            self::TAG_XI => 'xi',
            self::TAG_OMICRON => 'omicron',
            self::TAG_PI => 'pi',
            self::TAG_RHO => 'rho',
        ];

        foreach ($tags as $reference => $tag) {
            $tagFixture = new Tag();
            $tagFixture->setTag($tag);

            $manager->persist($tagFixture);

            $this->addReference($reference, $tagFixture);
        }

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function addReference($name, $object)
    {
        parent::addReference($name, $object);

        self::$tagReferences[] = $name;
    }

    /**
     * {@inheritdoc}
     */
    public static function getRandomFixtureReference()
    {
        $randomReferenceName = self::$tagReferences[mt_rand(0, count(self::$tagReferences) - 1)];

        return $randomReferenceName;
    }
}
