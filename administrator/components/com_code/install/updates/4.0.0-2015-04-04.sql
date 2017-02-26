ALTER TABLE `#__code_tracker_status` DROP `instructions`;
ALTER TABLE `#__code_tracker_status` DROP `jc_tracker_id`;

ALTER TABLE `#__code_tracker_issues` DROP `jc_tracker_id`;
ALTER TABLE `#__code_tracker_issues` DROP `state`;
ALTER TABLE `#__code_tracker_issues` DROP `status_name`;

ALTER TABLE `#__code_tracker_issue_responses` DROP `jc_tracker_id`;

ALTER TABLE `#__code_tracker_issue_changes` DROP `jc_tracker_id`;

ALTER TABLE `#__code_trackers` DROP `open_item_count`;

ALTER TABLE `#__code_tracker_issue_tag_map` DROP `tag`;

ALTER TABLE `#__code_users` DROP `address`;
ALTER TABLE `#__code_users` DROP `address2`;
ALTER TABLE `#__code_users` DROP `city`;
ALTER TABLE `#__code_users` DROP `region`;
ALTER TABLE `#__code_users` DROP `country`;
ALTER TABLE `#__code_users` DROP `postal_code`;
ALTER TABLE `#__code_users` DROP `latitude`;
ALTER TABLE `#__code_users` DROP `longitude`;
ALTER TABLE `#__code_users` DROP `phone`;
ALTER TABLE `#__code_users` DROP `agreed_tos`;
ALTER TABLE `#__code_users` DROP `jca_document_id`;
ALTER TABLE `#__code_users` DROP `signed_jca`;

ALTER TABLE `#__code_users` ADD `username` varchar(150) NOT NULL DEFAULT '' AFTER `last_name`;
ALTER TABLE `#__code_users` ADD `email` varchar(100) NOT NULL DEFAULT '' AFTER `username`;

DROP TABLE `#__code_tracker_snapshots`;
