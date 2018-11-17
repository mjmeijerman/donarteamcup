<?php

namespace AppBundle\Controller;

class PrijswinnaarsPdfController extends AlphaPDFController
{
    private $categorie;
    private $niveau;

    public function setNiveau($niveau)
    {
        $this->niveau = $niveau;
    }

    public function setCategorie($categorie)
    {
        $this->categorie = $categorie;
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
            "Donar Team Cup " . date('Y', time()) . ": Prijswinnaars " . $this->categorie . " " .
            $this->niveau,
            0,
            1,
            'C'
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
        $w = 94;
        $this->SetFont('Helvetica', 'B', 15);
        $this->Cell($w, 7, "Sprong", 1, 0, 'C');
        $this->Cell(1, 7, "", 0, 0, 'C');
        $this->Cell($w, 7, "Brug", 1, 0, 'C');
        $this->Cell(1, 7, "", 0, 0, 'C');
        $this->Cell($w, 7, "Balk", 1, 0, 'C');
        $this->Ln();
        $w = array(33, 48, 9, 4, 1, 33, 48, 9, 4, 1, 33, 48, 9, 4);
        $this->SetFont('Helvetica', 'B', 6.5);
        $header2 = [
            'Naam',
            'Vereniging',
            'Score',
            'Pl',
            '',
            'Naam',
            'Vereniging',
            'Score',
            'Pl',
            '',
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
        $limit = max(count($waardes[0]), count($waardes[1]), count($waardes[2]));
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
        $this->Ln();
        $this->Ln();
        $w = 94;
        $this->SetFont('Helvetica', 'B', 15);
        $this->Cell($w, 7, "Vloer", 1, 0, 'C');
        $this->Cell(1, 7, "", 0, 0, 'C');
        $this->Cell($w, 7, "Totaal", 1, 0, 'C');
        $this->Ln();
        $w = array(33, 48, 9, 4, 1, 33, 48, 9, 4);
        $this->SetFont('Helvetica', 'B', 6.5);
        $header2 = array('Naam', 'Vereniging', 'Score', 'Pl', '', 'Naam', 'Vereniging', 'Score', 'Pl');
        for ($i = 0; $i < count($header2); $i++) {
            if (($i + 1) % 5 == 0) {
                $this->Cell($w[$i], 7, $header2[$i], 0, 0);
            } else {
                $this->Cell($w[$i], 7, $header2[$i], 1, 0);
            }
        }
        $this->Ln();
        $this->SetFont('Helvetica', '', 6.5);
        $limit = max(count($waardes[3]), count($waardes[4]));
        for ($i = 0; $i < $limit; $i++) {
            for ($k = 3; $k < 5; $k++) {
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
