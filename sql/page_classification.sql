CREATE TABLE page_classification (
    page_id          INT NOT NULL PRIMARY KEY,
    page_class       VARCHAR(255) NOT NULL,
    page_sci         VARCHAR(255) DEFAULT NULL,
    page_fgi         VARCHAR(255) DEFAULT NULL,
    page_dis         VARCHAR(255) DEFAULT NULL,
    rel_to_countries TEXT DEFAULT NULL,
    declass_date     DATE NOT NULL,
    FOREIGN KEY (page_id) REFERENCES page(page_id) ON DELETE CASCADE
);
