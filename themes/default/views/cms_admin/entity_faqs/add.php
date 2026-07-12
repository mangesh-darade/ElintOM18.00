<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$entity_faqs = isset($entity_faqs) && is_array($entity_faqs) ? $entity_faqs : array();
$entity_faqs_schema_ready = !empty($entity_faqs_schema_ready);
$entity_types = isset($entity_types) && is_array($entity_types) ? $entity_types : array();
$entity_items = isset($entity_items) && is_array($entity_items) ? $entity_items : array();
$selected_master_id = (int) $this->input->post('entity_master_id');
$selected_entity_id = (int) $this->input->post('entity_id');
?>
<div class="box">
    <div class="box-content">
        <?= form_open('cms_admin/entity_faqs/add', array('id' => 'entityFaqForm')); ?>
        <div class="row entity-wrap">
            <div class="col-lg-12">
                <div class="row">
                    <div class="col-md-3" style="display:flex; align-items:center; min-height:40px;">
                        <h4 class="entity-title"><i class="fa fa-sliders"></i> Entity Selection</h4>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Entity Type <span class="text-danger">*</span></label>
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
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Entity Id <span class="text-danger">*</span></label>
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
                </div>
            </div>
        </div>

        <div class="cms-collapsible-panel" id="cmsEntityFaqsCollapsible">
            <div class="cms-collapsible-header-row">
                <button type="button" class="cms-collapsible-header" aria-expanded="false" aria-controls="cmsEntityFaqsCollapsibleBody">
                    <span class="cms-collapsible-chevron" aria-hidden="true"><i class="fa fa-chevron-right"></i></span>
                    <span class="cms-collapsible-header-text">
                        <i class="fa fa-question-circle"></i>
                        Entity FAQs
                    </span>
                    <?php if (count($entity_faqs) > 0) { ?>
                        <span class="cms-collapsible-count badge"><?= count($entity_faqs); ?> row<?= count($entity_faqs) === 1 ? '' : 's'; ?></span>
                    <?php } ?>
                </button>
                <div class="cms-collapsible-header-actions">
                    <button type="button" id="btnCmsAddEntityFaqRow" class="btn btn-default btn-sm">
                        <i class="fa fa-plus"></i> Add field
                    </button>
                    <button type="submit" name="save_entity_faqs" value="1" class="btn btn-primary btn-sm">
                        <i class="fa fa-save"></i> Save FAQs
                    </button>
                </div>
            </div>
            <div class="cms-collapsible-body" id="cmsEntityFaqsCollapsibleBody">
                <?php $this->load->view('default/views/cms_admin/entity_faqs/_faq_form', array(
                    'entity_faqs'              => $entity_faqs,
                    'entity_faqs_schema_ready' => $entity_faqs_schema_ready,
                )); ?>
            </div>
        </div>

        <?= form_close(); ?>
    </div>
</div>

<?php $this->load->view('default/views/cms_admin/entity_faqs/_faq_scripts'); ?>
