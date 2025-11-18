<?php

namespace ClassificationTool\Tests;

use ClassificationTool\PageClassificationForm;
use MediaWikiUnitTestCase;
use InvalidArgumentException;

class PageClassificationFormTest extends MediaWikiUnitTestCase {
    public function testClassificationOptionsIncludedInForm() {
        $html = PageClassificationForm::getClassificationFormHTML();
    
        // Pick a known value from VALID_CLASSES
        $this->assertStringContainsString('<option value="TOP SECRET">TOP SECRET</option>', $html);
    }
    
    public function testDisseminationOptionsIncludedInForm() {
        $html = PageClassificationForm::getClassificationFormHTML();
    
        $this->assertStringContainsString('<option value="RELTO">RELTO</option>', $html);
        $this->assertStringContainsString('<option value="NOFORN">NOFORN</option>', $html);
    }

    public function testFgiCheckboxesIncludedInForm() {
        $html = PageClassificationForm::getClassificationFormHTML();
    
        $this->assertStringContainsString('name="page-fgi[]" value="FRA"', $html);
        $this->assertStringContainsString('name="page-fgi[]" value="NATO"', $html);
    }
    
    
    

}