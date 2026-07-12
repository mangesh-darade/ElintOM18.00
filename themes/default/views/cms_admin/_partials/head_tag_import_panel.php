<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php if (empty($head_tag_import_url)) { return; } ?>
<hr class="cms-tag-import-divider">
<div
    class="cms-head-tag-import-panel"
    id="cms-head-tag-import"
    data-import-url="<?= htmlspecialchars((string) $head_tag_import_url, ENT_QUOTES, 'UTF-8'); ?>"
>
    <h5 class="cms-section-subtitle"><i class="fa fa-code"></i> Import from head script</h5>
    <p class="cms-panel-desc">
        Paste <code>&lt;head&gt;</code> HTML (title, meta, canonical, Open Graph, geo, JSON-LD, AI meta).
        Values fill the tag fields above. If a tag is not in Tag Master, it is auto-created (with category tab) before values are applied.
    </p>
    <div class="form-group">
        <label for="cms-head-tag-import-textarea">Head markup / scripts</label>
        <textarea
            id="cms-head-tag-import-textarea"
            class="form-control skip cms-head-tag-import-textarea"
            rows="10"
            placeholder="&lt;title&gt;...&lt;/title&gt;&#10;&lt;meta name=&quot;description&quot; content=&quot;...&quot;&gt;&#10;&lt;script type=&quot;application/ld+json&quot;&gt;...&lt;/script&gt;"
        ></textarea>
    </div>
    <button type="button" class="btn btn-warning" id="cms-head-tag-import-apply">
        <i class="fa fa-magic"></i> Parse &amp; fill tag fields
    </button>
    <span id="cms-head-tag-import-status" class="cms-head-tag-import-status" aria-live="polite"></span>
</div>
