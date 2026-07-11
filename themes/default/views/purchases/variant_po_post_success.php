<div class="box" style="display:none">
  <div class="box-header">
    <h2 class="blue"><i class="fa-fw fa fa-check"></i> Generate Variant PO - Success</h2>
  </div>
  <div class="box-content">
    <p class="introtext">Purchase Order and Raw Material Transfer have been created successfully.</p>

    <div class="row" style="margin-top:10px;">
      <div class="col-md-6">
        <div class="panel panel-default">
          <div class="panel-heading"><strong>Purchase</strong></div>
          <div class="panel-body">
            <p>Purchase ID: <strong><?= (int)$purchase_id ?></strong></p>
            <a class="btn btn-primary" target="_blank" href="<?= site_url('purchases/view/' . (int)$purchase_id) ?>">Open Purchase</a>
            <a class="btn btn-default" target="_blank" href="<?= site_url('purchases/view/' . (int)$purchase_id . '?print=1') ?>">Print Purchase</a>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="panel panel-default">
          <div class="panel-heading"><strong>Transfer<?= count($transfer_ids) > 1 ? 's' : '' ?></strong></div>
          <div class="panel-body">
            <?php if (count($transfer_ids) > 1): ?>
              <p><strong><?= count($transfer_ids) ?> Transfers Created</strong> (one per source location)</p>
              <?php foreach ($transfer_ids as $idx => $tid): ?>
                <div style="margin-bottom: 10px; padding: 8px; border: 1px solid #ddd; background: #f9f9f9;">
                  <p style="margin: 0 0 5px 0;">Transfer #<?= $idx + 1 ?>: <strong>ID <?= (int)$tid ?></strong></p>
                  <a class="btn btn-sm btn-primary" target="_blank" href="<?= site_url('transfers/view/' . (int)$tid) ?>">Open</a>
                  <a class="btn btn-sm btn-default" target="_blank" href="<?= site_url('transfers/view/' . (int)$tid . '?print=1') ?>">Print</a>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <p>Transfer ID: <strong><?= (int)$transfer_id ?></strong></p>
              <a class="btn btn-primary" target="_blank" href="<?= site_url('transfers/view/' . (int)$transfer_id) ?>">Open Transfer</a>
              <a class="btn btn-default" target="_blank" href="<?= site_url('transfers/view/' . (int)$transfer_id . '?print=1') ?>">Print Transfer</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-12">
        <a class="btn btn-success" href="<?= site_url('purchases') ?>">Back to Purchases</a>
      </div>
    </div>
  </div>

<script>
  // Screen: Variant PO Post Success
  // Chain printing: Purchase → Transfer(s) → Back to purchases
  (function(){
  var purchaseId = <?= (int)$purchase_id ?>;
  var transferIds = <?= json_encode($transfer_ids) ?>;
  var pUrl = '<?= site_url('purchases/modal_view/') ?>' + purchaseId + '?print=1';
  var backUrl = '<?= site_url('purchases') ?>';

  // Build chain of transfer print URLs
  var currentUrl = backUrl;
  
  // Build transfer chain in reverse (so we navigate forward through them)
  for (var i = transferIds.length - 1; i >= 0; i--) {
    var tUrl = '<?= site_url('transfers/view/') ?>' + transferIds[i] + '?print=1';
    tUrl += '&back=' + encodeURIComponent(currentUrl);
    currentUrl = tUrl;
  }
  
  // First print Purchase, then chain to transfers
  var firstUrl = pUrl + '&next=' + encodeURIComponent(currentUrl);
  
  // Navigate in the same tab to start the print chain
  window.location.href = firstUrl;
  })();
</script>

</div>
