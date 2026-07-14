<?php
/**
 * Variant BOM needs sma_produnit_bill_of_materials.job_works (code already uses it).
 * Run once: php upgrade/add_bom_job_works_column.php
 */
$m = new mysqli('localhost', 'root', '', 'sitadmin_phpupgarde');
if ($m->connect_error) {
    fwrite(STDERR, $m->connect_error . "\n");
    exit(1);
}
$col = $m->query("SHOW COLUMNS FROM sma_produnit_bill_of_materials LIKE 'job_works'");
if ($col && $col->num_rows > 0) {
    echo "OK: job_works already exists\n";
    exit(0);
}
$sql = "ALTER TABLE `sma_produnit_bill_of_materials`
  ADD COLUMN `job_works` INT NULL DEFAULT NULL AFTER `version_no`,
  ADD INDEX `idx_job_works` (`job_works`)";
if (!$m->query($sql)) {
    fwrite(STDERR, $m->error . "\n");
    exit(1);
}
echo "OK: added job_works\n";
