<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$entity_faqs = isset($entity_faqs) && is_array($entity_faqs) ? $entity_faqs : array();
$entity_faqs_schema_ready = !empty($entity_faqs_schema_ready);
if (empty($entity_faqs)) {
    $entity_faqs = array(array(
        'id'       => 0,
        'category' => '',
        'question' => '',
        'answer'   => '',
    ));
}
?>
<?php if (!$entity_faqs_schema_ready) { ?>
<div class="alert alert-warning cms-entity-faqs-schema-alert">FAQ table is not ready. Save once to create <code>sma_cms_entity_faqs</code> automatically.</div>
<?php } ?>
<p class="cms-panel-desc cms-pe-collapsible__intro">
    FAQs for the selected entity: <strong>Entity Type</strong>, <strong>Entity Id</strong>, <strong>Category</strong>, <strong>Question</strong>, <strong>Answer</strong>
    (<code>sma_cms_entity_faqs</code>). Rows with an empty question are ignored on save.
</p>
<div class="cms-table-wrap cms-page-faqs-table-wrap">
    <table class="table table-bordered cms-table cms-page-faqs-table">
        <thead>
            <tr>
                <th style="width:16%;">Category</th>
                <th style="width:28%;">Question</th>
                <th>Answer</th>
                <th style="width:56px;" aria-label="Remove row"></th>
            </tr>
        </thead>
        <tbody id="cmsEntityFaqsBody">
            <?php foreach ($entity_faqs as $faq_index => $faq) { ?>
                <?php
                $faq_id = isset($faq['id']) ? (int) $faq['id'] : 0;
                $faq_category = isset($faq['category']) ? (string) $faq['category'] : '';
                $faq_question = isset($faq['question']) ? (string) $faq['question'] : '';
                $faq_answer = isset($faq['answer']) ? (string) $faq['answer'] : '';
                ?>
                <tr class="cms-entity-faq-row" data-row-index="<?= (int) $faq_index; ?>">
                    <td>
                        <input type="text"
                               name="entity_faqs[<?= (int) $faq_index; ?>][category]"
                               class="form-control cms-input cms-entity-faq-category"
                               placeholder="e.g. General"
                               value="<?= htmlspecialchars($faq_category, ENT_QUOTES, 'UTF-8'); ?>">
                    </td>
                    <td>
                        <input type="text"
                               name="entity_faqs[<?= (int) $faq_index; ?>][question]"
                               class="form-control cms-input cms-entity-faq-question"
                               placeholder="Question"
                               value="<?= htmlspecialchars($faq_question, ENT_QUOTES, 'UTF-8'); ?>">
                    </td>
                    <td>
                        <textarea name="entity_faqs[<?= (int) $faq_index; ?>][answer]"
                                  class="form-control cms-input cms-entity-faq-answer"
                                  rows="2"
                                  placeholder="Answer"><?= htmlspecialchars($faq_answer, ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </td>
                    <td class="cms-page-faq-actions">
                        <input type="hidden"
                               name="entity_faqs[<?= (int) $faq_index; ?>][id]"
                               class="cms-entity-faq-id"
                               value="<?= $faq_id > 0 ? (int) $faq_id : ''; ?>">
                        <button type="button"
                                class="btn btn-xs btn-danger cms-entity-faq-remove"
                                title="Remove row"
                                aria-label="Remove FAQ row">
                            <i class="fa fa-trash-o"></i>
                        </button>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<script type="text/template" id="cmsEntityFaqRowTemplate">
    <tr class="cms-entity-faq-row" data-row-index="__INDEX__">
        <td>
            <input type="text" name="entity_faqs[__INDEX__][category]" class="form-control cms-input cms-entity-faq-category" placeholder="e.g. General" value="">
        </td>
        <td>
            <input type="text" name="entity_faqs[__INDEX__][question]" class="form-control cms-input cms-entity-faq-question" placeholder="Question" value="">
        </td>
        <td>
            <textarea name="entity_faqs[__INDEX__][answer]" class="form-control cms-input cms-entity-faq-answer" rows="2" placeholder="Answer"></textarea>
        </td>
        <td class="cms-page-faq-actions">
            <input type="hidden" name="entity_faqs[__INDEX__][id]" class="cms-entity-faq-id" value="">
            <button type="button" class="btn btn-xs btn-danger cms-entity-faq-remove" title="Remove row" aria-label="Remove FAQ row">
                <i class="fa fa-trash-o"></i>
            </button>
        </td>
    </tr>
</script>
