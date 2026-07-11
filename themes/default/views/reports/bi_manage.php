<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
  <div class="box-content">
    <div class="row">
      <div class="col-lg-12">
        <div class="clearfix"></div>
        <div class="row">
          <div class="col-sm-12">
            <div class="box" style="border: 1px solid #eee;">
              <div class="box-header" style="background: #f9f9f9;">
                <h2 class="blue">
                  <i class="fa fa-bar-chart"></i> Manage BI Reports
                </h2>
              </div>
              <div class="box-content" style="padding: 15px;">
                <?php if ($this->session->flashdata('message')) { ?>
                  <div class="alert alert-success" role="alert"><?= $this->session->flashdata('message'); ?></div>
                <?php } ?>
                <?php if (validation_errors()) { ?>
                  <div class="alert alert-danger" role="alert"><?= validation_errors(); ?></div>
                <?php } ?>
                <style>
                  .form-horizontal .control-label { font-weight: 600; }
                  .help-block { margin: 5px 0 0; color: #888; }
                  .table-fixed { table-layout: fixed; }
                  .text-ellipsis { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%; display: inline-block; vertical-align: bottom; }
                  .slug-preview { font-size: 12px; color: #666; margin-top: 5px; }
                  .input-group .input-group-addon { min-width: 42px; }
                </style>
                <div class="row">
                  <div class="col-md-6">
                    <h3 style="margin-top:0;">Add / Update</h3>
                    <form action="<?= site_url('reports/bi_manage'); ?>" method="post" class="form-horizontal">
                      <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                      <div class="form-group">
                        <label class="col-sm-4 control-label" for="manu_name">Menu Name *</label>
                        <div class="col-sm-8">
                          <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-list"></i></span>
                            <input type="text" id="manu_name" name="manu_name" class="form-control" placeholder="e.g. Sales" required>
                          </div>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-4 control-label" for="name">Report Name *</label>
                        <div class="col-sm-8">
                          <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-file-text-o"></i></span>
                            <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Sales Dashboard" required>
                          </div>
                          <div class="slug-preview">
                            Slug preview: <code id="slugPrev1">-</code>
                            <span style="color:#bbb;">/</span>
                            <code id="slugPrev2">-</code>
                          </div>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Source</label>
                        <div class="col-sm-8">
                          <label class="radio-inline"><input type="radio" name="source_type" value="internal" checked> Internal</label>
                          <label class="radio-inline"><input type="radio" name="source_type" value="external"> External</label>
                        </div>
                      </div>
                      <div class="form-group js-internal">
                        <label class="col-sm-4 control-label" for="links">Internal Link</label>
                        <div class="col-sm-8">
                          <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-link"></i></span>
                            <input type="text" id="links" name="links" class="form-control" placeholder="reports/sales_dashboard">
                          </div>
                          <span class="help-block">Controller/method. Leave empty if using External URL.</span>
                        </div>
                      </div>
                      <div class="form-group js-external" style="display:none;">
                        <label class="col-sm-4 control-label" for="report_url">External URL</label>
                        <div class="col-sm-8">
                          <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-external-link"></i></span>
                            <input type="url" id="report_url" name="report_url" class="form-control" placeholder="https://app.powerbi.com/...">
                          </div>
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="col-sm-4 control-label">Active</label>
                        <div class="col-sm-8">
                          <div class="checkbox" style="margin:0;">
                            <label><input type="checkbox" name="is_active" value="1" checked> Active</label>
                          </div>
                        </div>
                      </div>
                      <div class="form-group">
                        <div class="col-sm-8 col-sm-offset-4">
                          <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save</button>
                        </div>
                      </div>
                    </form>
                  </div>
                  <div class="col-md-6">
                    <h3 style="margin-top:0;">Existing Items</h3>
                    <div class="table-responsive">
                      <table class="table table-bordered table-striped table-fixed">
                        <thead>
                          <tr>
                            <th>ID</th>
                            <th>Menu</th>
                            <th>Name</th>
                            <th style="width:55%;">Link / URL</th>
                            <th>Active</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php if (!empty($bi_items)) { foreach ($bi_items as $it) { ?>
                            <tr>
                              <td><?= (int)$it->id; ?></td>
                              <td><?= htmlspecialchars($it->manu_name, ENT_QUOTES, 'UTF-8'); ?></td>
                              <td><?= htmlspecialchars($it->name, ENT_QUOTES, 'UTF-8'); ?></td>
                              <td>
                                <?php if (!empty($it->links)) { echo '<span class="text-ellipsis" title="'.htmlspecialchars($it->links, ENT_QUOTES, 'UTF-8').'">'.htmlspecialchars($it->links, ENT_QUOTES, 'UTF-8').'</span>'; }
                                      elseif (isset($it->report_url) && !empty($it->report_url)) { echo '<a class="text-ellipsis" title="'.htmlspecialchars($it->report_url, ENT_QUOTES, 'UTF-8').'" href="'.htmlspecialchars($it->report_url, ENT_QUOTES, 'UTF-8').'" target="_blank">'.htmlspecialchars($it->report_url, ENT_QUOTES, 'UTF-8').'</a>'; }
                                      else { echo '-'; } ?>
                              </td>
                              <td><?= !empty($it->is_active) ? '<span class="label label-success">Yes</span>' : '<span class="label label-default">No</span>'; ?></td>
                            </tr>
                          <?php } } else { ?>
                            <tr><td colspan="5" class="text-center">No items found</td></tr>
                          <?php } ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  (function(){
    var radios = document.querySelectorAll('input[name="source_type"]');
    function toggle(){
      var val = document.querySelector('input[name="source_type"]:checked').value;
      var internal = document.querySelector('.js-internal');
      var external = document.querySelector('.js-external');
      if(val === 'internal'){
        internal.style.display = '';
        external.style.display = 'none';
      } else {
        internal.style.display = 'none';
        external.style.display = '';
      }
    }
    [].forEach.call(radios, function(r){ r.addEventListener('change', toggle); });
    toggle();

    function sanitizeSlug(s){
      s = (s||'').toLowerCase();
      s = s.replace(/[^a-z0-9_]+/g, '_');
      s = s.replace(/_+/g, '_');
      return s.replace(/^_+|_+$/g, '');
    }
    function updateSlugs(){
      var manu = document.getElementById('manu_name').value;
      var name = document.getElementById('name').value;
      var s1 = sanitizeSlug((manu||'') + '_' + (name||''));
      var s2 = sanitizeSlug(manu||'');
      document.getElementById('slugPrev1').textContent = s1 || '-';
      document.getElementById('slugPrev2').textContent = s2 || '-';
    }
    document.getElementById('manu_name').addEventListener('input', updateSlugs);
    document.getElementById('name').addEventListener('input', updateSlugs);
    updateSlugs();
  })();
</script>
