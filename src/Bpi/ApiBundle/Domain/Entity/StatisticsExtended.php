<?php

namespace Bpi\ApiBundle\Domain\Entity;

use Bpi\RestMediaTypeBundle\XmlResponse;

/**
 * Class StatisticsExtended.
 */
class StatisticsExtended extends Statistics {

    /**
     * StatisticsExtended constructor.
     *
     * @param array $stats
     *   A set of extended stats items.
     */
    public function __construct(array $stats = [])
    {
        parent::__construct($stats);
    }

    /**
     * {@inheritDoc}
     */
    public function transform(XmlResponse $document)
    {
        $a = 1;
    }
}
