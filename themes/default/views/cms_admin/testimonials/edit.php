<?php defined('BASEPATH') OR exit('No direct script access allowed');
$post = isset($post) && is_array($post) ? $post : array();
$is_new = !empty($is_new);
$schema_ready = !empty($schema_ready);
$id = isset($post['id']) ? (int) $post['id'] : 0;
$photoPreview = '';
if (!empty($post['photo'])) {
    $this->load->model('cms_testimonials_model');
    $photoPreview = $this->cms_testimonials_model->build_photo_url($post['photo']);
}
?>

<div class="cms-testimonials-edit">
    <?php if (!$schema_ready) { ?>
    <div class="alert alert-warning">Testimonials table will be created when you save.</div>
    <?php } ?>

    <?php
    $attrib = array('role' => 'form', 'id' => 'form_cms_testimonial_edit', 'class' => 'cms-testimonial-edit-form');
    echo form_open_multipart('cms_admin/testimonials/' . ($is_new ? 'add' : 'edit/' . $id), $attrib);
    ?>
    <div class="cms-panel-header">
        <div class="cms-panel-header-text">
            <h4 class="cms-section-title"><i class="fa fa-quote-left"></i> <?= $is_new ? 'Add Testimonial' : 'Edit Testimonial'; ?></h4>
            <p class="cms-panel-desc">Person name, photo, and comments appear on the storefront testimonials grid.</p>
        </div>
        <div>
            <a href="<?= site_url('cms_admin/testimonials'); ?>" class="btn btn-default">Back to list</a>
            <button type="submit" name="save_cms_testimonial" value="1" class="btn btn-primary"><i class="fa fa-save"></i> Save</button>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="ws-card-box" style="padding:18px;margin-bottom:16px;">
                <div class="form-group">
                    <label for="testimonial_person_name">Person Name <span class="text-danger">*</span></label>
                    <input type="text" name="person_name" id="testimonial_person_name" class="form-control" required
                           value="<?= htmlspecialchars((string) (isset($post['person_name']) ? $post['person_name'] : ''), ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="form-group">
                    <label for="testimonial_comments">Comments <span class="text-danger">*</span></label>
                    <textarea name="comments" id="testimonial_comments" class="form-control" rows="8" required><?= htmlspecialchars((string) (isset($post['comments']) ? $post['comments'] : ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                    <p class="help-block">The quote or review text shown on the storefront card.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="ws-card-box" style="padding:18px;margin-bottom:16px;">
                <div class="form-group">
                    <label for="testimonial_status">Status</label>
                    <select name="status" id="testimonial_status" class="form-control">
                        <option value="draft"<?= (isset($post['status']) && $post['status'] === 'draft') ? ' selected' : ''; ?>>Draft</option>
                        <option value="published"<?= (isset($post['status']) && $post['status'] === 'published') ? ' selected' : ''; ?>>Published</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="checkbox-inline">
                        <input type="checkbox" name="is_active" value="1"<?= !isset($post['is_active']) || !empty($post['is_active']) ? ' checked' : ''; ?>> Active
                    </label>
                </div>
                <div class="form-group">
                    <label for="testimonial_sort_order">Sort order</label>
                    <input type="number" name="sort_order" id="testimonial_sort_order" class="form-control" min="0"
                           value="<?= (int) (isset($post['sort_order']) ? $post['sort_order'] : 0); ?>">
                </div>
                <hr>
                <div class="form-group">
                    <label for="testimonial_photo">Photo filename / path</label>
                    <input type="text" name="photo" id="testimonial_photo" class="form-control" placeholder="cms_testimonials/photo.jpg"
                           value="<?= htmlspecialchars((string) (isset($post['photo']) ? $post['photo'] : ''), ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="form-group">
                    <label for="testimonial_photo_file">Or upload photo</label>
                    <input type="file" name="photo_file" id="testimonial_photo_file" class="form-control" accept="image/*">
                </div>
                <?php if ($photoPreview !== '') { ?>
                <div class="form-group">
                    <img src="<?= htmlspecialchars($photoPreview, ENT_QUOTES, 'UTF-8'); ?>" alt="" style="max-width:100%;border-radius:50%;width:120px;height:120px;object-fit:cover;border:1px solid #e2e8f0;">
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <?= form_close(); ?>
</div>
