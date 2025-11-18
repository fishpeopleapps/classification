<?php

namespace ClassificationTool;

/**
 * EnvPolicy
 *
 * Returns the list of allowed classification levels for a given deployment
 * environment. Keep values UPPERCASE to match the frontend comparison.
 */
class EnvPolicy {
    /** @var array<string, array{levels: string[]}> */
    private const POLICIES = [
        // IL4
        'NIPR' => [
            'levels' => ['UNCLASSIFIED'],
        ],

        'SIPR' => [
            'levels' => ['UNCLASSIFIED', 'CONFIDENTIAL', 'SECRET'],
        ],

        'JWICS' => [
            'levels' => ['UNCLASSIFIED', 'CONFIDENTIAL', 'SECRET', 'TOP SECRET'],
        ],

        'TESTING' => [
            'levels' => ['UNCLASSIFIED', 'CONFIDENTIAL', 'SECRET', 'TOP SECRET'],
        ],
    ];

    /**
     * Get policy for the provided environment string.
     * Falls back to TESTING if the env is unrecognized.
     */
    public static function get(string $env): array {
        $key = strtoupper(trim($env));
        return self::POLICIES[$key] ?? self::POLICIES['TESTING'];
    }
}
