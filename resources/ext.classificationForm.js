document.addEventListener("DOMContentLoaded", function () {
    const classificationDropdown = document.getElementById("page-classification");
    const sciCheckboxes = document.querySelectorAll("input[name='page-sci[]']");
    const fgiCheckboxes = document.querySelectorAll("input[name='page-fgi[]']");
    const saveButton = document.querySelector("button#wpSave");
    const errorMsg = document.createElement("div");
    errorMsg.style.color = "red";
    errorMsg.style.marginTop = "10px";
    errorMsg.id = "classification-error";
    classificationDropdown.parentElement.appendChild(errorMsg);

    function validateForm() {
        let classification = classificationDropdown.value;
        let sciSelected = Array.from(sciCheckboxes).some(cb => cb.checked);
        let fgiSelected = Array.from(fgiCheckboxes).some(cb => cb.checked);
        let error = "";

        if (classification === "TOP SECRET" && !sciSelected) {
            error = "Error: 'TOP SECRET' pages require at least one SCI marking.";
        }
        if (classification === "SECRET" && !fgiSelected) {
            error = "Error: 'SECRET' pages require at least one FGI marking.";
        }

        if (error) {
            errorMsg.innerText = error;
            saveButton.disabled = true;
        } else {
            errorMsg.innerText = "";
            saveButton.disabled = false;
        }
    }

    classificationDropdown.addEventListener("change", validateForm);
    sciCheckboxes.forEach(cb => cb.addEventListener("change", validateForm));
    fgiCheckboxes.forEach(cb => cb.addEventListener("change", validateForm));
});
