<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
/**
 * Shared partial: Entity tag mapping form (tabs + inputs).
 * Used by both entity_mapping_add.php and entity_mapping_edit.php.
 *
 * Expected variables:
 *   $tags_by_category  - array (category => tags[])
 *   $existing_values   - array (tag_id => value) — empty array for "add" mode
 */
$tags_by_category = isset($tags_by_category) ? $tags_by_category : array();
$existing_values  = isset($existing_values)  ? $existing_values  : array();
?>
<div class="row entity-wrap">
    <div class="col-lg-12">
        <ul class="nav nav-tabs" role="tablist">
            <?php $i = 0; foreach ($tags_by_category as $cat => $items) { ?>
                <li role="presentation" class="<?= $i === 0 ? 'active' : ''; ?>">
                    <a href="#tab_cat_<?= $i; ?>" role="tab" data-toggle="tab">
                        <?= htmlspecialchars(trim((string) $cat), ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                </li>
            <?php $i++; } ?>
        </ul>
        <div class="tab-content" style="padding:15px; border:1px solid #ddd; border-top:0;">
            <?php $i = 0; foreach ($tags_by_category as $cat => $items) { ?>
                <div role="tabpanel" class="tab-pane <?= $i === 0 ? 'active' : ''; ?>" id="tab_cat_<?= $i; ?>">
                    <div class="row">
                        <?php foreach ($items as $tag) { ?>
                            <?php $tag_id = (int) $tag['id']; ?>
                            <?php
                            $display_tag_name = preg_replace('/\s+/', ' ', trim(str_replace('_', ' ', (string) $tag['tag_name'])));
                            ?>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tag_value_<?= $tag_id; ?>"><?= htmlspecialchars($display_tag_name, ENT_QUOTES, 'UTF-8'); ?></label>
                                    <input
                                        type="text"
                                        class="form-control entity-tag-input"
                                        id="tag_value_<?= $tag_id; ?>"
                                        name="tag_values[<?= $tag_id; ?>]"
                                        value="<?= isset($existing_values[$tag_id]) ? htmlspecialchars($existing_values[$tag_id], ENT_QUOTES, 'UTF-8') : ''; ?>"
                                        placeholder="Enter value for <?= htmlspecialchars($display_tag_name, ENT_QUOTES, 'UTF-8'); ?>"
                                    >
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            <?php $i++; } ?>
        </div>
    </div>
</div>
