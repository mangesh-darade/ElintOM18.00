<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Shared helpers for CMS Admin Panel models.
 */
class Cms_admin_base_model extends CI_Model
{
    /**
     * Normalise page_section_mapping.is_enabled for admin UI and storefront queries.
     *
     * @param mixed $value
     * @return bool
     */
    protected function isSectionRowEnabled($value)
    {
        if ($value === true || $value === 1) {
            return true;
        }
        $normalized = strtolower(trim((string) $value));
        return in_array($normalized, array('1', 'true', 'yes', 'on'), true);
    }
}
