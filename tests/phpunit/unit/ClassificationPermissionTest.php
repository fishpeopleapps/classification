<?php

use ClassificationTool\ClassificationPermissions;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionLookup;
use MediaWiki\Revision\RevisionRecord;


class ClassificationPermissionsTest extends MediaWikiUnitTestCase {
    private $user;
    private $title;
    private $revision;
    private $revisionLookup;

    protected function setUp(): void {
        parent::setUp();

        $this->user = $this->createMock( User::class );
        $this->title = $this->createMock( Title::class );
        $this->revision = $this->createMock( RevisionRecord::class );
        $this->revisionLookup = $this->createMock( RevisionLookup::class );

        // Mock the service container
        $services = $this->createMock( MediaWikiServices::class );
        $services->method( 'getRevisionLookup' )
            ->willReturn( $this->revisionLookup );
        MediaWikiServices::forceGlobalInstance( $services );
    }

    public function testCanEdit_WhenUserHasPermission() {
        $this->user->method( 'isAllowed' )
            ->with( 'classificationeditor' )
            ->willReturn( true );

        $this->revisionLookup->method( 'getFirstRevision' )
            ->willReturn( null ); // no revision

        $canEdit = ClassificationPermissions::canEditClassification( $this->user, $this->title );
        $this->assertTrue( $canEdit );
    }

    public function testCanEdit_WhenUserIsAuthor() {
        $user = $this->createMock( User::class );
        $pageAuthor = $this->createMock( User::class );
        $this->title = $this->createMock( Title::class );
    
        $user->method( 'isAllowed' )
            ->with( 'classificationeditor' )
            ->willReturn( false );
    
        // Here's the magic: force equals() to return true
        $pageAuthor->method( 'equals' )
            ->with( $user )
            ->willReturn( true );
    
        $this->revision->method( 'getUser' )
            ->willReturn( $pageAuthor );
    
        $this->revisionLookup->method( 'getFirstRevision' )
            ->willReturn( $this->revision );
    
        $canEdit = ClassificationPermissions::canEditClassification( $user, $this->title );
        $this->assertTrue( $canEdit );
    }
    

    public function testCanEdit_WhenUserHasNoPermissionAndIsNotAuthor() {
        $this->user->method( 'isAllowed' )
            ->with( 'classificationeditor' )
            ->willReturn( false );

        $otherUser = $this->createMock( User::class );
        $otherUser->method( 'equals' )
            ->with( $this->user )
            ->willReturn( false );

        $this->revision->method( 'getUser' )
            ->willReturn( $otherUser );

        $this->revisionLookup->method( 'getFirstRevision' )
            ->willReturn( $this->revision );

        $canEdit = ClassificationPermissions::canEditClassification( $this->user, $this->title );
        $this->assertFalse( $canEdit );
    }
}
