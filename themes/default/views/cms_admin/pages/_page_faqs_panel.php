<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$page_faqs = isset($page_faqs) && is_array($page_faqs) ? $page_faqs : array();
$page_faqs_count = count($page_faqs);
$page_faqs_schema_ready = !empty($page_faqs_schema_ready);
$page_faq_edit_url = site_url('cms_admin/pages/edit/' . (int) $page_data['id']);
if (empty($page_faqs)) {
    $page_faqs = array(array(
        'id'       => 0,
        'category' => '',
        'question' => '',
        'answer'   => '',
    ));
}
?>
<div class="row cms-edit-wrap cms-page-faqs-wrap is-hidden" id="cmsPeSectionFaqsWrap">
    <div class="col-lg-12">
        <div class="cms-page-details-card cms-pe-collapsible" id="cmsPeSectionFaqs" data-pe-section="page-faqs">
            <div class="cms-pe-collapsible__bar">
                <button type="button" class="cms-pe-collapsible__toggle" aria-expanded="false" aria-controls="cmsPeSectionFaqsBody">
                    <span class="cms-pe-collapsible__chevron" aria-hidden="true"><i class="fa fa-chevron-right"></i></span>
                    <span class="cms-pe-collapsible__heading">
                        <span class="cms-pe-collapsible__title">
                            <i class="fa fa-question-circle"></i> Page FAQs
                            <?php if ($page_faqs_count > 0) { ?>
                            <span class="cms-pe-collapsible__badge"><?= (int) $page_faqs_count; ?></span>
                            <?php } ?>
                        </span>
                        <span class="cms-pe-collapsible__desc">Add questions and answers for this page (<code>sma_cms_pages_faqs</code>)</span>
                    </span>
                </button>
                <div class="cms-pe-collapsible__actions">
                    <button type="button" id="btnCmsAddFaqRow" class="btn btn-default cms-btn-toolbar">
                        <i class="fa fa-plus"></i> Add field
                    </button>
                    <button type="submit" form="form_save_cms_page_faqs" name="save_cms_page_faqs" value="1" class="btn btn-primary cms-btn-toolbar">
                        <i class="fa fa-save"></i> Save FAQ
                    </button>
                </div>
            </div>
            <div class="cms-pe-collapsible__body" id="cmsPeSectionFaqsBody">
                <?php if (!$page_faqs_schema_ready) { ?>
                <div class="alert alert-warning">FAQ table is not ready. Save once to create <code>sma_cms_pages_faqs</code> automatically.</div>
                <?php } ?>
                <?= form_open($page_faq_edit_url, array('role' => 'form', 'id' => 'form_save_cms_page_faqs', 'class' => 'cms-page-faqs-form')); ?>
                <p class="cms-panel-desc cms-pe-collapsible__intro">
                    Each row is stored with <strong>Page_Id</strong>, <strong>Category</strong>, <strong>Question</strong>, and <strong>Answer</strong>.
                    Rows with an empty question are ignored on save. Removing a row and saving deletes it from this page.
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
                        <tbody id="cmsPageFaqsBody">
                            <?php foreach ($page_faqs as $faq_index => $faq) { ?>
                                <?php
                                $faq_id = isset($faq['id']) ? (int) $faq['id'] : 0;
                                $faq_category = isset($faq['category']) ? (string) $faq['category'] : '';
                                $faq_question = isset($faq['question']) ? (string) $faq['question'] : '';
                                $faq_answer = isset($faq['answer']) ? (string) $faq['answer'] : '';
                                ?>
                                <tr class="cms-page-faq-row" data-row-index="<?= (int) $faq_index; ?>">
                                    <td>
                                        <input type="text"
                                               name="page_faqs[<?= (int) $faq_index; ?>][category]"
                                               class="form-control cms-input cms-page-faq-category"
                                               placeholder="e.g. General"
                                               value="<?= htmlspecialchars($faq_category, ENT_QUOTES, 'UTF-8'); ?>">
                                    </td>
                                    <td>
                                        <input type="text"
                                               name="page_faqs[<?= (int) $faq_index; ?>][question]"
                                               class="form-control cms-input cms-page-faq-question"
                                               placeholder="Question"
                                               value="<?= htmlspecialchars($faq_question, ENT_QUOTES, 'UTF-8'); ?>">
                                    </td>
                                    <td>
                                        <textarea name="page_faqs[<?= (int) $faq_index; ?>][answer]"
                                                  class="form-control cms-input cms-page-faq-answer"
                                                  rows="2"
                                                  placeholder="Answer"><?= htmlspecialchars($faq_answer, ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    </td>
                                    <td class="cms-page-faq-actions">
                                        <input type="hidden"
                                               name="page_faqs[<?= (int) $faq_index; ?>][id]"
                                               class="cms-page-faq-id"
                                               value="<?= $faq_id > 0 ? (int) $faq_id : ''; ?>">
                                        <button type="button"
                                                class="btn btn-xs btn-danger cms-page-faq-remove"
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
                <?= form_close(); ?>
            </div>
        </div>
    </div>
</div>

<script type="text/template" id="cmsPageFaqRowTemplate">
    <tr class="cms-page-faq-row" data-row-index="__INDEX__">
        <td>
            <input type="text" name="page_faqs[__INDEX__][category]" class="form-control cms-input cms-page-faq-category" placeholder="e.g. General" value="">
        </td>
        <td>
            <input type="text" name="page_faqs[__INDEX__][question]" class="form-control cms-input cms-page-faq-question" placeholder="Question" value="">
        </td>
        <td>
            <textarea name="page_faqs[__INDEX__][answer]" class="form-control cms-input cms-page-faq-answer" rows="2" placeholder="Answer"></textarea>
        </td>
        <td class="cms-page-faq-actions">
            <input type="hidden" name="page_faqs[__INDEX__][id]" class="cms-page-faq-id" value="">
            <button type="button" class="btn btn-xs btn-danger cms-page-faq-remove" title="Remove row" aria-label="Remove FAQ row">
                <i class="fa fa-trash-o"></i>
            </button>
        </td>
    </tr>
</script>
