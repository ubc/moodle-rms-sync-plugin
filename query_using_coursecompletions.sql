/****** 
Script for the Moodle-HRMS interface  
Nasa Rouf nasarouf@cs.ubc.ca
15/Aug/2016 - 12/Sep/2016
Updated to look back 365 days on 2 nov 2017 - Michael Lonsdale-Eccles
July 2019
Converted to MySQL
Moodlized Query: https://docs.moodle.org/dev/Data_manipulation_API
******/

-- DECLARE @epoch DATETIME
-- SET     @epoch = CONVERT( DATETIME, '01 JAN 1970', 106 )

SELECT 
-- TOP 1000  
	   -- we don't have employee id in the database I believe. optional field.
	   -- expected min len=max len=7, therefore we send in 7 spaces instead of null.
	   -- original file sends empty strings
       '' as user_employee_id,
       -- let's assume the bottom will behave well, string-lengthwise.
       -- shibboleth-only entries are being reported here (see the where clause below), 
       -- so we are expecting that this column will have something in every row.
       -- sending in one space to satisfy the min len=1 requirement.
       case 
	   -- hrms requires minimum 1 char
           when ISNULL(username) then ' '
           -- hrms requires max 30 chars
           else LEFT(username,30)
       end user_cwl,
       -- sending in one space to satisfy the min len=1 requirement.
       case 
	   -- hrms requires minimum 1 char
           when ISNULL(dbouser.idnumber) then ' '
           -- hrms requires max 30 chars
           else LEFT(dbouser.idnumber,30)
       end user_cwl_puid,
       -- dbouser.id,
       -- assuming the bigint field has seconds since 1970 epoch. dates look plausible this way 
       FROM_UNIXTIME(timecompleted, '%m/%d/%Y') as completion_date,
       -- course code is supplied by HRMS and should be six-letter code
       dbocourse.idnumber as course_code,
       case 
	   -- sending in one space to satisfy the min len=2 requirement.
	   when ISNULL(dbocourse.shortname) then ' '
           -- hrms requires max 30 chars
	   else TRIM(LEFT(dbocourse.shortname,30))
       end course_title,
       -- not sure where the session is stored in this db
       -- hrms requires exactly 4 numerics
       '0001' as course_session,
       case 
	   -- sending in one space to satisfy the min len=1 requirement.
	   when ISNULL(firstname) then ' '
           -- hrms requires max 30 chars
	   else LEFT(firstname,30) 
       end user_first_name, 
       case 
	   -- sending in one space to satisfy the min len=1 requirement.
	   when ISNULL(lastname) then ' '
           -- hrms requires max 30 chars
           else LEFT(lastname,30) 
       end user_last_name,
       if(email is NULL, repeat(' ', 6), substr(email, 1, 70))
--        LEFT(RPAD(email, 6, ' '), 70) as user_email
FROM   {course_completions} as dbocourse_completions
       LEFT JOIN {course} as dbocourse
              ON dbocourse_completions.course = dbocourse.id 
       LEFT JOIN {user}  as dbouser
              ON dbocourse_completions.userid = dbouser.id 
WHERE  
		auth='shibboleth'    -- not reporting non-cwl users
		AND timecompleted is not null    -- reporting completions only
		and timecompleted > UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 365 day))
		and LENGTH(dbocourse.idnumber)=6   -- only courses with hrms supplied code which is expected to be a six-letter code.
order by 
		user_cwl, 
		timecompleted 
