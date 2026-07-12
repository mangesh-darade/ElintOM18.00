/**
 * CMS entity FAQs — add / remove / reindex rows.
 */
(function ($) {
    'use strict';

    function nextFaqRowIndex() {
        var max = -1;
        $('#cmsEntityFaqsBody .cms-entity-faq-row').each(function () {
            var idx = parseInt($(this).attr('data-row-index'), 10);
            if (!isNaN(idx) && idx > max) {
                max = idx;
            }
        });
        return max + 1;
    }

    function reindexFaqRows() {
        $('#cmsEntityFaqsBody .cms-entity-faq-row').each(function (rowIdx) {
            var $row = $(this);
            $row.attr('data-row-index', rowIdx);
            $row.find('.cms-entity-faq-category').attr('name', 'entity_faqs[' + rowIdx + '][category]');
            $row.find('.cms-entity-faq-question').attr('name', 'entity_faqs[' + rowIdx + '][question]');
            $row.find('.cms-entity-faq-answer').attr('name', 'entity_faqs[' + rowIdx + '][answer]');
            $row.find('.cms-entity-faq-id').attr('name', 'entity_faqs[' + rowIdx + '][id]');
        });
    }

    function addFaqRow() {
        var $tpl = $('#cmsEntityFaqRowTemplate');
        var $body = $('#cmsEntityFaqsBody');
        if (!$tpl.length || !$body.length) {
            return;
        }
        var index = nextFaqRowIndex();
        var html = $tpl.html().replace(/__INDEX__/g, String(index));
        $body.append(html);
        reindexFaqRows();
        var $newRow = $body.find('.cms-entity-faq-row').last();
        $newRow.find('.cms-entity-faq-question').focus();
    }

    function removeFaqRow($btn) {
        var $body = $('#cmsEntityFaqsBody');
        var $row = $btn.closest('.cms-entity-faq-row');
        if (!$row.length || !$body.length) {
            return;
        }
        if ($body.find('.cms-entity-faq-row').length <= 1) {
            $row.find('input[type="text"], textarea').val('');
            $row.find('.cms-entity-faq-id').val('');
            return;
        }
        $row.remove();
        reindexFaqRows();
    }

    $(function () {
        $('#btnCmsAddEntityFaqRow').on('click', function (e) {
            e.preventDefault();
            addFaqRow();
        });

        $(document).on('click', '.cms-entity-faq-remove', function (e) {
            e.preventDefault();
            removeFaqRow($(this));
        });

        $('#entityFaqForm').on('submit', function () {
            reindexFaqRows();
        });

        $('#entityMapForm').on('submit', function () {
            reindexFaqRows();
        });
    });
}(jQuery));
