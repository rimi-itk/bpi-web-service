<?php
namespace Bpi\ApiBundle\Domain\Service;

// @todo delete
use Bpi\ApiBundle\Domain\Repository\AudienceRepository;
use Bpi\ApiBundle\Domain\Repository\CategoryRepository;
//

use Doctrine\Common\Persistence\ObjectManager;
use Bpi\ApiBundle\Domain\Aggregate\ProfileDictionary;

/**
 * Domain service for profile related operations
 */
class ProfileService
{
    /**
     *
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $manager;

    /**
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Create profile dictionary
     *
     * @return \Bpi\ApiBundle\Domain\Aggregate\ProfileDictionary
     */
    public function provideDictionary()
    {
        $dictionary = $this->manager
            ->getRepository('BpiApiBundle:Aggregate\ProfileDictionary')
            ->findAll();

        // This is kind of singleton row.
        foreach($dictionary as $e) {
            return $e;
        }
    }
}
