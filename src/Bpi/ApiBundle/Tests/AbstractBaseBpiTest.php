<?php

namespace Bpi\ApiBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class BaseBpiTest.
 */
abstract class AbstractBaseBpiTest extends WebTestCase
{
    /**
     * {@inheritdoc}
     */
    protected static function getKernelClass()
    {
        return \AppKernel::class;
    }
}
