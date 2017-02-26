ALTER TABLE `#__code_trackers` CHANGE `tracker_id` `tracker_id` int(10) unsigned NOT NULL;
UPDATE `#__code_trackers` SET `tracker_id` = `jc_tracker_id`;
ALTER TABLE `#__code_trackers` CHANGE `tracker_id` `tracker_id` int(10) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__code_trackers` DROP `jc_tracker_id`;

UPDATE `#__code_tracker_issues` SET `jc_close_by` = 0 WHERE `jc_close_by` IS NULL;

CREATE TABLE IF NOT EXISTS `#__code_tracker_issues_temp` (
  `issue_id` int(10) unsigned NOT NULL,
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
  PRIMARY KEY (`issue_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

INSERT INTO `#__code_tracker_issues_temp` (`issue_id`, `tracker_id`, `status`, `priority`, `created_date`, `created_by`, `modified_date`, `modified_by`, `close_date`, `close_by`, `title`, `description`)
  SELECT `jc_issue_id`, `tracker_id`, `status`, `priority`, `created_date`, `jc_created_by`, `modified_date`, `jc_modified_by`, `close_date`, `jc_close_by`, `title`, `description` FROM `#__code_tracker_issues`;

DROP TABLE `#__code_tracker_issues`;
RENAME TABLE `#__code_tracker_issues_temp` TO `#__code_tracker_issues`;
ALTER TABLE `#__code_tracker_issues` CHANGE `issue_id` `issue_id` int(10) unsigned NOT NULL AUTO_INCREMENT;

CREATE TABLE IF NOT EXISTS `#__code_tracker_issue_changes_temp` (
  `change_id` int(10) unsigned NOT NULL,
  `issue_id` int(10) unsigned NOT NULL,
  `tracker_id` int(10) unsigned NOT NULL,
  `change_date` datetime NOT NULL,
  `change_by` int(11) NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY (`change_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

INSERT INTO `#__code_tracker_issue_changes_temp` (`change_id`, `issue_id`, `tracker_id`, `change_date`, `change_by`, `data`)
  SELECT `jc_change_id`, `jc_issue_id`, `tracker_id`, `change_date`, `jc_change_by`, `data` FROM `#__code_tracker_issue_changes`;

DROP TABLE `#__code_tracker_issue_changes`;
RENAME TABLE `#__code_tracker_issue_changes_temp` TO `#__code_tracker_issue_changes`;
ALTER TABLE `#__code_tracker_issue_changes` CHANGE `change_id` `change_id` int(10) unsigned NOT NULL AUTO_INCREMENT;

CREATE TABLE IF NOT EXISTS `#__code_tracker_issue_commits_temp` (
  `commit_id` int(10) unsigned NOT NULL,
  `issue_id` int(10) unsigned NOT NULL,
  `tracker_id` int(10) unsigned NOT NULL,
  `created_date` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY (`commit_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

INSERT INTO `#__code_tracker_issue_commits_temp` (`commit_id`, `issue_id`, `tracker_id`, `created_date`, `created_by`, `message`)
  SELECT `jc_commit_id`, `jc_issue_id`, `tracker_id`, `created_date`, `jc_created_by`, `message` FROM `#__code_tracker_issue_commits`;

DROP TABLE `#__code_tracker_issue_commits`;
RENAME TABLE `#__code_tracker_issue_commits_temp` TO `#__code_tracker_issue_commits`;
ALTER TABLE `#__code_tracker_issue_commits` CHANGE `commit_id` `commit_id` int(10) unsigned NOT NULL AUTO_INCREMENT;

CREATE TABLE IF NOT EXISTS `#__code_tracker_issue_responses_temp` (
  `response_id` int(10) unsigned NOT NULL,
  `issue_id` int(10) unsigned NOT NULL,
  `tracker_id` int(10) unsigned NOT NULL,
  `created_date` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  `body` text NOT NULL,
  PRIMARY KEY (`response_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

INSERT INTO `#__code_tracker_issue_responses_temp` (`response_id`, `issue_id`, `tracker_id`, `created_date`, `created_by`, `body`)
  SELECT `response_id`, `jc_issue_id`, `tracker_id`, `created_date`, `jc_created_by`, `body` FROM `#__code_tracker_issue_responses`;

DROP TABLE `#__code_tracker_issue_responses`;
RENAME TABLE `#__code_tracker_issue_responses_temp` TO `#__code_tracker_issue_responses`;
ALTER TABLE `#__code_tracker_issue_responses` CHANGE `response_id` `response_id` int(10) unsigned NOT NULL AUTO_INCREMENT;

CREATE TABLE IF NOT EXISTS `#__code_tracker_status_temp` (
  `status_id` int(10) unsigned NOT NULL,
  `tracker_id` int(10) unsigned NOT NULL,
  `state_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY (`status_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

INSERT INTO `#__code_tracker_status_temp`(`status_id`, `tracker_id`, `state_id`, `title`)
  SELECT `jc_status_id`, `tracker_id`, `state_id`, `title` FROM `#__code_tracker_status`;

DROP TABLE `#__code_tracker_status`;
RENAME TABLE `#__code_tracker_status_temp` TO `#__code_tracker_status` ;
ALTER TABLE `#__code_tracker_status` CHANGE `status_id` `status_id` int(10) unsigned NOT NULL AUTO_INCREMENT;

CREATE TABLE IF NOT EXISTS `#__code_users_temp` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

INSERT INTO `#__code_users_temp`(`user_id`, `first_name`, `last_name`)
  SELECT `jc_user_id`, `first_name`, `last_name` FROM `#__code_users`;

DROP TABLE `#__code_users`;
RENAME TABLE `#__code_users_temp` TO `#__code_users`;
ALTER TABLE `#__code_users` CHANGE `user_id` `user_id` int(11) NOT NULL AUTO_INCREMENT;
