<?php

use MediaWikiIntegrationTestCase;
use OutputPage;
use Title;
use WikiPage;
use ContentHandler;

class HooksTest extends MediaWikiIntegrationTestCase {

    public function testOnBeforePageDisplayAddsJsConfigAndModules() {
        $user = $this->getTestSysop()->getUser();
        \RequestContext::getMain()->setUser( $user );

        // Create a page
        $title = Title::newFromText( 'Hooks Integration Test Page' );
        $page = WikiPage::factory( $title );
        $content = ContentHandler::makeContent( 'Hooks test content', $title );
        $pageStore = \MediaWiki\MediaWikiServices::getInstance()->getWikiPageFactory();
        $page = $pageStore->newFromTitle( $title );
        $pageUpdater = $page->newPageUpdater( $user );
        $pageUpdater->setContent( 'main', $content );
        $pageUpdater->saveRevision( \CommentStoreComment::newUnsavedComment( 'Hooks test edit' ) );

        // Simulate a page view
        $context = new \RequestContext();
        $context->setTitle( $title );
        $context->setUser( $user );
        $context->setRequest( new \FauxRequest( [ 'action' => 'view' ] ) );

        $out = $context->getOutput();


        // Manually call the hook
        \ClassificationTool\Hooks::onBeforePageDisplay( $out, null );

        // Assert module is loaded
        $this->assertContains( 'ext.classificationForm', $out->getModules(), 'JS module should be added' );

        // Assert JS config var
        $config = $out->getJsConfigVars();
        $this->assertArrayHasKey( 'wgCanEditClassification', $config );

        // Assert some HTML is injected (banner or missing box)
        $html = $out->getHTML();
        $this->assertNotEmpty( $html, 'Expected HTML from classification banner or missing box' );
    }

    // Optional: test that table is created (light check)
    public function testSchemaUpdateHookRegistersTable() {
        $updater = $this->createMock( DatabaseUpdater::class );
        $updater->expects( $this->once() )
                ->method( 'addExtensionTable' )
                ->with(
                    $this->equalTo( 'page_classification' ),
                    $this->stringContains( 'page_classification.sql' )
                );

        \ClassificationTool\Hooks::onLoadExtensionSchemaUpdates( $updater );
    }
}
