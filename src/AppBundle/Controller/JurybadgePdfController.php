<?php

namespace AppBundle\Controller;

class JurybadgePdfController extends AlphaPDFController
{
    private $datumHBC;

    /**
     * @param mixed $datumHBC
     */
    public function setDatumHBC($datumHBC)
    {
        $this->datumHBC = $datumHBC;
    }

    function Header()
    {
        //LOGO
        $this->SetFillColor(0);
        $this->SetAlpha(0.5);
        $this->Rect(0, 0, 85.6, 11, 'F');
        $this->SetAlpha(1);
        $this->Image('images/BadgeHeader.png',0,0);

        //LINKS EN DATUM
        $this->SetFont('Gotham', '', 8);
        $this->SetTextColor(0, 0, 0);
        $this->SetAlpha(0.6);
        $this->Text(30.1, 14, '- ' . $this->datumHBC . ' -');
    }

    function Footer()
    {

    }

    //ROUNDED RECTANGLE
    function RoundedRect($x, $y, $w, $h, $r, $style = '', $angle = '1234')
    {
        $k  = $this->k;
        $hp = $this->h;
        if ($style == 'F')
            $op = 'f';
        elseif ($style == 'FD' or $style == 'DF')
            $op = 'B';
        else
            $op = 'S';
        $MyArc = 4 / 3 * (sqrt(2) - 1);
        $this->_out(sprintf('%.2f %.2f m', ($x + $r) * $k, ($hp - $y) * $k));

        $xc = $x + $w - $r;
        $yc = $y + $r;
        $this->_out(sprintf('%.2f %.2f l', $xc * $k, ($hp - $y) * $k));
        if (strpos($angle, '2') === false)
            $this->_out(sprintf('%.2f %.2f l', ($x + $w) * $k, ($hp - $y) * $k));
        else
            $this->_Arc($xc + $r * $MyArc, $yc - $r, $xc + $r, $yc - $r * $MyArc, $xc + $r, $yc);

        $xc = $x + $w - $r;
        $yc = $y + $h - $r;
        $this->_out(sprintf('%.2f %.2f l', ($x + $w) * $k, ($hp - $yc) * $k));
        if (strpos($angle, '3') === false)
            $this->_out(sprintf('%.2f %.2f l', ($x + $w) * $k, ($hp - ($y + $h)) * $k));
        else
            $this->_Arc($xc + $r, $yc + $r * $MyArc, $xc + $r * $MyArc, $yc + $r, $xc, $yc + $r);

        $xc = $x + $r;
        $yc = $y + $h - $r;
        $this->_out(sprintf('%.2f %.2f l', $xc * $k, ($hp - ($y + $h)) * $k));
        if (strpos($angle, '4') === false)
            $this->_out(sprintf('%.2f %.2f l', ($x) * $k, ($hp - ($y + $h)) * $k));
        else
            $this->_Arc($xc - $r * $MyArc, $yc + $r, $xc - $r, $yc + $r * $MyArc, $xc - $r, $yc);

        $xc = $x + $r;
        $yc = $y + $r;
        $this->_out(sprintf('%.2f %.2f l', ($x) * $k, ($hp - $yc) * $k));
        if (strpos($angle, '1') === false) {
            $this->_out(sprintf('%.2f %.2f l', ($x) * $k, ($hp - $y) * $k));
            $this->_out(sprintf('%.2f %.2f l', ($x + $r) * $k, ($hp - $y) * $k));
        } else
            $this->_Arc($xc - $r, $yc - $r * $MyArc, $xc - $r * $MyArc, $yc - $r, $xc, $yc - $r);
        $this->_out($op);
    }

    function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
    {
        $h = $this->h;
        $this->_out(
            sprintf(
                '%.2f %.2f %.2f %.2f %.2f %.2f c ',
                $x1 * $this->k,
                ($h - $y1) * $this->k,
                $x2 * $this->k,
                ($h - $y2) * $this->k,
                $x3 * $this->k,
                ($h - $y3) * $this->k
            )
        );
    }

    function badgeContent($jurylid)
    {
        $this->SetFont('Gotham', '', 12);
        $this->SetTextColor(0, 0, 0);

        //DAG
        $this->SetFontSize(10);
        $this->SetAlpha(1);
        $this->Text(34.8, 50, $jurylid['dag']);

        //NAAM
        //FILL
        $this->SetFillColor(255, 255, 0);
        $this->SetAlpha(0.5);
        $this->RoundedRect(3, 24.7, 80, 8, 2, 'F');
        $this->SetAlpha(1);

        //TEKST
        $this->Ln(29);
        $this->SetFont('Gotham', '', 16);
        $this->Cell(85.6, 0, utf8_decode($jurylid['naam']), 0, 1, 'C');

        //TAAK
        $this->SetFont('Gotham', '', 12);
        $this->Text(33, 37, 'JURYLID');
        //$pdf->Text(28.25,37,'HOOFD JURY'); // HOOFD JURY

        //LUNCH EN DINER
        //FILL
        $this->SetAlpha(0.5);
        $this->SetFillColor(0);
        $this->RoundedRect(3, 45.98, 25, 5, 1, 'F'); //LUNCH
        $this->RoundedRect(57.6, 45.98, 25, 5, 1, 'F'); //DINER
        $this->SetAlpha(1);

        //TEKST
        $this->SetTextColor(255, 255, 0);
        $this->SetFontSize(10);
        //LUNCH
        $this->Text(12, 49.8, 'Lunch');
        $this->SetDrawColor(255, 255, 0);
        $this->SetFillColor(255, 255, 0);
        $this->Rect(6, 47.5, 2, 2, 'D');

        //DINER
        $this->Text(66.6, 49.8, 'Diner');
        $this->SetDrawColor(255, 255, 0);
        $this->SetFillColor(255, 255, 0);
        $this->Rect(60.6, 47.5, 2, 2, 'D');
    }
}
