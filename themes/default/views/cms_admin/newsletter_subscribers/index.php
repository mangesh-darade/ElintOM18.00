<?php defined('BASEPATH') OR exit('No direct script access allowed');
$rows = isset($rows) && is_array($rows) ? $rows : array();
$schema_ready = !empty($schema_ready);
?>

<div class="cms-newsletter-subscribers-index">
    <?php if (!$schema_ready) { ?>
    <div class="alert alert-warning">
        <strong>Newsletter table not found.</strong> The table <code>sma_cms_newsletter_subscriber</code> is created automatically on the first footer signup.
    </div>
    <?php } ?>

    <div class="ws-card-box">
        <div class="cms-table-scroll">
            <table id="newsletterSubscribersTable" class="ws-modern-table cms-datatable" data-cms-list="newsletter_subscribers">
                <thead>
                    <tr>
                        <th class="cms-col-id">ID</th>
                        <th class="cms-col-srno">Sr.</th>
                        <th>Email</th>
                        <th style="width:180px;">Source</th>
                        <th style="width:100px;text-align:center;">Status</th>
                        <th style="width:140px;">IP address</th>
                        <th style="width:170px;">Subscribed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($rows)) {
                        foreach ($rows as $row) {
                            $email = (string) $row['email'];
                            $sort_email = function_exists('mb_strtolower') ? mb_strtolower($email) : strtolower($email);
                            $status = strtolower((string) $row['status']);
                            ?>
                    <tr>
                        <td class="cms-col-id"><?= (int) $row['id']; ?></td>
                        <td class="cms-col-srno"></td>
                        <td data-order="<?= htmlspecialchars($sort_email, ENT_QUOTES, 'UTF-8'); ?>" style="font-weight:600;color:#0f172a;">
                            <a href="mailto:<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?></a>
                        </td>
                        <td style="font-size:13px;color:#475569;"><?= $row['source'] !== '' ? htmlspecialchars((string) $row['source'], ENT_QUOTES, 'UTF-8') : '—'; ?></td>
                        <td style="text-align:center;">
                            <?php if ($status === 'active') { ?>
                            <span class="label label-success">Active</span>
                            <?php } elseif ($status !== '') { ?>
                            <span class="label label-default"><?= htmlspecialchars((string) $row['status'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php } else { ?>—<?php } ?>
                        </td>
                        <td style="font-size:13px;color:#64748b;"><?= $row['ip_address'] !== '' ? htmlspecialchars((string) $row['ip_address'], ENT_QUOTES, 'UTF-8') : '—'; ?></td>
                        <td style="font-size:13px;color:#64748b;"><?= $row['created_at'] !== '' ? htmlspecialchars((string) $row['created_at'], ENT_QUOTES, 'UTF-8') : '—'; ?></td>
                    </tr>
                    <?php }
                    } ?>
                </tbody>
            </table>
        </div>
    </div>

    <p class="text-muted" style="margin-top:14px;font-size:13px;">
        Subscribers are saved when visitors use the newsletter email field in your webshop footer.
    </p>
</div>
