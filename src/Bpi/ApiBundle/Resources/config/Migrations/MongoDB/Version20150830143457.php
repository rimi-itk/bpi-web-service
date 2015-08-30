<?php

namespace Bpi\ApiBundle\Migrations\MongoDB;

use AntiMattr\MongoDB\Migrations\AbstractMigration;
use Bpi\ApiBundle\Domain\Aggregate\Assets;
use Doctrine\MongoDB\Database;
use Gaufrette\Util;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Bpi\ApiBundle\Domain\ValueObject\NodeId;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150830143457 extends AbstractMigration implements ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return "Migrate assets.";
    }

    public function up(Database $db)
    {
        $fs = $this->container->get('knp_gaufrette.filesystem_map')->get('assets');
        $nodeQb = $db
            ->selectCollection('Node')
            ->createQueryBuilder()
        ;

        $request = $nodeQb
            ->field('resource.assets')->exists(true)
            ->not($nodeQb->expr()->size(0))
        ;
        $result = $request->getQuery()->execute();

        foreach ($result as $key => $node) {
            if (empty($node['resource']['assets'])) {
                continue;
            }

//            $nodeRepository = $this
//                ->container
//                ->get('doctrine.odm.mongodb.document_manager')
//                ->getRepository('BpiApiBundle:Aggregate\Node')
//            ;
//            var_dump($node['_id']);exit;
//
//            $nodeId  = new NodeId((string)$node['_id']);
//            var_dump($nodeId);exit;
//            $assetsObj = new Assets();

            foreach ($node['resource']['assets'] as $key => $asset) {
                $file = $fs->get($asset['file']);
                $rootDir = Util\Path::normalize(__DIR__.'/../../../../../web/uploads/assets');
                $newFs = new \Gaufrette\Filesystem(new \Gaufrette\Adapter\Local($rootDir, true, 777));
                $newFile = $fs->createFile("{$asset['file']}.{$asset['extension']}", $newFs);
                $newFile->setContent($file->getContent());
                $assetsObj->addElem($newFile);
                var_dump($file);exit;
            }

            var_dump($node);exit;
            $file = $fs->get($asset['filename']);
            var_dump($file->getContent());exit;
        }
    }

    public function down(Database $db)
    {

    }
}
