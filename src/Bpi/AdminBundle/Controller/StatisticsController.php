<?php

namespace Bpi\AdminBundle\Controller;

use Bpi\ApiBundle\Domain\Aggregate\Agency;
use Bpi\ApiBundle\Domain\Entity\History;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/statistics")
 */
class StatisticsController extends Controller
{
    /**
     * @Route(path="/", name="bpi_admin_statistics")
     * @Template("BpiAdminBundle:Statistics:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        /** @var \Bpi\ApiBundle\Domain\Aggregate\Agency[] $agencies */
        $agencies = $this
            ->get('doctrine_mongodb')
            ->getRepository(Agency::class)
            ->findAll();

        $agenciesChoice = [];
        /** @var \Bpi\ApiBundle\Domain\Aggregate\Agency $agency */
        foreach ($agencies as $agency) {
            $label = "{$agency->getName()} ({$agency->getPublicId()})";
            $agenciesChoice[$label] = $agency->getPublicId();
        }

        $formBuilder = $this->createFormBuilder()
            ->add('dateFrom', DateType::class, [
                'widget' => 'single_text',
                'html5' => false,
            ])
            ->add('dateTo', DateType::class, [
                'widget' => 'single_text',
                'html5' => false,
            ])
            ->add('agencies', ChoiceType::class, [
                'required' => true,
                'choices' => $agenciesChoice,
                'multiple' => true,
            ])
            ->add('excludeDeleted', CheckboxType::class, [
                'required' => false,
                'label_attr' => [
                    'style' => 'display: inline; margin-right: 5px;'
                ]
            ])
            ->add('excludeInternal', CheckboxType::class, [
                'required' => false,
                'label_attr' => [
                    'style' => 'display: inline; margin-right: 5px;'
                ]
            ])
            ->add('show', SubmitType::class, [
                'attr' => ['class' => 'btn'],
            ]);
        $formBuilder->setMethod('get');

        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        $statistics = [];
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \Doctrine\Common\Persistence\ObjectManager $dm */
            $dm = $this->get('doctrine_mongodb');

            $data = $form->getData();

            /** @var \Bpi\ApiBundle\Domain\Repository\AgencyRepository $agencyRepository */
            $agencyRepository = $dm->getRepository(Agency::class);
            $qb = $agencyRepository->createQueryBuilder();

            $qb->field('public_id')->in($data['agencies']);
            if ($data['excludeDeleted']) {
                $qb->addAnd($qb->expr()->field('deleted')->equals(false));
            }

            if ($data['excludeInternal']) {
                $qb->addAnd($qb->expr()->field('internal')->equals(false));
            }

            $agencies = $qb->getQuery()->execute();

            $agencyIds = [];
            foreach ($agencies as $agency) {
                $agencyIds[] = $agency->getPublicId();
            }

            $data['dateTo']->modify('+23 hours 59 minutes');

            /** @var \Bpi\ApiBundle\Domain\Repository\HistoryRepository $historyRepository */
            $historyRepository = $dm->getRepository(History::class);
            $statistics = $historyRepository
                ->getStatisticsByDateRangeForAgency(
                    $data['dateFrom'],
                    $data['dateTo'],
                    $agencyIds
                )->getStats();
        }

        return [
            'form' => $form->createView(),
            'statistics' => $statistics,
        ];
    }
}
