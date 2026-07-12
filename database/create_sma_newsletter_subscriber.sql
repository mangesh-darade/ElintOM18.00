-- Newsletter subscribers from webshop footer signup (API: submitnewslettersubscriber)
CREATE TABLE IF NOT EXISTS `sma_newsletter_subscriber` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NOT NULL,
  `source` VARCHAR(128) NOT NULL DEFAULT 'footer_newsletter',
  `status` VARCHAR(32) NOT NULL DEFAULT 'active',
  `ip_address` VARCHAR(45) NULL DEFAULT NULL,
  `user_agent` VARCHAR(512) NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_newsletter_email` (`email`),
  KEY `idx_newsletter_status` (`status`),
  KEY `idx_newsletter_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
