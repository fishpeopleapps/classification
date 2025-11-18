<?php

namespace ClassificationTool\Tests;

use ClassificationTool\PageClassification;
use MediaWikiUnitTestCase;
use InvalidArgumentException;

class PageClassificationTest extends MediaWikiUnitTestCase {

    public function testValidConstructionToArray() {
        $declass_date = date('Ymd', mktime(0, 0, 0, date('m'), date('d'), date('Y') + 25));

        $page = new PageClassification(
            1123,
            'CONFIDENTIAL',
            'NOFORN',
            null,
            null,
            null,
            $declass_date
        );

        $expected = [
            'page_id' => 1123,
            'page_class' => 'CONFIDENTIAL',
            'page_dis' => 'NOFORN',
            'rel_to_countries' => null,
            'page_sci' => null,
            'page_fgi' => null,
            'declass_date' => $declass_date
        ];

        $this->assertSame($expected, $page->toArray());
    }

    public function testInvalidClassificationThrowsException() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid page classification: ULTRA SECRET');
    
        new PageClassification(
            456,
            'ULTRA SECRET',   
            null,
            null,
            null,
            null,
            '20500327'
        );
    }

    public function testInvalidDisseminationThrowsException() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid dissemination control: SHAREWIDE');
    
        new PageClassification(
            789,
            'SECRET',
            'SHAREWIDE', 
            null,
            null,
            null,
            '20500327'
        );
    }
    
    public function testReltoRequiresRelToCountries() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('rel_to_countries is required when page_dis is RELTO');
    
        new PageClassification(
            321,
            'SECRET',
            'RELTO',
            null, 
            null,
            null,
            '20500327'
        );
    }
    public function testUnclassifiedForcesDeclassNone() {
        $page = new PageClassification(
            111,
            'UNCLASSIFIED',
            null,
            null,
            null,
            null,
            '20991231'   
        );
    
        $this->assertSame('none', $page->toArray()['declass_date']);
    }
    
}