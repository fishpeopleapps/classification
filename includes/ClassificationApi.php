<?php

namespace ClassificationTool;

use ApiBase;
use Title;
use MediaWiki\MediaWikiServices;

/*
* Handle classification data storage by getting database parameteres
* validating input, and call the ClassificationDBManager to save the data
*/
class ClassificationApi extends ApiBase {

    public function execute() {
        $params = $this->extractRequestParams();
        $page_id = (int) $params['page_id'];
        $page_class = isset($params['page_class']) ? trim($params['page_class']) : '';
        $page_dis = !empty($params['page_dis']) ? $params['page_dis'] : null;
        $rel_to_countries = isset($params['rel_to_countries']) ? (is_array($params['rel_to_countries']) ? $params['rel_to_countries'] : explode(',', $params['rel_to_countries'])) : [];
        $page_sci = isset($params['page_sci']) ? (is_array($params['page_sci']) ? $params['page_sci'] : explode(',', $params['page_sci'])) : [];
        $page_fgi = isset($params['page_fgi']) ? (is_array($params['page_fgi']) ? $params['page_fgi'] : explode(',', $params['page_fgi'])) : [];
        $declass_date = !empty($params['declass_date']) ? $params['declass_date'] : null;
        $is_cui = isset( $params['is_cui'] ) ? (bool)$params['is_cui'] : false;

        // Normalize classification string
        $norm = strtoupper( preg_replace( '/\s+/', ' ', trim( (string)$page_class ) ) );
        if ( $norm === 'TOPSECRET' ) { 
            $norm = 'TOP SECRET'; 
        }

        // to determine if the user has permission
        $this->checkClassificationPermission();
        
        // For read requests
        if ($page_id && $page_class === '') {
            $this->handleReadRequest($page_id);
            return;
        }

        // For write requests
        if (!$page_id || $page_class === '') {
            $this->getResult()->addValue(null, 'error', 'Missing required parameters: page_id and/or page_class');
            return;
        }
        // --- ENVIRONMENT POLICY --
        $env = MediaWikiServices::getInstance()->getMainConfig()->get( 'DeploymentEnvironment' );
        $policy = EnvPolicy::get( $env );

        // Environment gate: reject if not in the allowed levels
        if ( !in_array( $norm, $policy['levels'], true ) ) {
            $this->getResult()->addValue(
                null,
                'error',
                "Classification '$page_class' not allowed in $env."
            );
            return;
        }

        $page_class = $norm;
        // --- ENVIRONMENT POLICY --


        $manager = new ClassificationDBManager();
        $result = $manager->saveClassification($page_id, $page_class, $page_dis, $rel_to_countries, $page_sci, $page_fgi, $declass_date, $is_cui);

        if ($result === true) {
            wfDebugLog( 'classification', "API Response: Success for page_id $page_id with class $page_class" );
            $this->getResult()->addValue(null, 'success', 'true');
        } else {
            $errorMsg = 'Database save failed.';
            if ($result !== true && is_string($result)) {
                $errorMsg .= " Error: " . $result;
            }
            wfDebugLog( 'classification', "API Error: $errorMsg for page_id $page_id" );
            $this->getResult()->addValue(null, 'error', $errorMsg);
        }
    }

    protected function getAllowedParams() {
        return [
            'page_id' => [
                'type' => 'integer',
                'required' => true
            ],
            'page_class' => [
                'type' => 'string',
                'required' => false
            ],
            'page_dis' => [
                'type' => 'string',
                'required' => false
            ],
            'rel_to_countries' => [
                'type' => 'array',
                'required' => false
            ],
            'page_sci' => [
                'type' => 'array',
                'required' => false
            ],
            'page_fgi' => [
                'type' => 'array',
                'required' => false
            ],
            'declass_date' => [
                'type' => 'string',
                'required' => false
            ],
            'is_cui' => [
                'type' => 'bool',
                'required' => false
            ]
        ];
    }

    // Helper function for data retrieval
    private function handleReadRequest(int $page_id) {
        $result = ClassificationDBManager::getClassification($page_id);
    
        if ($result) {
            $this->getResult()->addValue(null, 'classification', [
                'page_id' => $result->page_id,
                'page_class' => $result->page_class,
                'page_dis' => $result->page_dis,
                'rel_to_countries' => $result->rel_to_countries,
                'page_sci' => $result->page_sci,
                'page_fgi' => $result->page_fgi,
                'declass_date' => $result->declass_date,
                'is_cui' => $result->is_cui
            ]);
        } else {
            $this->getResult()->addValue(null, 'error', 'No classification found for this page.');
            wfDebugLog( 'classification', "No classification found for page_id $page_id" );
        }
    }
    // Helper function to ensure only specific people can submit/edit classifications
    private function checkClassificationPermission() {
        $user = $this->getUser();
        $pageId = $this->getParameter( 'page_id' );
        $title = Title::newFromID( $pageId );
    
        if ( !\ClassificationTool\ClassificationPermissions::canEditClassification( $user, $title ) ) {
            wfDebugLog( 'classification', "Permission denied for user {$user->getName()} on page ID $pageId" );
            $this->dieWithError( 'classificationapi-permission-denied' );
        }
    }
    
    
}