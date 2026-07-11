<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!-- Required CSS & JS -->
<script type="text/javascript" src="<?= $assets ?>pos/js/customer_family_relation.js?v=20260408_1"></script>
<script type="text/javascript" src="<?= $assets ?>js/customer_add_address_modal.js?v=20260630_2"></script>
<script type="text/javascript" src="<?= $assets ?>pos/js/edit_customer_details.js?v=20260620"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<link rel="stylesheet" href="<?= $assets ?>pos/css/customer_relation.css" type="text/css" />

<!-- Styling -->
<style>
.tab-nav {
    display: flex;
    list-style: none;
    padding-left: 0;
    border-bottom: 2px solid #e0e0e0;
    margin-bottom: 10px;
}

.tab-nav li {
    margin-right: 10px;
}

.tab-button {
    background: none;
    border: none;
    padding: 10px 20px;
    font-weight: bold;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    color: #333;
    transition: all 0.2s ease;
}

.tab-button.active {
    color: #007bff;
    border-bottom: 3px solid #007bff;
    background-color: #f5f5f5;
}

.hidden_div {
    display: none;
}

@media (min-width: 1441px) and (max-width: 1600px) {
    .form-container {
        gap: 3px !important;
        padding: 8px !important;
        /* margin-left: 10px; */
    }

    div#name-error {
        position: absolute;
        margin-top: 54px;
        margin-left: 21rem !important;
    }

    div#event_type-error {
        position: absolute;
        margin-top: 55px;
        margin-left: 46rem !important;
    }

    div#date-error {
        position: absolute;
        margin-top: 57px;
        margin-left: 65rem !important;
    }

    input#personName {
        width: 160px !important;
    }

}
@media (min-width: 1366px) and (max-height: 768px) {
    .form-container {
        gap: 2px !important;
        
    }
    input#personName{
        width: 150px !important;
    }
}
</style>

<!-- Modal Structure -->
<div class="mymodal" id="modal-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true"><i class="fa fa-times-circle"></i></span>
                        <span class="sr-only"><?= lang('close'); ?></span>
                    </button>

                    <ul class="tab-nav sub-tab-nav">
                        <li><button type="button" id="showDivButton3" class="tab-button active showDivButton3">Profile</button></li>
                        <li><button type="button" id="showDivButton5" class="tab-button showDivButton5">Addresses</button></li>
                        <li><button type="button" id="showDivButton4" class="tab-button showDivButton4">Family & Relations</button></li>
                    </ul>
                </div>

                <div class="modal-body profile profileSection" id="profileSection" style="display: block;">
                    <?php $this->load->view($this->theme . 'pos/edit_customer_details', $this->data); ?>
                </div>

                <div class="modal-body family_relation relationSection" id="relationSection" style="display: none;">
                    <?php $this->load->view($this->theme . 'pos/customer_family_relation', $this->data); ?>
                </div>
                <div class="modal-body family_relation addressSection" id="addressSection" style="display: none; top: -16px;">
                    <?php $this->load->view($this->theme . 'pos/customer_addresses', $this->data); ?>
                </div>
            </div>
        </div>
</div>

<!-- Tab Behavior Script -->
<script>
var phone = <?= json_encode($phone); ?>;
customer_details(phone);
setTimeout(function () {
    if (typeof initCrmAddressBook === 'function') {
        initCrmAddressBook();
        renderAddressCards();
    }
    if (typeof getCrmAddressModal === 'function') {
        getCrmAddressModal();
    }
    if (typeof resetDeletedCustomerAddressIds === 'function') {
        resetDeletedCustomerAddressIds();
    }
}, 0);
function crmShowTab(tabKey) {
    var $root = $('.mymodal');
    if (!$root.length) {
        return;
    }
    var $btnProfile = $root.find('.showDivButton3');
    var $btnRelation = $root.find('.showDivButton4');
    var $btnAddress = $root.find('.showDivButton5');
    var $profile = $root.find('.profileSection');
    var $relation = $root.find('.relationSection');
    var $address = $root.find('.addressSection');

    $btnProfile.removeClass('active');
    $btnRelation.removeClass('active');
    $btnAddress.removeClass('active');
    $profile.hide();
    $relation.hide();
    $address.hide();

    if (tabKey === 'relation') {
        $btnRelation.addClass('active');
        $relation.show();
    } else if (tabKey === 'address') {
        $btnAddress.addClass('active');
        $address.show();
        if (typeof refreshCrmAddressesTab === 'function') {
            refreshCrmAddressesTab();
        } else if (typeof initCrmAddressBook === 'function') {
            initCrmAddressBook();
            renderAddressCards();
            if (typeof getCrmAddressModal === 'function') {
                getCrmAddressModal();
            }
        }
    } else {
        $btnProfile.addClass('active');
        $profile.show();
    }
}

// Default tab when modal content is injected via AJAX.
crmShowTab('profile');

// Delegated handlers ensure clicks always work for dynamically loaded modal content.
$(document).off('click.crmTabs', '.showDivButton3');
$(document).off('click.crmTabs', '.showDivButton4');
$(document).off('click.crmTabs', '.showDivButton5');

$(document).on('click.crmTabs', '.showDivButton3', function () {
    crmShowTab('profile');
});
$(document).on('click.crmTabs', '.showDivButton4', function () {
    crmShowTab('relation');
});
$(document).on('click.crmTabs', '.showDivButton5', function () {
    crmShowTab('address');
});
</script>