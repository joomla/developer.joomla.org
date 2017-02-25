# Release Notes
SELECT CASE WHEN ISNULL(m.tag) THEN 'None' ELSE m.tag END as Category, 
i.title as 'Issue Title', i.jc_issue_id, i.close_date
FROM `eem_code_tracker_issues` i
LEFT JOIN `jos_code_tracker_issue_tag_map` m 
ON i.issue_id = m.issue_id
AND m.tag_id IN (39,1,29,44,36,85,11,40,17,82,13,6,35,22,27,21,23,20,49,34,19,25,43,94,88)
WHERE DATE(close_date) BETWEEN '2011-01-10' AND '2011-02-22'
AND status_name LIKE '%Fixed in SVN%'
AND i.tracker_id = 3
ORDER BY CASE WHEN ISNULL(m.tag) THEN 'None' ELSE m.tag END, close_date asc

# Total Closed by Month
SELECT COUNT(*) AS total_closed, 
SUM(
CASE WHEN (DATE(i.close_date) BETWEEN Date(DATE_ADD(now(), INTERVAL -119 DAY)) AND Date(DATE_ADD(now(), INTERVAL -90 DAY)))
AND status_name like '%fixed%'
THEN 1 ELSE 0 END
) AS fixed_p4,
SUM(
CASE WHEN (DATE(i.close_date) BETWEEN Date(DATE_ADD(now(), INTERVAL -89 DAY)) AND Date(DATE_ADD(now(), INTERVAL -60 DAY)))
AND status_name like '%fixed%'
THEN 1 ELSE 0 END
) AS fixed_p3,
SUM(
CASE WHEN (DATE(i.close_date) BETWEEN Date(DATE_ADD(now(), INTERVAL -59 DAY)) AND Date(DATE_ADD(now(), INTERVAL -30 DAY)))
AND status_name like '%fixed%'
THEN 1 ELSE 0 END
) AS fixed_p2,
SUM(
CASE WHEN (DATE(i.close_date) BETWEEN Date(DATE_ADD(now(), INTERVAL -29 DAY)) AND Date(DATE_ADD(now(), INTERVAL -0 DAY)))
AND status_name like '%fixed%'
THEN 1 ELSE 0 END
) AS fixed_p1,
SUM(
CASE WHEN (DATE(i.close_date) BETWEEN Date(DATE_ADD(now(), INTERVAL -119 DAY)) AND Date(DATE_ADD(now(), INTERVAL -90 DAY)))
AND status_name NOT like '%fixed%'
THEN 1 ELSE 0 END
) AS closed_p4,
SUM(
CASE WHEN (DATE(i.close_date) BETWEEN Date(DATE_ADD(now(), INTERVAL -89 DAY)) AND Date(DATE_ADD(now(), INTERVAL -60 DAY)))
AND status_name NOT like '%fixed%'
THEN 1 ELSE 0 END
) AS closed_p3,
SUM(
CASE WHEN (DATE(i.close_date) BETWEEN Date(DATE_ADD(now(), INTERVAL -59 DAY)) AND Date(DATE_ADD(now(), INTERVAL -30 DAY)))
AND status_name NOT like '%fixed%'
THEN 1 ELSE 0 END
) AS closed_p2,
SUM(
CASE WHEN (DATE(i.close_date) BETWEEN Date(DATE_ADD(now(), INTERVAL -29 DAY)) AND Date(DATE_ADD(now(), INTERVAL -0 DAY)))
AND status_name NOT like '%fixed%'
THEN 1 ELSE 0 END
) AS closed_p1
FROM `eem_code_tracker_issues` AS i
WHERE DATE(i.close_date) BETWEEN Date(DATE_ADD(now(), INTERVAL -119 DAY)) AND Date(now())
AND i.state = 0


# Total open by Month
SELECT COUNT(*) AS total_closed, 
SUM(
CASE WHEN DATE(i.created_date) BETWEEN Date(DATE_ADD(now(), INTERVAL -119 DAY)) AND Date(DATE_ADD(now(), INTERVAL -90 DAY))
THEN 1 ELSE 0 END
) AS total_opened_p4,
SUM(
CASE WHEN DATE(i.created_date) BETWEEN Date(DATE_ADD(now(), INTERVAL -89 DAY)) AND Date(DATE_ADD(now(), INTERVAL -60 DAY))
THEN 1 ELSE 0 END
) AS total_opened_p3,
SUM(
CASE WHEN DATE(i.created_date) BETWEEN Date(DATE_ADD(now(), INTERVAL -59 DAY)) AND Date(DATE_ADD(now(), INTERVAL -30 DAY))
THEN 1 ELSE 0 END
) AS total_opened_p2,
SUM(
CASE WHEN DATE(i.created_date) BETWEEN Date(DATE_ADD(now(), INTERVAL -29 DAY)) AND Date(DATE_ADD(now(), INTERVAL -0 DAY))
THEN 1 ELSE 0 END
) AS total_opened_p1
FROM `eem_code_tracker_issues` AS i
WHERE DATE(i.created_date) BETWEEN Date(DATE_ADD(now(), INTERVAL -119 DAY)) AND Date(now())
