<?php defined('BASEPATH') OR exit('No direct script access allowed');
require __DIR__ . DIRECTORY_SEPARATOR . '_init.php';
?>

    <div class="cms-layout-builder__workspace cms-layout-builder__workspace--<?= htmlspecialchars($tab, ENT_QUOTES, 'UTF-8'); ?>">
        <section class="cms-layout-builder__preview cms-layout-builder__preview--<?= $tab === 'footer' ? 'side' : 'top'; ?> cms-layout-builder__preview--<?= htmlspecialchars($tab, ENT_QUOTES, 'UTF-8'); ?>">
            <div class="cms-layout-builder__preview-bar">
                <span><i class="fa fa-desktop"></i> <?= $tab === 'footer' ? 'Footer live preview' : 'Header live preview'; ?></span>
                <button type="button" class="btn btn-default btn-xs" id="cmsLbRefreshPreview"><i class="fa fa-refresh"></i></button>
                <?php
                $preview_q = 'tab=' . rawurlencode($tab)
                    . '&amp;header_profile=' . rawurlencode($header_profile_slug)
                    . '&amp;footer_profile=' . rawurlencode($footer_profile_slug);
                ?>
                <a href="<?= htmlspecialchars($preview_url . '?' . $preview_q, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="btn btn-default btn-xs">Open</a>
            </div>
            <div class="cms-layout-builder__iframe-wrap">
                <iframe id="cmsLbPreviewFrame" class="cms-layout-builder__iframe" title="Layout preview"
                    scrolling="<?= $tab === 'footer' ? 'auto' : 'no'; ?>"
                    loading="lazy"
                    data-header-profile="<?= htmlspecialchars($header_profile_slug, ENT_QUOTES, 'UTF-8'); ?>"
                    data-footer-profile="<?= htmlspecialchars($footer_profile_slug, ENT_QUOTES, 'UTF-8'); ?>"
                    data-active-profile="<?= htmlspecialchars($profile_slug, ENT_QUOTES, 'UTF-8'); ?>"></iframe>
            </div>
        </section>

        <aside class="cms-layout-builder__panel">
            <?php if ($tab === 'header') { ?>
            <?= form_open_multipart('cms_admin/layout_builder?' . $profile_q, array(
                'id' => 'cmsHeaderBuilderForm',
                'class' => 'cms-layout-builder__form',
                'data-upload-base' => rtrim($upload_base, '/'),
            )); ?>
            <input type="hidden" name="header_layout_profile" value="<?= htmlspecialchars($header_profile_slug, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="footer_layout_profile" value="<?= htmlspecialchars($footer_profile_slug, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="save_layout_builder" value="1">
            <input type="hidden" name="layout_profile" value="<?= htmlspecialchars($profile_slug, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="logo_image" id="logoImagePath" value="<?= htmlspecialchars(isset($h['logo_image']) ? $h['logo_image'] : '', ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="favicon_image" id="faviconImagePath" value="<?= htmlspecialchars(isset($h['favicon_image']) ? $h['favicon_image'] : '', ENT_QUOTES, 'UTF-8'); ?>">

            <section class="cms-lb-section cms-lb-section--header-elements">
                <h4><i class="fa fa-eye"></i> Header elements</h4>
                <?= cms_layout_builder_render_header_show_elements($h, 'cms-lb-live'); ?>
            </section>

            <section class="cms-lb-section">
                <h4><i class="fa fa-picture-o"></i> Images</h4>
                <div class="form-group cms-lb-file-upload">
                    <label class="cms-lb-file-upload__label">Upload logo</label>
                    <input type="file" name="logo_file" accept=".jpg,.jpeg,.png,.gif,.webp" class="cms-lb-file-upload__input">
                    <div class="cms-media-pick-row">
                        <button type="button" class="btn btn-default btn-xs cms-media-pick-btn"
                                data-media-preset="logos"
                                data-media-target="#logoImagePath"
                                data-media-preview="#logoPreviewThumb">
                            <i class="fa fa-picture-o"></i> Choose from library
                        </button>
                    </div>
                    <?php if ($logo_src !== '') { ?>
                    <p class="cms-lb-logo-preview cms-media-pick-preview"><img src="<?= htmlspecialchars($logo_src, ENT_QUOTES, 'UTF-8'); ?>" alt="Logo" id="logoPreviewThumb"></p>
                    <?php } else { ?>
                    <p class="cms-lb-logo-preview cms-media-pick-preview" style="display:none;"><img src="" alt="Logo" id="logoPreviewThumb"></p>
                    <?php } ?>
                </div>
                <div class="form-group cms-lb-file-upload cms-lb-favicon-upload" style="margin-top:15px; border-top:1px dashed #cbd5e1; padding-top:15px;">
                    <label class="cms-lb-file-upload__label">Upload favicon</label>
                    <p class="cms-hd-field-help" style="margin:0 0 8px;">Recommended <strong>32×32</strong> or <strong>64×64</strong> px (.ico, .png). Shown in the browser tab on every webshop page.</p>
                    <input type="file" name="favicon_file" accept=".ico,.jpg,.jpeg,.png,.gif,.webp" class="cms-lb-file-upload__input">
                    <div class="cms-media-pick-row">
                        <button type="button" class="btn btn-default btn-xs cms-media-pick-btn"
                                data-media-preset="favicons"
                                data-media-target="#faviconImagePath"
                                data-media-preview="#faviconPreviewThumb">
                            <i class="fa fa-picture-o"></i> Choose from library
                        </button>
                    </div>
                    <div class="cms-lb-favicon-preview-wrap cms-media-pick-preview<?= $favicon_src !== '' ? '' : ' is-empty'; ?>" style="margin-top:10px;">
                        <span class="cms-lb-favicon-preview__label">Tab icon preview (32×32)</span>
                        <div class="cms-lb-favicon-preview__box" aria-hidden="true">
                            <img src="<?= $favicon_src !== '' ? htmlspecialchars($favicon_src, ENT_QUOTES, 'UTF-8') : ''; ?>" alt="Favicon" id="faviconPreviewThumb" width="32" height="32">
                        </div>
                        <p class="cms-lb-favicon-preview__path" id="faviconPreviewPath"><?= !empty($h['favicon_image']) ? htmlspecialchars((string) $h['favicon_image'], ENT_QUOTES, 'UTF-8') : ''; ?></p>
                    </div>
                </div>
            </section>

            <section class="cms-lb-section">
                <h4><i class="fa fa-paint-brush"></i> Colors</h4>
                <div class="cms-lb-colors">
                    <label>Background <input type="color" name="bg_color" class="cms-lb-color cms-lb-live" data-preview-key="bg_color" value="<?= htmlspecialchars(isset($h['bg_color']) ? $h['bg_color'] : '#0f172a', ENT_QUOTES, 'UTF-8'); ?>"></label>
                    <label>Text <input type="color" name="text_color" class="cms-lb-color cms-lb-live" data-preview-key="text_color" value="<?= htmlspecialchars(isset($h['text_color']) ? $h['text_color'] : '#ffffff', ENT_QUOTES, 'UTF-8'); ?>"></label>
                    <label>Accent <input type="color" name="accent_color" class="cms-lb-color cms-lb-live" data-preview-key="accent_color" value="<?= htmlspecialchars(isset($h['accent_color']) ? $h['accent_color'] : '#3b82f6', ENT_QUOTES, 'UTF-8'); ?>"></label>
                    <label>Icon bg <input type="color" name="icon_bg" class="cms-lb-color cms-lb-live" data-preview-key="icon_bg" value="<?= htmlspecialchars(isset($h['icon_bg']) && $h['icon_bg'] !== 'transparent' ? $h['icon_bg'] : '#334155', ENT_QUOTES, 'UTF-8'); ?>"></label>
                </div>
            </section>

            <section class="cms-lb-section cms-lb-section--note">
                <p class="cms-hd-field-help" style="margin:0;">
                    <i class="fa fa-info-circle"></i> <strong>Nav bar</strong> shows pages from <a href="<?= site_url('cms_admin/pages'); ?>">CMS Pages</a> with <strong>Show In Header</strong> (not configured here). Preview uses the same list.
                </p>
            </section>

            <?php
            $_lb_custom_tag_count = 0;
            if (function_exists('cms_header_builder_get_custom_header_tags')) {
                foreach (cms_header_builder_get_custom_header_tags($h) as $_lb_tag_row) {
                    if (trim((string) (isset($_lb_tag_row['code']) ? $_lb_tag_row['code'] : '')) !== '') {
                        $_lb_custom_tag_count++;
                    }
                }
            }
            $_lb_ga_has_value = trim((string) (isset($h['google_analytics']) ? $h['google_analytics'] : '')) !== '';
            ?>
            <section class="cms-lb-section cms-lb-section--tracking cms-lb-section--collapsible" id="cmsLbGoogleAnalyticsSection">
                <h4 class="cms-lb-section__heading cms-lb-section__heading--toggle" role="button" tabindex="0"
                    aria-expanded="false" aria-controls="cmsLbGoogleAnalyticsBody">
                    <span class="cms-lb-section__chevron" aria-hidden="true"><i class="fa fa-chevron-right"></i></span>
                    <span class="cms-lb-section__heading-main"><i class="fa fa-line-chart"></i> Google Analytics</span>
                    <?php if ($_lb_ga_has_value) { ?>
                    <span class="cms-lb-section__badge cms-lb-section__badge--dot" title="Script configured">&#9679;</span>
                    <?php } ?>
                </h4>
                <div class="cms-lb-section__collapse-body" id="cmsLbGoogleAnalyticsBody">
                    <input type="hidden" name="google_analytics_present" value="1">
                    <div class="form-group cms-lb-tracking-field">
                        <label for="googleAnalyticsScript">Google tag (gtag.js) script</label>
                        <textarea name="google_analytics" id="googleAnalyticsScript" class="form-control input-sm cms-lb-header-tracking-input" rows="10"
                                  placeholder="Paste the full Google tag (gtag.js) snippet from your Google Analytics account"><?= htmlspecialchars(isset($h['google_analytics']) ? $h['google_analytics'] : '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                        <p class="cms-hd-field-help">Paste the <strong>full script</strong> from Google Analytics (comment + both <code>&lt;script&gt;</code> blocks). It is saved and shown on the webshop <strong>exactly as you paste it</strong> — nothing is auto-generated in code.</p>
                    </div>
                </div>
            </section>

            <section class="cms-lb-section cms-lb-section--tracking cms-lb-section--collapsible" id="cmsLbCustomHeaderTagsSection">
                <h4 class="cms-lb-section__heading cms-lb-section__heading--toggle" role="button" tabindex="0"
                    aria-expanded="false" aria-controls="cmsLbCustomHeaderTagsBody">
                    <span class="cms-lb-section__chevron" aria-hidden="true"><i class="fa fa-chevron-right"></i></span>
                    <span class="cms-lb-section__heading-main"><i class="fa fa-code"></i> Custom tags for header</span>
                    <?php if ($_lb_custom_tag_count > 0) { ?>
                    <span class="cms-lb-section__badge"><?= (int) $_lb_custom_tag_count; ?></span>
                    <?php } ?>
                </h4>
                <div class="cms-lb-section__collapse-body" id="cmsLbCustomHeaderTagsBody">
                    <p class="cms-hd-field-help">Store-wide GEO / marketing <code>&lt;head&gt;</code> tags on <strong>every webshop page</strong>. Add one tag (or JSON-LD block) per row in <em>Header code</em>: <code>&lt;title&gt;</code>, <code>&lt;meta name="description" …&gt;</code>, <code>&lt;link rel="canonical" …&gt;</code>, <code>&lt;meta name="robots" …&gt;</code>, hreflang/geo/AI <code>&lt;meta&gt;</code>, Meta Pixel <code>&lt;script&gt;</code>, or paste <strong>schema.org JSON</strong> as-is (auto-wrapped in <code>&lt;script type="application/ld+json"&gt;</code>). Saved output appears in webshop view source.</p>
                    <input type="hidden" name="custom_header_tags_present" value="1">
                    <?= cms_layout_builder_render_custom_header_tags_editor($h); ?>
                </div>
            </section>

            <section class="cms-lb-section">
                <h4><i class="fa fa-text-width"></i> Extra text</h4>
                <div class="form-group">
                    <label>Promo bar text</label>
                    <textarea name="promo_text" class="form-control input-sm cms-lb-live" data-preview-key="promo_text"
                           rows="3" placeholder="e.g. <strong>Free shipping</strong> over &#8377;999"><?= htmlspecialchars(isset($h['promo_text']) ? $h['promo_text'] : '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                    <p class="cms-hd-field-help">Text is saved even when <strong>Promo bar</strong> is off (hidden on the site until you turn it on again). You can use HTML code here.</p>
                </div>
                <div class="form-group">
                    <label>Phone number</label>
                    <input type="text" name="phone" class="form-control input-sm cms-lb-live" data-preview-key="phone"
                           value="<?= htmlspecialchars(isset($h['phone']) ? $h['phone'] : '', ENT_QUOTES, 'UTF-8'); ?>"
                           placeholder="e.g. +91 98765 43210">
                    <p class="cms-hd-field-help">Number is saved even when <strong>Phone</strong> is off (hidden on the site until you turn it on again).</p>
                </div>
                <div class="form-group">
                    <label>Button text</label>
                    <textarea name="button_text" class="form-control input-sm cms-lb-live" data-preview-key="button_text"
                              rows="2" placeholder="e.g. Shop now or &lt;strong&gt;Free trial&lt;/strong&gt;"><?= htmlspecialchars(isset($h['button_text']) ? $h['button_text'] : '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                    <p class="cms-hd-field-help">Saved even when <strong>Button</strong> is off. HTML is allowed (e.g. <code>&lt;strong&gt;</code>, <code>&lt;i class=&quot;fa fa-arrow-right&quot;&gt;&lt;/i&gt;</code>) for look and feel. Do not add a nested <code>&lt;a&gt;</code> — use <strong>Button link</strong> for the URL.</p>
                </div>
                <div class="form-group">
                    <label>Button Link</label>
                    <input type="text" name="button_link" class="form-control input-sm"
                           value="<?= htmlspecialchars(isset($h['button_link']) ? $h['button_link'] : '', ENT_QUOTES, 'UTF-8'); ?>"
                           placeholder="e.g. /shop or https://google.com">
                </div>
            </section>

            <button type="submit" class="btn btn-primary btn-lg btn-block cms-lb-save"><i class="fa fa-check"></i> Save header layout</button>
            <?= form_close(); ?>

            <?php } else { ?>

            <?= form_open_multipart('cms_admin/layout_builder?tab=footer&amp;' . $profile_q, array('id' => 'cmsFooterBuilderForm')); ?>
            <input type="hidden" name="header_layout_profile" value="<?= htmlspecialchars($header_profile_slug, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="footer_layout_profile" value="<?= htmlspecialchars($footer_profile_slug, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="save_layout_builder" value="1">
            <input type="hidden" name="layout_profile" value="<?= htmlspecialchars($profile_slug, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="logo_image" id="footerLogoImagePath" value="<?= htmlspecialchars(isset($f['logo_image']) ? $f['logo_image'] : '', ENT_QUOTES, 'UTF-8'); ?>">

            <section class="cms-lb-section cms-lb-section--header-elements">
                <h4><i class="fa fa-eye"></i> Footer elements</h4>
                <?= cms_layout_builder_render_footer_show_elements($f, 'cms-lb-ft-live'); ?>
            </section>

            <section class="cms-lb-section">
                <h4><i class="fa fa-columns"></i> Footer menu sections</h4>
                <p class="cms-hd-field-help">Add columns with a <strong>section name</strong>. If the name matches a CMS page (e.g. Solutions), that page is linked automatically. Pick a page from the dropdown (<strong>★</strong> = same page as header nav) or choose <strong>Other</strong> for a custom URL. The <strong>Opens:</strong> line shows the exact redirect — same path the header uses. Assign this footer layout on each <a href="<?= site_url('cms_admin/pages'); ?>">CMS Page</a> → <strong>Page header/footer design</strong> so the webshop uses it.</p>
                <input type="hidden" name="footer_sections_present" value="1">
                <?= cms_layout_builder_render_footer_sections_editor($f, isset($footer_link_pages) ? $footer_link_pages : null); ?>
            </section>

            <section class="cms-lb-section">
                <h4><i class="fa fa-picture-o"></i> Images</h4>
                <div class="form-group cms-lb-file-upload">
                    <label class="cms-lb-file-upload__label">Upload logo</label>
                    <input type="file" name="logo_file" accept=".jpg,.jpeg,.png,.gif,.webp" class="cms-lb-file-upload__input">
                    <div class="cms-media-pick-row">
                        <button type="button" class="btn btn-default btn-xs cms-media-pick-btn"
                                data-media-preset="logos"
                                data-media-target="#footerLogoImagePath"
                                data-media-preview="#footerLogoPreviewThumb">
                            <i class="fa fa-picture-o"></i> Choose from library
                        </button>
                    </div>
                    <?php if ($f_logo_src !== '') { ?>
                    <p class="cms-lb-logo-preview cms-media-pick-preview"><img src="<?= htmlspecialchars($f_logo_src, ENT_QUOTES, 'UTF-8'); ?>" alt="Logo" id="footerLogoPreviewThumb" style="max-height:40px;"></p>
                    <?php } else { ?>
                    <p class="cms-lb-logo-preview cms-media-pick-preview" style="display:none;"><img src="" alt="Logo" id="footerLogoPreviewThumb" style="max-height:40px;"></p>
                    <?php } ?>
                </div>
            </section>

            <section class="cms-lb-section">
                <h4><i class="fa fa-paint-brush"></i> Colors</h4>
                <div class="cms-lb-colors">
                    <label>Background <input type="color" name="footer_bg_color" class="cms-lb-ft-color cms-lb-ft-live" data-ft-key="bg_color" value="<?= htmlspecialchars(isset($f['bg_color']) ? $f['bg_color'] : '#ffffff', ENT_QUOTES, 'UTF-8'); ?>"></label>
                    <label>Text <input type="color" name="footer_text_color" class="cms-lb-ft-color cms-lb-ft-live" data-ft-key="text_color" value="<?= htmlspecialchars(isset($f['text_color']) ? $f['text_color'] : '#1e293b', ENT_QUOTES, 'UTF-8'); ?>"></label>
                    <label>Accent <input type="color" name="footer_accent_color" class="cms-lb-ft-color cms-lb-ft-live" data-ft-key="accent_color" value="<?= htmlspecialchars(isset($f['accent_color']) ? $f['accent_color'] : '#c28913', ENT_QUOTES, 'UTF-8'); ?>"></label>
                </div>
            </section>

            <section class="cms-lb-section">
                <h4><i class="fa fa-text-width"></i> Brand &amp; bottom bar</h4>
                <div class="form-group">
                    <label>Tagline</label>
                    <textarea name="tagline" class="form-control input-sm cms-lb-ft-text" data-ft-key="tagline" rows="2" placeholder="e.g. Your U.S. Market Entry Partner for Global Manufacturers."><?= htmlspecialchars(isset($f['tagline']) ? $f['tagline'] : '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>
                <div class="form-group">
                    <label>Copyright (bottom bar)</label>
                    <input type="text" name="copyright" class="form-control input-sm cms-lb-ft-text" data-ft-key="copyright"
                           value="<?= htmlspecialchars(isset($f['copyright']) ? $f['copyright'] : '', ENT_QUOTES, 'UTF-8'); ?>"
                           placeholder="e.g. © {year} Swasthe. All rights reserved.">
                    <p class="cms-hd-field-help">Use <code>{year}</code> for the current year.</p>
                </div>
            </section>

            <section class="cms-lb-section">
                <h4><i class="fa fa-share-alt"></i> Social icons</h4>
                <p class="cms-hd-field-help">Add any social link with Font Awesome icon class or upload a custom icon image. Drag to reorder. Toggle <strong>Social Links</strong> in Footer elements above.</p>
                <input type="hidden" name="social_items_present" value="1">
                <?= cms_layout_builder_render_social_items_editor($f, $upload_base); ?>
            </section>

            <section class="cms-lb-section">
                <h4><i class="fa fa-envelope-o"></i> Newsletter</h4>
                <div class="form-group">
                    <label>Heading</label>
                    <input type="text" name="newsletter_title" class="form-control input-sm cms-lb-ft-text" data-ft-key="newsletter_title"
                           value="<?= htmlspecialchars(isset($f['newsletter_title']) ? $f['newsletter_title'] : 'Stay Updated', ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="newsletter_desc" class="form-control input-sm cms-lb-ft-text" data-ft-key="newsletter_desc" rows="2"><?= htmlspecialchars(isset($f['newsletter_desc']) ? $f['newsletter_desc'] : '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>
                <div class="form-group">
                    <label>Email placeholder</label>
                    <input type="text" name="newsletter_placeholder" class="form-control input-sm cms-lb-ft-text" data-ft-key="newsletter_placeholder"
                           value="<?= htmlspecialchars(isset($f['newsletter_placeholder']) ? $f['newsletter_placeholder'] : 'Enter your email', ENT_QUOTES, 'UTF-8'); ?>">
                </div>
            </section>

            <section class="cms-lb-section">
                <h4><i class="fa fa-whatsapp"></i> Floating WhatsApp button</h4>
                <p class="cms-hd-field-help">Shows a fixed green chat button on every webshop page (bottom-right). Phone must include country code without <code>+</code> (e.g. <code>919899087682</code> for India). If left empty, the header <strong>Phone</strong> number is used when set.</p>
                <div class="checkbox" style="margin-bottom:12px;">
                    <label>
                        <input type="checkbox" name="show_floating_whatsapp" value="1" <?= !empty($f['show_floating_whatsapp']) ? 'checked' : ''; ?>>
                        Enable floating WhatsApp button on webshop
                    </label>
                </div>
                <div class="form-group">
                    <label>WhatsApp number</label>
                    <input type="text" name="floating_whatsapp_phone" class="form-control input-sm cms-lb-ft-text" data-ft-key="floating_whatsapp_phone"
                           value="<?= htmlspecialchars(isset($f['floating_whatsapp_phone']) ? $f['floating_whatsapp_phone'] : '', ENT_QUOTES, 'UTF-8'); ?>"
                           placeholder="e.g. 919899087682">
                </div>
                <div class="form-group">
                    <label>Pre-filled chat message (optional)</label>
                    <textarea name="floating_whatsapp_message" class="form-control input-sm cms-lb-ft-text" data-ft-key="floating_whatsapp_message" rows="2"
                              placeholder="Hi, I would like to know more about your products."><?= htmlspecialchars(isset($f['floating_whatsapp_message']) ? $f['floating_whatsapp_message'] : '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>
            </section>

            <section class="cms-lb-section">
                <h4><i class="fa fa-balance-scale"></i> Legal links (bottom bar)</h4>
                <div class="form-group">
                    <label>Link 1 label</label>
                    <input type="text" name="legal_link_1_label" class="form-control input-sm cms-lb-ft-text" data-ft-key="legal_link_1_label"
                           value="<?= htmlspecialchars(isset($f['legal_link_1_label']) ? $f['legal_link_1_label'] : 'Privacy Policy', ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="form-group">
                    <label>Link 1 URL</label>
                    <input type="text" name="legal_link_1_url" class="form-control input-sm cms-lb-ft-text" data-ft-key="legal_link_1_url"
                           value="<?= htmlspecialchars(isset($f['legal_link_1_url']) ? $f['legal_link_1_url'] : '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="/privacy or https://...">
                </div>
                <div class="form-group">
                    <label>Link 2 label</label>
                    <input type="text" name="legal_link_2_label" class="form-control input-sm cms-lb-ft-text" data-ft-key="legal_link_2_label"
                           value="<?= htmlspecialchars(isset($f['legal_link_2_label']) ? $f['legal_link_2_label'] : 'Terms of Service', ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="form-group">
                    <label>Link 2 URL</label>
                    <input type="text" name="legal_link_2_url" class="form-control input-sm cms-lb-ft-text" data-ft-key="legal_link_2_url"
                           value="<?= htmlspecialchars(isset($f['legal_link_2_url']) ? $f['legal_link_2_url'] : '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="/terms">
                </div>
            </section>

            <button type="submit" class="btn btn-primary btn-lg btn-block"><i class="fa fa-check"></i> Save footer</button>
            <?= form_close(); ?>

            <?php } ?>
        </aside>
    </div>
