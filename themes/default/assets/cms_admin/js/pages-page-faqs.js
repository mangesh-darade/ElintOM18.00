/**
 * CMS page edit — per-page FAQ rows (add field / remove / reindex).
 */
(function ($) {
    'use strict';

    function nextFaqRowIndex() {
        var max = -1;
        $('#cmsPageFaqsBody .cms-page-faq-row').each(function () {
            var idx = parseInt($(this).attr('data-row-index'), 10);
            if (!isNaN(idx) && idx > max) {
                max = idx;
            }
        });
        return max + 1;
    }

    function reindexFaqRows() {
        $('#cmsPageFaqsBody .cms-page-faq-row').each(function (rowIdx) {
            var $row = $(this);
            $row.attr('data-row-index', rowIdx);
            $row.find('.cms-page-faq-category').attr('name', 'page_faqs[' + rowIdx + '][category]');
            $row.find('.cms-page-faq-question').attr('name', 'page_faqs[' + rowIdx + '][question]');
            $row.find('.cms-page-faq-answer').attr('name', 'page_faqs[' + rowIdx + '][answer]');
            $row.find('.cms-page-faq-id').attr('name', 'page_faqs[' + rowIdx + '][id]');
        });
    }

    function addFaqRow() {
        var $tpl = $('#cmsPageFaqRowTemplate');
        var $body = $('#cmsPageFaqsBody');
        if (!$tpl.length || !$body.length) {
            return;
        }
        var index = nextFaqRowIndex();
        var html = $tpl.html().replace(/__INDEX__/g, String(index));
        $body.append(html);
        reindexFaqRows();
        var $newRow = $body.find('.cms-page-faq-row').last();
        $newRow.find('.cms-page-faq-question').focus();
    }

    function removeFaqRow($btn) {
        var $body = $('#cmsPageFaqsBody');
        var $row = $btn.closest('.cms-page-faq-row');
        if (!$row.length || !$body.length) {
            return;
        }
        if ($body.find('.cms-page-faq-row').length <= 1) {
            $row.find('input[type="text"], textarea').val('');
            $row.find('.cms-page-faq-id').val('');
            return;
        }
        $row.remove();
        reindexFaqRows();
    }

    function expandPageFaqsPanel(scrollIntoView) {
        var $sec = $('#cmsPeSectionFaqs');
        if (!$sec.length) {
            return;
        }
        $('#cmsPeSectionFaqsWrap').removeClass('is-hidden');
        $sec.addClass('is-expanded cms-pe-collapsible--highlight');
        $sec.find('.cms-pe-collapsible__toggle').first().attr('aria-expanded', 'true');
        window.setTimeout(function () {
            $sec.removeClass('cms-pe-collapsible--highlight');
        }, 2200);
        if (scrollIntoView) {
            var top = Math.max(0, $sec.offset().top - 80);
            $('html, body').animate({ scrollTop: top }, 280);
        }
    }

    function hidePageFaqsPanel() {
        var $sec = $('#cmsPeSectionFaqs');
        $sec.removeClass('is-expanded cms-pe-collapsible--highlight');
        $sec.find('.cms-pe-collapsible__toggle').first().attr('aria-expanded', 'false');
        $('#cmsPeSectionFaqsWrap').addClass('is-hidden');
    }

    function isFaqSectionType(sectionType) {
        sectionType = String(sectionType || '').toLowerCase();
        return sectionType === 'page_faq' || sectionType === 'faq_accordion' || sectionType === 'faq';
    }

    window.CmsPageFaqs = {
        expandPanel: expandPageFaqsPanel,
        hidePanel: hidePageFaqsPanel,
        isFaqSectionType: isFaqSectionType
    };

    $(function () {
        $('#btnCmsAddFaqRow').on('click', function (e) {
            e.preventDefault();
            addFaqRow();
        });

        $(document).on('click', '.cms-page-faq-remove', function (e) {
            e.preventDefault();
            removeFaqRow($(this));
        });

        $('#form_save_cms_page_faqs').on('submit', function () {
            reindexFaqRows();
        });
    });
})(jQuery);
