<?php
namespace Bpi\ApiBundle\Domain\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Bpi\ApiBundle\Domain\Repository\ICategory;

class CategoryRepository extends DocumentRepository implements ICategory
{
	
}