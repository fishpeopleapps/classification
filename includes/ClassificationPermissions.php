<?php

namespace ClassificationTool;

use MediaWiki\MediaWikiServices;
use Title;
use User;

/**
 * Checks if a user can edit a page’s classification.
 * 
 * Allows if the user has the 'classificationeditor' right
 * or if they are the page’s original author.
 */


class ClassificationPermissions {

    /**
     * Return true if the given user can edit the classification
     * for the specified page title.
     */
    public static function canEditClassification( User $user, Title $title ): bool {
        $hasPermission = $user->isAllowed( 'classificationeditor' );
    
        $rev = MediaWikiServices::getInstance()
            ->getRevisionLookup()
            ->getFirstRevision( $title );
    
        $pageAuthor = $rev ? $rev->getUser() : null;
        $isAuthor = $pageAuthor && $pageAuthor->equals( $user );
    
        // Debug info to inject into the page
        $debug = [
            'user' => $user->getName(),
            'hasPermission' => $hasPermission,
            'isAuthor' => $isAuthor,
            'pageAuthor' => $pageAuthor ? $pageAuthor->getName() : 'null'
        ];
    
        // For debugging ----
        // $context = \RequestContext::getMain();
        // $context->getOutput()->addJsConfigVars( [ 'classificationDebug' => $debug ] );
    
        return $hasPermission || $isAuthor;
    }
    
}