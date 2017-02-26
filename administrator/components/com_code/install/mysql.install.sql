CREATE TABLE IF NOT EXISTS `#__code_tags` (
  `tag_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tag` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__code_trackers` (
  `tracker_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `summary` varchar(512) NOT NULL,
  `description` text NOT NULL,
  `state` int(11) NOT NULL,
  `options` text NOT NULL,
  `metadata` text NOT NULL,
  `item_count` int(11) NOT NULL,
  `created_date` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  `modified_date` datetime NOT NULL,
  `modified_by` int(11) NOT NULL,
  `jc_tracker_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`tracker_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__code_tracker_issues` (
  `issue_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tracker_id` int(10) unsigned NOT NULL,
  `status` int(10) unsigned NOT NULL,
  `priority` int(11) NOT NULL,
  `created_date` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  `modified_date` datetime NOT NULL,
  `modified_by` int(11) NOT NULL,
  `close_date` datetime NOT NULL,
  `close_by` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` mediumtext,
  `jc_issue_id` int(10) unsigned NOT NULL,
  `jc_created_by` int(11) NOT NULL,
  `jc_modified_by` int(11) NOT NULL,
  `jc_close_by` int(11) NOT NULL,
  PRIMARY KEY (`issue_id`),
  UNIQUE KEY `idx_tracker_issues_legacy` (`jc_issue_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__code_tracker_issue_changes` (
  `change_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `issue_id` int(10) unsigned NOT NULL,
  `tracker_id` int(10) unsigned NOT NULL,
  `change_date` datetime NOT NULL,
  `change_by` int(11) NOT NULL,
  `data` text NOT NULL,
  `jc_change_id` int(10) DEFAULT NULL,
  `jc_issue_id` int(10) DEFAULT NULL,
  `jc_tracker_id` int(10) DEFAULT NULL,
  `jc_change_by` int(10) DEFAULT NULL,
  PRIMARY KEY (`change_id`),
  UNIQUE KEY `jc_change_id` (`jc_change_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__code_tracker_issue_commits` (
  `commit_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `issue_id` int(10) unsigned NOT NULL,
  `tracker_id` int(10) unsigned NOT NULL,
  `created_date` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  `message` text NOT NULL,
  `jc_commit_id` int(10) DEFAULT NULL,
  `jc_issue_id` int(10) DEFAULT NULL,
  `jc_tracker_id` int(10) DEFAULT NULL,
  `jc_created_by` int(10) DEFAULT NULL,
  PRIMARY KEY (`commit_id`),
  UNIQUE KEY `jc_commit_id` (`jc_commit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__code_tracker_issue_responses` (
  `response_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `issue_id` int(10) unsigned NOT NULL,
  `tracker_id` int(10) unsigned NOT NULL,
  `created_date` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  `body` text NOT NULL,
  `jc_response_id` int(10) DEFAULT NULL,
  `jc_issue_id` int(10) DEFAULT NULL,
  `jc_created_by` int(10) DEFAULT NULL,
  PRIMARY KEY (`response_id`),
  UNIQUE KEY `idx_tracker_responses_legacy` (`jc_response_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__code_tracker_issue_tag_map` (
  `issue_id` int(10) unsigned DEFAULT NULL,
  `tag_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`issue_id`, `tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__code_tracker_status` (
  `status_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tracker_id` int(10) unsigned NOT NULL,
  `state_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `jc_status_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__code_users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `username` varchar(150) NOT NULL DEFAULT '',
  `email` varchar(100) NOT NULL DEFAULT '',
  `jc_user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `idx_legacy_user_id` (`jc_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
