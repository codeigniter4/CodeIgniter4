<?php

if (! function_exists('jdate')) {
    function jdate($format, $timestamp = null, $none = '', $time_zone = 'Asia/Tehran', $tr_num = 'fa')
    {
        $T_sec = 0; /* <= رفع خطای زمان سرور ، با اعداد '+' و '-' بر حسب ثانیه */

        if ($timestamp === null) {
            $timestamp = time();
        }

        $date_parts = explode(' ', date('Y m d H i s', $timestamp));
        list($g_y, $g_m, $g_d, $g_h, $g_i, $g_s) = $date_parts;

        $g_days_in_month = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        $j_days_in_month = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];

        $gy = $g_y - 1600;
        $gm = $g_m - 1;
        $gd = $g_d - 1;

        $g_day_no = 365 * $gy + floor(($gy + 3) / 4) - floor(($gy + 99) / 100) + floor(($gy + 399) / 400);

        for ($i = 0; $i < $gm; ++$i) {
            $g_day_no += $g_days_in_month[$i];
        }
        if ($gm > 1 && (($gy % 4 == 0 && $gy % 100 != 0) || ($gy % 400 == 0))) {
            $g_day_no++;
        }
        $g_day_no += $gd;

        $j_day_no = $g_day_no - 79;

        $j_np = floor($j_day_no / 12053); /* 12053 = 365*33 + 32/4 */
        $j_day_no = $j_day_no % 12053;

        $jy = 979 + 33 * $j_np + 4 * floor($j_day_no / 1461); /* 1461 = 365*4 + 4/4 */

        $j_day_no %= 1461;

        if ($j_day_no >= 366) {
            $jy += floor(($j_day_no - 1) / 365);
            $j_day_no = ($j_day_no - 1) % 365;
        }

        for ($i = 0; $i < 11 && $j_day_no >= $j_days_in_month[$i]; ++$i) {
            $j_day_no -= $j_days_in_month[$i];
        }
        $jm = $i + 1;
        $jd = $j_day_no + 1;

        $out = '';
        for ($i = 0; $i < strlen($format); $i++) {
            $sub = substr($format, $i, 1);
            if ($sub == 'Y') $out .= $jy;
            elseif ($sub == 'm') $out .= ($jm < 10) ? '0' . $jm : $jm;
            elseif ($sub == 'd') $out .= ($jd < 10) ? '0' . $jd : $jd;
            elseif ($sub == 'H') $out .= $g_h;
            elseif ($sub == 'i') $out .= $g_i;
            elseif ($sub == 's') $out .= $g_s;
            else $out .= $sub;
        }

        return $out;
    }
}
