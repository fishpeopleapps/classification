<?php

namespace ClassificationTool;

class PageClassification {

    private int $pageId;
    private string $pageClass;
    private ?string $pageSci = null;
    private ?string $pageFgi = null;
    private ?string $pageDis = null;
    public array $relToCountries = [];

    /** @var string Declassification date (YYYYMMDD) */
    private string $declassDate;

    public const VALID_CLASSES = ['UNCLASSIFIED', "CONFIDENTIAL", "SECRET", "TOP SECRET"];
    public const VALID_DISSEMINATION = ['NOFORN', 'REL TO', 'ORCON'];
    public const VALID_SCI = ['SI', 'TK', 'HCS'];
    public const VALID_FGI = [
        'USA', 'UK', 'CAN', 'AUS', 'NZL', 'FRA', 'GER', 'ITA', 'JPN', 'KOR',
        'NATO', 'FVEY', 'EU', 'UN', 'ASEAN'
    ];


    /**
     * Constructor to initialize the page classification class.
     */
    public function __construct(int $pageId, string $pageClass, ?string $pageSci, ?string $pageFgi, ?string $pageDis, array $relToCountries, string $declassDate) {
        $this->pageId = $pageId;
        $this->pageClass = $pageClass;
        $this->pageSci = $pageSci;
        $this->pageFgi = $pageFgi;
        $this->pageDis = $pageDis;
        $this->relToCountries = $relToCountries;
        $this->setDeclassDate();
    }

    /**
     * Get the classification as an array.
     * Easier to manage data, easier to insert/update database table
     * Can be used for the API functionality
     */
    public function toArray(): array {
        return [
            'page_id' => $this->pageId,
            'page_class' => $this->pageClass,
            'page_sci' => $this->pageSci,
            'page_fgi' => $this->pageFgi,
            'page_dis' => $this->pageDis,
            'rel_to_countries' => implode(',', $this->relToCountries),
            'declass_date' => $this->declassDate
        ];
    }
#####################################
// Check to ensure values are in the constants above, if not throw an error
// If they are valid, set the vars
####################################
    public function setPageClass(string $class): void {
        if (!in_array($class, self::VALID_CLASSES, true)) {
            throw new InvalidArgumentException("Invalid classification level: $class");
        }
        $this->pageClass = $class;
    }
    public function setPageSci(?string $sci): void {
        if ($sci !== null && !in_array($sci, self::VALID_SCI, true)) {
            throw new InvalidArgumentException("Invalid SCI marking: $sci");
        }
        $this->pageSci = $sci;
    }
    public function setPageFgi(?string $fgi): void {
        if ($fgi !== null && !in_array($fgi, self::VALID_FGI, true)) {
            throw new InvalidArgumentException("Invalid FGI marking: $fgi");
        }
        $this->pageFgi = $fgi;
    }
    public function setPageDis(?string $dis): void {
        if ($dis !== null && !in_array($dis, self::VALID_DISSEMINATION, true)) {
            throw new InvalidArgumentException("Invalid dissemination marking: $dis");
        }
        $this->pageDis = $dis;
    }
    // Automatically set declass date 25 years from now
    public function setDeclassDate(): void {
        $this->declassDate = date('Ymd', strtotime('+25 years'));
    }
    

}
