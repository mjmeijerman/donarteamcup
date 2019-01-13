<?php

namespace AppBundle\Controller;

use AppBundle\Entity\WedstrijdRonde;

class PrijswinnaarsPdfController extends AlphaPDFController
{
    private $categorie;
    private $niveau;
    private $wedstrijdInfo;
    private $year;

    public function setNiveau($niveau)
    {
        $this->niveau = $niveau;
    }

    public function setCategorie($categorie)
    {
        $this->categorie = $categorie;
    }

    public function setWedstrijdInfo(WedstrijdRonde $wedstrijdRonde)
    {
        $this->wedstrijdInfo = $wedstrijdRonde->getDag() . ' wedstrijd ' . $wedstrijdRonde->getRonde(
            ) . ' baan ' . $wedstrijdRonde->getBaan();
        $this->year = $wedstrijdRonde->getStartTijd()->format('Y');
    }


    function Header()
    {
        $this->SetFillColor(127);
        $this->Rect(0, 0, 297, 32, 'F');
        $this->Image('images/HDCFactuurHeader.png', 30, -1);
        $this->Ln(40);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Helvetica', 'B', 20);
        $this->Cell(
            277,
            10,
            "Donar Team Cup" . ' ' . $this->year . ': ' . $this->wedstrijdInfo . ' ' . $this->categorie . " " . $this->niveau,
            0,
            1
        );
        $this->Ln(14);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Helvetica', 'I', 8);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    function Table($waardes)
    {
        $this->SetFont('Helvetica', 'B', 15);
        $w = array(33, 48, 9, 4);
        $this->SetFont('Helvetica', 'B', 6.5);
        $header2 = [
            'Naam',
            'Vereniging',
            'Score',
            'Pl',
        ];
        for ($i = 0; $i < count($header2); $i++) {
            if (($i + 1) % 5 == 0) {
                $this->Cell($w[$i], 7, $header2[$i], 0, 0);
            } else {
                $this->Cell($w[$i], 7, $header2[$i], 1, 0);
            }
        }
        $this->Ln();
        $this->SetFont('Helvetica', '', 6.5);
        $limit = count($waardes[0]);
        for ($i = 0; $i < $limit; $i++) {
            for ($k = 0; $k < 3; $k++) {
                $w = array(33, 48, 9, 4, 1);
                for ($j = 0; $j < 5; $j++) {
                    if (($j + 1) % 5 == 0) {
                        $this->Cell($w[$j], 7, '', 0, 0);
                    } elseif ($j == 2) {
                        if (isset($waardes[$k][$i][$j])) {
                            $this->Cell(
                                $w[$j],
                                7,
                                utf8_decode(
                                    number_format
                                    (
                                        $waardes[$k][$i][$j],
                                        3,
                                        ',',
                                        '.'
                                    )
                                ),
                                1,
                                0
                            );
                        } else {
                            $this->Cell($w[$j], 7, '', 0, 0);
                        }
                    } else {
                        if (isset($waardes[$k][$i][$j])) {
                            $this->Cell($w[$j], 7, utf8_decode($waardes[$k][$i][$j]), 1, 0);
                        } else {
                            $this->Cell($w[$j], 7, '', 0, 0);
                        }

                    }
                }
            }
            $this->Ln();
        }
    }
}
