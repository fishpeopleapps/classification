<?php

namespace ClassificationTool;

use DatabaseUpdater;

class Hooks {
    public static function onLoadExtensionSchemaUpdates( DatabaseUpdater $updater ) {
        $updater->addExtensionTable(
            'page_classification',
            __DIR__ . '/../sql/page_classification.sql'
        );
    }
    public static function onBeforePageDisplay($out, $skin) {
        $out->addModules('ext.classificationForm');
    }
}
