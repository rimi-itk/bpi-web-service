<?php

namespace Bpi\ApiBundle\DataFixtures\MongoDB;

/**
 * Interface RandomFixtureReferenceInterface.
 */
interface RandomFixtureReferenceInterface
{
    /**
     * Creates an entity reference.
     *
     * @param string $name Reference name.
     * @param \stdClass $object Referenced object.
     *
     * @return mixed
     */
    public function addReference($name, $object);

    /**
     * Gets a random object reference name.
     *
     * @return mixed
     */
    public static function getRandomFixtureReference();
}
