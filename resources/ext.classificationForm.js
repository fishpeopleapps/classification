/**
 * Fil Purpose: Client-side behavior for the Page Classification UI.
 * How it works:
 * - Loads after the ResourceLoader module "ext.classificationForm".
 * - Wires up banner placement, permission gating, and form toggling.
 * - Dynamically shows/hides sections (RELTO, CUI) based on selections and rules.
 * - Enforces declassification date logic and validates required controls.
 * - Submits the form to MediaWiki via the action=classification API.
 *
 * Key DOM contracts (from classification-form.html):
 * - #edit-classification-button: toggles the form.
 * - #classification-form: root form container (hidden by default).
 * - #page-classification, #page-dis, #relto-select: main select inputs.
 * - #declass-date, #reset-declass-date, #manual-review-declass: declass controls.
 * - #sci-options, #fgi-options: containers for dynamically-rendered checkbox groups.
 * - #classification-save-button: triggers validation + save.
 *
 * Environment:
 * - Uses mw.config (wgArticleId, wgAction, wgCanEditClassification).
 * - Uses mw.util.wikiScript('api') to resolve the API endpoint for the current wiki.
 */

mw.loader.using("ext.classificationForm").then(() => {

    // For local environment: const apiUrl = "http://localhost:8080/w/api.php";
    // Try 1 for Staging
    const apiUrl = mw.util.wikiScript('api');
    // Try 2 for Staging
    // const apiUrl = `${window.location.origin}${mw.util.wikiScript('api')}`;

    // Core elements and page metadata
    const classificationForm = document.getElementById("classification-form");
    const editClassificationButton = document.getElementById("edit-classification-button");
    const pageId = Number(mw.config.get("wgArticleId"));

    // console.log("Updating: ");

    // Banner/notice placements
	const banner = document.querySelector('.classification-banner');
	const missingBox = document.querySelector('.classification-missing-box');
	const citizenNotice = document.querySelector('.citizen-sitenotice-container');
	const vectorNotice = document.querySelector('.vector-sitenotice-container');
    const content = document.getElementById("mw-content-text");

	// Only show missing box on view pages (not edit)
	const isViewAction = mw.config.get('wgAction') === 'view';

    // Place the banner at top + clone to bottom; fallback to "missing" box when appropriate.
    if (content) {
        if (banner) {
            if (citizenNotice) {
                citizenNotice.appendChild(banner);
            } else if (vectorNotice) {
                vectorNotice.appendChild(banner);
            } else {
                content.prepend(banner);
            }
            const clonedBanner = banner.cloneNode(true);
            // prevent disappearing editButton
            const dupButton = clonedBanner.querySelector('#edit-classification-button');
		    if (dupButton) dupButton.remove();
            // apend bottom banner
            content.appendChild(clonedBanner);
        } else if (missingBox && isViewAction) {
            content.prepend(missingBox);
        }
    }

    // Show an informational notice when the page doesn't exist yet; don't show the edit button.
    // (This must occur before the permission check to ensure the notice appears for new pages.)
    if (pageId === 0 && editClassificationButton) {
        editClassificationButton.style.display = "none";
        const notice = document.createElement("div");
        notice.className = "classification-notice";
        notice.textContent = "This page needs to be created before classification can be assigned.";
        editClassificationButton.parentNode.insertBefore(notice, editClassificationButton);
    } 

    // Permission gate: hide/disable all classification UI if the user may not edit classification.
    if (!mw.config.get('wgCanEditClassification')) {
        // Fully disable the classification UI
        if (editClassificationButton) {
            editClassificationButton.style.display = "none";
        }
        if (classificationForm) {
            classificationForm.style.display = "none";
        }
    
        const saveBtn = document.getElementById("classification-save-button");
        if (saveBtn) {
            saveBtn.disabled = true;
        }
        // console.log("Classification UI disabled: user lacks permission.");
        return;
    }

    // If user can edit and the page exists, show the toggle button.
    if (
        mw.config.get('wgCanEditClassification') &&
        editClassificationButton &&
        pageId !== 0
    ) {
        editClassificationButton.style.display = "inline-block";
    }

    // Toggle form visibility on button click.
    editClassificationButton.addEventListener("click", function () {     
        if (classificationForm.style.display === "none") {
            classificationForm.style.display = "block";
        } else {
            classificationForm.style.display = "none";
        }
        
    });


    const classificationDropdown = document.getElementById("page-classification");
    const classificationSaveButton = document.getElementById("classification-save-button");

    /**
     * ENVIRONMENTAL POLICY BEGIN -------------------------------
     */

    function applyEnvPolicyToDropdown() {
    const env = mw.config.get('wgDeploymentEnvironment') || 'TESTING';
    const cls = document.getElementById('page-classification');
    if (!cls) return;

    const envLevels = {
        NIPR:   ['UNCLASSIFIED'],
        SIPR:   ['UNCLASSIFIED', 'CONFIDENTIAL', 'SECRET'],
        JWICS:  ['UNCLASSIFIED', 'CONFIDENTIAL', 'SECRET', 'TOP SECRET'],
        TESTING:['UNCLASSIFIED', 'CONFIDENTIAL', 'SECRET', 'TOP SECRET']
    };

    const allowed = envLevels[env] || envLevels.TESTING;

    // Restrict dropdown
    Array.from(cls.options).forEach(opt => {
        if (!allowed.includes(opt.value)) {
            opt.remove();
        }
    });

    // If current value is no longer valid, fix it
    if (!allowed.includes(cls.value)) {
        cls.value = allowed[0];
    }

    // Environment-based control hiding
    if (env === "NIPR") {

        // Hide SCI/FGI fieldsets
        const sciInner = document.getElementById("sci-options");
        if (sciInner) sciInner.parentElement.style.display = "none";
        const fgiInner = document.getElementById("fgi-options");
        if (fgiInner) fgiInner.parentElement.style.display = "none";

        // Hide Dissemination (label + dropdown)
        const disLabel = document.querySelector('label[for="page-dis"]');
        const disSelect = document.getElementById("page-dis");
        if (disLabel) disLabel.style.display = "none";
        if (disSelect) disSelect.style.display = "none";
    }

    if (env === "SIPR") {
        const sciInner = document.getElementById("sci-options");
        if (sciInner) sciInner.parentElement.style.display = "none";
    }

    // Trigger classification-dependent behavior
    cls.dispatchEvent(new Event("change"));
    }

    /**
     * ENVIRONMENTAL POLICY END -------------------------------------
     */


    // ------------ START RELTO
    const reltoSelect = document.getElementById("relto-select");
    if (reltoSelect && typeof Choices !== "undefined") {
        new Choices(reltoSelect, {
            removeItemButton: true,
            placeholder: true,
            placeholderValue: 'Select REL TO countries...',
            searchPlaceholderValue: 'Search countries...'
        });
    }
    const pageDis = document.getElementById("page-dis");

    if (reltoSelect && pageDis) {
        // RELTO fieldset hidden by default; appears only when dissemination is RELTO
        reltoSelect.closest('fieldset').style.display = "none";
        pageDis.addEventListener("change", function () {
            if (this.value === "RELTO") {
            reltoSelect.closest('fieldset').style.display = "block";
            } else {
            reltoSelect.closest('fieldset').style.display = "none";
            }
        });
    }

    // ------------ START IS CUI
    // Hide the CUI control for SECRET / TOP SECRET; otherwise show. Resets the checkbox if hidden.
    const isCUI = document.getElementById("is-cui");
    const pageClass = document.getElementById("page-classification");

    if (isCUI && pageClass) {
        const isCuiFieldset = isCUI.closest('fieldset');
        
        // Hide CUI option if page classification is marked as SECRET or TOP SECRET
        function updateIsCuiVisibility() {
            if (pageClass.value === "SECRET" || pageClass.value === "TOP SECRET") {
                isCuiFieldset.style.display = "none";
                isCUI.checked = false; 
            } else {
                isCuiFieldset.style.display = "block";
            }
        }
        updateIsCuiVisibility();
        pageClass.addEventListener("change", updateIsCuiVisibility);
    }

    // -------- START Declassification Logic ---------
    const declassDateInput = document.getElementById("declass-date");
    const resetDeclassDateButton = document.getElementById("reset-declass-date");
    const manualReviewButton = document.getElementById("manual-review-declass");

    /**
     * Validate MM/DD/YYYY with real calendar checks (e.g., 02/30 is invalid).
     * @param {string} dateString - The user-entered date.
     * @returns {boolean} true if valid; false otherwise.
     */
    function isValidDate(dateString) {
        const regex = /^(0[1-9]|1[0-2])\/(0[1-9]|[12]\d|3[01])\/\d{4}$/;
        if (!regex.test(dateString)) return false;
    
        const [month, day, year] = dateString.split('/').map(Number);
        const date = new Date(year, month - 1, day);
        return (
            date.getFullYear() === year &&
            date.getMonth() === month - 1 &&
            date.getDate() === day
        );
    }
    
    // Return a default declassification date 25 years from today in MM/DD/YYYY.
    function getDefaultDeclassDate() {
        const now = new Date();
        now.setFullYear(now.getFullYear() + 25);
    
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const year = now.getFullYear();
    
        return `${month}/${day}/${year}`; 
    }
    
    // When classification switches to UNCLASSIFIED, use "none"; otherwise apply default date.
    classificationDropdown.addEventListener("change", function () {
        if (classificationDropdown.value === "UNCLASSIFIED") {
            declassDateInput.value = "none";
        } else {
            declassDateInput.value = getDefaultDeclassDate();
        }
    });
    classificationDropdown.dispatchEvent(new Event("change"));

    resetDeclassDateButton.addEventListener("click", function () {
        declassDateInput.value = getDefaultDeclassDate();
    });

    manualReviewButton.addEventListener("click", function () {
        declassDateInput.value = "MR";
    });

    function formatDateForDatabase(dateString) {
        if (dateString === 'MR' || dateString === 'none') {
            return dateString;
        }
    
        const parts = dateString.split('/');
        if (parts.length === 3) {
            const [month, day, year] = parts.map(str => str.trim());
            return `${year}${month.padStart(2, '0')}${day.padStart(2, '0')}`; // e.g. "20500407"
        }
    
        return dateString; 
    }

    // -------- END Declassification Logic ---------

    // Save handler: validate, collect values, then POST to action=classification.
    classificationSaveButton.addEventListener("click", function () {
        if (!validateForm()) return;

        const page_id = mw.config.get("wgArticleId"); 
        const page_class = classificationDropdown.value;
        const page_dis = document.getElementById("page-dis").value;

        // Collect SCI / FGI selections (checkbox groups rendered in the options containers).
        const selectedSCI = Array.from(document.querySelectorAll('input[name="page-sci[]"]:checked'))
                                 .map(input => input.value)
                                 .join(",");
        const selectedFGI = Array.from(document.querySelectorAll('input[name="page-fgi[]"]:checked'))
                                 .map(input => input.value)
                                 .join(",");

        const declass_value = declassDateInput.value.trim();
        let declass_date;
        if (declass_value === 'MR' || declass_value === 'none') {
            declass_date = declass_value;
        } else if (isValidDate(declass_value)) {
            declass_date = formatDateForDatabase(declass_value);
        } else {
            alert("Invalid date. Please enter a valid date in MM/DD/YYYY format, or use 'MR' or 'none'.");
            return;
        }

        const selectedRELTO = Array.from(reltoSelect.selectedOptions).map(opt => opt.value).join(",");
        const is_cui = document.getElementById("is-cui").checked ? 1 : 0;

        fetch(apiUrl, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({
                action: "classification",
                format: "json",
                page_id: page_id,
                page_class: page_class,
                page_dis: page_dis,
                rel_to_countries: selectedRELTO,
                page_sci: selectedSCI,
                page_fgi: selectedFGI,
                declass_date: declass_date,
                is_cui: is_cui
            })
        })
        .then(response => response.json()) 
        .then(data => {
            if (data.success) {
                alert("Classification saved successfully!");  
                
                // Quick visual feedback: fade out the form then hide it.
                classificationForm.style.transition = "opacity 0.5s ease-out";
                classificationForm.style.opacity = "0";
                setTimeout(() => {
                    classificationForm.style.display = "none";
                    classificationForm.style.opacity = "1"; // Reset opacity for next open
                }, 500); 
            } else {
                alert("Error saving classification: " + (data.error || "Unknown error, is the Classification Set?"));
            }
            
        })
        .catch(error => {
            console.error("Fetch error:", error);
            alert("Fetch error: " + error);
        });
        
    });

    applyEnvPolicyToDropdown()


    ////////// SUPER IMPORTANT FORM VALIDATION -------->
    // Check if the page has an ID
    // Check if the page has a classification
    // IF the classification is UNCLASSIFIED -> no declassification date should be set
    // IF the classification is SECRET -> FGI must be set
    // IF the classification is TOP SECRET -> SCI must be set

    function validateForm() {
        let errorMsg = document.getElementById("classification-error");
        if (!errorMsg) {
            errorMsg = document.createElement("div");
            errorMsg.style.color = "navy";
            errorMsg.style.fontWeight = "bold";
            errorMsg.style.marginTop = "10px";
            errorMsg.id = "classification-error";
            classificationSaveButton.insertAdjacentElement("afterend", errorMsg);
        }

        const page_id = mw.config.get("wgArticleId"); 
        if (!page_id) {
            alert("Error: Page ID is missing.");
            return false;
        }

        const page_class = classificationDropdown.value;
        const selectedSCI = Array.from(document.querySelectorAll('input[name="page-sci[]"]:checked'))
                                 .map(input => input.value)
                                 .join(",");
        const selectedFGI = Array.from(document.querySelectorAll('input[name="page-fgi[]"]:checked'))
                                 .map(input => input.value)
                                 .join(",");
    
        if (page_class === "SECRET" && selectedFGI === "") {
            errorMsg.innerText = "Error: A SECRET classification requires at least one FGI control.";
            return false;
        }
        if (page_class === "TOP SECRET" && selectedSCI === "") {
            errorMsg.innerText = "Error: A TOP SECRET classification requires at least one SCI control.";
            return false;
        }
        const page_dis = document.getElementById("page-dis").value;

        const reltoSelect = document.getElementById("relto-select");
        const selectedCountries = reltoSelect
        ? Array.from(reltoSelect.selectedOptions).map(opt => opt.value)
        : [];

        if (page_dis === "RELTO" && selectedCountries.length === 0) {
            errorMsg.innerText = "Error: Please select at least one country code.";
            return false;
        }


        errorMsg.innerText = "";
        return true;
    }

    // Clear error message / re-enable save when users fix issues interactively.
    const fgiCheckboxes = document.querySelectorAll('input[name="page-fgi[]"]');
    fgiCheckboxes.forEach(cb => cb.addEventListener("change", function () {
        if (validateForm()) {
            classificationSaveButton.disabled = false; 
        }
    }));
    const sciCheckboxes = document.querySelectorAll('input[name="page-sci[]"]');
    sciCheckboxes.forEach(cb => cb.addEventListener("change", function () {
        if (validateForm()) {
            classificationSaveButton.disabled = false;
        }
    }));
});