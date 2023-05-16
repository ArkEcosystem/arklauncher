<?php

declare(strict_types=1);

namespace Support;

final class AddressPrefixes
{
    private static array $prefixes = [
        '1' => 0,
        '2' => 3,
        '3' => 5,
        '4' => 8,
        '5' => 10,
        '6' => 13,
        '7' => 15,
        '8' => 18,
        '9' => 20,
        'A' => 23,
        'B' => 25,
        'C' => 28,
        'D' => 30,
        'E' => 33,
        'F' => 35,
        'G' => 38,
        'H' => 40,
        'J' => 43,
        'K' => 45,
        'L' => 48,
        'M' => 50,
        'N' => 53,
        'P' => 55,
        'Q' => 58,
        'R' => 60,
        'S' => 63,
        'T' => 65,
        'U' => 68,
        'V' => 70,
        'W' => 73,
        'X' => 75,
        'Y' => 78,
        'Z' => 80,
        'a' => 83,
        'b' => 85,
        'c' => 87,
        'd' => 90,
        'e' => 92,
        'f' => 95,
        'g' => 97,
        'h' => 100,
        'i' => 102,
        'j' => 105,
        'k' => 107,
        'm' => 110,
        'n' => 112,
        'o' => 115,
        'p' => 117,
        'q' => 120,
        'r' => 122,
        's' => 125,
        't' => 127,
        'u' => 130,
        'v' => 132,
        'w' => 135,
        'x' => 137,
        'y' => 140,
        'z' => 142,
    ];

    public static function get(string $prefix): int
    {
        return static::$prefixes[$prefix];
    }

    public static function valid(string $prefix): bool
    {
        return array_key_exists($prefix, static::$prefixes);
    }
}
