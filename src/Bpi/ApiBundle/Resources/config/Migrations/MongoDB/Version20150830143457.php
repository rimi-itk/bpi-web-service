<?php

namespace Bpi\ApiBundle\Migrations\MongoDB;

use AntiMattr\MongoDB\Migrations\AbstractMigration;
use Bpi\ApiBundle\Domain\Aggregate\Assets;
use Bpi\ApiBundle\Domain\Entity\File;
use Doctrine\MongoDB\Database;
use Gaufrette\Util;
use Symfony\Component\Config\Definition\Exception\Exception;
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
        $dm = $this->container->get('doctrine_mongodb')->getManager();
        $fs = $this->container->get('knp_gaufrette.filesystem_map')->get('assets');
        $uploadDir = Util\Path::normalize(__DIR__ . '/../../../../../../../web/uploads/assets');
        $nodeQb = $db
            ->selectCollection('Node')
            ->createQueryBuilder()
        ;
        $request = $nodeQb
            ->field('resource.assets')->exists(true)
            ->not($nodeQb->expr()->size(0))
        ;
        $result = $request->getQuery()->execute();

        $nodeRepository = $this
            ->container
            ->get('doctrine.odm.mongodb.document_manager')
            ->getRepository('BpiApiBundle:Aggregate\Node')
        ;
        foreach ($result as $key => $data) {
            if (empty($data['resource']['assets'])) {
                continue;
            }
            $node = $nodeRepository->findOneById($data['_id']);
            $assets = new Assets();
            foreach ($data['resource']['assets'] as $key => $asset) {
                try {
                    $fs_file = $fs->get($asset['file']);
                    $newFs = new \Gaufrette\Filesystem(new \Gaufrette\Adapter\Local($uploadDir, true, 777));
                    $new = $newFs->createFile("{$asset['file']}.{$asset['extension']}", $newFs);
                    $new->setContent($fs_file->getContent());
                    $params = array(
                        'title' => $asset['file'],
                        'extension' => $asset['extension'],
                        'type' => $asset['type'] == 'embedded' ? 'body' : $asset['type'],
                    );
                    $file = new File($params);
                    $file->setName($asset['file']);
                    $file->setFilesystem($newFs);
                    $file->setPath(NULL);
                    $assets->addElem($file);
                }
                catch (\MongoGridFSException $e) {
                }
            }
            $node->setAssets($assets);
            $dm->persist($node);
        }
        $dm->flush();
    }

    public function down(Database $db)
    {

    }
}

