<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Server-rendered contact form field rows (Form Templates builder).
 *
 * @var array<int,array<string,mixed>> $cf_fields
 */
$cf_fields = isset($cf_fields) && is_array($cf_fields) ? $cf_fields : array();
$type_options = array(
    'text'     => 'Text',
    'email'    => 'Email',
    'tel'      => 'Phone',
    'textarea' => 'Textarea',
    'select'   => 'Dropdown',
    'country'  => 'Country',
    'date'     => 'Date',
    'hidden'   => 'Hidden',
);
$map_options = array(
    'name'    => 'Name',
    'phone'   => 'Phone',
    'email'   => 'Email',
    'message' => 'Message',
    'country' => 'Country',
    'extra'   => 'Extra field',
);

foreach ($cf_fields as $field) {
    if (!is_array($field)) {
        continue;
    }
    $name = isset($field['name']) ? (string) $field['name'] : '';
    $label = isset($field['label']) ? (string) $field['label'] : $name;
    $type = isset($field['type']) ? strtolower((string) $field['type']) : 'text';
    if (!isset($type_options[$type])) {
        $type = 'text';
    }
    $map = isset($field['map']) ? strtolower((string) $field['map']) : 'extra';
    if (!isset($map_options[$map])) {
        $map = 'extra';
    }
    $required = !empty($field['required']);
    $placeholder = isset($field['placeholder']) ? (string) $field['placeholder'] : '';
    ?>
    <tr class="cms-cf-field-row">
        <td>
            <input type="text" class="form-control input-sm cms-cf-name" value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" placeholder="field_name">
        </td>
        <td>
            <input type="text" class="form-control input-sm cms-cf-label" value="<?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>">
        </td>
        <td>
            <select class="form-control input-sm cms-cf-type cms-native-select">
                <?php foreach ($type_options as $type_key => $type_label) { ?>
                <option value="<?= htmlspecialchars($type_key, ENT_QUOTES, 'UTF-8'); ?>"<?= ($type_key === $type) ? ' selected' : ''; ?>><?= htmlspecialchars($type_label, ENT_QUOTES, 'UTF-8'); ?></option>
                <?php } ?>
            </select>
        </td>
        <td class="cms-cf-req-cell">
            <input type="checkbox" class="cms-cf-required"<?= $required ? ' checked' : ''; ?>>
        </td>
        <td>
            <select class="form-control input-sm cms-cf-map cms-native-select">
                <?php foreach ($map_options as $map_key => $map_label) { ?>
                <option value="<?= htmlspecialchars($map_key, ENT_QUOTES, 'UTF-8'); ?>"<?= ($map_key === $map) ? ' selected' : ''; ?>><?= htmlspecialchars($map_label, ENT_QUOTES, 'UTF-8'); ?></option>
                <?php } ?>
            </select>
        </td>
        <td class="cms-cf-extra-cell">
            <?php if ($type === 'country') { ?>
            <span class="cms-cf-country-hint text-muted"><i class="fa fa-globe"></i> Options from <code>country_master</code> table</span>
            <?php } elseif ($type === 'select') { ?>
            <div class="cms-cf-options-wrap">
                <?php
                $options = isset($field['options']) && is_array($field['options']) ? $field['options'] : array();
                foreach ($options as $opt) {
                    $opt_value = is_array($opt) ? (string) (isset($opt['value']) ? $opt['value'] : '') : (string) $opt;
                    $opt_label = is_array($opt) ? (string) (isset($opt['label']) ? $opt['label'] : $opt_value) : (string) $opt;
                    ?>
                <div class="cms-cf-opt-row">
                    <input type="text" class="form-control input-sm cms-cf-opt-value" placeholder="value" value="<?= htmlspecialchars($opt_value, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="text" class="form-control input-sm cms-cf-opt-label" placeholder="label" value="<?= htmlspecialchars($opt_label, ENT_QUOTES, 'UTF-8'); ?>">
                    <button type="button" class="btn btn-xs btn-danger cms-cf-remove-opt">&times;</button>
                </div>
                    <?php
                }
                ?>
                <button type="button" class="btn btn-default btn-xs cms-cf-add-opt"><i class="fa fa-plus"></i> Option</button>
            </div>
            <?php } else { ?>
            <input type="text" class="form-control input-sm cms-cf-placeholder" placeholder="Placeholder" value="<?= htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8'); ?>">
            <?php } ?>
        </td>
        <td class="cms-cf-actions-cell">
            <button type="button" class="btn btn-xs btn-danger cms-cf-remove-field" title="Remove">&times;</button>
        </td>
    </tr>
    <?php
}
