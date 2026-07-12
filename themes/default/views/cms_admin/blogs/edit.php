<?php defined('BASEPATH') OR exit('No direct script access allowed');
$post = isset($post) && is_array($post) ? $post : array();
$is_new = !empty($is_new);
$schema_ready = !empty($schema_ready);
$id = isset($post['id']) ? (int) $post['id'] : 0;
$imagePreview = '';
$isPublished = isset($post['status']) && strtolower((string) $post['status']) === 'published';
$isActive = !isset($post['is_active']) || !empty($post['is_active']);
$blogContent = '';
$publishedAtInput = '';
if (!empty($post)) {
    $this->load->model('Cms_blogs_model', 'cms_blogs_model');
    $blogContent = $this->cms_blogs_model->admin_blog_content_display($post);
    $publishedAtInput = $this->cms_blogs_model->published_at_input_value(isset($post['published_at']) ? $post['published_at'] : '');
}
if ($publishedAtInput === '' && $is_new) {
    $publishedAtInput = date('Y-m-d');
}
if (!empty($post['image'])) {
    if (!isset($this->cms_blogs_model)) {
        $this->load->model('Cms_blogs_model', 'cms_blogs_model');
    }
    $imagePreview = $this->cms_blogs_model->build_image_url($post['image']);
}
?>

<div class="cms-blogs-edit">
    <?php if (!$schema_ready) { ?>
    <div class="alert alert-warning">Blog table will be created when you save.</div>
    <?php } ?>

    <?php
    $attrib = array('role' => 'form', 'id' => 'form_cms_blog_edit', 'class' => 'cms-blog-edit-form');
    echo form_open_multipart('cms_admin/blogs/' . ($is_new ? 'add' : 'edit/' . $id), $attrib);
    ?>
    <div class="cms-panel-header">
        <div class="cms-panel-header-text">
            <h4 class="cms-section-title"><i class="fa fa-newspaper-o"></i> <?= $is_new ? 'Add Blog Post' : 'Edit Blog Post'; ?></h4>
            <p class="cms-panel-desc">Card fields appear in Blog Grid. <strong>Blog Content</strong> is the full article on the detail page (HTML or plain text).</p>
        </div>
        <div>
            <a href="<?= site_url('cms_admin/blogs'); ?>" class="btn btn-default">Back to list</a>
            <button type="submit" name="save_cms_blog" value="1" class="btn btn-primary"><i class="fa fa-save"></i> Save</button>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="ws-card-box" style="padding:18px;margin-bottom:16px;">
                <div class="form-group">
                    <label for="blog_title">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" id="blog_title" class="form-control" required
                           value="<?= htmlspecialchars((string) (isset($post['title']) ? $post['title'] : ''), ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="form-group">
                    <label for="blog_subtitle">Subtitle</label>
                    <input type="text" name="subtitle" id="blog_subtitle" class="form-control"
                           value="<?= htmlspecialchars((string) (isset($post['subtitle']) ? $post['subtitle'] : ''), ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="form-group">
                    <label for="blog_slug">URL slug</label>
                    <input type="text" name="slug" id="blog_slug" class="form-control" placeholder="auto-from-title"
                           value="<?= htmlspecialchars((string) (isset($post['slug']) ? $post['slug'] : ''), ENT_QUOTES, 'UTF-8'); ?>">
                    <p class="help-block">Storefront: <code>webshop/blog_details/{slug}</code></p>
                </div>
                <div class="form-group">
                    <label for="blog_short_description">Short description (card excerpt)</label>
                    <textarea name="short_description" id="blog_short_description" class="form-control" rows="3"><?= htmlspecialchars((string) (isset($post['short_description']) ? $post['short_description'] : ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="blog_html_content">Blog Content</label>
                    <textarea name="html_content" id="blog_html_content" class="form-control" rows="14" placeholder="Write HTML or plain text"><?= htmlspecialchars($blogContent, ENT_QUOTES, 'UTF-8'); ?></textarea>
                    <p class="help-block">Paste HTML for rich layout, or plain text — both work on the storefront detail page.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="ws-card-box" style="padding:18px;margin-bottom:16px;">
                <div class="form-group cms-blog-toggle-group">
                    <label class="cms-blog-toggle-label">Published</label>
                    <div class="cms-section-toggle-wrap cms-blog-toggle-wrap">
                        <label class="cms-toggle-switch <?= $isPublished ? 'is-on' : 'is-off'; ?>" title="<?= $isPublished ? 'Published on storefront' : 'Saved as draft'; ?>">
                            <input type="checkbox" name="status_published" value="1" class="skip cms-blog-published-toggle"<?= $isPublished ? ' checked' : ''; ?> aria-label="Published">
                            <span class="cms-toggle-slider" aria-hidden="true"></span>
                        </label>
                        <span class="cms-section-status-label cms-blog-toggle-caption<?= $isPublished ? ' is-active' : ' is-inactive'; ?>">
                            <i class="fa fa-check-circle" aria-hidden="true"></i>
                            <span class="cms-blog-toggle-text"><?= $isPublished ? 'Published' : 'Draft'; ?></span>
                        </span>
                    </div>
                </div>
                <div class="form-group cms-blog-toggle-group">
                    <label class="cms-blog-toggle-label">Active</label>
                    <div class="cms-section-toggle-wrap cms-blog-toggle-wrap">
                        <label class="cms-toggle-switch <?= $isActive ? 'is-on' : 'is-off'; ?>" title="<?= $isActive ? 'Visible when published' : 'Hidden on storefront'; ?>">
                            <input type="checkbox" name="is_active" value="1" class="skip cms-blog-active-toggle"<?= $isActive ? ' checked' : ''; ?> aria-label="Active">
                            <span class="cms-toggle-slider" aria-hidden="true"></span>
                        </label>
                        <span class="cms-section-status-label cms-blog-toggle-caption<?= $isActive ? ' is-active' : ' is-inactive'; ?>">
                            <i class="fa fa-eye" aria-hidden="true"></i>
                            <span class="cms-blog-toggle-text"><?= $isActive ? 'Active' : 'Inactive'; ?></span>
                        </span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="blog_published_at">Publish date</label>
                    <input type="date" name="published_at" id="blog_published_at" class="form-control"
                           value="<?= htmlspecialchars($publishedAtInput, ENT_QUOTES, 'UTF-8'); ?>">
                    <p class="help-block">Shown on blog cards and used for sorting.</p>
                </div>
                <?php
                $this->load->view($this->theme . 'cms_admin/blogs/_category_field', array(
                    'categories'           => isset($categories) ? $categories : array(),
                    'selected_category_id' => isset($post['category_id']) ? (int) $post['category_id'] : 0,
                ));
                ?>
                <div class="form-group">
                    <label for="blog_sort_order">Sort order</label>
                    <input type="number" name="sort_order" id="blog_sort_order" class="form-control" min="0"
                           value="<?= (int) (isset($post['sort_order']) ? $post['sort_order'] : 0); ?>">
                </div>
                <div class="form-group">
                    <label for="blog_button_text">Button text (card / detail)</label>
                    <input type="text" name="button_text" id="blog_button_text" class="form-control"
                           value="<?= htmlspecialchars((string) (isset($post['button_text']) ? $post['button_text'] : 'Read Full Story'), ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <hr>
                <div class="form-group">
                    <label for="blog_image">Image filename / path</label>
                    <input type="text" name="image" id="blog_image" class="form-control" placeholder="cms_blogs/photo.jpg"
                           value="<?= htmlspecialchars((string) (isset($post['image']) ? $post['image'] : ''), ENT_QUOTES, 'UTF-8'); ?>">
                    <p class="help-block">Shown on blog grid cards and the article detail page.</p>
                </div>
                <div class="form-group">
                    <label for="blog_image_file">Or upload image</label>
                    <input type="file" name="image_file" id="blog_image_file" class="form-control" accept="image/*">
                    <p class="help-block">Recommended size: <strong>1024 × 576 px</strong> (16:9). Max upload: <strong>1024 × 768 px</strong>. Formats: JPG, PNG, GIF.</p>
                </div>
                <div class="form-group cms-blog-image-preview-wrap" id="blogImagePreviewWrap"<?= $imagePreview === '' ? ' style="display:none;"' : ''; ?>>
                    <label class="control-label">Image preview</label>
                    <img src="<?= htmlspecialchars($imagePreview, ENT_QUOTES, 'UTF-8'); ?>" alt="" id="blogImagePreview" class="cms-blog-image-preview">
                </div>
            </div>
        </div>
    </div>
    <?= form_close(); ?>

    <?php
    $this->load->view($this->theme . 'cms_admin/blogs/_category_modal', array(
        'blog_id' => $id,
    ));
    ?>
</div>

<script type="text/javascript">
(function ($) {
    function syncBlogToggle($input) {
        var $label = $input.closest('.cms-toggle-switch');
        var on = $input.is(':checked');
        $label.toggleClass('is-on', on).toggleClass('is-off', !on);
        var $wrap = $input.closest('.cms-blog-toggle-wrap');
        var $caption = $wrap.find('.cms-blog-toggle-caption');
        var $text = $wrap.find('.cms-blog-toggle-text');
        $caption.toggleClass('is-active', on).toggleClass('is-inactive', !on);
        if ($input.hasClass('cms-blog-published-toggle')) {
            $text.text(on ? 'Published' : 'Draft');
        } else if ($input.hasClass('cms-blog-active-toggle')) {
            $text.text(on ? 'Active' : 'Inactive');
        }
    }
    $('.cms-blog-published-toggle, .cms-blog-active-toggle').on('change', function () {
        syncBlogToggle($(this));
    });

    $('#blog_image_file').on('change', function () {
        var file = this.files && this.files[0];
        if (!file || !/^image\//i.test(file.type)) {
            return;
        }
        var reader = new FileReader();
        reader.onload = function (e) {
            $('#blogImagePreview').attr('src', e.target.result);
            $('#blogImagePreviewWrap').show();
        };
        reader.readAsDataURL(file);
    });
}(jQuery));
</script>
<style>
.cms-blog-image-preview {
    display: block;
    max-width: 100%;
    max-height: 220px;
    width: auto;
    height: auto;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    object-fit: contain;
    background: #f8fafc;
}
</style>
