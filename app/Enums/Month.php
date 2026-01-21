<?php

namespace App\Enums;

enum Month: int
{
    case January = 1;
    case February = 2;
    case March = 3;
    case April = 4;
    case May = 5;
    case June = 6;
    case July = 7;
    case August = 8;
    case September = 9;
    case October = 10;
    case November = 11;
    case December = 12;

    public function label(): string
    {
        return match ($this) {
            //            self::January => 'يناير',
            //            self::February => 'فبراير',
            //            self::March => 'مارس',
            //            self::April => 'أبريل',
            //            self::May => 'مايو',
            //            self::June => 'يونيو',
            //            self::July => 'يوليو',
            //            self::August => 'أغسطس',
            //            self::September => 'سبتمبر',
            //            self::October => 'أكتوبر',
            //            self::November => 'نوفمبر',
            //            self::December => 'ديسمبر',
            self::January => 'January 1',
            self::February => 'February 2',
            self::March => 'March 3',
            self::April => 'April 4',
            self::May => 'May 5',
            self::June => 'June 6',
            self::July => 'July 7',
            self::August => 'August 8',
            self::September => 'September 9',
            self::October => 'October 10',
            self::November => 'November 11',
            self::December => 'December 12',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function options(int $startMonth = 7): array
    {
        $options = [];

        foreach (self::orderFrom($startMonth) as $month) {
            $options[$month] = self::labelFor($month);
        }

        return $options;
    }

    /**
     * @param  array<int, int|string>  $months
     * @return array<int, string>
     */
    public static function optionsFor(array $months, int $startMonth = 7): array
    {
        $options = [];

        foreach (self::orderedMonths($months, $startMonth) as $month) {
            $options[$month] = self::labelFor($month);
        }

        return $options;
    }

    /**
     * @param  array<int, int|string>  $months
     * @return array<int, int>
     */
    public static function orderedMonths(array $months, int $startMonth = 7): array
    {
        $normalized = [];

        foreach ($months as $month) {
            $month = (int) $month;

            if ($month < 1 || $month > 12) {
                continue;
            }

            $normalized[$month] = true;
        }

        if ($normalized === []) {
            return [];
        }

        $ordered = [];

        foreach (self::orderFrom($startMonth) as $month) {
            if (! isset($normalized[$month])) {
                continue;
            }

            $ordered[] = $month;
        }

        return $ordered;
    }

    /**
     * @return array<int, int>
     */
    public static function orderFrom(int $startMonth = 7): array
    {
        $startMonth = max(1, min(12, $startMonth));
        $order = [];

        for ($month = $startMonth; $month <= 12; $month++) {
            $order[] = $month;
        }

        for ($month = 1; $month < $startMonth; $month++) {
            $order[] = $month;
        }

        return $order;
    }

    public static function labelFor(int|string|null $month): string
    {
        if ($month === null || $month === '') {
            return '';
        }

        $resolved = self::tryFrom((int) $month);

        return $resolved?->label() ?? (string) $month;
    }
}
