<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
/**
 * Entity Tag Mapping — Add New
 * Uses shared partials: _tag_form.php, _tag_scripts.php
 */
$tags_by_category = array();
if (!empty($tags_master)) {
    foreach ($tags_master as $t) {
        $cat = !empty($t['category']) ? $t['category'] : 'General';
        if (!isset($tags_by_category[$cat])) {
            $tags_by_category[$cat] = array();
        }
        $tags_by_category[$cat][] = $t;
    }
}
$existing_values = array();
?>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

    .entity-wrap {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 12px !important;
        padding: 24px !important;
        margin-bottom: 24px !important;
        font-family: 'Inter', sans-serif;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    
    .entity-title {
        font-size: 16px !important;
        font-weight: 700 !important;
        color: #0f172a !important;
        margin: 0 !important;
        letter-spacing: -0.02em;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    /* ── Header ── */
    .cms-back-header {
        background: #ffffff;
        padding: 24px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        margin-bottom: 24px;
        font-family: 'Inter', sans-serif;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    .cms-back-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #0f172a;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        background: #f8fafc;
        padding: 8px 16px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease;
        margin-bottom: 12px;
    }
    .cms-back-btn:hover {
        background: #f1f5f9;
        color: #1e293b;
        transform: translateX(-3px);
        text-decoration: none;
    }
    .cms-page-title {
        font-size: 24px;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 6px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .cms-page-title i {
        color: #059669;
    }
    .cms-page-subtitle {
        font-size: 14px;
        color: #64748b;
        margin: 0;
    }

    /* Form Controls */
    .form-group label {
        font-weight: 600 !important;
        font-size: 13px !important;
        color: #334155 !important;
        margin-bottom: 6px !important;
    }
    .form-control {
        height: 40px !important;
        border-radius: 8px !important;
        border: 1px solid #cbd5e1 !important;
        padding: 8px 14px !important;
        font-size: 13.5px !important;
        color: #1e293b !important;
        background-color: #ffffff !important;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
        transition: all 0.2s ease !important;
    }
    .form-control:focus {
        border-color: #059669 !important;
        box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.15) !important;
        outline: none !important;
    }
    select.form-control {
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
        appearance: none !important;
        background-image: url("data:image/svg+xml;utf8,<svg fill='%2364748b' height='24' viewBox='0 0 24 24' width='24' xmlns='http://www.w3.org/2000/svg'><path d='M7 10l5 5 5-5z'/><path d='M0 0h24v24H0z' fill='none'/></svg>") !important;
        background-repeat: no-repeat !important;
        background-position: right 12px center !important;
        padding-right: 32px !important;
    }

    /* Select2 dropdown custom premium styles */
    .select2-container.form-control {
        background: transparent !important;
        border: none !important;
        padding: 0 !important;
        box-shadow: none !important;
        height: 40px !important;
    }
    .select2-container.form-control .select2-choice {
        height: 40px !important;
        line-height: 38px !important;
        border-radius: 8px !important;
        border: 1px solid #cbd5e1 !important;
        padding: 0 14px !important;
        font-size: 13.5px !important;
        color: #1e293b !important;
        background: #ffffff !important;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
        transition: all 0.2s ease !important;
        display: block !important;
    }
    .select2-container.form-control.select2-container-active .select2-choice,
    .select2-container.form-control .select2-choice:focus {
        border-color: #059669 !important;
        box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.15) !important;
        outline: none !important;
    }
    .select2-container.form-control .select2-choice .select2-chosen {
        line-height: 38px !important;
        color: #1e293b !important;
        padding-right: 24px !important;
    }
    .select2-container.form-control .select2-choice .select2-arrow {
        background: transparent !important;
        border-left: none !important;
        width: 32px !important;
        display: flex !important;
        justify-content: center !important;
    }
    .select2-container.form-control .select2-choice .select2-arrow b {
        background: none !important;
        display: block !important;
        width: 0 !important;
        height: 0 !important;
        border-style: solid !important;
        border-width: 5px 4px 0 4px !important;
        border-color: #64748b transparent transparent transparent !important;
        margin-top: 18px !important;
    }

    /* Tabs Styling */
    .nav-tabs {
        border-bottom: 1px solid #e2e8f0 !important;
        display: flex;
        gap: 8px;
        margin-bottom: 0 !important;
    }
    .nav-tabs > li {
        margin-bottom: -1px !important;
    }
    .nav-tabs > li > a {
        border: none !important;
        background: transparent !important;
        color: #64748b !important;
        font-weight: 600 !important;
        font-size: 13px !important;
        padding: 10px 16px !important;
        border-radius: 8px 8px 0 0 !important;
        transition: all 0.2s ease !important;
    }
    .nav-tabs > li.active > a,
    .nav-tabs > li.active > a:hover,
    .nav-tabs > li.active > a:focus {
        color: #059669 !important;
        background: #ecfdf5 !important;
        border-bottom: 2px solid #059669 !important;
    }
    .tab-content {
        border: 1px solid #e2e8f0 !important;
        border-top: none !important;
        border-radius: 0 0 12px 12px !important;
        padding: 24px !important;
        background: #ffffff !important;
    }

    /* Action Buttons */
    .btn-info {
        background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
        border: none !important;
        border-radius: 8px !important;
        font-weight: 600 !important;
        padding: 10px 20px !important;
        color: #ffffff !important;
        box-shadow: 0 4px 6px rgba(5, 150, 105, 0.2) !important;
        transition: all 0.2s ease !important;
    }
    .btn-info:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 6px 12px rgba(5, 150, 105, 0.3) !important;
    }
</style>
<div class="box">
    <div class="box-content">
        <div class="cms-back-header">
            <a href="<?= site_url('cms_admin_panel'); ?>" class="cms-back-btn">
                <i class="fa fa-arrow-left"></i> Back to CMS Admin Panel
            </a>
            <h1 class="cms-page-title"><i class="fa fa-tags"></i> Entity Tag Mapping / Add</h1>
            <p class="cms-page-subtitle">Configure search engine metadata, schema properties, SEO tags and dynamic values.</p>
        </div>

        <?= form_open('entity_mapping/add', array('id' => 'entityMapForm')); ?>
        <div class="row entity-wrap">
            <div class="col-lg-12">
                <div class="row">
                    <div class="col-md-3" style="display:flex; align-items:center; min-height:40px;">
                        <h4 class="entity-title"><i class="fa fa-sliders"></i> Filter Parameters</h4>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Type <span class="text-danger">*</span></label>
                            <select name="entity_master_id" id="entity_master_id" class="form-control">
                                <option value="">Select Type</option>
                                <?php foreach ($entity_types as $type) { ?>
                                    <option value="<?= (int) $type['id']; ?>" data-code="<?= htmlspecialchars($type['entity_code'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <?= htmlspecialchars($type['entity_name'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Entity <span class="text-danger">*</span></label>
                            <select name="entity_id" id="entity_id" class="form-control">
                                <option value="">Select Entity</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group" style="margin-top:24px;">
                            <button type="submit" name="save_mapping" value="1" class="btn btn-info btn-block">Save Tag Values</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php $this->load->view('default/views/entity_mapping/_tag_form', array(
            'tags_by_category' => $tags_by_category,
            'existing_values'  => $existing_values,
        )); ?>

        <?= form_close(); ?>
    </div>
</div>

<?php $this->load->view('default/views/entity_mapping/_tag_scripts'); ?>
