<?php 

namespace ClassificationTool;

/**
 * Renders classification display elements for a page.
 * 
 * Includes the top/bottom classification banner and the
 * “missing classification” notice box with edit link.
 */
class ClassificationDisplayHelper {

    /**
     * Build the classification banner HTML with all
     * applicable fields from the provided data object.
     */
    public static function renderBanner( $data ) {
        $classSlug = strtolower( str_replace( ' ', '-', $data->page_class ) );
        $bgClass = "class-{$classSlug}";

        $fields = [];

        if ( $data->is_cui ) {
        $fields[] = "<span class='is-cui-label'>CUI</span>";
        }
        if ( $data->page_dis ) {
            $fields[] = "<strong>Dissemination:</strong> {$data->page_dis}";
        }
        if ( $data->page_sci ) {
            $fields[] = "<strong>SCI:</strong> {$data->page_sci}";
        }
        if ( $data->page_fgi ) {
            $fields[] = "<strong>FGI:</strong> {$data->page_fgi}";
        }
        if ( $data->rel_to_countries ) {
            $fields[] = "<strong>REL TO:</strong> {$data->rel_to_countries}";
        }
        if ( $data->declass_date && $data->declass_date !== 'none' ) {
            $raw = $data->declass_date;
            if ( $raw === 'MR' ) {
                $formatted = 'Manual Review';
            } elseif ( preg_match('/^\d{8}$/', $raw ) ) {
                $year = substr($raw, 0, 4);
                $month = substr($raw, 4, 2);
                $day = substr($raw, 6, 2);
                $formatted = date("F j, Y", strtotime("{$year}-{$month}-{$day}"));
            } else {
                $formatted = $raw;
            }

            $fields[] = "<strong>Declass Date:</strong> {$formatted}";
        }

        $fieldHtml = implode( '<br>', $fields );

        return "
            <div class='classification-banner {$bgClass}'>
                <div class='classification-overall'><strong>Classification: {$data->page_class}</strong></div>
                {$fieldHtml}
            </div>
        ";
    }

    /**
     * Build the “missing classification” notice with
     * a link to edit the page in source mode.
     * (VisualEditor capabilities not working yet)
     */

    public static function renderMissingBox( $title ) {
        $sourceEditUrl = wfScript() . '?title=' . urlencode( $title->getPrefixedDBkey() ) . '&action=edit';


        return "
            <div class='classification-missing-box'>
                This page has no classification assigned.<br>
                <a href='{$sourceEditUrl}' class='classification-edit-source-button'>Add Classification</a>
            </div>
        ";
    }
}
