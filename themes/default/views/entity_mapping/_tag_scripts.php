<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
/**
 * Shared partial: JS for entity mapping forms.
 * Handles dynamic entity loading and form validation.
 */
?>
<script type="text/javascript">
$(document).ready(function () {
    $('#entity_master_id').on('change', function () {
        var id = $(this).val();
        var $entity = $('#entity_id');
        $entity.html('<option value="">Loading...</option>');
        if (!id) {
            $entity.html('<option value="">Select Entity</option>');
            return;
        }
        $.getJSON('<?= site_url('entity_mapping/get_entities_by_type'); ?>', {entity_master_id: id}, function (res) {
            var html = '<option value="">Select Entity</option>';
            if (res && res.length) {
                for (var i = 0; i < res.length; i++) {
                    html += '<option value="' + res[i].id + '">' + $('<div/>').text(res[i].name).html() + '</option>';
                }
            }
            $entity.html(html);
        }).fail(function () {
            $entity.html('<option value="">Failed to load entities</option>');
        });
    });

    $('#entityMapForm').on('submit', function (e) {
        var masterId = $.trim($('#entity_master_id').val() || '');
        var entityId = $.trim($('#entity_id').val() || '');
        if (masterId === '' || entityId === '') {
            e.preventDefault();
            alert('Please select both Type and Entity before saving.');
            return;
        }

        var hasValue = false;
        $('.entity-tag-input').each(function () {
            if ($.trim($(this).val()) !== '') {
                hasValue = true;
                return false;
            }
        });
        if (!hasValue) {
            e.preventDefault();
            alert('At least one tag is required.');
        }
    });
});
</script>
