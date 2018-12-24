<?php

namespace Bpi\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Bpi\AdminBundle\Entity\Statistics;

/**
 * @Route(path="/statistics")
 */
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
     * @Route(path="/", name="bpi_admin_statistics")
     * @Template("BpiAdminBundle:Statistics:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        $statisitcs = null;

        $data = new Statistics();
        $form = $this->createStatisticsForm($data);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $statisitcs = $this->getRepository()
                    ->getStatisticsByDateRangeForAgency(
                        $data->getDateFrom(),
                        $data->getDateTo(),
                        $data->getAgency()
                    )->getStats();
            }
        }

        return [
            'form' => $form->createView(),
            'statistics' => $statisitcs,
        ];
    }

    private function createStatisticsForm($data)
    {
        $formBuilder = $this->createFormBuilder($data)
            ->add('dateFrom', DateType::class, ['widget' => 'single_text'])
            ->add('dateTo', DateType::class, ['widget' => 'single_text'])
            ->add('agency', TextType::class, ['required' => false])
            ->add('show', SubmitType::class, ['attr' => ['class' => 'btn']]);

        return $formBuilder->getForm();
    }
}
