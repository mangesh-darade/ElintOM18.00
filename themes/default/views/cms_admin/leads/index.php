<?php defined('BASEPATH') OR exit('No direct script access allowed');
$erp_lead_data = array(
    'error'                => isset($error) ? $error : null,
    'action'               => isset($action) ? $action : null,
    'statuses'             => isset($statuses) ? $statuses : array(),
    'converted_status_id'  => isset($converted_status_id) ? $converted_status_id : null,
    'assign_users'         => isset($assign_users) ? $assign_users : array(),
    'Owner'                => isset($Owner) ? $Owner : null,
    'Admin'                => isset($Admin) ? $Admin : null,
    'GP'                   => isset($GP) ? $GP : null,
    'Settings'             => isset($Settings) ? $Settings : null,
    'assets'               => isset($assets) ? $assets : base_url('themes/default/assets/'),
);
?>

<div class="cms-erp-embed cms-erp-embed-leads">
    <?php $this->load->view($this->theme . 'leads/index', $erp_lead_data); ?>
</div>
<script>window.CMS_LEADS_EMBED = true;</script>
