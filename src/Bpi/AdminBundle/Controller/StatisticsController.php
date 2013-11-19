<?php

namespace Bpi\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Bpi\AdminBundle\Entity\Statistics;

class StatisticsController extends Controller
{
    /**
     * @return \Bpi\ApiBundle\Domain\Repository\HistoryRepository
     */
    private function getRepository()
    {
        return $this->get('doctrine.odm.mongodb.document_manager')
            ->getRepository('BpiApiBundle:Entity\History');
    }

    /**
     * @Template("BpiAdminBundle:Statistics:index.html.twig")
     */
    public function indexAction()
    {

        $request = $this->getRequest();
        $statisitcs = null;

        $data = new Statistics();
        $form = $this->createStatisticsForm($data);

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $statisitcs = $this->getRepository()
                    ->getStatisticsByDateRangeForAgency(
                        $data->getDateFrom(),
                        $data->getDateTo(),
                        $data->getAgency()
                    )->getStats();
            }
        }

        return array(
            'form' => $form->createView(),
            'statistics'=> $statisitcs,
        );
    }

    private function createStatisticsForm($data)
    {
        $formBuilder = $this->createFormBuilder($data)
            ->add('dateFrom', 'date', array('widget' => 'single_text'))
            ->add('dateTo', 'date', array('widget' => 'single_text'))
            ->add('agency', 'text', array('required' => false))
        ;

        return $formBuilder->getForm();
    }
}
