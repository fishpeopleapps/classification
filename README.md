# ClassificationTool

**ClassificationTool** is a MediaWiki extension that provides a standardized classification banner interface for pages. This tool allows authorized users to assign and manage classification data such as overall classification level, dissemination controls, declassification dates, SCI and FGI indicators, and applicable RELTO countries.

---

## Features

- Add classification banners to pages via user input
- Supports marking for:
  - Overall page classification
  - Dissemination controls
  - Declassification dates
  - SCI and FGI controls
  - RELTO country codes (if applicable)
- Creates a permission to allow any user with the classification permission to update the banner

---

## Contractor Information
    Government Purpose Rights; Contract No. FA2518-23-F-0032;  Contractor Name: ITSC Secure Solutions;
    Contractor Address: 4510 Gresham Lane, SE Owens Cross Roads, AL 35763; Expir Date: 07 September 2026
    © ITSC Secure Solutions. 2024. All Rights Reserved.
    This work, authored by ITSC Secure Solutions, was funded in whole or in part by the U.S.
    Government under U.S. Government Contract FA2518-23-F-0032, and is, therefore, subject to the following license:
    The Government is granted for itself and others acting on its behalf a paid-up, nonexclusive, irrevocable worldwide
    license in this work to reproduce, prepare derivative works, distribute copies to the public, and perform publicly
    and display publicly, by or on behalf of the Government. All other rights are reserved by the copyright owner.
   Non-U.S. Government Entities may use and disclose only as permitted in writing by ITSC Secure Solutions or by the U.S. Gov't.

---

## Author & Maintainer

This extension was developed and is maintained by Kimberly Brewer, based on an original extension authored by Richard Goldberg (Intelink).
For questions, support, or contributions, contact:

📧 Kimberly.brewer.11.ctr@spaceforce.mil
📧 kimberlymotyka27@gmail.com

---

## Installation

To install the **ClassificationTool** extension:

1. **Add locally via Composer**
   Ensure the extension is available in your local environment.

2. **Update Composer (locally)**  
   Run the necessary Composer update to recognize the added extension.

3. **Modify your Dockerfile**  
   COPY extensions/Classificationmanager $MW_HOME/user-extensions/ClassificationTool

4. **Build your Image**
    Push those changes to the CI pipeline and ensure the image builds successfully

5. **Enable via your LocalSettings.php**
    $wgDebugLogFile = "/var/log/mediawiki/debug-{$wgDBname}.log";
    $wgGroupPermissions['classificationeditors']['classificationeditor'] = true;
    $wgGroupPermissions['classificationeditors']['read'] = true;
    $wgGroupPermissions['classificationeditors']['edit'] = true;
    wfLoadExtension('ClassificationTool');
    $wgDebugLogGroups['classification'] = $wgDebugLogFile;

---

## 🗃️ Database

The **ClassificationTool** extension creates a new database table:

### `page_classification`

This table stores the classification data for each page and links to the core MediaWiki `page` table via `page_id`. It is created during installation using a schema definition included with the extension, and is registered using the `LoadExtensionSchemaUpdates` hook.

#### Table Purpose:
- Stores all classification banner data submitted through the form:
  - Classification level
  - Dissemination controls
  - Declassification date
  - SCI/FGI markings
  - RELTO countries (if applicable)

This design ensures the classification data is stored and managed separately from the wikitext content, allowing for better control, versioning, and auditing of classification data.

---

## 🔐 User Permissions & Classification Logic

The extension introduces a new user permission: **`classificationeditor`**.

### Role of `classificationeditor`:
Users with this permission can:
- View and access the classification form
- Assign or update classification banners for all pages

### How Permissions Are Enforced:
- The form is only displayed if the current user has the `classificationeditor` permission, checked during the `BeforePageDisplay` hook.
- Classification data is stored in the `page_classification` table upon form submission.
- Additionally, **authors of a page** are always permitted to edit the classification for that page, even if they are not in the `classificationeditors` group.

This logic ensures that classification responsibilities are limited to authorized users while still enabling original contributors to maintain the classification accuracy of their own content.

---




