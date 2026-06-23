<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * Comprehensive Database Updates Migration
 *
 * This migration implements the following database changes:
 * 1. add_coppa_fields - Insert COPPA-related site settings
 * 2. users_add_approximate_age - Add approximate_age column to users table
 * 3. users_set_age_adults_students - Update approximate_age for eligible users
 * 4. school_roles_create_table - Create school_roles table with data
 * 5. emailcontents_insert_parent_notification - Insert parent notification email template
 * 6. roles_rename_student_to_user - Rename 'student' role to 'user'
 * 7. roles_add_developer_and_student - Add new developer and student roles
 * 8. users_school_users_to_student - Update school users to student role
 * 9. sitesettings_insert_version_fields - Insert app version settings
 */
class ComprehensiveDatabaseUpdates extends AbstractMigration
{
    /**
     * Up Method.
     *
     * @return void
     */
    public function up(): void
    {
        // Step 1: add_coppa_fields - Insert COPPA-related site settings
        $this->insertSiteSettingIfMissing(
            'Setting Minors Can Access Leaderboard',
            'setting_minors_can_access_leaderboard',
            '1'
        );
        $this->insertSiteSettingIfMissing(
            'Setting Minors Can Access Village',
            'setting_minors_can_access_village',
            '1'
        );
        $this->insertSiteSettingIfMissing(
            'Setting Minors Can Access Friends',
            'setting_minors_can_access_friends',
            '0'
        );
        $this->insertSiteSettingIfMissing('Feature Village', 'feature_village', '1');

        // Step 2: users_add_approximate_age - Add approximate_age column to users table
        $usersTable = $this->table('users');
        if (!$usersTable->hasColumn('approximate_age')) {
            $usersTable->addColumn('approximate_age', 'tinyinteger', [
                'null' => true,
                'signed' => false,
                'comment' => 'Approximate age to reduce sensitive data. Null if not updated.',
                'after' => 'dob'
            ])->update();
        }

        // Step 3: users_set_age_adults_students - Update approximate_age for eligible users
        if ($usersTable->hasColumn('approximate_age')) {
            $this->execute("
                UPDATE users U
                LEFT JOIN school_users SU ON SU.user_id = U.id
                SET U.approximate_age =
                    CASE
                        WHEN U.dob IS NULL THEN NULL -- Handle missing DOBs
                        WHEN TIMESTAMPDIFF(YEAR, U.dob, CURDATE()) >= 13 OR SU.user_id IS NOT NULL
                        THEN TIMESTAMPDIFF(YEAR, U.dob, CURDATE())
                        ELSE NULL
                    END
            ");
        }

        // Step 4: school_roles_create_table - Create school_roles table with data
        $this->ensureSchoolRolesTable();

        // Step 5: emailcontents_insert_parent_notification - Insert parent notification email template
        if (!$this->emailContentExists('parent_notification')) {
            $this->execute("
                INSERT INTO `emailcontents`
                (
                    `display_name`,
                    `key`,
                    `subject`,
                    `content`
                )
                VALUES (
                    'Parent Notification',
                    'parent_notification',
                    'Your child has created #AN_A #APPLICATIONNAME account',
                    '<tr>
        <td style=\"text-align:left;width:100%;padding-top:20px;padding-left:40px;padding-right:40px;color:#000000;font-family:''Open Sans'',Calibri,Arial,sans-serif;font-size:24px;font-weight:bold;line-height:24px\">
            <span style=\"font-size:16px\">Hi Parent,</span>
        </td>
    </tr>
    <tr>
        <td style=\"text-align:left;width:100%;padding-bottom:0px;padding-left:40px;padding-right:40px;color:#333f48;font-family:''Open Sans'',Calibri,Arial,sans-serif;font-size:15px;line-height:24px\">
            <p>
                Your child has created an account on #APPLICATIONNAME with the email address <a href=\"mailto:#CHILDS_EMAIL\" target=\"_blank\">#CHILDS_EMAIL</a> and username #USERNAME.
            </p>
            <p>
                If this is not your child and you didn''t create this account, we do not store your email address, and there is nothing further for you to do.
            </p>
            <p>
                If this is your child and you do not wish for your child to have a #APPLICATIONNAME account, you may either:
                <ol>
                    <li>Contact us at <a href=\"mailto:#SUPPORT_EMAIL\" target=\"_blank\">#SUPPORT_EMAIL</a> and we will delete the account, or</li>
                    <li>Ask your child to delete the account by logging in and following the instructions in the account settings.</li>
                </ol>
                When the account is deleted, all personal data associated with the account will be deleted.
            </p>
            <p>
                For more details about how we protect child data, please refer to our <a href=\"#SITE_URL/about/privacy\" target=\"_blank\">Privacy Policy</a>.
            </p>
            <p>
                To protect your child''s privacy and safety, some features of #APPLICATIONNAME have been disabled, as follows:
                <ul>
                    <li>They will not be visible on the public leaderboard or have access to it.</li>
                    <li>They will not have access to the village forum or be able to chat with other users.</li>
                    <li>Their profile will not be publicly accessible.</li>
                    <li>They will not be able to find and add friends, or have other users add them as a friend.</li>
                </ul>
            </p>
            <p>
                If you have any questions or concerns, please contact us at <a href=\"mailto:#SUPPORT_EMAIL\" target=\"_blank\">#SUPPORT_EMAIL</a>.
            </p>
            <p>
                Sincerely,
                <br>#APPLICATIONNAME Team
            </p>
        </td>
    </tr>'
                )
            ");
        }

        // Step 6: roles_rename_student_to_user - Rename 'student' role to 'user'
        // Only rename if 'user' role doesn't already exist
        $userRoleExists = $this->fetchRow("SELECT COUNT(*) as count FROM `roles` WHERE `role` = 'user'");
        if (!$userRoleExists || $userRoleExists['count'] == 0) {
            $this->execute("
                UPDATE `roles`
                SET `role`='user'
                WHERE `role`='student'
            ");
        }

        // Step 7: roles_add_developer_and_student - Add new developer and student roles
        $this->insertRoleIfMissing(5, 'content developer');
        $this->insertRoleIfMissing(6, 'student');

        // Step 8: users_school_users_to_student - Update school users to student role
        $this->updateSchoolUsersToStudentRole();

        // Step 9: sitesettings_insert_version_fields - Insert app version settings
        $this->insertSiteSettingIfMissing('Min Supported App Version', 'min_supported_app_version', '2.5.0');
        $this->insertSiteSettingIfMissing('Latest App Version', 'latest_app_version', '2.5.0');
    }

    /**
     * Down Method.
     *
     * @return void
     */
    public function down(): void
    {
        // Reverse Step 9: Remove version fields from sitesettings
        $this->execute("
            DELETE FROM `sitesettings`
            WHERE `key` IN ('min_supported_app_version', 'latest_app_version')
        ");

        // Reverse Step 8: Revert school users back to original role (assuming role_id 3)
        $this->execute("
            UPDATE users U
            INNER JOIN school_users SU ON SU.user_id = U.id
            SET U.role_id = 3
            WHERE U.role_id = 6 AND SU.role_id = 3
        ");

        // Reverse Step 7: Remove developer and student roles
        $this->execute("
            DELETE FROM `roles`
            WHERE `role` IN ('content developer', 'student')
        ");

        // Reverse Step 6: Rename 'user' role back to 'student'
        $this->execute("
            UPDATE `roles`
            SET `role`='student'
            WHERE `role`='user'
        ");

        // Reverse Step 5: Remove parent notification email content
        $this->execute("
            DELETE FROM `emailcontents`
            WHERE `key` = 'parent_notification'
        ");

        // Reverse Step 4: Drop school_roles table
        if ($this->hasTable('school_roles')) {
            $this->table('school_roles')->drop()->save();
        }

        // Reverse Step 2: Remove approximate_age column from users table
        $usersTable = $this->table('users');
        if ($usersTable->hasColumn('approximate_age')) {
            $usersTable->removeColumn('approximate_age')->update();
        }

        // Reverse Step 1: Remove COPPA-related site settings
        $this->execute("
            DELETE FROM `sitesettings`
            WHERE `key` IN (
                'setting_minors_can_access_leaderboard',
                'setting_minors_can_access_village',
                'setting_minors_can_access_friends',
                'feature_village'
            )
        ");
    }

    private function insertSiteSettingIfMissing(string $displayName, string $key, string $value): void
    {
        $existing = $this->fetchRow(
            'SELECT 1 AS found FROM `sitesettings` WHERE `key` = ' . $this->quoteSql($key) . ' LIMIT 1'
        );
        if ($existing) {
            return;
        }

        $this->execute(sprintf(
            'INSERT INTO `sitesettings` (`display_name`, `key`, `value`) VALUES (%s, %s, %s)',
            $this->quoteSql($displayName),
            $this->quoteSql($key),
            $this->quoteSql($value)
        ));
    }

    private function emailContentExists(string $key): bool
    {
        $existing = $this->fetchRow(
            'SELECT 1 AS found FROM `emailcontents` WHERE `key` = ' . $this->quoteSql($key) . ' LIMIT 1'
        );

        return (bool)$existing;
    }

    private function ensureSchoolRolesTable(): void
    {
        if (!$this->hasTable('school_roles')) {
            $this->table('school_roles', [
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'collation' => 'utf8mb4_general_ci'
            ])
                ->addColumn('name', 'string', [
                    'length' => 50,
                    'null' => false,
                    'collation' => 'utf8mb4_general_ci'
                ])
                ->create();
        }

        foreach (['teacher', 'substitute', 'student'] as $schoolRole) {
            $this->insertSchoolRoleIfMissing($schoolRole);
        }
    }

    private function insertSchoolRoleIfMissing(string $name): void
    {
        $existing = $this->fetchRow(
            'SELECT 1 AS found FROM `school_roles` WHERE `name` = ' . $this->quoteSql($name) . ' LIMIT 1'
        );
        if ($existing) {
            return;
        }

        $this->execute('INSERT INTO `school_roles` (`name`) VALUES (' . $this->quoteSql($name) . ')');
    }

    private function insertRoleIfMissing(int $id, string $role): void
    {
        $existingByRole = $this->fetchRow(
            'SELECT 1 AS found FROM `roles` WHERE `role` = ' . $this->quoteSql($role) . ' LIMIT 1'
        );
        if ($existingByRole) {
            return;
        }

        $existingById = $this->fetchRow(
            'SELECT 1 AS found FROM `roles` WHERE `id` = ' . $id . ' LIMIT 1'
        );
        if ($existingById) {
            return;
        }

        $this->execute(sprintf(
            'INSERT INTO `roles` (`id`, `role`) VALUES (%d, %s)',
            $id,
            $this->quoteSql($role)
        ));
    }

    private function updateSchoolUsersToStudentRole(): void
    {
        $userRole = $this->fetchRow("SELECT `id` FROM `roles` WHERE `role` = 'user' LIMIT 1");
        $studentRole = $this->fetchRow("SELECT `id` FROM `roles` WHERE `role` = 'student' LIMIT 1");
        $schoolStudentRole = $this->fetchRow(
            "SELECT `id` FROM `school_roles` WHERE `name` = 'student' LIMIT 1"
        );

        if (!$userRole || !$studentRole || !$schoolStudentRole) {
            return;
        }

        $this->execute(sprintf(
            'UPDATE users U
            INNER JOIN school_users SU ON SU.user_id = U.id
            SET U.role_id = %d
            WHERE U.role_id = %d AND SU.role_id = %d',
            (int)$studentRole['id'],
            (int)$userRole['id'],
            (int)$schoolStudentRole['id']
        ));
    }

    private function quoteSql(string $value): string
    {
        return "'" . str_replace("'", "''", $value) . "'";
    }
}
