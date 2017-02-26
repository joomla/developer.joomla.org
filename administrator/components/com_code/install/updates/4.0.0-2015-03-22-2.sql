DROP TABLE `#__code_projects`;
ALTER TABLE `#__code_trackers` DROP `project_id`;
ALTER TABLE `#__code_trackers` DROP `jc_project_id`;
ALTER TABLE `#__code_tracker_issues` DROP `project_id`;
ALTER TABLE `#__code_tracker_issues` DROP `jc_project_id`;

DROP TABLE `#__code_activity_detail`;
DROP TABLE `#__code_activity_types`;
DROP TABLE `#__code_tracker_issue_assignments`;
