<?php

namespace ClassificationTool;

use Wikimedia\Rdbms\IDatabase;
use MediaWiki\MediaWikiServices;

/**
 * Saves and fetches page classification rows from the DB.
 * 
 * Handles inserts/updates, normalizes inputs (arrays → CSV, empties → null),
 * and logs basic debug info. Use saveClassification() to write and
 * getClassification() to read a single page’s row.
 */

class ClassificationDBManager {
    private IDatabase $db;
    /** Create a write connection for save operations. */
    public function __construct() {
        $this->db = MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection(DB_PRIMARY);
    }
    /**
     * Upsert a classification row for the given page.
     * Returns true on success; false on invalid input or DB error.
     */
    public function saveClassification(int $page_id, string $page_class, ?string $page_dis, ?array $rel_to_countries, ?array $page_sci, ?array $page_fgi, ?string $declass_date, ?bool $is_cui): bool {

        if (trim($page_class) === '') {
            return false;
        }
        // Normalize/clean inputs
        $is_cui = $is_cui ? 1 : 0;
        $page_dis = !empty($page_dis) ? $page_dis : null;
        $declass_date = !empty($declass_date) ? $declass_date : null;

        // Convert RELTO, SCI & FGI array to a string for storage
        $rel_to_countries = empty($rel_to_countries) ? null : implode(',', $rel_to_countries);
        $page_sci = empty($page_sci) ? null : implode(',', $page_sci);
        $page_fgi = empty($page_fgi) ? null : implode(',', $page_fgi);

        try {
            // Check if the row already exists
            $existingRow = $this->db->selectRow(
                'page_classification',
                ['page_id', 'page_class', 'page_dis', 'rel_to_countries', 'page_sci', 'page_fgi', 'declass_date', 'is_cui'],
                ['page_id' => $page_id],
                __METHOD__
            );

            if ($existingRow) {
                wfDebugLog( 'classification', "Updating classification for page_id $page_id" );
                // Update existing row
                $updateResult = $this->db->update(
                    'page_classification',
                    [
                        'page_class' => $page_class, 
                        'page_dis' => $page_dis, 
                        'rel_to_countries' => $rel_to_countries, 
                        'page_sci' => $page_sci, 
                        'page_fgi' => $page_fgi, 
                        'declass_date' => $declass_date,
                        'is_cui' => $is_cui
                    ], 
                    ['page_id' => $page_id], // Where clause
                    __METHOD__
                );

                return (bool)$updateResult;

            } else {
                // Insert new row
                wfDebugLog( 'classification', "Inserting new classification for page_id $page_id" );
                $this->db->insert(
                    'page_classification', 
                    [
                        'page_id' => $page_id, 
                        'page_class' => $page_class, 
                        'page_dis' => $page_dis, 
                        'rel_to_countries' => $rel_to_countries, 
                        'page_sci' => $page_sci, 
                        'page_fgi' => $page_fgi, 
                        'declass_date' => $declass_date,
                        'is_cui' => $is_cui
                        ]
                );
                return true;
            }
        } catch (\Exception $e) {
            wfDebugLog( 'classification', 'DB error during save: ' . $e->getMessage() );
            return false;
        }        
    }
    /** Fetch a single classification row by page_id (read-only connection). */
    public static function getClassification(int $page_id) {
        $dbr = MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection(DB_REPLICA);
        return $dbr->selectRow(
            'page_classification', // table name
            // fields/columns to fetch from the table - should match verbatim what's in the table
            [
                'page_id',
                'page_class',
                'page_dis', 
                'rel_to_countries', 
                'page_sci', 
                'page_fgi', 
                'declass_date',
                'is_cui'
            ],
            // conditions
            ['page_id' => $page_id],
            __METHOD__ // used for logging/debugging
        );
    }
}