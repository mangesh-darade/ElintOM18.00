<?php defined('BASEPATH') OR exit('No direct script access allowed');
$categories = isset($categories) && is_array($categories) ? $categories : array();
$selected_category_id = isset($selected_category_id) ? (int) $selected_category_id : 0;
?>
<div class="form-group cms-blog-category-field">
    <label for="blog_category_id">Category</label>
    <div class="cms-blog-category-field-row">
        <select name="category_id" id="blog_category_id" class="form-control cms-blog-category-select">
            <option value="">— No category —</option>
            <?php foreach ($categories as $cat) {
                $catId = (int) $cat['id'];
                $catName = (string) $cat['name'];
                ?>
            <option value="<?= $catId; ?>"<?= $selected_category_id === $catId ? ' selected' : ''; ?>>
                <?= htmlspecialchars($catName, ENT_QUOTES, 'UTF-8'); ?>
            </option>
            <?php } ?>
        </select>
        <button type="button"
                class="btn btn-success btn-sm cms-blog-category-modal-open"
                data-toggle="modal"
                data-target="#cmsBlogCategoryModal"
                title="Add category"
                aria-label="Add category">
            <i class="fa fa-plus"></i>
        </button>
    </div>
</div>

<style>
.cms-blog-category-field-row {
    display: flex;
    gap: 8px;
    align-items: center;
}
.cms-blog-category-field-row .cms-blog-category-select { flex: 1; min-width: 0; }
.cms-blog-category-field-row .cms-blog-category-modal-open {
    flex-shrink: 0;
    height: 34px;
    min-width: 34px;
    padding-left: 10px;
    padding-right: 10px;
}
</style>
