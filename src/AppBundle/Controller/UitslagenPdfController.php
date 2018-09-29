<?php

namespace AppBundle\Controller;

class UitslagenPdfController extends AlphaPDFController
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
        $this->Image('images/header_uitslagen.png', 30, -1);
        $this->Ln(30);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Helvetica', 'B', 20);
        $this->Cell(
            277,
            10,
            "Donar Team Cup " . date('Y', time()) . ": Uitslagenlijst " . $this->categorie . " " .
            $this->niveau,
            0,
            1,
            'C'
        );
        $this->Ln(6);
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(0.2);
        $w = array(98, 70, 32, 32, 32, 16);
        $this->SetFont('Helvetica', 'B', 13);
        $this->Cell($w[0], 7, " ", 1, 0, 'C');
        $this->Cell($w[1], 7, "Sprong", 1, 0, 'C');
        $this->Cell($w[2], 7, "Brug", 1, 0, 'C');
        $this->Cell($w[3], 7, "Balk", 1, 0, 'C');
        $this->Cell($w[4], 7, "Vloer", 1, 0, 'C');
        $this->Cell($w[5], 7, "Totaal", 1, 0, 'C');
        $this->Ln();
        $w = array(8, 35, 55, 8, 8, 11, 8, 8, 11, 11, 5, 8, 8, 11, 5, 8, 8, 11, 5, 8, 8, 11, 5, 11, 5);
        $this->SetFont('Helvetica', 'B', 10);
        $header2 = array(
            'Nr.',
            'Naam',
            'Vereniging',
            'D1',
            'N1',
            'Spr1',
            'D2',
            'N2',
            'Spr2',
            'Pnt',
            'Pl',
            'D',
            'N',
            'Pnt',
            'Pl',
            'D',
            'N',
            'Pnt',
            'Pl',
            'D',
            'N',
            'Pnt',
            'Pl',
            'Pnt',
            'Pl'
        );
        for ($i = 0; $i < count($header2); $i++) {
            $this->Cell($w[$i], 7, $header2[$i], 1, 0);
        }
        $this->Ln();
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Helvetica', 'I', 7);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    function Table($turnsters, $userId)
    {
        foreach ($turnsters as $turnster) {
            $fill = false;
            if ($turnster['userId'] == $userId) {
                $fill = true;
                $this->SetFillColor(245, 245, 167);
            }
            $this->SetFont('Helvetica', '', 7);
            $this->Cell(8, 6, $turnster['wedstrijdnummer'], 1, 0, "", $fill);
            $this->Cell(35, 6, utf8_decode($turnster['naam']), 1, 0, "", $fill);
            $this->Cell(55, 6, utf8_decode($turnster['vereniging']), 1, 0, "", $fill);

            $this->Cell(8, 6, $turnster['dSprong1'], 1, 0, "", $fill);
            $this->Cell(8, 6, $turnster['nSprong1'], 1, 0, "", $fill);
            $this->Cell(11, 6, number_format($turnster['totaalSprong1'], 3, ",", "."), 1, 0, "", $fill);
            $this->Cell(8, 6, $turnster['dSprong2'], 1, 0, "", $fill);
            $this->Cell(8, 6, $turnster['nSprong2'], 1, 0, "", $fill);
            $this->Cell(11, 6, number_format($turnster['totaalSprong2'], 3, ",", "."), 1, 0, "", $fill);
            $this->Cell(11, 6, number_format($turnster['totaalSprong'], 3, ",", "."), 1, 0, "", $fill);
            if (in_array($turnster['rankSprong'], [1, 2, 3])) {
                $this->SetFont('Helvetica', 'B', 7);
                $this->SetFillColor(255, 255, 0);
                $this->Cell(5, 6, $turnster['rankSprong'], 1, 0, "", true);
                $this->SetFont('Helvetica', '', 7);
                $this->SetFillColor(245, 245, 167);
            } else {
                $this->Cell(5, 6, $turnster['rankSprong'], 1, 0, "", $fill);
            }

            $this->Cell(8, 6, $turnster['dBrug'], 1, 0, "", $fill);
            $this->Cell(8, 6, $turnster['nBrug'], 1, 0, "", $fill);
            $this->Cell(11, 6, number_format($turnster['totaalBrug'], 3, ",", "."), 1, 0, "", $fill);
            if (in_array($turnster['rankBrug'], [1, 2, 3])) {
                $this->SetFont('Helvetica', 'B', 7);
                $this->SetFillColor(255, 255, 0);
                $this->Cell(5, 6, $turnster['rankBrug'], 1, 0, "", true);
                $this->SetFont('Helvetica', '', 7);
                $this->SetFillColor(245, 245, 167);
            } else {
                $this->Cell(5, 6, $turnster['rankBrug'], 1, 0, "", $fill);
            }

            $this->Cell(8, 6, $turnster['dBalk'], 1, 0, "", $fill);
            $this->Cell(8, 6, $turnster['nBalk'], 1, 0, "", $fill);
            $this->Cell(11, 6, number_format($turnster['totaalBalk'], 3, ",", "."), 1, 0, "", $fill);
            if (in_array($turnster['rankBalk'], [1, 2, 3])) {
                $this->SetFont('Helvetica', 'B', 7);
                $this->SetFillColor(255, 255, 0);
                $this->Cell(5, 6, $turnster['rankBalk'], 1, 0, "", true);
                $this->SetFont('Helvetica', '', 7);
                $this->SetFillColor(245, 245, 167);
            } else {
                $this->Cell(5, 6, $turnster['rankBalk'], 1, 0, "", $fill);
            }

            $this->Cell(8, 6, $turnster['dVloer'], 1, 0, "", $fill);
            $this->Cell(8, 6, $turnster['nVloer'], 1, 0, "", $fill);
            $this->Cell(11, 6, number_format($turnster['totaalVloer'], 3, ",", "."), 1, 0, "", $fill);
            if (in_array($turnster['rankVloer'], [1, 2, 3])) {
                $this->SetFont('Helvetica', 'B', 7);
                $this->SetFillColor(255, 255, 0);
                $this->Cell(5, 6, $turnster['rankVloer'], 1, 0, "", true);
                $this->SetFont('Helvetica', '', 7);
                $this->SetFillColor(245, 245, 167);
            } else {
                $this->Cell(5, 6, $turnster['rankVloer'], 1, 0, "", $fill);
            }

            $this->Cell(11, 6, number_format($turnster['totaal'], 3, ",", "."), 1, 0, "", $fill);
            if (in_array($turnster['rank'], [1, 2, 3])) {
                $this->SetFont('Helvetica', 'B', 7);
                $this->SetFillColor(255, 255, 0);
                $this->Cell(5, 6, $turnster['rank'], 1, 0, "", true);
                $this->SetFont('Helvetica', '', 7);
                $this->SetFillColor(245, 245, 167);
            } else {
                $this->Cell(5, 6, $turnster['rank'], 1, 0, "", $fill);
            }
            $this->Ln();
        }
    }
}
