<?php

namespace ClassificationTool;

/**
 * Purpose: Server-side helper that injects the Classification UI into edit pages
 * and renders the HTML template with dynamic option lists. This class:
 * - Registers the ResourceLoader module (ext.classificationForm) so the JS behavior is available.
 * - Loads /resources/classification-form.html and replaces placeholder tokens with
 *   runtime-generated options derived from PageClassification::* allowlists.
 * Security/consistency notes:
 * - All dynamic values are escaped with htmlspecialchars().
 * - Placeholder tokens (e.g., <!-- CLASSIFICATION_OPTIONS -->) are used as simple
 *   anchors to avoid templating complexity and to keep the HTML static-friendly.
 */

class PageClassificationForm {

    /**
     * Hook handler for EditPage::showEditForm: injects module + form markup.
     * @param mixed $editor  EditPage instance (unused here).
     * @param \OutputPage $out OutputPage used to add modules and HTML.
     * @return void
     */
    public static function onEditPageShowEditFormFields($editor, &$out) {
        $out->addModules('ext.classificationForm');
        $out->addHTML(self::getClassificationFormHTML());
    }

    public static function getClassificationFormHTML() {
        $htmlPath = __DIR__ . "/../resources/classification-form.html";
        if (!file_exists($htmlPath)) {
            wfDebugLog( 'classification', "Missing classification-form.html at $htmlPath" );
            return '';
        }
        $html = file_get_contents($htmlPath);
    
        // Add dynamic options for Classification, Dissemination, SCI, and FGI
        $classificationOptions = '<option value="">-- Select --</option>';
        foreach (PageClassification::VALID_CLASSES as $class) {
            $classificationOptions .= '<option value="' . htmlspecialchars($class) . '">' . htmlspecialchars($class) . '</option>';
        }
        $html = str_replace("<!-- CLASSIFICATION_OPTIONS -->", $classificationOptions, $html);

        $cuiCheckbox = '<input type="checkbox" name="is_cui" id="is-cui" value="1">';
        $html = str_replace('<!-- CUI_CHECKBOX -->', $cuiCheckbox, $html);


        $disseminationOptions = '<option value="">-- Select --</option>';
        foreach (PageClassification::VALID_DISSEMINATION as $dis) {
            $disseminationOptions .= '<option value="' . htmlspecialchars($dis) . '">' . htmlspecialchars($dis) . '</option>';
        }
        $html = str_replace("<!-- DISSEMINATION_OPTIONS -->", $disseminationOptions, $html);

        $reltoOptions = '';
        foreach (PageClassification::VALID_RELTO as $relto) {
            $reltoOptions .= '<option value="' . htmlspecialchars($relto) . '">' . htmlspecialchars($relto) . '</option>';
        }
        $html = str_replace('<!-- RELTO_OPTIONS -->', $reltoOptions, $html);
    
        $sciOptions = "";
        foreach (PageClassification::VALID_SCI as $sci) {
            $sciOptions .= '<label><input type="checkbox" name="page-sci[]" value="' . htmlspecialchars($sci) . '"> ' . htmlspecialchars($sci) . '</label>';
        }
        $html = str_replace('<!-- SCI_OPTIONS -->', $sciOptions, $html);
    
        $fgiOptions = "";
        foreach (PageClassification::VALID_FGI as $fgi) {
            $fgiOptions .= '<label><input type="checkbox" name="page-fgi[]" value="' . htmlspecialchars($fgi) . '"> ' . htmlspecialchars($fgi) . '</label>';
        }
        $html = str_replace('<!-- FGI_OPTIONS -->', $fgiOptions, $html);

    
        return $html;
    }
}
