<?php

use MediaWikiIntegrationTestCase;

class ClassificationApiTest extends MediaWikiIntegrationTestCase {

    public function testSaveAndRetrieveClassification() {
        $user = $this->getTestSysop()->getUser();
        \RequestContext::getMain()->setUser( $user );

        // Step 1: Create test page
        $title = Title::newFromText( 'Classification Integration Test Page' );
        $content = ContentHandler::makeContent( 'Initial page content', $title );

        $pageStore = \MediaWiki\MediaWikiServices::getInstance()->getWikiPageFactory();
        $wikiPage = $pageStore->newFromTitle( $title );

        $pageUpdater = $wikiPage->newPageUpdater( $user );
        $pageUpdater->setContent( 'main', $content );
        $pageUpdater->saveRevision( \CommentStoreComment::newUnsavedComment( 'Creating test page' ) );

        $pageId = $title->getArticleID();
        $this->assertNotEquals( 0, $pageId, 'Page ID should not be 0 after creation' );

        // Step 3: Save classification
        $saveParams = [
            'action' => 'classification',
            'page_id' => $pageId,
            'page_class' => 'SECRET',
            'page_dis' => 'RELTO',
            'rel_to_countries' => 'USA,CAN',
            'page_sci' => 'SCI',
            'page_fgi' => 'FGI',
            'declass_date' => '2099-12-31',
            'format' => 'json'
        ];

        $saveResult = $this->executeApiRequest( $saveParams, $user );
        var_dump( $saveResult );
        $this->assertArrayHasKey( 'success', $saveResult );
        

        $this->assertSame( 'true', $saveResult['success'] );

        // Step 4: Read classification
        $readParams = [
            'action' => 'classification',
            'page_id' => $pageId,
            'page_class' => '', // triggers read mode
            'format' => 'json'
        ];

        $readResult = $this->executeApiRequest( $readParams, $user );
        $this->assertArrayHasKey( 'classification', $readResult );

        $data = $readResult['classification'];

        // Step 5: Assert saved values match expected
        $this->assertEquals( $pageId, $data['page_id'] );
        $this->assertSame( 'SECRET', $data['page_class'] );
        $this->assertSame( 'RELTO', $data['page_dis'] );
        $this->assertSame( 'USA,CAN', $data['rel_to_countries'] );
        $this->assertSame( 'SCI', $data['page_sci'] );
        $this->assertSame( 'FGI', $data['page_fgi'] );
        $this->assertSame( '2099-12-31', $data['declass_date'] );
    }

    private function executeApiRequest( array $params, \User $user ) {
        $context = new \RequestContext();
        $context->setUser( $user );

        $request = new \FauxRequest( $params );
        $context->setRequest( $request );

        $api = new \ApiMain( $request );
        $api->execute();

        return $api->getResult()->getResultData();
    }
}
