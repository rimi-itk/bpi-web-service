<?php
namespace Bpi\ApiBundle\Domain;

class EventListener
{
    public function postLoad(\Doctrine\ODM\MongoDB\Event\LifecycleEventArgs $eventArgs)
    {
    	if (!($eventArgs->getDocument() instanceof \Bpi\ApiBundle\Domain\Entity\Author)) {
    		return;
    	}

    	$author = $eventArgs->getDocument();
        $author->loadAgency($eventArgs->getDocumentManager()->getRepository('BpiApiBundle:Aggregate\Agency'));
    }
}
