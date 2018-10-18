<?php

namespace AppBundle\Controller;

class WedstrijdIndelingPdfController extends AlphaPDFController
{
    protected $wedstrijddag;
    protected $wedstrijdronde;
    protected $baan;
    protected $datumHBC;

    /**
     * @param mixed $datumHBC
     */
    public function setDatumHBC($datumHBC)
    {
        $this->datumHBC = $datumHBC;
    }

    /**
     * @param mixed $baan
     */
    public function setBaan($baan)
    {
        $this->baan = $baan;
    }

    /**
     * @param mixed $wedstrijdronde
     */
    public function setWedstrijdronde($wedstrijdronde)
    {
        $this->wedstrijdronde = $wedstrijdronde;
    }

    /**
     * @param mixed $wedstrijddag
     */
    public function setWedstrijddag($wedstrijddag)
    {
        $this->wedstrijddag = $wedstrijddag;
    }


    function Header()
    {
        //LOGO
        $this->SetFillColor(0);
        $this->SetAlpha(0.5);
        $this->Rect(0, 0, 297, 35, 'F');
        $this->SetAlpha(1);
        $this->Image('images/header_uitslagen.png', 0, 0);

        //TITEL
        $this->SetFont('Gotham', '', 20);
        $this->SetTextColor(0);
        $this->Ln(37.5);
        $this->SetFillColor(51, 51, 51);
        $this->Cell(210, 10, '- Wedstrijdindeling ' . $this->wedstrijddag . ' -', 0, 1, 'C');
        $this->Cell(210, 10, 'Wedstrijd ' . $this->wedstrijdronde . ', Baan ' . $this->baan, 0, 1, 'C');
    }

    //FOOTER
    function Footer()
    {
        $this->SetX(3);
        $this->SetAlpha(0.6);
        $this->SetFont('Gotham', '', 12);
        $this->SetTextColor(0);

        //DONAR SITE
        $this->Text(3, 294, 'www.donargym.nl');

        //HBC SITE
        $this->Text(154, 294, 'www.donarteamcup.nl');

        //DATUM
        $this->Text(87, 294, '- ' . $this->datumHBC . ' -');
    }

    //ROUNDED RECTANGLE
    function RoundedRect($x, $y, $w, $h, $r, $style = '', $angle = '1234')
    {
        $k = $this->k;
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

    function wedstrijdIndelingContent($turnsters, $userId)
    {
        $i = 1;
        foreach ($turnsters as $toestel => $turnsterPerGroep) {
            //Toestel
            $this->SetFontSize(12);
            $this->SetFillColor(51, 51, 51);
            $this->SetTextColor(255);
            $this->Cell(10, 8, '', 0, 0, '', 'F'); //FILL
            $this->Cell(160, 8, 'Groep ' . $i, 0, 0, 'L', 'F');
            $this->Cell(30, 8, $toestel, 0, 0, 'R', 'F');
            $this->Cell(10, 8, '', 0, 1, '', 'F');
            foreach ($turnsterPerGroep as $turnster) {
                $fill = false;
                if ($turnster['userId'] == $userId) {
                    $fill = true;
                }
                //TURNSTERS
                $this->SetTextColor(0);
                $this->SetFontSize(8);
                $this->SetFillColor(245, 245, 167);
                //TURNSTER 1
                $this->Cell(10, 6, '', 0, 0, '', $fill); //FILL
                $this->Cell(15, 6, $turnster['wedstrijdnummer'], 0, 0, '', $fill); //WEDSTRIJDNUMMER
                $this->Cell(50, 6, utf8_decode($turnster['naam']), 0, 0, 'L', $fill); //NAAM
                $this->Cell(70, 6, utf8_decode($turnster['vereniging']), 0, 0, 'L', $fill); //VERENIGING
                $this->Cell(
                    30,
                    6,
                    $turnster['categorie'] . ' ' . $turnster['niveau'],
                    0,
                    0,
                    'L',
                    $fill
                ); //CATEGORIE + NIVEAU
                $this->Cell(122, 6, '', 0, 1, '', $fill); //fill
            }
            $this->Ln(5);
            $i++;
        }
    }
}
