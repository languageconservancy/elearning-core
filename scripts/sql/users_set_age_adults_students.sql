UPDATE users U
LEFT JOIN school_users SU ON SU.user_id = U.id
SET U.approximate_age =
    CASE
        WHEN U.dob IS NULL THEN NULL -- Handle missing DOBs
        WHEN TIMESTAMPDIFF(YEAR, U.dob, CURDATE()) >= 13 OR SU.user_id IS NOT NULL
        THEN TIMESTAMPDIFF(YEAR, U.dob, CURDATE())
        ELSE NULL
    END;