<?php
// NumberToWords.php

namespace Finance\Finance\Classes;

class NumberToWords
{
    public static function convert($number)
    {
        if (!is_numeric($number)) {
            return 'رقم غير صحيح';
        }

        if ($number < 0) {
            return 'سالب ' . self::convert(abs($number));
        }

        if ($number == 0) {
            return 'صفر';
        }

        return self::convertNumber($number);
    }

    private static function convertNumber($number)
    {
        $words = [
            0 => 'صفر', 1 => 'واحد', 2 => 'اثنان', 3 => 'ثلاثة',
            4 => 'أربعة', 5 => 'خمسة', 6 => 'ستة', 7 => 'سبعة',
            8 => 'ثمانية', 9 => 'تسعة', 10 => 'عشرة',
            11 => 'أحد عشر', 12 => 'اثنا عشر', 13 => 'ثلاثة عشر',
            14 => 'أربعة عشر', 15 => 'خمسة عشر', 16 => 'ستة عشر',
            17 => 'سبعة عشر', 18 => 'ثمانية عشر', 19 => 'تسعة عشر',
            20 => 'عشرون', 30 => 'ثلاثون', 40 => 'أربعون',
            50 => 'خمسون', 60 => 'ستون', 70 => 'سبعون',
            80 => 'ثمانون', 90 => 'تسعون',
            100 => 'مائة', 200 => 'مائتان', 300 => 'ثلاثمائة',
            400 => 'أربعمائة', 500 => 'خمسمائة', 600 => 'ستمائة',
            700 => 'سبعمائة', 800 => 'ثمانمائة', 900 => 'تسعمائة'
        ];

        if (isset($words[$number])) {
            return $words[$number];
        }

        if ($number < 100) {
            $tens = floor($number / 10) * 10;
            $ones = $number % 10;
            return self::convertNumber($ones) . ' و ' . self::convertNumber($tens);
        }

        if ($number < 1000) {
            $hundreds = floor($number / 100) * 100;
            $remainder = $number % 100;
            if ($remainder == 0) {
                return self::convertNumber($hundreds);
            }
            return self::convertNumber($hundreds) . ' و ' . self::convertNumber($remainder);
        }

        if ($number < 1000000) {
            $thousands = floor($number / 1000);
            $remainder = $number % 1000;
            $result = '';
            
            if ($thousands == 1) {
                $result = 'ألف';
            } elseif ($thousands == 2) {
                $result = 'ألفان';
            } elseif ($thousands >= 3 && $thousands <= 10) {
                $result = self::convertNumber($thousands) . ' آلاف';
            } else {
                $result = self::convertNumber($thousands) . ' ألف';
            }
            
            if ($remainder > 0) {
                $result .= ' و ' . self::convertNumber($remainder);
            }
            return $result;
        }

        if ($number < 1000000000) {
            $millions = floor($number / 1000000);
            $remainder = $number % 1000000;
            $result = '';
            
            if ($millions == 1) {
                $result = 'مليون';
            } elseif ($millions == 2) {
                $result = 'مليونان';
            } elseif ($millions >= 3 && $millions <= 10) {
                $result = self::convertNumber($millions) . ' ملايين';
            } else {
                $result = self::convertNumber($millions) . ' مليون';
            }
            
            if ($remainder > 0) {
                $result .= ' و ' . self::convertNumber($remainder);
            }
            return $result;
        }

        return 'رقم كبير جداً';
    }
}

// اختبار الكود
// echo NumberToWords::convert(1234567); // مليون و مائتان و أربعة و ثلاثون ألف و خمسمائة و سبعة و ستون
?>