<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
/**
 * Entity Tag Mapping — Add New
 * Uses shared partials: _tag_form.php, _tag_scripts.php
 */
$this->load->helper('cms_tags');
$entity_scope_code = isset($entity_scope_code) ? (string) $entity_scope_code : '';
$tags_by_category = cms_group_tags_by_category(!empty($tags_master) ? $tags_master : array());
$existing_values = array();
$entity_types = (isset($entity_types) && is_array($entity_types)) ? $entity_types : array();
$entity_items = (isset($entity_items) && is_array($entity_items)) ? $entity_items : array();
$selected_master_id = (int) $this->input->post('entity_master_id');
$selected_entity_id = (int) $this->input->post('entity_id');
?>
<div class="box">
    <div class="box-content">
        <?= form_open('cms_admin/entity_tags/add', array('id' => 'entityMapForm')); ?>
        <div class="row entity-wrap">
            <div class="col-lg-12">
                <div class="row">
                    <div class="col-md-3" style="display:flex; align-items:center; min-height:40px;">
                        <h4 class="entity-title"><i class="fa fa-sliders"></i> Filter Parameters</h4>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Type <span class="text-danger">*</span></label>
                            <select name="entity_master_id" id="entity_master_id" class="form-control cms-native-select">
                                <option value="">Select Type</option>
                                <?php foreach ($entity_types as $type) {
                                    $type_label = trim((string) (isset($type['entity_name']) ? $type['entity_name'] : ''));
                                    $type_code = trim((string) (isset($type['entity_code']) ? $type['entity_code'] : ''));
                                    if ($type_code !== '') {
                                        $type_label = $type_label !== '' ? ($type_label . ' (' . $type_code . ')') : ucfirst($type_code);
                                    }
                                ?>
                                    <option value="<?= (int) $type['id']; ?>" data-code="<?= htmlspecialchars($type_code, ENT_QUOTES, 'UTF-8'); ?>" <?= ((int) $type['id'] === $selected_master_id) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($type_label, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <?php if (empty($entity_types)) { ?>
                                <p class="help-block text-danger" style="margin-top:6px;">No entity types found. Ensure <code>sma_cms_entities_master</code> has Product/Category rows.</p>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Entity <span class="text-danger">*</span></label>
                            <select name="entity_id" id="entity_id" class="form-control cms-native-select">
                                <option value="">Select Entity</option>
                                <?php foreach ($entity_items as $item) { ?>
                                    <option value="<?= (int) $item['id']; ?>" <?= ((int) $item['id'] === $selected_entity_id) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group" style="margin-top:24px;">
                            <button type="submit" name="save_mapping" value="1" class="btn btn-info btn-block">Save Tag Values &amp; FAQs</button>
                        </div>
                    </div>
                </div>
                <div class="row cms-entity-details-row" id="cmsEntityDetailsRow" style="display:none;">
                    <div class="col-md-12">
                        <div class="cms-collapsible-panel" id="cmsEntityDetailsCollapsible">
                            <button type="button" class="cms-collapsible-header" aria-expanded="false" aria-controls="cmsEntityDetailsCollapsibleBody">
                                <span class="cms-collapsible-chevron" aria-hidden="true"><i class="fa fa-chevron-right"></i></span>
                                <span class="cms-collapsible-header-text">
                                    <i class="fa fa-info-circle"></i>
                                    <span id="cmsEntityDetailsTitleText">Entity Details</span>
                                </span>
                            </button>
                            <div class="cms-collapsible-body" id="cmsEntityDetailsCollapsibleBody">
                                <div id="cmsEntityDetailsPreview" class="cms-entity-product-preview" style="display:none;"></div>
                            </div>
                        </div>
                        <div class="cms-collapsible-panel" id="cmsEntitySchemaCollapsible" style="display:none;">
                            <button type="button" class="cms-collapsible-header" aria-expanded="false" aria-controls="cmsEntitySchemaCollapsibleBody">
                                <span class="cms-collapsible-chevron" aria-hidden="true"><i class="fa fa-chevron-right"></i></span>
                                <span class="cms-collapsible-header-text">
                                    <i class="fa fa-code"></i>
                                    Product Schema (JSON-LD)
                                </span>
                            </button>
                            <div class="cms-collapsible-body" id="cmsEntitySchemaCollapsibleBody">
                                <p class="cms-entity-schema-hint text-muted">Generated from catalog data, or updated when you import a <code>&lt;head&gt;</code> script with Product JSON-LD below.</p>
                                <div id="cmsEntitySchemaPreview" class="cms-entity-schema-preview" style="display:none;"></div>
                            </div>
                        </div>
                        <?php $this->load->view('default/views/cms_admin/entity_tags/_entity_faqs_panel', array(
                            'entity_faqs'              => isset($entity_faqs) ? $entity_faqs : array(),
                            'entity_faqs_schema_ready' => !empty($entity_faqs_schema_ready),
                        )); ?>
                    </div>
                </div>
            </div>
        </div>

        <div id="cmsEntityTagFormWrap">
        <?php $this->load->view('default/views/cms_admin/entity_tags/_tag_form', array(
            'tags_by_category'    => $tags_by_category,
            'existing_values'     => $existing_values,
            'scope_code'          => $entity_scope_code,
            'head_tag_import_url' => site_url('cms_admin/entity_tags/ajax_import_head_tags'),
        )); ?>
        </div>

        <?= form_close(); ?>
    </div>
</div>

<?php $this->load->view('default/views/cms_admin/entity_tags/_tag_scripts'); ?>
<?php if (!empty($cms_enhancements)) { $this->load->view('default/views/cms_admin/entity_tags/_entity_enhancements_scripts'); } ?>
