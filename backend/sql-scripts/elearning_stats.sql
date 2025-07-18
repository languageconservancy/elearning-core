select count(*) as 'Total Users' from users where not email like "%@languageconservancy.org";

select count(*) as 'Total active users last 6 months' from users where modified > curdate() - interval (dayofmonth(curdate()) - 1) day - interval 6 month and not email like "%@languageconservancy.org" && not email like "%@lakhota.org";

select count(*) as 'Total activities attempted' from user_activities;

select count(*) as 'Total units completed' from user_unit_activities where percent = 100;

select count(*) as 'Total classrooms setup' from classrooms;

select count(*) as 'Total Teacher accounts' from users where role_id = 2;

select count(*) as 'Total Student accounts' from users where role_id = 3;

select count(*) as 'Total Schools setup' from schools;
