CREATE TABLE IF NOT EXISTS `#__ghmarkdowndisplay_repositories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `repository_owner` varchar(255) NOT NULL,
  `repository_name` varchar(255) NOT NULL,
  `repository_path` varchar(255) NOT NULL DEFAULT '',
  `published` tinyint(3) NOT NULL DEFAULT 0,
  `checked_out` int(10) unsigned NOT NULL DEFAULT 0,
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `idx_published` (`published`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
