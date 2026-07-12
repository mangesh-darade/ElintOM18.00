<?php defined('BASEPATH') OR exit('No direct script access allowed');
$blog_id = isset($blog_id) ? (int) $blog_id : 0;
$ajax_save_url = site_url('cms_admin/blogs/ajax_save_category');
?>
<div class="modal fade cms-blog-category-modal" id="cmsBlogCategoryModal" tabindex="-1" role="dialog" aria-labelledby="cmsBlogCategoryModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="cmsBlogCategoryModalTitle"><i class="fa fa-folder-open-o"></i> Add category</h4>
            </div>
            <form id="cmsBlogCategoryModalForm" class="cms-blog-category-modal-form" autocomplete="off" novalidate>
                <div class="modal-body">
                    <div class="alert alert-danger cms-blog-category-modal-error" style="display:none;"></div>
                    <div class="form-group">
                        <label for="modal_blog_category_name">Category name <span class="text-danger">*</span></label>
                        <input type="text" name="category_name" id="modal_blog_category_name" class="form-control" placeholder="e.g. Wellness">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label for="modal_blog_category_slug">Slug <span class="text-muted">(optional)</span></label>
                        <input type="text" name="category_slug" id="modal_blog_category_slug" class="form-control" placeholder="auto-from-name">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.cms-blog-category-modal .modal-title { font-size: 16px; }
.cms-blog-category-modal .modal-title .fa { margin-right: 6px; color: #3b82f6; }
</style>

<script type="text/javascript">
(function ($) {
    var ajaxUrl = <?= json_encode($ajax_save_url); ?>;
    var blogId = <?= (int) $blog_id; ?>;

    function appendCategoryOption(id, name) {
        var $select = $('#blog_category_id');
        if (!$select.length) {
            return;
        }
        var val = String(id);
        if ($select.find('option[value="' + val + '"]').length === 0) {
            $select.append($('<option>', { value: val, text: name }));
        }
        $select.val(val);
    }

    function resetModalForm() {
        var form = document.getElementById('cmsBlogCategoryModalForm');
        if (form && typeof form.reset === 'function') {
            form.reset();
        }
        $('#cmsBlogCategoryModalForm .cms-blog-category-modal-error').hide().text('');
    }

    $('#cmsBlogCategoryModal').on('show.bs.modal', resetModalForm);

    $('#cmsBlogCategoryModal').on('shown.bs.modal', function () {
        $('#modal_blog_category_name').trigger('focus');
    });

    $('#cmsBlogCategoryModalForm').on('submit', function (e) {
        e.preventDefault();
        var $form = $(this);
        var $err = $form.find('.cms-blog-category-modal-error');
        var $btn = $form.find('button[type="submit"]');
        var name = $.trim($('#modal_blog_category_name').val());
        if (name === '') {
            $err.text('Category name is required.').show();
            $('#modal_blog_category_name').trigger('focus');
            return;
        }
        $err.hide();
        $btn.prop('disabled', true);
        var cfg = window.CmsAdmin && window.CmsAdmin.config ? window.CmsAdmin.config : {};
        var payload = {
            blog_id: blogId,
            category_name: name,
            category_slug: $.trim($('#modal_blog_category_slug').val())
        };
        if (cfg.csrfName && cfg.csrfHash) {
            payload[cfg.csrfName] = cfg.csrfHash;
        }
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            dataType: 'json',
            data: payload
        }).done(function (res) {
            if (res && res.csrf_hash && cfg.csrfName) {
                cfg.csrfHash = res.csrf_hash;
                $('input[name="' + cfg.csrfName + '"]').val(res.csrf_hash);
            }
            if (!res || !res.ok) {
                $err.text((res && res.message) ? res.message : 'Could not save category.').show();
                return;
            }
            if (res.category && res.category.id) {
                appendCategoryOption(res.category.id, res.category.name);
            }
            $('#cmsBlogCategoryModal').modal('hide');
        }).fail(function (xhr) {
            var msg = 'Could not save category.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
            }
            $err.text(msg).show();
        }).always(function () {
            $btn.prop('disabled', false);
        });
    });
}(jQuery));
</script>
