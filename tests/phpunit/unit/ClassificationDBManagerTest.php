<?php

namespace ClassificationTool\Tests;

use ClassificationTool\ClassificationDBManager;
use MediaWikiUnitTestCase;
use Wikimedia\Rdbms\IDatabase;

class ClassificationDBManagerTest extends MediaWikiUnitTestCase {

    private $dbMock;
    private $dbManager;

    protected function setUp(): void {
        parent::setUp();

        // Mock the database connection
        $this->dbMock = $this->createMock(IDatabase::class);
        
        // Inject the mock database into the ClassificationDBManager
        $this->dbManager = new ClassificationDBManager();
        $reflection = new \ReflectionClass($this->dbManager);
        $property = $reflection->getProperty('db');
        $property->setAccessible(true);
        $property->setValue($this->dbManager, $this->dbMock);

    }

    // Tests saving new values to an established row (updating)
    public function testSaveClassificationUpdatesExistingRow() {
        // Set up test variables
        $pageId = 123;
        $oldClass = 'SECRET';
        $newClass = 'TOP SECRET';
        $oldDis = 'NOFORN';
        $newDis = 'RELTO';
        $oldRelto = 'USA';
        $newRelto = ['UK'];
        $newReltoString = implode(',', $newRelto);
        $oldSci = "SI";
        $newSci = ["HCS"];
        $newSciString = implode(',', $newSci);
        $oldFgi = "USA";
        $newFgi = ["UK"];
        $newFgiString = implode(',', $newFgi);
        $oldDeclassDate = 'MR';
        $newDeclassDate = '03202050';
    
        // Mocking the selectRow method to simulate an existing row
        $this->dbMock->expects($this->once())
                     ->method('selectRow')
                     ->with(
                         'page_classification',
                         ['page_id', 'page_class', 'page_dis', 'rel_to_countries', 'page_sci', 'page_fgi', 'declass_date'],
                         ['page_id' => $pageId],
                         $this->anything()
                     )
                     ->willReturn([
                        'page_id' => $pageId, 
                        'page_class' => $oldClass, 
                        'page_dis' => $oldDis, 
                        'rel_to_countries' => $oldRelto, 
                        'page_sci' => $oldSci, 
                        'page_fgi' => $oldFgi, 
                        'declass_date' => $oldDeclassDate
                    ]);
    
        // Mocking the update method and verifying it's called correctly
        $this->dbMock->expects($this->once())
                     ->method('update')
                     ->with(
                         'page_classification',
                         [
                            'page_class' => $newClass, 
                            'page_dis' => $newDis, 
                            'rel_to_countries' => $newReltoString,
                            'page_sci' => $newSciString, 
                            'page_fgi' => $newFgiString,
                            'declass_date' => $newDeclassDate
                        ], // arguments
                         ['page_id' => $pageId], // where clause
                         $this->anything()
                     )
                     ->willReturn(true);
    
        // Call method under test
        $result = $this->dbManager->saveClassification($pageId, $newClass, $newDis, $newRelto, $newSci, $newFgi, $newDeclassDate);
    
        // Verify result
        $this->assertTrue($result);
    }
    
    
    // Tests adding a new row (add new)
    public function testInsertNewValues() {
        $relToCountriesArray = ['USA'];
        $sciArray = ['HCS'];
        $fgiArray = ['USA'];
        // Mock a missing row (no classification exists)
        $this->dbMock->method('selectRow')
            ->willReturn(null);

        // Expect an insert to happen
        $this->dbMock->expects($this->once())
            ->method('insert')
            ->with(
                'page_classification',
                [
                    'page_id' => 9999, 
                    'page_class' => 'SECRET', 
                    'page_dis' => 'NOFORN',
                    'rel_to_countries' => 'USA',
                    'page_sci' => 'HCS', 
                    'page_fgi' => "USA", 
                    'declass_date' => "MR"
                ]
            )
            ->willReturn(true);

        $result = $this->dbManager->saveClassification(9999, 'SECRET', 'NOFORN', $relToCountriesArray, $sciArray, $fgiArray, "MR");
        $this->assertTrue($result, 'Inserting new classification values should return true');
    }


    // Attempts to insert on unknown page ID, tests to ensure saveClassification can handle unknown page_ids
    public function testHandleNonExistentPageId() {
        // Assume page ID doesn't exist (but no real foreign key check here)
        $this->dbMock->method('selectRow')->willReturn(null);
        
        // Expect an insert attempt
        $this->dbMock->expects($this->once())->method('insert');

        $result = $this->dbManager->saveClassification(999999, 'ShouldNotWork', 'NOPAGEDIS', ['USA'], ['HCS'], ['UK'], 'MR');
        $this->assertTrue($result, 'Should attempt to insert classification even if page ID is unknown');
    }


    // If there's not a page classification marked then it shouldn't save
    public function testRejectEmptyClassification() {
        // Ensure no database calls are made
        $this->dbMock->expects($this->never())->method('insert');
        $this->dbMock->expects($this->never())->method('update');
    
        $result = $this->dbManager->saveClassification(123, '', 'NOFORN', ['USA'], ['HCS'], ['UK'], 'MR');
        $this->assertFalse($result, 'Empty classification should return false');
    }


    // test null values
    public function testInsertWithNullValues() {
        // Simulate no existing classification entry
        $this->dbMock->method('selectRow')->willReturn(null);
    
        // Expect an insert with page_dis set to NULL
        $this->dbMock->expects($this->once())
            ->method('insert')
            ->with(
                'page_classification',
                ['page_id' => 5555, 'page_class' => 'SECRET', 'page_dis' => null, 'rel_to_countries' => null, 'page_sci' => null, 'page_fgi' => null, 'declass_date' => null]
            )
            ->willReturn(true); // Ensure insert() returns true
    
        $result = $this->dbManager->saveClassification(5555, 'SECRET', null, null, null, null, null);
        $this->assertTrue($result, 'Saving classification with null page_dis and page_sci should return true');
    }

    // test changing values null
    public function testUpdateApplicableValuesToNull() {
        // Simulate an existing entry with values set
        $this->dbMock->method('selectRow')->willReturn([
            'page_id' => 7777,
            'page_class' => 'SECRET',
            'page_dis' => 'NOFORN',
            'rel_to_countries' => 'UK',
            'page_sci' => 'HCS',
            'page_fgi' => 'USA'
        ]);
    
        // Expect an update that sets values to NULL
        $this->dbMock->expects($this->once())
            ->method('update')
            ->with(
                'page_classification',
                ['page_class' => 'SECRET', 'page_dis' => null, 'rel_to_countries' => null, 'page_sci' => null, 'page_fgi' => null, 'declass_date' => null],
                ['page_id' => 7777]
            )
            ->willReturn(true);
    
        $result = $this->dbManager->saveClassification(7777, 'SECRET', null, null, null, null, null);
        $this->assertTrue($result, 'Updating applicable values to null should return true');
    }
    // and the opposite
    public function testUpdateApplicableValuesFromNull() {
        // Simulate an existing entry with applicable values set to NULL
        $this->dbMock->method('selectRow')->willReturn([
            'page_id' => 8888,
            'page_class' => 'SECRET',
            'page_dis' => null,
            'rel_to_countries' => null,
            'page_sci' => null,
            'page_fgi' => null,
            'declass_date' => null
        ]);
    
        // Expect an update setting applicable values to their given type/value
        $this->dbMock->expects($this->once())
            ->method('update')
            ->with(
                'page_classification',
                ['page_class' => 'SECRET', 'page_dis' => 'REL TO', 'rel_to_countries' => 'USA', 'page_sci' => 'HCS', 'page_fgi' => 'USA', 'declass_date' => 'MR'],
                ['page_id' => 8888]
            )
            ->willReturn(true);
    
        $result = $this->dbManager->saveClassification(8888, 'SECRET', 'REL TO', ['USA'], ['HCS'], ['USA'], 'MR');
        $this->assertTrue($result, 'Updating values from null should return true');
    }

    // Unclassified pages do not have a declassification date
    public function testSaveClassification_Unclassified_DoesNotFail() {
        $dbManager = new ClassificationDBManager();
        $invalidDate = "20500320"; // Should be ignored
    
        $result = $dbManager->saveClassification(125, "UNCLASSIFIED", null, [], [], [], $invalidDate);
    
        $this->assertTrue($result, "Expected saveClassification() to succeed with UNCLASSIFIED forcing declass_date = 'none'");
    }

    // forces an exception to confirm it returns false
    public function testSaveClassificationReturnsFalseOnDBException() {
        $this->dbMock->method('selectRow')->willThrowException(new \Exception('DB error'));
    
        $result = $this->dbManager->saveClassification(321, 'SECRET', 'NOFORN', ['USA'], ['HCS'], ['UK'], 'MR');
    
        $this->assertFalse($result, 'Expected false when DB throws exception');
    }

    public function testNonReltoDisClearsRelToCountries() {
        $this->dbMock->method('selectRow')->willReturn([
            'page_id' => 1234,
            'page_class' => 'SECRET',
            'page_dis' => 'RELTO',
            'rel_to_countries' => 'USA',
            'page_sci' => '',
            'page_fgi' => '',
            'declass_date' => 'MR'
        ]);
    
        $this->dbMock->expects($this->once())
            ->method('update')
            ->with(
                'page_classification',
                $this->callback(function($data) {
                    return $data['rel_to_countries'] === null;
                }),
                ['page_id' => 1234]
            );
    
        $this->dbManager->saveClassification(1234, 'SECRET', 'NOFORN', null, [], [], 'MR');
    }
    
    
    
    
    
    
}
