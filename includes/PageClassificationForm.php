<?php

namespace ClassificationTool;
//use ClassificationTool\includes\PageClassification;

class PageClassificationForm {
    public static function onEditPageShowEditFormFields($editor, &$out) {
        $out->addHTML(self::getClassificationFormHTML());
    }

    private static function getClassificationFormHTML() {
        $html = '<fieldset>';
        $html .= '<legend>Page Classification</legend>';

        // Classification Dropdown
        $html .= '<label for="page-classification">Classification:</label>';
        $html .= '<select name="page-classification" id="page-classification">';
        $html .= '<option value="">-- Select --</option>';
        foreach (PageClassification::VALID_CLASSES as $class) {
            $html .= '<option value="' . htmlspecialchars($class) . '">' . htmlspecialchars($class) . '</option>';
        }
        $html .= '</select><br>';

        // Dissemination Dropdown
        $html .= '<label for="page-dis">Dissemination:</label>';
        $html .= '<select name="page-dis" id="page-dis">';
        $html .= '<option value="">-- Select --</option>';
        foreach (PageClassification::VALID_DISSEMINATION as $dis) {
            $html .= '<option value="' . htmlspecialchars($dis) . '">' . htmlspecialchars($dis) . '</option>';
        }
        $html .= '</select><br>';

        // SCI Checkboxes
        $html .= '<fieldset>';
        $html .= '<legend>SCI:</legend>';
        foreach (PageClassification::VALID_SCI as $sci) {
            $html .= '<input type="checkbox" name="page-sci[]" value="' . htmlspecialchars($sci) . '"> ' . htmlspecialchars($sci) . '<br>';
        }
        $html .= '</fieldset>';

        // FGI Checkboxes
        $html .= '<fieldset>';
        $html .= '<legend>FGI:</legend>';
        foreach (PageClassification::VALID_FGI as $fgi) {
            $html .= '<input type="checkbox" name="page-fgi[]" value="' . htmlspecialchars($fgi) . '"> ' . htmlspecialchars($fgi) . '<br>';
        }
        $html .= '</fieldset>';

        // Declassification Date
        $html .= '<label for="declass-date">Declassification Date:</label>';
        $html .= '<input type="date" name="declass-date" id="declass-date"><br>';

        $html .= '</fieldset>';

        return $html;
    }
}
