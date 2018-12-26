<?php

namespace Bpi\ApiBundle\Migrations\MongoDB;

use AntiMattr\MongoDB\Migrations\AbstractMigration;
use Doctrine\MongoDB\Database;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150715152904 extends AbstractMigration
{
    /**
     * @return string
     */
    public function getDescription()
    {
        return "Add internal/external flag to agencies";
    }

    public function up(Database $db)
    {
        $agencies = $db->selectCollection('Agency');
        $agencies->createQueryBuilder()
            ->update()
            ->multiple(true)
            ->field('internal')->set(true)
            ->field('deleted')->equals(false)
            ->field('internal')->exists(false)
            ->getQuery()
            ->execute();
        $this->analyze($agencies);
    }

    public function down(Database $db)
    {
        $agencies = $db->selectCollection('Agency');
        $agencies->createQueryBuilder()
            ->update()
            ->multiple(true)
            ->field('internal')->unsetField()->exists(true)
            ->getQuery()
            ->execute();
        $this->analyze($agencies);
    }
}
