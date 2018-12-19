<?php

namespace Bpi\ApiBundle\DataFixtures\MongoDB;

interface RandomFixtureReferenceInterface
{
    public function addReference($name, $object);

    public static function getRandomFixtureReference();
}
