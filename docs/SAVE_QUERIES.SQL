
SELECT * FROM project_types;
SELECT * FROM projects WHERE id = 10;
SELECT * FROM project_milestones WHERE project_id = 1;
SELECT * FROM project_disbursements WHERE project_id = 1;
SELECT * FROM project_approvals WHERE project_id = 1;
SELECT * FROM project_images;
SELECT * FROM progress_reports;
SELECT * FROM progress_report_images; 
SELECT * FROM roles;
SELECT * FROM users;

EXPLAIN projects;

SELECT u.username, r.name, r.description FROM users u inner join roles r on u.role_id = r.id;

select p.title, s.id, s.name from projects p
inner join lgu_sectors s on p.sector_id = s.id
inner join municipalities m on p.municipality_id = m.id
inner join provinces pr on p.province_id = pr.id
