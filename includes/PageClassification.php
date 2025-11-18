<?php

namespace ClassificationTool;

/**
 * Holds and validates all classification details for a page.
 * 
 * Includes classification level, dissemination, REL TO countries,
 * SCI/FGI markings, declass date, and CUI flag. Checks values against
 * allowed lists, applies rules (e.g., REL TO requires countries,
 * UNCLASSIFIED forces “none” declass date), and sets defaults.
 * 
 * Use toArray() to get values ready for DB/API use.
 */



class PageClassification {
    private int $page_id;
    private string $page_class;
    private ?string $page_dis;
    private ?array $rel_to_countries = [];
    private ?array $page_sci = [];
    private ?array $page_fgi = [];
    private ?string $declass_date;
    private ?bool $is_cui;

    public const VALID_CLASSES = ['UNCLASSIFIED', 'CONFIDENTIAL', 'SECRET', 'TOP SECRET'];
    public const VALID_DISSEMINATION = ['NOFORN', 'RELTO', 'ORCON', 'RSEN', 'IMC'];
    public const VALID_SCI = ['SI', 'TK', 'HCS', 'G', 'HCS-P'];
    public const VALID_FGI = [
        'USA', 'UK', 'CAN', 'AUS', 'NZL', 'FRA', 'DEU', 'ITA', 'JPN', 'KOR',
        'NATO', 'FVEY', 'EU', 'UN', 'ASEAN'
    ];
    public const VALID_RELTO = [
        'USA', 'AFG', 'ALB', 'DZA', 'ASM', 'AND', 'AGO', 'AIA', 'ATA', 'ATG', 'ARG', 'ARM', 'ABW', 'AUS', 'AUT', 'AZE',
        'BHS', 'BHR', 'BGD', 'BRB', 'BLR', 'BEL', 'BLZ', 'BEN', 'BMU', 'BTN', 'BOL', 'BIH', 'BWA', 'BVT', 'BRA', 'IOT',
        'VGB', 'BRN', 'BGR', 'BFA', 'MMR', 'BDI', 'KHM', 'CMR', 'CAN', 'CPV', 'CYM', 'CAF', 'TCD', 'CHL', 'CHN', 'CXR',
        'CCK', 'COL', 'COM', 'COD', 'COG', 'COK', 'CRI', 'CIV', 'HRV', 'CUB', 'CYP', 'CZE', 'DNK', 'DJI', 'DMA', 'DOM',
        'ECU', 'EGY', 'SLV', 'GNQ', 'ERI', 'EST', 'ETH', 'FLK', 'FRO', 'FJI', 'FIN', 'FRA', 'FXX', 'GUF', 'PYF', 'ATF',
        'GAB', 'GMB', 'PSE', 'GEO', 'DEU', 'GHA', 'GIB', 'GRC', 'GRL', 'GRD', 'GLP', 'GUM', 'GTM', 'GGY', 'GIN', 'GNB',
        'GUY', 'HTI', 'HMD', 'VAT', 'HND', 'HKG', 'HUN', 'ISL', 'IND', 'IDN', 'IRN', 'IRQ', 'IRL', 'IMN', 'ISR', 'ITA',
        'JAM', 'JPN', 'JEY', 'JOR', 'KAZ', 'KEN', 'KIR', 'PRK', 'KOR', 'KWT', 'KGZ', 'LAO', 'LVA', 'LBN', 'LSO', 'LBR',
        'LBY', 'LIE', 'LTU', 'LUX', 'MAC', 'MKD', 'MDG', 'MWI', 'MYS', 'MDV', 'MLI', 'MLT', 'MHL', 'MTQ', 'MRT', 'MUS',
        'MYT', 'MEX', 'FSM', 'MDA', 'MCO', 'MNG', 'MNE', 'MSR', 'MAR', 'MOZ', 'NAM', 'NRU', 'NPL', 'NLD', 'ANT', 'NCL',
        'NZL', 'NIC', 'NER', 'NGA', 'NIU', 'NFK', 'MNP', 'NOR', 'OMN', 'PAK', 'PLW', 'PAN', 'PNG', 'PRY', 'PER', 'PHL',
        'PCN', 'POL', 'PRT', 'PRI', 'QAT', 'REU', 'ROU', 'RUS', 'RWA', 'BLM', 'SHN', 'KNA', 'LCA', 'MAF', 'SPM', 'VCT',
        'WSM', 'SMR', 'STP', 'SAU', 'SEN', 'SRB', 'SYC', 'SLE', 'SGP', 'SVK', 'SVN', 'SLB', 'SOM', 'ZAF', 'SGS', 'ESP',
        'LKA', 'SDN', 'SUR', 'SJM', 'SWZ', 'SWE', 'CHE', 'SYR', 'TWN', 'TJK', 'TZA', 'THA', 'TLS', 'TGO', 'TKL', 'TON',
        'TTO', 'TUN', 'TUR', 'TKM', 'TCA', 'TUV', 'UGA', 'UKR', 'ARE', 'GBR', 'UMI', 'URY', 'UZB', 'VUT', 'VEN', 'VNM',
        'VIR', 'WLF', 'ESH', 'YEM', 'ZMB', 'ZWE'
    ];
    

    public function __construct(
        int $page_id, 
        string $page_class,
        ?string $page_dis,
        ?string $rel_to_countries,
        ?string $page_sci,
        ?string $page_fgi,
        ?string $declass_date,
        ?bool $is_cui
    ) {
        if (!in_array($page_class, self::VALID_CLASSES, true)) {
            throw new \InvalidArgumentException("Invalid page classification: $page_class");
        }
    
        if ($page_dis !== null && !in_array($page_dis, self::VALID_DISSEMINATION, true)) {
            throw new \InvalidArgumentException("Invalid dissemination control: $page_dis");
        }
        if (!is_array($rel_to_countries)) {
            $rel_to_countries = []; // Ensure it's an array before looping
        }

        foreach ($rel_to_countries as $relto) {
            if (!in_array($relto, self::VALID_RELTO, true)) {
                throw new \InvalidArgumentException("Invalid REL TO: $relto");
            }
        }

        if (!is_array($page_sci)) {
            $page_sci = []; // Ensure it's an array before looping
        }
        foreach ($page_sci as $sci) {
            if (!in_array($sci, self::VALID_SCI, true)) {
                throw new \InvalidArgumentException("Invalid SCI marking: $sci");
            }
        }
        if (!is_array($page_fgi)) {
            $page_fgi = []; // Ensure it's an array before looping
        }
        foreach ($page_fgi as $fgi) {
            if (!in_array($fgi, self::VALID_FGI, true)) {
                wfDebugLog( 'classification', "Validation failed: Invalid FGI marking '$fgi'" );
                throw new \InvalidArgumentException("Invalid FGI marking: $fgi");
            }
        }
        // Ensure Relto is only set if dissemination is marked to RELTO
        if ($page_dis === 'RELTO' && empty($rel_to_countries)) {
            throw new \InvalidArgumentException('rel_to_countries is required when page_dis is RELTO');
        }
        
        if ($page_dis !== 'RELTO') {
            $rel_to_countries = null; 
        }
        
        // Handle declass date logic
        if ($page_class === "UNCLASSIFIED") {
            $declass_date = "none";
        } elseif ($declass_date === "MR") {
            // Allowed: Manual Review override, no change
        } elseif (is_numeric($declass_date) && strlen($declass_date) === 8) {
            $year = substr($declass_date, 0, 4);
            $month = substr($declass_date, 4, 2);
            $day = substr($declass_date, 6, 2);
            $date_unix = mktime(0, 0, 0, $month, $day, $year);
            $future_unix = mktime(0, 0, 0, date("m"), date("d"), date("Y") + 25);

        } else {
            $declass_date = self::getDefaultDeclassDate();
        }
        
        $this->page_id = $page_id;
        $this->page_class = $page_class;
        $this->page_dis = $page_dis;
        $this->rel_to_countries = $rel_to_countries;
        $this->page_sci = $page_sci;
        $this->page_fgi = $page_fgi;
        $this->declass_date = $declass_date;
        $this->is_cui = $is_cui ?? false;
    }

    public function toArray(): array {
        return [
            'page_id' => $this->page_id,
            'page_class' => $this->page_class,
            'page_dis' => $this->page_dis,
            'rel_to_countries' => empty($this->rel_to_countries) ? null : implode(',', $this->rel_to_countries),
            'page_sci' => empty($this->page_sci) ? null : implode(',', $this->page_sci),
            'page_fgi' => empty($this->page_fgi) ? null : implode(',', $this->page_fgi),
            'declass_date' => $this->declass_date,
            'is_cui' => $this->is_cui
        ];
    }

    private static function getDefaultDeclassDate(): string {
        return date('Ymd', mktime(0, 0, 0, date('m'), date('d'), date('Y') + 25));
    }
    
}

