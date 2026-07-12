<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$form = isset($form) && is_array($form) ? $form : array();
$form_key = isset($form_key) ? (string) $form_key : '';
$fields = isset($form['fields']) && is_array($form['fields']) ? $form['fields'] : array();
$title = isset($form['title']) ? trim((string) $form['title']) : '';
$subtitle = isset($form['subtitle']) ? trim((string) $form['subtitle']) : '';
$button = isset($form['button_text']) ? (string) $form['button_text'] : 'Send Message';
$success = $this->session->flashdata('contact_success');
$errors = $this->session->flashdata('contact_errors');
$showNotice = ((string) $this->input->get('contact_notice', true) === '1');
$contactStatus = strtolower((string) $this->input->get('contact_status', true));
// Cookieless fallback: inside a cross-site iframe third-party cookies are
// blocked, so flashdata never survives the redirect. Rebuild the message
// from the redirect query params instead.
if ($showNotice && empty($success) && $contactStatus === 'success') {
    $first = trim((string) $this->input->get('contact_first', true));
    $first = preg_replace('/[^\p{L}\p{N} \'.-]/u', '', $first);
    $action = strtolower((string) $this->input->get('contact_action', true));
    $greeting = $first !== '' ? 'Thank you, ' . $first . '!' : 'Thank you!';
    $success = ($action === 'updated')
        ? $greeting . ' Your details have been updated successfully. Our team will get in touch with you shortly.'
        : $greeting . ' We have received your details successfully. Our team will review your request and get in touch with you shortly.';
}
if ($showNotice && (empty($errors) || !is_array($errors)) && $contactStatus === 'error') {
    $errors = array('Could not submit form. Please check your details and try again.');
}
$formSubmitted = ($showNotice && !empty($success));
$phone_countries = isset($phone_countries) && is_array($phone_countries) ? $phone_countries : array();
if (empty($phone_countries)) {
    $CI =& get_instance();
    $CI->load->helper('contact_form_phone');
    $phone_countries = contact_form_phone_countries();
}
$form_countries = isset($form_countries) && is_array($form_countries) ? $form_countries : array();
if (empty($form_countries)) {
    if (!isset($CI)) {
        $CI =& get_instance();
    }
    $CI->load->helper(array('contact_form_phone', 'contact_form_country'));
    $form_countries = function_exists('contact_form_country_list') ? contact_form_country_list() : array();
}
$phone_asset_base = base_url('themes/default/assets/cms_admin/');
?>
<!doctype html>
<html lang="en"<?= $formSubmitted ? ' class="is-submitted"' : '' ?>>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
    <?php if (!empty($recaptcha_site_key)) : ?>
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <?php endif; ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($phone_asset_base . 'css/contact-form-phone.css', ENT_QUOTES, 'UTF-8') ?>">
    <style>
        html, body { overflow: hidden; background: transparent; }
        body {
            margin: 0;
            padding: 8px;
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
        }
        .embed-form {
            max-width: 720px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #f1f5f9;
            border-radius: 16px;
            padding: 32px;
            box-sizing: border-box;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.10);
        }
        .embed-form h3 {
            margin: 0 0 6px;
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.01em;
        }
        .embed-form .subtitle {
            margin: 0 0 18px;
            font-size: 13px;
            color: #64748b;
            line-height: 1.5;
        }
        .embed-form label {
            display: block;
            font-size: 12.5px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 6px;
        }
        .embed-form label.embed-label--required::after {
            content: none;
        }
        .embed-form .embed-label-req {
            color: #dc2626;
            font-weight: 700;
        }
        .embed-form input,
        .embed-form select,
        .embed-form textarea {
            width: 100%;
            padding: 11px 14px;
            margin-bottom: 16px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 14px;
            font-family: inherit;
            color: #334155;
            background: #fff;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }
        .embed-form input::placeholder,
        .embed-form textarea::placeholder { color: #9ca3af; }
        .embed-form input:focus,
        .embed-form select:focus,
        .embed-form textarea:focus {
            border-color: #b9860b;
            box-shadow: 0 0 0 3px rgba(185, 134, 11, 0.12);
        }
        .embed-form select {
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpath d='M19 9l-7 7-7-7'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 14px;
            padding-right: 36px;
            color: #334155;
        }
        .embed-form textarea { min-height: 100px; resize: vertical; }
        .embed-form button[type="submit"] {
            width: 100%;
            padding: 14px;
            border: 0;
            border-radius: 6px;
            background: #b9860b;
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: background .2s;
        }
        .embed-form button[type="submit"]:hover { background: #a07409; }
        .embed-form button[type="submit"] .chevron { margin-left: 4px; }
        .embed-form button[type="submit"]:disabled { opacity: 0.85; cursor: default; }
        .embed-form .cf-phone-field {
            margin-bottom: 16px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            background: #fff;
            overflow: visible;
            transition: border-color .2s, box-shadow .2s;
        }
        .embed-form .cf-phone-field:focus-within {
            border-color: #b9860b;
            box-shadow: 0 0 0 3px rgba(185, 134, 11, 0.12);
        }
        .embed-form .cf-phone-number {
            margin-bottom: 0 !important;
            border: none !important;
            border-radius: 0;
            box-shadow: none !important;
            background: transparent;
        }
        .embed-form .cf-phone-number:focus {
            border-color: transparent !important;
            box-shadow: none !important;
        }
        .btn-spinner {
            display: inline-block;
            width: 15px;
            height: 15px;
            border: 2px solid rgba(255, 255, 255, 0.4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: efspin 0.7s linear infinite;
            vertical-align: -3px;
            margin-right: 8px;
        }
        @keyframes efspin { to { transform: rotate(360deg); } }
        .embed-security {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-top: 14px;
            font-size: 11.5px;
            color: #94a3b8;
        }
        .embed-security svg { flex: 0 0 auto; }
        .err { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 10px 12px; border-radius: 8px; margin-bottom: 14px; font-size: 13px; }
        .err ul { margin: 0; padding-left: 18px; }
        .missing { text-align: center; color: #64748b; padding: 24px; background: #fff; border-radius: 16px; border: 1px solid #f1f5f9; }
        .success-state { text-align: center; padding: 40px 24px; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 12px; }
        .success-state__icon { width: 56px; height: 56px; margin: 0 auto 16px; border-radius: 50%; background: #10b981; color: #fff; display: flex; align-items: center; justify-content: center; }
        .success-state__text { margin: 0; color: #065f46; font-size: 15px; line-height: 1.65; font-weight: 500; }
        /* Submitted view: card keeps the full iframe height (= the form's height),
           success message centered inside it — no layout height change. */
        html.is-submitted, html.is-submitted body { height: 100%; }
        .embed-form--submitted {
            min-height: calc(100vh - 16px);
            display: flex;
            flex-direction: column;
        }
        .embed-form--submitted .success-state {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
<?php if (!empty($form_not_found)) { ?>
    <p class="missing">This form is not available. Check that the template is active and the form key is correct.</p>
<?php } else { ?>
<div class="embed-form<?= $formSubmitted ? ' embed-form--submitted' : '' ?>">
    <?php if ($title !== '') { ?>
    <h3><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h3>
    <?php } ?>
    <?php if ($subtitle !== '' && !$formSubmitted) { ?>
        <p class="subtitle"><?= htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8') ?></p>
    <?php } ?>
    <?php if ($formSubmitted) { ?>
        <div class="success-state" role="status">
            <div class="success-state__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="28" height="28"><path d="M20 6L9 17l-5-5"/></svg>
            </div>
            <p class="success-state__text"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    <?php } ?>
    <?php if ($showNotice && $errors && is_array($errors)) { ?>
        <div class="err"><ul><?php foreach ($errors as $e) { ?><li><?= htmlspecialchars((string) $e, ENT_QUOTES, 'UTF-8') ?></li><?php } ?></ul></div>
    <?php } ?>
    <?php if (!$formSubmitted) { ?>
    <form method="post" action="<?= htmlspecialchars(site_url('webshop/contact_us_submit'), ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="<?= htmlspecialchars($this->security->get_csrf_token_name(), ENT_QUOTES, 'UTF-8') ?>" value="<?= htmlspecialchars($this->security->get_csrf_hash(), ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="form_key" value="<?= htmlspecialchars($form_key, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="return_url" value="<?= htmlspecialchars(current_url(), ENT_QUOTES, 'UTF-8') ?>">
        <?php foreach ($fields as $field) {
            if (!is_array($field) || empty($field['name'])) {
                continue;
            }
            $name = preg_replace('/[^a-z0-9_]/', '', strtolower((string) $field['name']));
            if ($name === '') {
                continue;
            }
            $type = isset($field['type']) ? strtolower((string) $field['type']) : 'text';
            if ($type === 'hidden') {
                $hiddenVal = isset($field['value']) ? (string) $field['value'] : '';
                echo '<input type="hidden" name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" value="' . htmlspecialchars($hiddenVal, ENT_QUOTES, 'UTF-8') . '">';
                continue;
            }
            $label = isset($field['label']) ? (string) $field['label'] : ucfirst($name);
            $placeholder = isset($field['placeholder']) ? (string) $field['placeholder'] : '';
            $req = !empty($field['required']) ? ' required' : '';
            $labelClass = 'embed-label' . ($req !== '' ? ' embed-label--required' : '');
            ?>
        <label class="<?= htmlspecialchars($labelClass, ENT_QUOTES, 'UTF-8') ?>" for="ef_<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?><?= $req !== '' ? '<span class="embed-label-req" aria-hidden="true">*</span>' : '' ?></label>
        <?php if ($type === 'textarea') { ?>
        <textarea id="ef_<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>" name="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"<?= $placeholder !== '' ? ' placeholder="' . htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8') . '"' : '' ?><?= $req ?>></textarea>
        <?php } elseif ($type === 'select') { ?>
        <select id="ef_<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>" name="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"<?= $req ?>>
            <option value=""><?= htmlspecialchars($placeholder !== '' ? $placeholder : 'Select...', ENT_QUOTES, 'UTF-8') ?></option>
            <?php
            $options = isset($field['options']) && is_array($field['options']) ? $field['options'] : array();
            foreach ($options as $opt) {
                if (!is_array($opt)) {
                    continue;
                }
                $optVal = isset($opt['value']) ? (string) $opt['value'] : '';
                $optLbl = isset($opt['label']) ? (string) $opt['label'] : $optVal;
                if ($optVal === '') {
                    continue;
                }
                echo '<option value="' . htmlspecialchars($optVal, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($optLbl, ENT_QUOTES, 'UTF-8') . '</option>';
            }
            ?>
        </select>
        <?php } elseif ($type === 'country') { ?>
        <select id="ef_<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>" name="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"<?= $req ?>>
            <option value=""><?= htmlspecialchars($placeholder !== '' ? $placeholder : 'Select country...', ENT_QUOTES, 'UTF-8') ?></option>
            <?php foreach ($form_countries as $countryOpt) {
                if (!is_array($countryOpt)) {
                    continue;
                }
                $optVal = isset($countryOpt['value']) ? (string) $countryOpt['value'] : '';
                $optLbl = isset($countryOpt['label']) ? (string) $countryOpt['label'] : $optVal;
                if ($optVal === '') {
                    continue;
                }
                echo '<option value="' . htmlspecialchars($optVal, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($optLbl, ENT_QUOTES, 'UTF-8') . '</option>';
            } ?>
        </select>
        <?php } elseif ($type === 'date') { ?>
        <input type="date" id="ef_<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>" name="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"<?= $req ?>>
        <?php } elseif ($type === 'tel' && function_exists('contact_form_phone_render_field')) { ?>
        <?= contact_form_phone_render_field($field, $phone_countries, array(
            'id_prefix'   => 'ef_',
            'input_class' => 'cf-phone-number',
            'required'    => !empty($field['required']),
        )) ?>

        <?php } else {
            $inputType = in_array($type, array('email', 'tel'), true) ? $type : 'text';
            ?>
        <input type="<?= htmlspecialchars($inputType, ENT_QUOTES, 'UTF-8') ?>" id="ef_<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>" name="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"<?= $placeholder !== '' ? ' placeholder="' . htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8') . '"' : '' ?><?= $req ?>>
        <?php } ?>
        <?php } ?>

        <?php if (!empty($recaptcha_site_key)) : ?>
            <div class="form-group" style="margin-bottom: 16px;">
                <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars($recaptcha_site_key, ENT_QUOTES, 'UTF-8'); ?>"></div>
            </div>
        <?php else : ?>
            <div class="form-group" style="margin-bottom: 16px;">
                <label style="display: flex; align-items: center; cursor: pointer; gap: 8px;">
                    <input type="checkbox" name="not_robot" id="embed_not_robot" required="required" style="width: 20px; height: 20px; margin-bottom: 0; cursor: pointer;">
                    <span style="font-weight: 500; font-size: 13.5px; color: #334155;">I am not a robot</span>
                </label>
            </div>
        <?php endif; ?>

        <button type="submit"><?= htmlspecialchars($button, ENT_QUOTES, 'UTF-8') ?> <span class="chevron">›</span></button>
    </form>
    <div class="embed-security">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="13" height="13" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        <span>Your information is secure and confidential.</span>
    </div>
    <?php } ?>
</div>
<?php } ?>
<script>
(function () {
    "use strict";
    var formSubmitted = <?= $formSubmitted ? 'true' : 'false' ?>;

    // Loading spinner on the submit button while the form is being saved.
    var form = document.querySelector(".embed-form form");
    if (form) {
        form.addEventListener("submit", function (e) {
            var checkbox = document.getElementById("embed_not_robot");
            if (checkbox && !checkbox.checked) {
                e.preventDefault();
                alert("Please confirm that you are not a robot.");
                return;
            }
            var hasRecaptcha = document.querySelector(".g-recaptcha");
            if (hasRecaptcha && window.grecaptcha && !grecaptcha.getResponse()) {
                e.preventDefault();
                alert("Please complete the reCAPTCHA verification.");
                return;
            }
            var btn = form.querySelector('button[type="submit"]');
            if (btn && !btn.disabled) {
                btn.disabled = true;
                btn.innerHTML = '<span class="btn-spinner" aria-hidden="true"></span>Sending...';
            }
        });
    }

    if (!window.parent || window.parent === window) { return; }

    // After a successful submit, keep the iframe at the height the form had —
    // do not report the (smaller) success-card height to the parent page.
    if (formSubmitted) { return; }

    var lastHeight = 0;
    function postHeight() {
        var height = Math.ceil(document.documentElement.scrollHeight);
        if (!height || height === lastHeight) { return; }
        lastHeight = height;
        window.parent.postMessage({
            type: "elintom:embedFormHeight",
            formKey: <?= json_encode((string) $form_key) ?>,
            height: height
        }, "*");
    }
    window.addEventListener("load", postHeight);
    window.addEventListener("resize", postHeight);
    document.addEventListener("DOMContentLoaded", postHeight);
    if (window.ResizeObserver) {
        new ResizeObserver(postHeight).observe(document.body);
    } else {
        setInterval(postHeight, 800);
    }
    postHeight();
})();
</script>
<script>window.CF_PHONE_COUNTRIES = <?= json_encode($phone_countries, JSON_UNESCAPED_UNICODE) ?>;</script>
<script src="<?= htmlspecialchars($phone_asset_base . 'js/contact-form-phone.js', ENT_QUOTES, 'UTF-8') ?>"></script>
<script>
(function () {
    if (window.ContactFormPhone) {
        window.ContactFormPhone.init(document);
    }
})();
</script>
</body>
</html>
