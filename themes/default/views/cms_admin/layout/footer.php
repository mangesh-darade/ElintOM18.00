<?php defined('BASEPATH') OR exit('No direct script access allowed');
$cms_assets = isset($cms_assets) ? $cms_assets : base_url('themes/default/assets/cms_admin/');
$cms_list_datatable = !empty($cms_list_datatable);
$cms_section = isset($cms_section) ? $cms_section : '';
$cms_layout_builder = !empty($cms_layout_builder);
$cms_erp_embed = !empty($cms_erp_embed);
?>
        </div><!-- .cms-content -->
        <footer class="cms-app-footer">
            <span>&copy; <?= date('Y') ?> <?= htmlspecialchars($Settings->site_name, ENT_QUOTES, 'UTF-8') ?></span>
            <span class="cms-app-footer-tag">CMS Admin</span>
        </footer>
    </main>
<?php if ($cms_layout_builder) { echo $this->load->view($this->theme . 'cms_admin/layout/header_lb_sidebar', get_defined_vars(), true); } ?>
</div><!-- .cms-shell -->
<?php
if (isset($Settings) && is_object($Settings)) {
    unset(
        $Settings->setting_id,
        $Settings->smtp_user,
        $Settings->smtp_pass,
        $Settings->smtp_port,
        $Settings->update,
        $Settings->reg_ver,
        $Settings->allow_reg,
        $Settings->default_email,
        $Settings->mmode,
        $Settings->timezone,
        $Settings->restrict_calendar,
        $Settings->restrict_user,
        $Settings->auto_reg,
        $Settings->reg_notification,
        $Settings->protocol,
        $Settings->mailpath,
        $Settings->smtp_crypto,
        $Settings->corn,
        $Settings->customer_group,
        $Settings->envato_username,
        $Settings->purchase_code
    );
}
$catalog_config = null;
$cms_enhancements = !empty($cms_enhancements);
$product_modal_config = null;
if ($cms_section === 'catalog') {
    $catalog_config = array(
        'manageUrl' => site_url('cms_admin/catalog'),
        'ajaxUrl'   => site_url('cms_admin/catalog/ajax'),
        'csrfName'  => $this->security->get_csrf_token_name(),
        'csrfHash'  => $this->security->get_csrf_hash(),
    );
}
$catalog_erp_modal = ($cms_enhancements && in_array($cms_section, array('catalog', 'prices'), true));
if ($cms_enhancements && !$catalog_erp_modal) {
    $product_modal_config = array(
        'detailsUrl' => site_url('cms_admin/catalog/getProductDetailsAjax/0'),
    );
}
$prices_config = null;
if ($cms_section === 'prices' && $cms_enhancements) {
    $prices_config = array('filterUrl' => site_url('cms_admin/prices/filter_products'));
}
?>
<script type="text/javascript">
    var site = <?= json_encode(array('base_url' => base_url(), 'settings' => $Settings, 'dateFormats' => $dateFormats)) ?>;
    var lang = {
        r_u_sure: <?= json_encode(lang('r_u_sure')) ?>
    };
    window.CmsAdmin = window.CmsAdmin || {};
    window.CmsAdmin.config = {
        csrfName: <?= json_encode($this->security->get_csrf_token_name()) ?>,
        csrfHash: <?= json_encode($this->security->get_csrf_hash()) ?>,
        catalog: <?= $catalog_config !== null ? json_encode($catalog_config) : 'null' ?>,
        prices: <?= $prices_config !== null ? json_encode($prices_config) : 'null' ?>,
        productModal: <?= $product_modal_config !== null ? json_encode($product_modal_config) : 'null' ?>
    };
</script>
<?php if (!empty($catalog_erp_modal)) { ?>
<?php $this->load->view($this->theme . 'cms_admin/layout/_erp_modal_shell'); ?>
<?php } elseif ($product_modal_config !== null) { ?>
<?php $this->load->view($this->theme . 'cms_admin/layout/_product_modal_shell'); ?>
<?php } ?>
<?php if (!empty($cms_layout_builder)) { ?>
<script src="<?= $assets ?>js/jquery-2.0.3.min.js"></script>
<script src="<?= $assets ?>js/jquery-migrate-1.2.1.min.js"></script>
<script src="<?= $assets ?>js/bootstrap.min.js"></script>
<script src="<?= $assets ?>js/jquery-ui.min.js"></script>
<script src="<?= cms_admin_asset_url('js/core-shim.js', $cms_assets) ?>"></script>
<script src="<?= cms_admin_asset_url('js/app.js', $cms_assets) ?>"></script>
<?php } else { ?>
<script src="<?= $assets ?>js/bootstrap.min.js"></script>
<?php if ($cms_erp_embed) { ?>
<script src="<?= $assets ?>js/jquery.dataTables.min.js"></script>
<?php } ?>
<?php if (!empty($simple_datatable)) { ?>
<script src="<?= $assets ?>js/datatable_column/jquery.dataTables.min.js"></script>
<script src="<?= $assets ?>js/datatable_column/dataTables.bootstrap.min.js"></script>
<?php } elseif (empty($cms_erp_embed)) { ?>
<script src="<?= $assets ?>js/jquery.dataTables.min.js"></script>
<?php } ?>
<script src="<?= $assets ?>js/select2.min.js"></script>
<script src="<?= $assets ?>js/jquery-ui.min.js"></script>
<script src="<?= $assets ?>js/bootstrapValidator.min.js"></script>
<script src="<?= $assets ?>js/custom.js"></script>
<script src="<?= $assets ?>js/jquery.calculator.min.js"></script>
<script src="<?= $assets ?>js/perfect-scrollbar.min.js"></script>
<script src="<?= cms_admin_asset_url('js/core-shim.js', $cms_assets) ?>"></script>
<script src="<?= $assets ?>js/core.js"></script>
<script src="<?= cms_admin_asset_url('js/cms-native-select.js', $cms_assets) ?>"></script>
<script src="<?= cms_admin_asset_url('js/app.js', $cms_assets) ?>"></script>
<?php } ?>
<?php if ($cms_section === 'catalog') { ?>
<script src="<?= cms_admin_asset_url('js/catalog.js', $cms_assets) ?>"></script>
<?php } ?>
<?php if ($cms_enhancements) { ?>
<?php if (empty($catalog_erp_modal)) { ?>
<script src="<?= cms_admin_asset_url('js/cms-product-modal.js', $cms_assets) ?>"></script>
<?php } ?>
<script src="<?= cms_admin_asset_url('js/cms-catalog-ui.js', $cms_assets) ?>"></script>
<script src="<?= cms_admin_asset_url('js/cms-datatable-grids.js', $cms_assets) ?>"></script>
<?php } ?>
<?php if ($cms_section === 'prices' && $cms_enhancements) { ?>
<script src="<?= cms_admin_asset_url('js/cms-prices-enhancements.js', $cms_assets) ?>"></script>
<?php } ?>
<?php if ($cms_list_datatable) { ?>
<script src="<?= cms_admin_asset_url('js/lists.js', $cms_assets) ?>"></script>
<?php } ?>
<?php if ($cms_section === 'leads' && $cms_erp_embed) { ?>
<script src="<?= cms_admin_asset_url('js/cms-leads-embed.js', $cms_assets) ?>"></script>
<?php } ?>
<?php if ($cms_section === 'pages' && $this->router->fetch_method() === 'index') { ?>
<script src="<?= cms_admin_asset_url('js/pages-index.js', $cms_assets) ?>"></script>
<?php } ?>
<?php if ($cms_section === 'pages' && $this->router->fetch_method() === 'edit') { ?>
<script>window.CmsAdmin=window.CmsAdmin||{};window.CmsAdmin.config=Object.assign(window.CmsAdmin.config||{},{pageTagsFormUrl:<?= json_encode(site_url('cms_admin/pages/tags_form_partial')); ?>});</script>
<script src="<?= cms_admin_asset_url('js/pages-section-editor.js', $cms_assets) ?>"></script>
<script src="<?= cms_admin_asset_url('js/pages-edit.js', $cms_assets) ?>"></script>
<script src="<?= cms_admin_asset_url('js/pages-page-faqs.js', $cms_assets) ?>"></script>
<script src="<?= cms_admin_asset_url('js/cms-tag-form-scope.js', $cms_assets) ?>"></script>
<script src="<?= cms_admin_asset_url('js/page-tag-import.js', $cms_assets) ?>"></script>
<?php } ?>
<?php if ($cms_section === 'tags_master') { ?>
<script>window.CmsAdmin=window.CmsAdmin||{};window.CmsAdmin.config=Object.assign(window.CmsAdmin.config||{},{tagsMasterSaveUrl:<?= json_encode(site_url('cms_admin/tags_master/ajax_update')); ?>});</script>
<script src="<?= cms_admin_asset_url('js/tags-master.js', $cms_assets) ?>"></script>
<?php } ?>
<?php if ($cms_section === 'entity_tags' && in_array($this->router->fetch_method(), array('add', 'edit'), true)) { ?>
<script>window.CmsAdmin=window.CmsAdmin||{};window.CmsAdmin.config=Object.assign(window.CmsAdmin.config||{},{entityTagsFormUrl:<?= json_encode(site_url('cms_admin/entity_tags/tags_form_partial')); ?>,entityFaqsFormUrl:<?= json_encode(site_url('cms_admin/entity_tags/faqs_form_partial')); ?>});</script>
<script src="<?= cms_admin_asset_url('js/cms-collapsible.js', $cms_assets) ?>"></script>
<script src="<?= cms_admin_asset_url('js/cms-tag-form-scope.js', $cms_assets) ?>"></script>
<script src="<?= cms_admin_asset_url('js/entity-faqs.js', $cms_assets) ?>"></script>
<script src="<?= cms_admin_asset_url('js/entity-tags-faqs.js', $cms_assets) ?>"></script>
<script src="<?= cms_admin_asset_url('js/page-tag-import.js', $cms_assets) ?>"></script>
<?php } ?>
<?php if ($cms_section === 'form_templates') { ?>
<link href="<?= cms_admin_asset_url('css/form-templates.css', $cms_assets) ?>" rel="stylesheet"/>
<link href="<?= cms_admin_asset_url('css/contact-form-phone.css', $cms_assets) ?>" rel="stylesheet"/>
<script src="<?= cms_admin_asset_url('js/contact-form-phone.js', $cms_assets) ?>"></script>
<script src="<?= cms_admin_asset_url('js/contact-form-builder.js', $cms_assets) ?>"></script>
<script src="<?= cms_admin_asset_url('js/form-template-builder.js', $cms_assets) ?>"></script>
<script src="<?= cms_admin_asset_url('js/form-template-embed.js', $cms_assets) ?>"></script>
<?php } ?>
<?php if ($cms_section === 'guide') { ?>
<link href="<?= cms_admin_asset_url('css/guide.css', $cms_assets) ?>" rel="stylesheet"/>
<?php } ?>
<?php if ($cms_section === 'media') { ?>
<link href="<?= cms_admin_asset_url('css/media-library.css', $cms_assets) ?>" rel="stylesheet"/>
<?php
$this->load->helper('cms_media');
$cms_customer_assets = isset($Customer_assets) ? $Customer_assets : (isset($this->Customer_assets) ? $this->Customer_assets : 'localhost');
$this->load->view($this->theme . 'cms_admin/_partials/media_picker_modal', array(
    'presets'     => cms_media_presets(),
    'upload_base' => cms_media_uploads_base_url($cms_customer_assets),
    'mode'        => 'embed',
));
?>
<script src="<?= cms_admin_asset_url('js/media-library.js', $cms_assets) ?>"></script>
<?php } ?>
<?php if ($cms_section === 'pages' && $this->router->fetch_method() === 'edit') { ?>
<link href="<?= cms_admin_asset_url('css/media-library.css', $cms_assets) ?>" rel="stylesheet"/>
<?php
$this->load->helper('cms_media');
$cms_customer_assets = isset($Customer_assets) ? $Customer_assets : (isset($this->Customer_assets) ? $this->Customer_assets : 'localhost');
$this->load->view($this->theme . 'cms_admin/_partials/media_picker_modal', array(
    'presets'     => cms_media_presets(),
    'upload_base' => cms_media_uploads_base_url($cms_customer_assets),
    'mode'        => 'embed',
));
?>
<script src="<?= cms_admin_asset_url('js/media-library.js', $cms_assets) ?>"></script>
<?php } ?>
<?php if (!empty($cms_header_designs_editor) || !empty($cms_header_designs)) { ?>
<script src="<?= cms_admin_asset_url('js/header-designs.js', $cms_assets) ?>"></script>
<?php } ?>
<?php if (!empty($cms_footer_designs_editor) || !empty($cms_footer_designs)) { ?>
<script src="<?= cms_admin_asset_url('js/footer-designs.js', $cms_assets) ?>"></script>
<?php } ?>
<?php if (!empty($cms_layout_builder)) { ?>
<script defer src="<?= cms_admin_asset_url('js/layout-builder.js', $cms_assets) ?>"></script>
<link href="<?= cms_admin_asset_url('css/media-library.css', $cms_assets) ?>" rel="stylesheet"/>
<?php
$this->load->helper('cms_media');
$cms_customer_assets = isset($Customer_assets) ? $Customer_assets : (isset($this->Customer_assets) ? $this->Customer_assets : 'localhost');
$this->load->view($this->theme . 'cms_admin/_partials/media_picker_modal', array(
    'presets'     => cms_media_presets(),
    'upload_base' => cms_media_uploads_base_url($cms_customer_assets),
    'mode'        => 'embed',
));
?>
<script src="<?= cms_admin_asset_url('js/media-library.js', $cms_assets) ?>"></script>
<?php } ?>
</body>
</html>
