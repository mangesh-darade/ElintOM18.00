<?php defined('BASEPATH') OR exit('No direct script access allowed');
$categories = isset($categories) && is_array($categories) ? $categories : array();
$return_url = isset($return_url) ? (string) $return_url : site_url('cms_admin/blogs/add');
$collapsible = !empty($collapsible);
$add_panel_open = empty($categories);
$panel_expanded = $collapsible ? empty($categories) : true;
$category_count = count($categories);
?>
<div class="cms-blog-categories-panel ws-card-box<?= $collapsible ? ' cms-blog-categories-panel--collapsible' : ''; ?><?= $panel_expanded ? ' is-expanded' : ''; ?>">
    <?php if ($collapsible) { ?>
    <button type="button"
            class="cms-blog-categories-panel__head cms-blog-categories-panel__toggle"
            aria-expanded="<?= $panel_expanded ? 'true' : 'false'; ?>"
            aria-controls="cmsBlogCategoriesBody">
        <span class="cms-blog-categories-panel__chevron" aria-hidden="true"><i class="fa fa-chevron-right"></i></span>
        <span class="cms-blog-categories-panel__title">
            <i class="fa fa-folder-open-o"></i> Blog Categories
        </span>
        <?php if ($category_count > 0) { ?>
        <span class="cms-blog-categories-panel__count"><?= (int) $category_count; ?></span>
        <?php } ?>
    </button>
    <?php } else { ?>
    <div class="cms-blog-categories-panel__head">
        <h4 class="cms-section-title">
            <i class="fa fa-folder-open-o"></i> Blog Categories
        </h4>
    </div>
    <?php } ?>

    <div id="cmsBlogCategoriesBody" class="cms-blog-categories-panel__body"<?= $collapsible && !$panel_expanded ? ' style="display:none;"' : ''; ?>>
        <div id="cmsBlogCategoryAddPanel" class="cms-blog-category-add-panel<?= $add_panel_open ? ' is-open' : ''; ?>"<?= (!$collapsible && !$add_panel_open) ? ' style="display:none;"' : ''; ?>>
            <?= form_open($return_url, array('class' => 'cms-blog-category-add-form')); ?>
            <input type="hidden" name="return_url" value="<?= htmlspecialchars($return_url, ENT_QUOTES, 'UTF-8'); ?>">
            <div class="form-group">
                <label for="blog_category_name">Category name <span class="text-danger">*</span></label>
                <input type="text" name="category_name" id="blog_category_name" class="form-control" placeholder="e.g. Wellness" required>
            </div>
            <div class="form-group">
                <label for="blog_category_slug">Slug <span class="text-muted">(optional)</span></label>
                <input type="text" name="category_slug" id="blog_category_slug" class="form-control" placeholder="auto-from-name">
            </div>
            <button type="submit" name="save_blog_category" value="1" class="btn btn-success btn-sm">
                <i class="fa fa-save"></i> Save category
            </button>
            <?= form_close(); ?>
        </div>

        <?php if (!empty($categories)) { ?>
        <div class="cms-blog-category-list-wrap">
            <p class="cms-panel-desc">All categories</p>
            <div class="cms-table-scroll">
                <table class="table table-condensed table-striped cms-blog-category-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th class="text-center">Active</th>
                            <th class="text-center cms-col-actions"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat) { ?>
                        <tr>
                            <td><?= htmlspecialchars((string) $cat['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><code><?= htmlspecialchars((string) $cat['slug'], ENT_QUOTES, 'UTF-8'); ?></code></td>
                            <td class="text-center"><?= !empty($cat['is_active']) ? 'Yes' : 'No'; ?></td>
                            <td class="text-center cms-col-actions">
                                <?= form_open('cms_admin/blogs/delete_category/' . (int) $cat['id'], array('style' => 'display:inline;', 'onsubmit' => "return confirm('Delete this category? Posts will be uncategorized.');")); ?>
                                <input type="hidden" name="return_url" value="<?= htmlspecialchars($return_url, ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit" class="btn btn-xs btn-danger" title="Delete"><i class="fa fa-trash-o"></i></button>
                                <?= form_close(); ?>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php } ?>
    </div>
</div>

<style>
.cms-blog-categories-panel { padding: 18px; margin-bottom: 16px; }
.cms-blog-categories-panel__head {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 0;
    width: 100%;
    border: 0;
    background: transparent;
    padding: 0;
    text-align: left;
}
.cms-blog-categories-panel__head .cms-section-title { margin: 0; font-size: 15px; flex: 1; }
.cms-blog-categories-panel--collapsible .cms-blog-categories-panel__toggle {
    cursor: pointer;
    user-select: none;
}
.cms-blog-categories-panel--collapsible .cms-blog-categories-panel__toggle:focus {
    outline: 2px solid #93c5fd;
    outline-offset: 2px;
    border-radius: 6px;
}
.cms-blog-categories-panel__chevron {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    flex-shrink: 0;
    color: #64748b;
    transition: transform 0.2s ease;
}
.cms-blog-categories-panel__chevron .fa { font-size: 12px; }
.cms-blog-categories-panel--collapsible.is-expanded .cms-blog-categories-panel__chevron {
    transform: rotate(90deg);
}
.cms-blog-categories-panel__title {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    flex: 1;
    font-size: 15px;
    font-weight: 600;
    color: #0f172a;
}
.cms-blog-categories-panel__count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 22px;
    height: 22px;
    padding: 0 7px;
    border-radius: 999px;
    background: #e2e8f0;
    color: #475569;
    font-size: 12px;
    font-weight: 600;
    line-height: 1;
}
.cms-blog-categories-panel__body { padding-top: 14px; }
.cms-blog-categories-panel--collapsible:not(.is-expanded) .cms-blog-categories-panel__body { display: none; padding-top: 0; }
.cms-blog-category-add-panel {
    padding-bottom: 4px;
    border-bottom: 1px solid #e2e8f0;
    margin-bottom: 14px;
}
.cms-blog-category-add-panel .form-group:last-of-type { margin-bottom: 12px; }
.cms-blog-category-list-wrap { margin-top: 4px; }
.cms-blog-category-list-wrap .cms-panel-desc { margin-bottom: 8px; }
.cms-blog-category-table { margin-bottom: 0; font-size: 13px; }
.cms-blog-category-field-row {
    display: flex;
    gap: 8px;
    align-items: center;
}
.cms-blog-category-field-row .cms-blog-category-select,
.cms-blog-category-field-row .cms-blog-category-empty { flex: 1; min-width: 0; }
.cms-blog-category-field-row .cms-blog-category-empty { font-size: 13px; line-height: 34px; }
.cms-blog-category-field-row .cms-blog-category-add-toggle { flex-shrink: 0; height: 34px; min-width: 34px; padding-left: 10px; padding-right: 10px; }
</style>

<script type="text/javascript">
(function ($) {
    $(document).on('click', '.cms-blog-categories-panel__toggle', function () {
        var $panel = $(this).closest('.cms-blog-categories-panel--collapsible');
        var expanded = !$panel.hasClass('is-expanded');
        $panel.toggleClass('is-expanded', expanded);
        $(this).attr('aria-expanded', expanded ? 'true' : 'false');
        var $body = $panel.find('.cms-blog-categories-panel__body');
        if (expanded) {
            $body.stop(true, true).slideDown(200);
        } else {
            $body.stop(true, true).slideUp(200);
        }
    });
}(jQuery));
</script>
