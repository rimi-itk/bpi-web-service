<?php
namespace Bpi\ApiBundle\Domain\Service;

use Bpi\ApiBundle\Domain\Repository\AudienceRepository;
use Bpi\ApiBundle\Domain\Repository\CategoryRepository;
use \Bpi\ApiBundle\Domain\Aggregate\ProfileDictionary;

/**
 * Domain service for profile related operations
 */
class ProfileService
{
    /**
     * Create profile dictionary
     *
     * @return \Bpi\ApiBundle\Domain\Aggregate\ProfileDictionary
     */
    public function provideDictionary()
    {
        $audience_repository = new AudienceRepository();
        $audiences = $audience_repository ->findAll();

        $category_repository = new CategoryRepository();
        $categories = $category_repository->findAll();

        return new ProfileDictionary($audiences, $categories);
    }
}
