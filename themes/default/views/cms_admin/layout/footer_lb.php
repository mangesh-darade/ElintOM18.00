<?php defined('BASEPATH') OR exit('No direct script access allowed');
$cms_assets = isset($cms_assets) ? $cms_assets : base_url('themes/default/assets/cms_admin/');
$assets = isset($assets) ? $assets : base_url('themes/default/assets/');
$Settings = isset($Settings) ? $Settings : null;
$cms_section = isset($cms_section) ? $cms_section : 'layout_builder';
?>
        </div><!-- .cms-content -->
    </main>
<?php echo $this->load->view($this->theme . 'cms_admin/layout/header_lb_sidebar', compact('Settings', 'cms_section'), true); ?>
</div><!-- .cms-shell -->
<script type="text/javascript">
    window.CmsAdmin = window.CmsAdmin || {};
    window.CmsAdmin.config = {
        csrfName: <?= json_encode($this->security->get_csrf_token_name()) ?>,
        csrfHash: <?= json_encode($this->security->get_csrf_hash()) ?>,
        catalog: null,
        prices: null,
        productModal: null
    };
</script>
<script src="<?= $assets ?>js/jquery-2.0.3.min.js"></script>
<script src="<?= $assets ?>js/jquery-migrate-1.2.1.min.js"></script>
<script src="<?= $assets ?>js/bootstrap.min.js"></script>
<script src="<?= $assets ?>js/jquery-ui.min.js"></script>
<script src="<?= cms_admin_asset_url('js/core-shim.js', $cms_assets) ?>"></script>
<script src="<?= cms_admin_asset_url('js/app.js', $cms_assets) ?>"></script>
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
</body>
</html>
