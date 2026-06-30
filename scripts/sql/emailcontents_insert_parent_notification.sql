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
        <td style="text-align:left;width:100%;padding-top:20px;padding-left:40px;padding-right:40px;color:#000000;font-family:\'Open Sans\',Calibri,Arial,sans-serif;font-size:24px;font-weight:bold;line-height:24px">
            <span style="font-size:16px">Hi Parent,</span>
        </td>
    </tr>
    <tr>
        <td style="text-align:left;width:100%;padding-bottom:0px;padding-left:40px;padding-right:40px;color:#333f48;font-family:\'Open Sans\',Calibri,Arial,sans-serif;font-size:15px;line-height:24px">
            <p>
                Your child has created an account on #APPLICATIONNAME with the email address <a href="mailto:#CHILDS_EMAIL" target="_blank">#CHILDS_EMAIL</a> and username #USERNAME.
            </p>
            <p>
                If this is not your child and you didn''t create this account, we do not store your email address, and there is nothing further for you to do.
            </p>
            <p>
                If this is your child and you do not wish for your child to have a #APPLICATIONNAME account, you may either:
                <ol>
                    <li>Contact us at <a href="mailto:#SUPPORT_EMAIL" target="_blank">#SUPPORT_EMAIL</a> and we will delete the account, or</li>
                    <li>Ask your child to delete the account by logging in and following the instructions in the account settings.</li>
                </ol>
                When the account is deleted, all personal data associated with the account will be deleted.
            </p>
            <p>
                For more details about how we protect child data, please refer to our <a href="#SITE_URL/about/privacy" target="_blank">Privacy Policy</a>.
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
                If you have any questions or concerns, please contact us at <a href="mailto:#SUPPORT_EMAIL" target="_blank">#SUPPORT_EMAIL</a>.
            </p>
            <p>
                Sincerely,
                <br>#APPLICATIONNAME Team
            </p>
        </td>
    </tr>'
);
