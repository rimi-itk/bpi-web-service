<?php

namespace Bpi\ApiBundle\Migrations\MongoDB;

use AntiMattr\MongoDB\Migrations\AbstractMigration;
use Bpi\ApiBundle\Domain\Entity\Audience;
use Bpi\ApiBundle\Domain\Entity\Category;
use Doctrine\MongoDB\Database;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20181227165036 extends AbstractMigration implements ContainerAwareInterface
{

    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return "Creates 'disabled' flag for audience/category entities.";
    }

    /**
     * {@inheritdoc}
     */
    public function up(Database $db)
    {
        /** @var \Doctrine\Common\Persistence\ObjectManager $dm */
        $dm = $this->container->get('doctrine_mongodb')->getManager();

        /** @var \Bpi\ApiBundle\Domain\Entity\Audience[] $audiences */
        $audiences = $dm->getRepository(Audience::class)->findAll();
        foreach ($audiences as $audience) {
            $audience->setDisabled(false);
        }

        /** @var \Bpi\ApiBundle\Domain\Entity\Category[] $categories */
        $categories = $dm->getRepository(Category::class)->findAll();
        foreach ($categories as $category) {
            $category->setDisabled(false);
        }

        $dm->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function down(Database $db)
    {

    }
}
