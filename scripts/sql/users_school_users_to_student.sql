UPDATE users U
INNER JOIN school_users SU ON SU.user_id = U.id
SET U.role_id = 6
WHERE U.role_id = 3 AND SU.role_id = 3;
