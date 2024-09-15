<?php

namespace Agencia\Close\Adapters\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DataDiff extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('dateDiff', [$this, 'TimeToBr']),
        ];
    }

    public function TimeToBr($timeBD): string
    {
        $timeBD = strtotime($timeBD);
        $timeNow = time();
        $timeRes = $timeNow - $timeBD;
        $nar = 0;

        // variável de retorno
        $r = "";

        // Agora
        if ($timeRes == 0) {
            $r = "agora";
        } else if ($timeRes > 0 and $timeRes < 60) {
            $r = $timeRes . " segundos";
        } else if (($timeRes > 59) and ($timeRes < 3599)) {
            $timeRes = $timeRes / 60;
            if (round($timeRes, $nar) >= 1 and round($timeRes, $nar) < 2) {
                $r = round($timeRes, $nar) . " minuto";
            } else {
                $r = round($timeRes, $nar) . " minutos";
            }
        } else if ($timeRes > 3559 and $timeRes < 85399) {
            $timeRes = $timeRes / 3600;

            if (round($timeRes, $nar) >= 1 and round($timeRes, $nar) < 2) {
                $r = round($timeRes, $nar) . " hora";
            } else {
                $r = round($timeRes, $nar) . " horas";
            }
        } else if ($timeRes > 86400 and $timeRes < 2591999) {

            $timeRes = $timeRes / 86400;
            if (round($timeRes, $nar) >= 1 and round($timeRes, $nar) < 2) {
                $r = round($timeRes, $nar) . " dia";
            } else {

                preg_match('/(\d*)\.(\d)/', $timeRes, $matches);

                if ($matches[2] >= 5) {
                    $ext = round($timeRes, $nar) - 1;

                    // Imprime o dia
                    $r = $ext;

                    // Formata o dia, singular ou plural
                    if ($ext >= 1 and $ext < 2) {
                        $r .= " dia";
                    } else {
                        $r .= " dias";
                    }

                    // Imprime o final da data
                    $r .= "";


                } else {
                    $r = round($timeRes, 0) . " dias";
                }

            }

        } else if ($timeRes > 2592000 and $timeRes < 31103999) {

            $timeRes = $timeRes / 2592000;
            if (round($timeRes, $nar) >= 1 and round($timeRes, $nar) < 2) {
                $r = round($timeRes, $nar) . " mês";
            } else {

                preg_match('/(\d*)\.(\d)/', $timeRes, $matches);

                if ($matches[2] >= 5) {
                    $ext = round($timeRes, $nar) - 1;

                    // Imprime o mes
                    $r .= $ext;

                    // Formata o mes, singular ou plural
                    if ($ext >= 1 and $ext < 2) {
                        $r .= " mês";
                    } else {
                        $r .= " meses";
                    }

                    // Imprime o final da data
                    $r .= "";
                } else {
                    $r = round($timeRes, 0) . " meses";
                }

            }
        } else if ($timeRes > 31104000 and $timeRes < 155519999) {

            $timeRes /= 31104000;
            if (round($timeRes, $nar) >= 1 and round($timeRes, $nar) < 2) {
                $r = round($timeRes, $nar) . " ano";
            } else {
                $r = round($timeRes, $nar) . " anos";
            }
        } else if ($timeRes > 155520000) {

            $localTimeRes = localtime($timeRes);
            $localTimeNow = localtime(time());

            $timeRes /= 31104000;
            $gmt = array();
            $gmt['mes'] = $localTimeRes[4];
            $gmt['ano'] = round($localTimeNow[5] + 1900 - $timeRes, 0);

            $mon = array("Jan ", "Fev ", "Mar ", "Abr ", "Mai ", "Jun ", "Jul ", "Ago ", "Set ", "Out ", "Nov ", "Dez ");

            $r = $mon[$gmt['mes']] . $gmt['ano'];
        }

        return $r;
    }
}