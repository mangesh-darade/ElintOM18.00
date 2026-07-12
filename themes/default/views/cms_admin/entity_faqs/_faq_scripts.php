<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
$(document).ready(function () {
    function stripEntitySelect2() {
        if (window.CmsNativeSelect && typeof window.CmsNativeSelect.strip === 'function') {
            window.CmsNativeSelect.strip();
            return;
        }
        $('#entity_master_id, #entity_id').each(function () {
            var $sel = $(this);
            if ($sel.data('select2')) {
                $sel.select2('destroy');
            }
        });
    }

    $('#entity_master_id').on('change', function () {
        var id = $(this).val();
        var $entity = $('#entity_id');
        $entity.html('<option value="">Loading...</option>');
        stripEntitySelect2();
        if (!id) {
            $entity.html('<option value="">Select Entity</option>');
            stripEntitySelect2();
            return;
        }
        $.getJSON('<?= site_url('cms_admin/entity_tags/entities_by_type'); ?>', {entity_master_id: id}, function (res) {
            var html = '<option value="">Select Entity</option>';
            if (res && res.length) {
                for (var i = 0; i < res.length; i++) {
                    html += '<option value="' + res[i].id + '">' + $('<div/>').text(res[i].name).html() + '</option>';
                }
            }
            $entity.html(html);
            stripEntitySelect2();
        }).fail(function () {
            $entity.html('<option value="">Failed to load entities</option>');
            stripEntitySelect2();
        });
    });

    $('#entityFaqForm').on('submit', function (e) {
        var masterId = $.trim($('#entity_master_id').val() || '');
        var entityId = $.trim($('#entity_id').val() || '');
        if (masterId === '' || entityId === '') {
            e.preventDefault();
            alert('Please select both Entity Type and Entity Id before saving.');
            return;
        }

        var hasQuestion = false;
        $('.cms-entity-faq-question').each(function () {
            if ($.trim($(this).val()) !== '') {
                hasQuestion = true;
                return false;
            }
        });
        if (!hasQuestion) {
            e.preventDefault();
            alert('At least one FAQ question is required.');
        }
    });
});
</script>
