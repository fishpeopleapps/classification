<?php

namespace ClassificationTool;

use DatabaseUpdater;
use MediaWiki\Hook\ResourceLoaderGetConfigVarsHook;

class Hooks {
    public static function onLoadExtensionSchemaUpdates( DatabaseUpdater $updater ) {
        $updater->addExtensionTable(
            'page_classification',
            __DIR__ . '/../sql/page_classification.sql'
        );

        $updater->addExtensionUpdate( [
            'addField',
            'page_classification',
            'is_cui',
            __DIR__ . '/../sql/is_cui_patch.sql',
            true
        ] );
    }

    public static function onBeforePageDisplay( $out, $skin ) {
        $title = $out->getTitle();
        $pageId = $title->getArticleID();
        $user = $out->getUser();
        $hasValidPage = $title && $title->getArticleID() !== 0;
        $action = $out->getRequest()->getVal( 'action', 'view' );

        $out->addModules( 'ext.classificationForm' );

        $out->addJsConfigVars( [
            'wgCanEditClassification' => ClassificationPermissions::canEditClassification( $user, $title ),
            'wgDeploymentEnvironment' => $out->getConfig()->get( 'DeploymentEnvironment' )
        ] );

        $data = null;

        if ( $action !== 'edit' && $action !== 'submit' ) {

            $data = \ClassificationTool\ClassificationDBManager::getClassification( $pageId );            
            if ( $data ) {
                wfDebugLog( 'classification', "Rendering classification banner for page_id $pageId" );
                $html = ClassificationDisplayHelper::renderBanner( $data );
            
               $out->addHTML( $html );
            }
        }
        
        if ( !$data && $hasValidPage && ClassificationPermissions::canEditClassification( $user, $title ) && $action !== 'edit' ) {
            wfDebugLog( 'classification', "Rendering missing classification box for page_id $pageId" );
            $out->addHTML( ClassificationDisplayHelper::renderMissingBox( $title ) );
        }
    }

        public function onResourceLoaderGetConfigVars( array &$vars, $skin, $config ) : void {
        $vars['wgDeploymentEnvironment'] = $config->get( 'DeploymentEnvironment' );
    }

}
