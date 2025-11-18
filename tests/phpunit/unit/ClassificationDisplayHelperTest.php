<?php

use PHPUnit\Framework\TestCase;
use ClassificationTool\ClassificationDisplayHelper;

class ClassificationDisplayHelperTest extends TestCase {

    public function testRenderBannerWithAllFields() {
        $data = (object)[
            'page_class' => 'SECRET',
            'page_dis' => 'NOFORN',
            'page_sci' => 'SI',
            'page_fgi' => 'FGI-ABC',
            'rel_to_countries' => 'USA, GBR',
            'declass_date' => '2029-05-01'
        ];

        $html = ClassificationDisplayHelper::renderBanner( $data );

        $this->assertStringContainsString( 'class-secret', $html );
        $this->assertStringContainsString( 'SECRET', $html );
        $this->assertStringContainsString( 'NOFORN', $html );
        $this->assertStringContainsString( 'SI', $html );
        $this->assertStringContainsString( 'FGI-ABC', $html );
        $this->assertStringContainsString( 'USA, GBR', $html );
        $this->assertStringContainsString( '2029-05-01', $html );
    }

    public function testRenderBannerSkipsEmptyFields() {
        $data = (object)[
            'page_class' => 'CONFIDENTIAL',
            'page_dis' => '',
            'page_sci' => null,
            'page_fgi' => '',
            'rel_to_countries' => '',
            'declass_date' => 'none'
        ];

        $html = ClassificationDisplayHelper::renderBanner( $data );

        $this->assertStringContainsString( 'class-confidential', $html );
        $this->assertStringContainsString( 'CONFIDENTIAL', $html );
        $this->assertStringNotContainsString( 'Dissemination:', $html );
        $this->assertStringNotContainsString( 'SCI:', $html );
        $this->assertStringNotContainsString( 'FGI:', $html );
        $this->assertStringNotContainsString( 'REL TO:', $html );
        $this->assertStringNotContainsString( 'Declass Date:', $html );
    }

    public function testRenderMissingBoxContainsLink() {
        $mockTitle = $this->createMock( \Title::class );
        $mockTitle->method( 'getLocalURL' )->willReturn( '/wiki/TestPage?action=edit&veaction=editsource' );

        $html = ClassificationDisplayHelper::renderMissingBox( $mockTitle );

        $this->assertStringContainsString( 'classification-missing-box', $html );
        $this->assertStringContainsString( 'Add Classification', $html );
        $this->assertStringContainsString( '/wiki/TestPage?action=edit&veaction=editsource', $html );
    }
}
