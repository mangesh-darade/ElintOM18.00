<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$entity_faqs = isset($entity_faqs) && is_array($entity_faqs) ? $entity_faqs : array();
$entity_faqs_schema_ready = !empty($entity_faqs_schema_ready);
$entity_faqs_count = 0;
foreach ($entity_faqs as $faq) {
    if (is_array($faq) && trim((string) (isset($faq['question']) ? $faq['question'] : '')) !== '') {
        $entity_faqs_count++;
    }
}
if (empty($entity_faqs)) {
    $entity_faqs = array(array(
        'id'       => 0,
        'category' => '',
        'question' => '',
        'answer'   => '',
    ));
}
?>
<div class="cms-collapsible-panel" id="cmsEntityFaqsCollapsible">
    <div class="cms-collapsible-header-row">
        <button type="button" class="cms-collapsible-header" aria-expanded="false" aria-controls="cmsEntityFaqsCollapsibleBody">
            <span class="cms-collapsible-chevron" aria-hidden="true"><i class="fa fa-chevron-right"></i></span>
            <span class="cms-collapsible-header-text">
                <i class="fa fa-question-circle"></i>
                Entity FAQs
                <?php if ($entity_faqs_count > 0) { ?>
                    <span class="cms-collapsible-count badge" id="cmsEntityFaqsCountBadge"><?= (int) $entity_faqs_count; ?> row<?= $entity_faqs_count === 1 ? '' : 's'; ?></span>
                <?php } else { ?>
                    <span class="cms-collapsible-count badge" id="cmsEntityFaqsCountBadge" style="display:none;"></span>
                <?php } ?>
            </span>
        </button>
        <div class="cms-collapsible-header-actions">
            <button type="button" id="btnCmsAddEntityFaqRow" class="btn btn-default btn-sm">
                <i class="fa fa-plus"></i> Add field
            </button>
        </div>
    </div>
    <div class="cms-collapsible-body" id="cmsEntityFaqsCollapsibleBody">
        <div id="cmsEntityFaqsPanelInner">
            <?php $this->load->view('default/views/cms_admin/entity_tags/_entity_faqs_fields', array(
                'entity_faqs'              => $entity_faqs,
                'entity_faqs_schema_ready' => $entity_faqs_schema_ready,
            )); ?>
        </div>
    </div>
</div>
