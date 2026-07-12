-- =============================================================================
-- llms.txt custom content (sma_webshop_settings)
-- =============================================================================
-- CMS Admin → LLMS.txt saves markdown here; storefront /llms.txt merges it.
-- PHP auto-adds columns via Cms_admin_llms_txt_model::ensureLlmsTxtColumns().
-- If you see error 1060 "Duplicate column name", columns already exist — skip ALTER.
-- =============================================================================

ALTER TABLE sma_webshop_settings
    ADD COLUMN llms_txt_include_custom TINYINT(1) NOT NULL DEFAULT 1;

ALTER TABLE sma_webshop_settings
    ADD COLUMN llms_txt_custom MEDIUMTEXT NULL;

ALTER TABLE sma_webshop_settings
    ADD COLUMN llms_txt_updated_at DATETIME NULL;

-- Verify:
-- SELECT id, llms_txt_include_custom, LEFT(llms_txt_custom, 80) AS preview, llms_txt_updated_at
-- FROM sma_webshop_settings WHERE id = 1;
