<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Jurylid;
use AppBundle\Entity\Scores;
use AppBundle\Entity\ScoresRepository;
use AppBundle\Entity\Turnster;
use AppBundle\Entity\TurnsterRepository;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Httpfoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class UitslagenController extends BaseController
{
    private function formatScoresForPrijswinnaars($turnsters)
    {
        $waardes    = [];
        $toestellen = ['Sprong', 'Brug', 'Balk', 'Vloer', ''];
        $count      = 0;
        foreach ($toestellen as $toestel) {
            usort(
                $turnsters,
                function ($a, $b) use ($toestel) {
                    if ($a['totaal' . $toestel] == $b['totaal' . $toestel]) {
                        return 0;
                    }
                    return ($a['totaal' . $toestel] > $b['totaal' . $toestel]) ? -1 : 1;
                }
            );
            foreach ($turnsters as $turnster) {
                if ($turnster['rank' . $toestel] < 4) {
                    $waardes[$count][] = [
                        0 => $turnster['naam'],
                        1 => $turnster['vereniging'],
                        2 => $turnster['totaal' . $toestel],
                        3 => $turnster['rank' . $toestel],
                    ];
                } else {
                    break;
                }
            }
            $count++;
        }
        return $waardes;
    }

    private function uitslagenPdf(Request $request, $turnsters, $userId)
    {
        $pdf = new UitslagenPdfController('L', 'mm', 'A4');
        $pdf->setCategorie($request->query->get('categorie'));
        $pdf->setNiveau($request->query->get('niveau'));
        $pdf->SetLeftMargin(7);
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->Table($turnsters, $userId);
        return new BinaryFileResponse(
            $pdf->Output(
                $request->query->get('categorie') . "_" . $request->query->get('niveau') . ".pdf",
                "I"
            ), 200, [
                'Content-Type' => 'application/pdf'
            ]
        );
    }

    private function prijswinnaarsPdf(Request $request, $turnsters)
    {
        $waardes = $this->formatScoresForPrijswinnaars($turnsters);
        $pdf     = new PrijswinnaarsPdfController('L', 'mm', 'A4');
        $pdf->setCategorie($request->query->get('categorie'));
        $pdf->setNiveau($request->query->get('niveau'));
        $pdf->SetLeftMargin(7);
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->Table($waardes);
        return new BinaryFileResponse(
            $pdf->Output(), 200, [
                              'Content-Type' => 'application/pdf'
                          ]
        );
    }

    /**
     * @Route("/uitslagen/", name="uitslagen", methods={"GET"})
     * @param Request $request
     *
     * @return Response
     */
    public function uitslagen(Request $request)
    {
        if ($request->query->get('categorie') && $request->query->get('niveau') && $this->checkIfNiveauToegestaan
            (
                $request->query->get('categorie'),
                $request->query->get('niveau')
            )
        ) {
            $userId = 0;
            if ($this->getUser()) {
                $userId = $this->getUser()->getId();
            }
            $order = 'totaal';
            if ($request->query->get('order')) {
                $order = $request->query->get('order');
            }
            /** @var Turnster[] $results */
            $results   = $this->getDoctrine()->getRepository("AppBundle:Turnster")
                ->getIngeschrevenTurnstersCatNiveau($request->query->get('categorie'), $request->query->get('niveau'));
            $turnsters = [];
            foreach ($results as $result) {
                $turnsters[] = $result->getUitslagenLijst();
            }
            $turnsters = $this->getRanking($turnsters, $request->query->get('order'));
            if ($request->query->get('prijswinnaars')) {
                return $this->prijswinnaarsPdf($request, $turnsters);
            } elseif ($request->query->get('pdf')) {
                return $this->uitslagenPdf($request, $turnsters, $userId);
            }
            return $this->render(
                'uitslagen/showUitslag.html.twig',
                [
                    'order'     => $order,
                    'turnsters' => $turnsters,
                    'userId'    => $userId,
                ]
            );
        }
        $niveaus = $this->getToegestaneNiveaus();
        return $this->render(
            'uitslagen/index.html.twig',
            array(
                'toegestaneNiveaus' => $niveaus,
            )
        );
    }

    /**
     * @Route("/diplomaWedstrijdnummerPdf/", name="diplomaWedstrijdnummerPdf", methods={"GET"})
     * @throws \Doctrine\DBAL\DBALException
     */
    public function diplomaWedstrijdnummerPdf()
    {
        /** @var TurnsterRepository $turnsterRepository */
        $turnsterRepository = $this->getDoctrine()->getRepository("AppBundle:Turnster");

//        /** @var Turnster[] $results */
//        $results = $turnsterRepository
//            ->findBy([
//                'wachtlijst' => 0,
//                'afgemeld' => 0,
//            ]);
        $results   = $turnsterRepository->getTurnstersOrderedByDayAndVereniging();
        $turnsters = [];
        foreach ($results as $result) {
            $turnsters[] = [
                'id'              => $result['id'],
                'categorie'       => $result['categorie'],
                'niveau'          => $result['niveau'],
                'naam'            => $result['voornaam'] . ' ' . $result['achternaam'],
                'vereniging'      => $result['vereniging_naam'] . ' ' . $result['vereniging_plaats'],
                'wedstrijdnummer' => $result['wedstrijdnummer'],
            ];
        }
//        usort($turnsters, function ($a, $b) {
//            return ($a['wedstrijdnummer'] < $b['wedstrijdnummer']) ? -1 : 1;
//        });
        $pdf = new DiplomaPdfController('L', 'mm', 'A5');
        $pdf->SetMargins(0, 0);
        $pdf->AddFont('Gotham', '', 'Gotham-Light.php');
        $pdf->AddFont('Franklin', '', 'Frabk.php');

        foreach ($turnsters as $turnster) {
            $pdf->AddPage();
            $pdf->Wedstrijdnummer($turnster);
            $pdf->AddPage();
            $pdf->SetFont('Gotham', '', 18);
            $pdf->HeaderDiploma();
            $pdf->FooterDiploma(self::DATUM_DTC);
            $pdf->ContentDiploma($turnster);
        }

        return new BinaryFileResponse(
            $pdf->Output(), 200, [
                              'Content-Type' => 'application/pdf'
                          ]
        );
    }

    /**
     * @Route("/leegDiplomaPdf/", name="leegDiplomaPdf", methods={"GET"})
     */
    public function emptyDiplomaPdf()
    {
        $pdf = new DiplomaPdfController('L', 'mm', 'A5');
        $pdf->SetMargins(0, 0);
        $pdf->AddFont('Gotham', '', 'Gotham-Light.php');
        $pdf->AddFont('Franklin', '', 'Frabk.php');

        $legeTurnster = [
            'naam'       => '',
            'vereniging' => '',
            'categorie'  => '',
            'niveau'     => '',
        ];

        $pdf->AddPage();
        $pdf->SetFont('Gotham', '', 18);
        $pdf->HeaderDiploma();
        $pdf->FooterDiploma(self::DATUM_DTC);
        $pdf->ContentDiploma($legeTurnster);

        return new BinaryFileResponse(
            $pdf->Output(), 200, [
                              'Content-Type' => 'application/pdf'
                          ]
        );
    }

    /**
     * @Route("/scores/", name="scores", methods={"GET"})
     * @param Request $request
     *
     * @return Response
     */
    public function scores(Request $request)
    {
        $activeBaan = '';
        $banen      = $this->getDoctrine()->getRepository("AppBundle:Scores")
            ->getBanen();
        $turnsters  = [];
        foreach ($banen as $baan) {
            if ($baan['baan'] == $request->query->get('baan')) {
                $activeBaan = $request->query->get('baan');
                /** @var ScoresRepository $repo */
                $repo       = $this->getDoctrine()->getRepository("AppBundle:Scores");
                $toestellen = ['Sprong', 'Brug', 'Balk', 'Vloer'];
                foreach ($toestellen as $toestel) {
                    $turnsters[$toestel] = [];
                    /** @var Scores[] $results */
                    $results = $repo->getLiveScoresPerBaanPerToestel($activeBaan, $toestel);
                    foreach ($results as $result) {
                        $turnsters[$toestel][] = $result->getScores();
                    }
                }
                break;
            }
        }
        return $this->render(
            'uitslagen/scores.html.twig',
            [
                'banen'      => $banen,
                'activeBaan' => $activeBaan,
                'turnsters'  => $turnsters,
            ]
        );
    }

    /**
     * @Route("/organisatie/Juryzaken/juryBadges/", name="juryBadges", methods={"GET"})
     */
    function juryBadges()
    {
        $juryleden = [];
        /** @var Jurylid[] $results */
        $results = $this->getDoctrine()->getRepository('AppBundle:Jurylid')
            ->findAll();
        foreach ($results as $result) {
            if ($result->getZaterdag()) {
                $juryleden[] = [
                    'naam' => $result->getVoornaam() . ' ' . $result->getAchternaam(),
                    'dag'  => 'Zaterdag',
                ];
            }
            if ($result->getZondag()) {
                $juryleden[] = [
                    'naam' => $result->getVoornaam() . ' ' . $result->getAchternaam(),
                    'dag'  => 'Zondag',
                ];
            }
        }
        $pdf = new JurybadgePdfController('L', 'mm', [85.6, 53.98]);
        $pdf->setDatumHBC(self::DATUM_DTC);
        $pdf->SetMargins(0, 0);
        $pdf->AddFont('Gotham', '', 'Gotham-Light.php');
        $pdf->AddFont('Franklin', '', 'Frabk.php');
        foreach ($juryleden as $jurylid) {
            $pdf->AddPage();
            $pdf->badgeContent($jurylid);
        }
        $pdf->Output();
    }

    /**
     * @Route("/pagina/Wedstrijdindeling/indelingPdf/{wedstrijdRondeId}", name="wedstrijdindelingPdf", methods={"GET"})
     * @param Request $request
     *
     * @return Response
     */
    function wedstrijdindelingPdf($wedstrijdRondeId)
    {
        $wedstrijdRonde = $this->getDoctrine()->getRepository('AppBundle:WedstrijdRonde')
            ->find($wedstrijdRondeId);

        $userId = 0;
        if ($this->getUser()) {
            $userId = $this->getUser()->getId();
        }


        $pdf = new WedstrijdIndelingPdfController();
        $pdf->setDatumHBC(self::DATUM_DTC);
        $pdf->setBaan($wedstrijdRonde->getBaan());
        $pdf->setWedstrijddag($wedstrijdRonde->getDag());
        $pdf->setWedstrijdronde($wedstrijdRonde->getRonde());
        $pdf->SetMargins(0, 0);
        $pdf->AddFont('Gotham', '', 'Gotham-Light.php');
        $pdf->AddFont('Franklin', '', 'Frabk.php');
        $pdf->AddPage();
        $pdf->SetFont('Gotham', '', 14);
        $pdf->SetY(60);
        $pdf->wedstrijdIndelingContent($wedstrijdRonde, $userId);
        return new BinaryFileResponse(
            $pdf->Output(
                'wedstrijdindeling DTC ' . self::DATUM_DTC . " " . $wedstrijdRonde->getDag() . " wedstrijdronde " .
                $wedstrijdRonde->getRonde() . " baan " . $wedstrijdRonde->getBaan() . ".pdf",
                "I"
            ), 200, [
                'Content-Type' => 'application/pdf'
            ]
        );
    }
}
