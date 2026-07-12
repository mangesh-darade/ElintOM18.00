<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Step 3 — assign header/footer design to a CMS page.
 * @var string $design_type header|footer
 * @var string $profile_slug
 * @var array  $cms_pages_for_assign
 * @var array  $pages_using_design
 * @var int    $item_count
 */
$design_type = isset($design_type) ? (string) $design_type : 'header';
$is_footer = ($design_type === 'footer');
$assign_url = site_url('cms_admin/' . ($is_footer ? 'footer' : 'header') . '_designs/assign_to_page/' . rawurlencode($profile_slug));
$pages = isset($cms_pages_for_assign) && is_array($cms_pages_for_assign) ? $cms_pages_for_assign : array();
$using = isset($pages_using_design) && is_array($pages_using_design) ? $pages_using_design : array();
$item_count = isset($item_count) ? (int) $item_count : 0;
$label_cap = $is_footer ? 'Footer' : 'Header';
?>
<div class="cms-design-assign cms-hd-panel-section is-open">
    <button type="button" class="cms-hd-panel-toggle"><i class="fa fa-link"></i> Step 3: Put this <?= strtolower($label_cap); ?> on a page</button>
    <div class="cms-hd-panel-body">
        <?php if ($item_count === 0) { ?>
        <div class="alert alert-warning" style="margin-bottom:12px;font-size:12px;">
            <strong>Add content first (Step 2).</strong> Assigning now will still work, but the <?= strtolower($label_cap); ?> will look empty on the site until you add pieces.
        </div>
        <?php } ?>

        <?php if (!empty($using)) { ?>
        <p class="cms-hd-field-help" style="margin-bottom:8px;"><strong>Already on these pages:</strong></p>
        <ul class="cms-design-assign__list">
            <?php foreach ($using as $pu) {
                $pid = isset($pu['id']) ? (int) $pu['id'] : 0;
                $pname = isset($pu['page_name']) ? (string) $pu['page_name'] : 'Page';
                ?>
                <li><a href="<?= site_url('cms_admin/pages/edit/' . $pid); ?>"><?= htmlspecialchars($pname, ENT_QUOTES, 'UTF-8'); ?></a></li>
            <?php } ?>
        </ul>
        <hr class="cms-hd-divider">
        <?php } ?>

        <?php if (empty($pages)) { ?>
        <p class="text-muted">No CMS pages found. <a href="<?= site_url('cms_admin/pages'); ?>">Create a page</a> first.</p>
        <?php } else { ?>
        <?= form_open($assign_url, array('class' => 'cms-design-assign__form')); ?>
        <div class="form-group">
            <label>Choose CMS page</label>
            <select name="page_id" class="form-control input-sm" required>
                <option value="">— Select page —</option>
                <?php foreach ($pages as $pg) {
                    $pid = isset($pg['id']) ? (int) $pg['id'] : 0;
                    if ($pid <= 0) {
                        continue;
                    }
                    $pname = isset($pg['page_name']) ? (string) $pg['page_name'] : ('Page #' . $pid);
                    $purl = isset($pg['url']) ? trim((string) $pg['url']) : '';
                    $opt = $pname . ($purl !== '' ? ' (' . $purl . ')' : '');
                    ?>
                    <option value="<?= $pid; ?>"><?= htmlspecialchars($opt, ENT_QUOTES, 'UTF-8'); ?></option>
                <?php } ?>
            </select>
            <p class="cms-hd-field-help">This adds or updates the page’s <strong><?= $label_cap; ?></strong> section to use profile <code><?= htmlspecialchars($profile_slug, ENT_QUOTES, 'UTF-8'); ?></code>.</p>
        </div>
        <button type="submit" class="btn btn-primary btn-sm btn-block">
            <i class="fa fa-check"></i> Assign to selected page
        </button>
        <?= form_close(); ?>
        <?php } ?>

        <p class="cms-hd-field-help" style="margin-top:12px;margin-bottom:0;">
            Manual path: <a href="<?= site_url('cms_admin/pages'); ?>">CMS Pages</a> → Edit page → Add section → <?= $label_cap; ?> → Storefront <?= strtolower($label_cap); ?> profile.
        </p>
    </div>
</div>
