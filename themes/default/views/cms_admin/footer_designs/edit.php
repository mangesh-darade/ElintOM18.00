<?php defined('BASEPATH') OR exit('No direct script access allowed');

$upload_base = isset($upload_base) ? $upload_base : '';
$preview_url = isset($preview_url) ? $preview_url : '';
$preview_strip = isset($preview_strip_html) ? $preview_strip_html : '';
$preview_ts = time();
?>

<div class="cms-header-designs-page cms-hd-editor cms-fd-editor" data-profile="<?= htmlspecialchars($profile_slug, ENT_QUOTES, 'UTF-8'); ?>"
     data-preview-url="<?= htmlspecialchars($preview_url, ENT_QUOTES, 'UTF-8'); ?>"
     data-preview-data-url="<?= htmlspecialchars(site_url('cms_admin/footer_designs/preview_data/' . rawurlencode($profile_slug)), ENT_QUOTES, 'UTF-8'); ?>">

    <div class="cms-hd-editor-topbar">
        <a href="<?= site_url('cms_admin/footer_designs'); ?>" class="cms-back-btn"><i class="fa fa-arrow-left"></i> All designs</a>
        <div class="cms-hd-editor-topbar-title">
            <h2><?= htmlspecialchars($profile_label, ENT_QUOTES, 'UTF-8'); ?></h2>
            <span class="cms-hd-slug-badge">Page profile ID: <code><?= htmlspecialchars($profile_slug, ENT_QUOTES, 'UTF-8'); ?></code></span>
        </div>
        <div class="cms-hd-editor-topbar-actions">
            <button type="button" class="btn btn-default btn-sm" id="cmsFdRefreshPreview" title="Refresh preview">
                <i class="fa fa-refresh"></i> Refresh
            </button>
            <a class="btn btn-default btn-sm" id="cmsFdOpenPreviewTab" href="<?= htmlspecialchars($preview_url . '?t=' . $preview_ts, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">
                <i class="fa fa-external-link"></i> Open preview
            </a>
            <button type="button" class="btn btn-default btn-sm" id="cmsFdDeviceDesktop" data-width="100%"><i class="fa fa-desktop"></i></button>
            <button type="button" class="btn btn-default btn-sm" id="cmsFdDeviceMobile" data-width="375px"><i class="fa fa-mobile"></i></button>
        </div>
    </div>

    <?php $this->load->view($this->theme . 'cms_admin/_partials/design_workflow_guide', array(
        'design_type' => 'footer',
        'context' => 'edit',
        'profile_slug' => $profile_slug,
        'item_count' => count($items),
    )); ?>

    <div class="cms-hd-customizer">
        <aside class="cms-hd-panel" id="cmsFdPanel">
            <div class="cms-hd-panel-section">
                <button type="button" class="cms-hd-panel-toggle"><i class="fa fa-cog"></i> Advanced: rename &amp; duplicate</button>
                <div class="cms-hd-panel-body">
                    <?= form_open('cms_admin/footer_designs/edit/' . rawurlencode($profile_slug)); ?>
                    <div class="form-group">
                        <label>Display name</label>
                        <input type="text" name="profile_label" class="form-control" value="<?= htmlspecialchars($profile_label, ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <button type="submit" name="save_profile_label" value="1" class="btn btn-primary btn-sm">Save name</button>
                    <?= form_close(); ?>

                    <hr class="cms-hd-divider">
                    <p class="text-muted" style="font-size:12px;margin-bottom:8px;"><i class="fa fa-copy"></i> Duplicate design</p>
                    <?= form_open('cms_admin/footer_designs/edit/' . rawurlencode($profile_slug)); ?>
                    <div class="form-group">
                        <label>New slug</label>
                        <input type="text" name="duplicate_slug" class="form-control" placeholder="e.g. landing-copy" pattern="[a-zA-Z0-9_-]+">
                    </div>
                    <div class="form-group">
                        <label>New name</label>
                        <input type="text" name="duplicate_label" class="form-control" placeholder="Copy of <?= htmlspecialchars($profile_label, ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <button type="submit" name="duplicate_footer_design" value="1" class="btn btn-default btn-sm">Duplicate design</button>
                    <?= form_close(); ?>
                </div>
            </div>

            <div class="cms-hd-panel-section is-open">
                <button type="button" class="cms-hd-panel-toggle"><i class="fa fa-plus-circle"></i> Step 2: Add a piece to this footer</button>
                <div class="cms-hd-panel-body">
                    <?= form_open_multipart('cms_admin/footer_designs/edit/' . rawurlencode($profile_slug), array('id' => 'cmsFdAddItemForm')); ?>
                    <p class="cms-hd-field-help" style="margin-bottom:10px;">Quick start — click a preset, edit the text, then <strong>Add item</strong>:</p>
                    <div class="cms-hd-preset-row cms-hd-presets" data-design="footer">
                        <button type="button" class="btn btn-default btn-xs cms-hd-preset-btn" data-key="footer_tagline" data-label="Tagline" data-sort="10">Tagline</button>
                        <button type="button" class="btn btn-default btn-xs cms-hd-preset-btn" data-key="footer_copyright" data-label="Copyright" data-value="© {year} Your Company" data-sort="200">Copyright</button>
                        <button type="button" class="btn btn-default btn-xs cms-hd-preset-btn" data-key="footer_heading_shop" data-label="Shop column" data-value="Shop" data-sort="30">Column title</button>
                        <button type="button" class="btn btn-default btn-xs cms-hd-preset-btn" data-key="footer_link_1" data-label="Link" data-value="/contact" data-sort="40">Footer link</button>
                    </div>
                    <div class="form-group">
                        <label>Piece type (technical key) <span class="text-danger">*</span></label>
                        <input type="text" name="item_key" id="cmsFdItemKey" class="form-control input-sm" placeholder="e.g. footer_tagline" list="cmsFooterItemKeys">
                        <p class="cms-hd-field-help">Usually keep the preset. Examples: <code>footer_tagline</code>, <code>footer_copyright</code>.</p>
                        <datalist id="cmsFooterItemKeys">
                            <option value="footer_tagline"><option value="footer_copyright">
                            <option value="footer_heading_shop"><option value="footer_heading_support">
                            <option value="footer_link_1"><option value="footer_link_2">
                            <option value="footer_social_facebook"><option value="footer_social_instagram">
                        </datalist>
                    </div>
                    <div class="form-group">
                        <label>Title shown to visitors (optional)</label>
                        <input type="text" name="label" id="cmsFdItemLabel" class="form-control input-sm">
                    </div>
                    <div class="form-group">
                        <label>Content <span class="text-danger">*</span></label>
                        <textarea name="value" id="cmsFdItemValue" class="form-control input-sm" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-xs-6">
                            <label>Order (low = first)</label>
                            <input type="number" name="sort_order" id="cmsFdItemSort" class="form-control input-sm" value="100">
                        </div>
                        <div class="col-xs-6">
                            <label>Icon (optional)</label>
                            <input type="text" name="icons" id="cmsFdItemIcon" class="form-control input-sm" placeholder="facebook">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Or upload image</label>
                        <input type="file" name="media_file" class="form-control input-sm" accept=".jpg,.jpeg,.png,.gif,.webp">
                    </div>
                    <label class="cms-storefront-checkbox"><input type="checkbox" name="is_active" value="1" checked> Show on website</label>
                    <button type="submit" name="save_footer_item" value="1" class="btn btn-success btn-sm btn-block" style="margin-top:10px;">
                        <i class="fa fa-plus"></i> Add item — preview updates on the right
                    </button>
                    <?= form_close(); ?>
                </div>
            </div>

            <div class="cms-hd-panel-section is-open">
                <button type="button" class="cms-hd-panel-toggle"><i class="fa fa-list"></i> Items (<span id="cmsFdItemCount"><?= count($items); ?></span>)</button>
                <div class="cms-hd-panel-body cms-hd-items-list">
                    <?php if (!empty($items)) { ?>
                        <?php foreach ($items as $row) {
                            $fk = isset($row['field_key']) ? (string) $row['field_key'] : '';
                            $val = isset($row['value']) ? (string) $row['value'] : '';
                            $active = !empty($row['is_active']);
                            ?>
                            <div class="cms-hd-item-card<?= $active ? '' : ' is-inactive'; ?>" data-item-id="<?= (int) $row['id']; ?>">
                                <div class="cms-hd-item-card__head">
                                    <code><?= htmlspecialchars($fk, ENT_QUOTES, 'UTF-8'); ?></code>
                                    <div class="cms-hd-item-card__actions">
                                        <button type="button" class="btn btn-xs btn-link cms-hd-edit-item" data-target="#editFtrItem_<?= (int) $row['id']; ?>">
                                            <i class="fa fa-pencil"></i>
                                        </button>
                                        <a class="btn btn-xs btn-link text-danger" href="<?= site_url('cms_admin/footer_designs/delete_item/' . rawurlencode($profile_slug) . '/' . (int) $row['id']); ?>"
                                           onclick="return confirm('Delete?');"><i class="fa fa-trash-o"></i></a>
                                    </div>
                                </div>
                                <p class="cms-hd-item-card__val"><?= htmlspecialchars(function_exists('mb_substr') ? mb_substr($val, 0, 80) : substr($val, 0, 80), ENT_QUOTES, 'UTF-8'); ?></p>
                                <div class="cms-section-toggle-wrap ftr-item-toggle-wrap"
                                     data-toggle-url="<?= htmlspecialchars(site_url('cms_admin/footer_designs/toggle_item_active/' . rawurlencode($profile_slug) . '/' . (int) $row['id']), ENT_QUOTES, 'UTF-8'); ?>">
                                    <label class="cms-toggle-switch <?= $active ? 'is-on' : 'is-off'; ?>">
                                        <input type="checkbox" class="skip ftr-item-active-toggle" <?= $active ? 'checked' : ''; ?>>
                                        <span class="cms-toggle-slider"></span>
                                    </label>
                                    <span class="cms-section-status-label"><?= $active ? 'Visible' : 'Hidden'; ?></span>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <p class="text-muted text-center" style="padding:12px;">No pieces yet.<br>Use <strong>Step 2</strong> (try <strong>Tagline</strong> or <strong>Copyright</strong>).</p>
                    <?php } ?>
                </div>
            </div>

            <?php $this->load->view($this->theme . 'cms_admin/_partials/design_assign_to_page', array(
                'design_type' => 'footer',
                'profile_slug' => $profile_slug,
                'cms_pages_for_assign' => isset($cms_pages_for_assign) ? $cms_pages_for_assign : array(),
                'pages_using_design' => isset($pages_using_design) ? $pages_using_design : array(),
                'item_count' => count($items),
            )); ?>
        </aside>

        <section class="cms-hd-preview-pane">
            <div class="cms-hd-preview-pane__label">
                <i class="fa fa-eye"></i> Live preview
                <span class="text-muted">— footer at bottom of page</span>
            </div>
            <div class="cms-hd-preview-frame-wrap" id="cmsFdPreviewWrap" style="max-width:100%;">
                <?php if (empty($items)) { ?>
                <div class="cms-hd-preview-empty-overlay" id="cmsFdPreviewEmptyHint">
                    <p><strong>Preview is empty</strong> until you add at least one piece on the left.<br>Click <strong>Tagline</strong> or <strong>Copyright</strong>, then <strong>Add item</strong>.</p>
                </div>
                <?php } ?>
                <iframe id="cmsFdPreviewFrame" class="cms-hd-preview-frame" title="Footer design preview"
                    src="<?= htmlspecialchars($preview_url . '?t=' . $preview_ts, ENT_QUOTES, 'UTF-8'); ?>"></iframe>
            </div>
            <div class="cms-hd-preview-fallback" id="cmsFdPreviewFallback" style="display:none;">
                <?= $preview_strip; ?>
            </div>
        </section>
    </div>
</div>

<?php if (!empty($items)) { ?>
    <?php foreach ($items as $row) {
        $val = isset($row['value']) ? $row['value'] : '';
        ?>
        <div class="modal fade cms-hd-item-modal" id="editFtrItem_<?= (int) $row['id']; ?>" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <?= form_open_multipart('cms_admin/footer_designs/edit/' . rawurlencode($profile_slug)); ?>
                    <input type="hidden" name="item_id" value="<?= (int) $row['id']; ?>">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Edit item</h4>
                    </div>
                    <div class="modal-body">
                        <p><code><?= htmlspecialchars(isset($row['field_key']) ? $row['field_key'] : '', ENT_QUOTES, 'UTF-8'); ?></code></p>
                        <div class="form-group">
                            <label>Label</label>
                            <input type="text" name="label" class="form-control" value="<?= htmlspecialchars(isset($row['label']) ? $row['label'] : '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="form-group">
                            <label>Value</label>
                            <textarea name="value" class="form-control" rows="6"><?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <label>Sort</label>
                                <input type="number" name="sort_order" class="form-control" value="<?= (int) (isset($row['sort_order']) ? $row['sort_order'] : 100); ?>">
                            </div>
                            <div class="col-sm-4">
                                <label>Icon</label>
                                <input type="text" name="icons" class="form-control" value="<?= htmlspecialchars(isset($row['icons']) ? $row['icons'] : '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="col-sm-4">
                                <label style="margin-top:24px;"><input type="checkbox" name="is_active" value="1" <?= !empty($row['is_active']) ? 'checked' : ''; ?>> Active</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Replace image</label>
                            <input type="file" name="media_file" class="form-control" accept=".jpg,.jpeg,.png,.gif,.webp">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" name="save_footer_item" value="1" class="btn btn-primary">Save &amp; update preview</button>
                    </div>
                    <?= form_close(); ?>
                </div>
            </div>
        </div>
    <?php } ?>
<?php } ?>
