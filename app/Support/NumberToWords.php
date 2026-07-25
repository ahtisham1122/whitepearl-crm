<?php

namespace App\Support;

/**
 * Turns a rupee amount into words for printed receipts, e.g.
 * 50000.00 -> "Rupees Fifty Thousand Only". Written by hand instead of
 * using PHP's intl extension (NumberFormatter::SPELLOUT) because intl is
 * an optional extension that isn't guaranteed to be enabled on shared
 * hosting -- this class needs nothing beyond plain PHP.
 */
class NumberToWords
{
    private const ONES = [
        '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
        'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen',
    ];

    private const TENS = [
        '', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety',
    ];

    private const SCALES = ['', 'Thousand', 'Million', 'Billion'];

    public static function convertRupees(float $amount): string
    {
        $rupees = (int) floor($amount);
        $paisa = (int) round(($amount - $rupees) * 100);

        $words = 'Rupees '.($rupees === 0 ? 'Zero' : self::convertWholeNumber($rupees));

        if ($paisa > 0) {
            $words .= ' and '.self::convertWholeNumber($paisa).' Paisa';
        }

        return $words.' Only';
    }

    private static function convertWholeNumber(int $number): string
    {
        $groupsOfThree = [];

        while ($number > 0) {
            $groupsOfThree[] = $number % 1000;
            $number = intdiv($number, 1000);
        }

        $words = [];

        foreach (array_reverse($groupsOfThree, true) as $scaleIndex => $group) {
            if ($group === 0) {
                continue;
            }

            $words[] = trim(self::convertUnderOneThousand($group).' '.self::SCALES[$scaleIndex]);
        }

        return implode(' ', $words);
    }

    private static function convertUnderOneThousand(int $number): string
    {
        $parts = [];

        if ($number >= 100) {
            $parts[] = self::ONES[intdiv($number, 100)].' Hundred';
            $number %= 100;
        }

        if ($number >= 20) {
            $parts[] = trim(self::TENS[intdiv($number, 10)].' '.self::ONES[$number % 10]);
        } elseif ($number > 0) {
            $parts[] = self::ONES[$number];
        }

        return implode(' ', $parts);
    }
}
