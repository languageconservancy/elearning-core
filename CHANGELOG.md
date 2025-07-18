# Changelog
All notable changes to this project will be documented in this file.
This includes changes to the backend, web, mobile and the database.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## Change types
- __Added__ for new features.
- __Changed__ for changes in existing functionality.
- __Deprecated__ for once-stable features removed in upcoming releases.
- __Removed__ for deprecated features removed in this release.
- __Fixed__ for any bug fixes.
- __Updated__ for any feature or software updates
- __Security__ to invite users to upgrade in case of vulnerabilities.

## [2.5.0] - 2025-05-15
- frontend: 079d891cc9779d6bcea4160eb1e459690b0574ef
- backend: eed81af6aec43f0df348cc4f1ce4a3da71a89a4d
### Added
- Mobile app version compatibility check and update prompt (db additions)

## [2.4.8] - 2025-05-14
- frontend: 71ea1d7d215cd5685e5660fcd9454b1021b923a2
### Fixed
- Setting of multiple-choice card response types

## [2.4.7] - 2025-05-07
- frontend: 6b5cbcfa016fd3ba07b7386eed12fbf32bd4cdb9
### Fixed
- Multiple-choice card-group exercise response types

## [2.4.6] - 2025-05-05
- frontend: 73f38fd28da8797a3318576f30ff2616487b1fb3
- backend: 1c76fd1bcbe5b7f53df0fd5c50b39559cc50ceda
### Fixed
- Prompt/response types for some exercises

## [2.4.3] - 2025-04-21
- frontend: 21d69947c7290fbc0b381e6dc39c33d13d8ecb81
- backend: 5d47ca2ddc95893efa2a1121c6673f1e8ec3b641
### Changed
- Switch platform configuration veriabe from language to platform to allow multiple platforms for the same language

## [2.4.2] - 2025-04-18
- frontend: 51548114039cae2f3c4014f3f270ca303018d994
- backend: 0f3a2c7a74030846e3a9b6e4079e542d2549365c
### Fixed
- notifyParent now properly being called
- Emails now using SMTP transport and setting return path

## [2.4.1] - 2025-03-25
- frontend: 1608e997bcfa249c2812de86e626d5ade1b841d7
- backend: 8544c16d347f86c3d99a7f427567bb3c6775938e
### Added
- Support for multiple of same blanks in Fill-in-the-blanks exercises
### Fixed
- Manage exercises datatables
- Fill-in-the-blank review questions

## [2.4.0] - 2025-03-11
- frontend: c90ae3cf8b18758ccd3ad52ee98465c53f3c47df
- backend: 2e0a0ebef7b1b867942f87c3e334dc6436c3221f
### Added
- Explicit ccceptance of terms and conditions is now required to use the app, in order to conform to regulations.
- Non-school users under the age of 13 are prompted on next login to enter age and possible change their email to their parent's email so they can be notified, for COPPA compliance.
- Region Policies added to future-proof adherence to multiple regional regulations, such as GDPR
- Users.approximate_age is now updated on login
- If a child user joins a classroom, they are prompted to change their email and name to their school email and real name for their teacher's sake
- Roles are now fetched from the backend to avoid errors and stay in sync
### Changed
- Sign-up requires Age now instead of Date-of-birth, to avoid collecting more personal data than required
- Student role is now called User. New Student role is just for users that are part of a school, since they are under the guardianship of their school, not their parents.
- Routes in Angular are no longer hardcoded and duplicated
- If a <13 child is signing up they are asked for a username instead of their real name
### Fixed
- Multiple Choice exercises with HTML responses now have appropriate padding
- AuthUser cookie was getting set and deleted all over the place, causing issues. Centralized and fixed
- Manage Exercises is now using server-side processing to speed things up!
- Password reset fixed by using proper timestamp, instead of 0, which was causing it to always fail

## [2.3.1] - 2024-11-24
- frontend: 7d6dc0a41c0bdd6d4f0e9f76d0a9322fead57e60
### Changed
- Made punctuation already part of anagram answers.
- Number of correct review answers to unlock next unit now based on number of review cards available.
### Updated
- Look of cards in exercises.
### Fixed
- Non-breaking spaces in translations by using unicode instead of html.
- Teachers no longer have access to admin-only learning paths.
- Virtual keyboard now adds padding to page or modal and autoscrolls.
- Blank form validation in village posts.
- Physical enter button now triggers submit in fill-in typing exercises.
### Added
- Continue button at right of review progress bar when complete.
- Clearer message in review when next unit is unlocked.
- Reusable selectable card component.
- Reusable non-selectable card component.
- Mock assets, database, and configs added for convenient/consistent testing.
### Removed
- User location from profile for privacy compliance.

## [2.2.6] - 2024-11-24
- backend: 3af438c146531e2646710789ad9ff422e0d985b3
### Added
- Mock assets, database, and configs added for convenient/consistent testing.
- Content developer platform role added for better user privacy (Can't view/edit/delete user data).
### Fixed
- CardUnits table being properly added to now.
- Available review cards fixed.
- Manage exercises add-file popup fixed.
- Audio loading in manage exercises.
- True/false now properly stores datatype checkbox info.
### Removed
- Unused user location for privacy compliance.

## [2.1.0] - 2024-06-20
- frontend: 95e877737b073314f1279757769010bcef9eb849
- backend: faecc3d6ec7b3528b2f141aebc7f3263314ab6af
### Changed
- Cards component rewritten as Unit component using state machine for handling events and transitions
- Wrong attempts are tracked by exercise options ids
- The progress nav bar is now a child component of the Unit component
- Crow production config now same as staging since staging db was migrated to production
- Refactored getExercise() to be reusable smaller functions
### Fixed
- Idle timer popup working properly again
- Fixed updateUser code
- Fixed unit completion calculation
- Custom HTML exercises now getting accounted for in unit completion calculation
- Fixed contactUs email that gets sent out
- Forgot password email fixed/updated to be more user friendly
### Removed
- Doorbell from all platforms except Owóksape
### Added
- Facebook SDK since it's required by ngx-facebook
- getPublicUser() endpoint added for public profile page
### Updated
- Updated AWS S3 credentials for all platforms

## [2.0.4] - 2024-04-16
- frontend: be9ac66ca179673fe7171544c165624342b58bfe
- backend: 88e0ad287796cb7530b413ce291f12e18275c472
### Added
- Added Cowlitz translations file
### Changed
- Only display +#XP in reward popups if #XP > 0
- Using metakeys and altkeys for language toggling in virtual keyboard
- Using various Cowlitz-specific icons now
### Fixed
- Sign-up form validation displaying useful messages now
- Language text now using Trebuchet MS, sans-serif font which displays all diacritcs properly and bold characters properly, and distinguishes b/t lower L and upper i
- Fixed keyboard for village search bar
- Added font-bold class to language related headers since bootstrap uses font-weight 500 which doesn't exist in Trebuchet MS, sans-serif
- Teacher portal user import by Excel no longer trying to process empty lines
### Updated
- Date of birth tooltip now click popup that disappears after 2.5s
- Teacher portal user import by Excel now displays useful error messages
- Social logins now not required. Had to add files in mobile projects

## [2.0.3] - 2024-03-19
- fontend: 8ece11d3a291a97f74a96d423fc5638c214077b9
- backend: 76558b51e98e8b07ab3975c6da41d447629654c3
### Added
- Added sign-up link below login submit button for new users
- Added warning in deactivate account modal
- Added request account deletion to settings
- Added privacy and terms to mobile dropdown since there's no footer
- Added account deletion in settings with multiple layers of confirmation
- Added info icon and tooltip about why we require date of birth (for Apple policy)
- Added Try it out button for trialing the elearning platform (for Apple policy)
- Added user email to account settings so users know
### Updated
- Updated look and ordering of admin sidebar menu
### Fixed
- Anagram for cowlitz now treats digraphs and trigraphs as one character
- Fixed login buttons for tablets
- Ensuring user data is only fetched or modified by that user
- On admin unit import, replace two-unicode-value digraphs with single unicode versions
### Removed
- Removed redundant device-info.service
- Removed text discussing visually impaired issues
- Removed admin mobile text inputs for about pages, since they are no longer used

## [2.0.2] - 2024-03-05
- frontend: 231b8a12e275b413b3e42e4ade40fd4a12abf5b8
- backend: n/a
### Fixed
- Fixed review answer null unit_id issue in typescript
- Fixed false showing of keyboard when tappping fill-in MCQ inputs

## [2.0.1] - 2024-03-03
- frontend: 817c495859e1824d114c9d8344bd33f9192acabc
- backend: 1d179805edb50e6229960e722985b58500f1a397
### Added
- Added splash screens for owoksape, ammilaau, hoite woiperes from figma
### Changed
- Separated production and staging apps in Google signin developer console to ease verification
### Fixed
- Fixed social logins so they don't try to initialize if not valid credentials don't exist

## [2.0.0] - 2024-02-26
- frontend: 23ff3fd6c523a5449b88a2501604158130e694a6
- backend: fd6343adf3538e46f8ae3588d11e6839b01453e3
### Added
- Added Cowlitz eLearning Platform to the repos
- Added Cypress testing framework since it's Angular's new standard
- Added Cypress screenshot testing to quickly check for UI responsiveness issues
- Videos capability added to lessons
- Videos added to all exercise types
- Added close button to new SimpleKeyboard
- Added backend/scripts script to update local backend on Mac using environment variables
### Updated
- Made entire app responsive except Teacher Portal
- Changed ClassroomUnits component duplication of Units component to subclass it instead
- Swapped out VirtualKeyboard for SimpleKeyboard for mobile compatibility
- Updated NPM packages, due to CapacitorJS v5 requirements
- Match-The-Pair matched responses now turn gray
- HoChunk keyboard diacritics now have blue background and white text
### Changed
- Replaced Unity apps with CapacitorJS v5 Android and iOS builds, enabling a single codebase
- Doorbell button only shows on large screens now, but can access in hamburger menu.
- Refactored social login code
- Default fonts: 16px on xs, sm, md; 18px on >= lg
- Using fewer custom font sizes
- Removed bold from prompt/response text to allow HTML formatting to work properly from card data.
- Replaced low resolution images with font-awesome icons for reward popups and audio type images.
- Site name now links to Learning Path page instead of dashboard
- Changed database users table apple_user_id field to apple_id for consistency with facebook and google
### Fixed
- Unit-specific Village and Review disabled icons fixed by replaced cover-up with filter
- Background image now always fills the page instead of showing colored background on edges on some screens
- Preventing site name from running into navbar buttons
- Refactored and removed duplicate welcome-box and container from all pages except app component
- Changed most px references to rem units to better works on different screen sizes
- Refactored settings screens to remove duplications
- Lesson next/previous buttons now aligned to right edge of lesson content
- Navbar no longer invisibly covers breadcrumb, which fixes breadcrumb click area
- Non audio-only cards audio icon fixed to prevent overflow which caused horitzonal scrolling
### Removed
- Removed empty social links in footer

## [1.11.3] - 2023-10-14
- web-app: 27bf6692b9327897ab50ab6637c2312f98249f6b
- backend: a6a5b798bd3fd7793910bc8b46f4a90aab3e310b
### Fixed
- Bad access of JavaScript variable 'google' before API loaded
- Facebook & Google login client IDs for all platforms using production IDs
- Data deletion switched to database table row

## [1.11.2] - 2023-10-14
- web-app: e49e812bf4228adeaed0d6caa1f563527345e3a4
- backend: 5917ca0b0556724f6fba4d9e5827c35973803717
### Fixed
- Google Sign-in
- Facebook Sign-in
- Navbar supports longer site names
- App background color now platform-specific
- Bug fixes
- Custom HTML exercises allow quotes now
- Clever Sign-in fixed
### Updated
- Google Sign-in button and API

## [1.11.1] - 2023-10-02
### Commit Hashes
- web-app: b8290c3aac0af32a7bf19d7cd110eaa9f84be70b
- backend: 4ed610a0959a77f374e1ddc4459a432cbbf8931e
- parent: 70db415a3d4d3d6ba52e16653fe2a028ec9864e9
### Fixed
- Login error messages display properly now.
- Failed logins now display clear messages to user.
- Social pages used when sharing posts are fixed.
- Fixed login issue where only /backend worked. Now backend/admin, backend/admin/users/login also work.
- Fixed admin interface so if admin navigates to page after session is expired, they are redirected to login, and then redirected back to the page they originally requested.

## [1.11.0] - 2023-07-29
### Commit Hashes
- web-app: 2e96a040027d4c93cc15878b3fb997a484455822
- backend: 212f74252ebabb3841bc0308c85b9a8d7fa7d277
### Added
- CapacitorJS with android and ios directories
- CapacitorCookies and CapacitorHttp plugins
- Capacitor Preferences plugin to use Android's SharedPreferences and iOS' UserDefaults for persistent AuthUser and AuthToken
- Ho-Chunk keyboard for typing exercises
- Ho-Chunk icons, loading gif, background
- Opengraph image for Ute
- Level reordering
- Level and unit resequencing when they are moved
### Changed
- Folder structure to have pages folder
- Angular v9 to v14
- Only display social login buttons if config is present
### Updated
- Angular v9 to v14
- ng-recaptcha
- ngx-wig
- angularx-social-login
- @danielmoncada/angular-datetime-picker to v14
- Android to SDK 33 and Gradle 8.0.1
- Nuuwyaga background
- Default loader gif with generic one
### Removed
- codelyzer
- all form protection in backend
### Fixed
- Match-the-pairs graying out bug
- Missing reward popup translations
- Exercise service conflation due to sharing it across exercise types
- Hardcoded owoksape references in teacher portal
- resetProgressData by changing requiresAuth from false to true
- Match-the-pairs answer check infinite loop
- recaptcha v2 site key
- unit reordering
- Social logins are now simpler and send clearer error messages

## [1.10.2] - 2023-06-09
### Commit Hashes
- backend: ba8b973e16377a72034cac6ae8f7cfe84ab3d9a3
- parent: f0360b38576bc56206ce4b5b6c0603bd1d5c7e70
### Fixed
- Authentication for mobile apps
- Login/logout for mobile apps
- Only using session authentication for admin site

## [1.10.1] - 2023-05-22
### Commit Hashes
- backend: 324cbbfeead7349e5391308ca470ddeeb4f6858e
### Added
- Config for testing.elearnresource.com for external party testing to get familiar with the platform
### Fixed
- CakePHP 4 Authentication & Authorization middleware
- File uploading in the backend
- Deprecated code
- Review exercise generation bug, by returning empty array instead of null
- Gallery image path by adding missing slash separator
- getUser bugs by checking for empty result
- Card Entity bugs by adding try/catches
### Changed
- CakePHP 3's authentication/authorization was changed to CakePHP 4's Authentication and Authorization middleware.
- Changed from ozee31/cakephp-cors package to our own forked one with a bug fix.
## [1.10.0] - 2023-03-29
### Commit Hashes
- backend: 04864f1d76fc36a5cd9117b1dd9c05772a82718b
- frontend: 4685cec58964dc91bc6937b3629fe521c3e03410
- parent: 85f0532fbc315a805e8ed87a9d21cd89c2a0ddb3
### Added
- RichText format now supported in Card Data lakota and english fields, and displayed in exercises
- Card management now has Table of RichText examples
- Teachers can now archive classrooms
- Unit tests
- Manage exercise UI now display 'loading...' when loading child templates
- Pre-commit checks for passing unit tests and PSR12 code style added
- Added route conversions to keep old endpoints mobile app compatible with new API
### Changed
- CakePHP upgrade from v3.6 to v4.2
- All edit buttons use pencil icon. Eye icon is reserved for pop-up views
### Fixed
- Teacher portal roles fixed
- Teacher invite link fixed
- Teacher portal now has teacher, student, and substitute roles. Substitute will eventually be read-only
- School management icon
- Match the pair editor now submits properly
- SweetAlert popup icons
- Endless loader in classroom tab
- Dictionary references loading in card management now only loads necessary data
- resetPassword works again
- Improved clarity of messages
- Unit contents move-up/move-down buttons now appear when they should
- Bug where if user hasn't done any units their global fire is null
- Add missing language variable to Exercise management for true/false card picker html
- Bug fixes

## [1.9.4] - 2022-12-28
### Commit Hashes
- frontend: 46ad5dcf1188aa315b7eaf5f7e46f8ae04e295f6
### Fixed
- Ute production site name switched back from Nu Wayga to Nuuwayga
- Ute translations.json file added

## [1.9.3] - 2022-12-16
### Commit Hashes
- backend: 2478167c216c0f7e12b7c40a8027152456201ac9
- frontend: 252ecfd406989fffac7419a282e26676a5c26bd8
### Fixed
- Bug fixes
### Added
- Added Ho-Chunk configs
### Changed
- Updated starter database

## [1.9.2] - 2022-11-10
### Commit Hashes
- backend: 1c16d81dfb7552746c6f9e630c72e3115798d986
- web-app: a8ae4c8354a1e23411e780adf498065e32dc47d4
### Fixed
- Fixed bugs
- Match-The-Pair exercises with long words is better supported now
### Added
- Google login credentials created for Ute and Crow staging and production sites

## [1.9.1] - 2022-10-17
### Commit Hashes
- backend: 3b7f69899dfbea4b4a6d38fee6c86ba967041a21
- web-app: 229672b6551b4f0ecf3f7e5fd2b6210383ed3bf9
### Fixed
- Preventing duplicate cards in review exercises (multiple-choice and match-the-pair)
- Fixed bugs
- Fixed login issues with Apple, Facebook, Google, and Clever
- Fixed web-app unit tests
- Fixed 3-block lessons duplicate 2nd block
### Added
- Removed composer.lock from version control
- Using site-specific environment variables via backend/config/.env
- Crow and Ute will now have doorbell.io feedback interfaces


## [1.9] - 2022-08-23
### Commit Hashes
- backend: 86a144aa42802b0620e34bf053d0ccbf93000421
- web-app: d2f29e5713ed45e13fcba1455fccf777cb937fb6
### Fixed
- Classroom levels won't get stuck in wrong learning path now
- Privacy Policy and Terms & Conditions updated
- About page using dynamic data
- Multiple apostrophe in typing exercises is handled correctly now
- Site Settings Undefined Server Error fixed
- Fixed deprecated PHP calls
- Fixed Unit Uploader trailing whitespace issues
### Added
- Ute keyboard
- Ute homepage graphic
- Ability to switch keyboard in forum
- Apple login
- Spaces in typing blanks are now supported

## [1.8.10] - 2022-06-21
### Commit Hashes
- backend: fee772842ba3c8c8d05014da5dfd3f151eb9fe84
- web-app: c2223f7700ac8c2ceb1b6fbc1c1dd94fecf701f6
### Fixed
- Snackbar service better handling null msgs
- count()s changed to empty() to comply with PHP7

## [1.8.9] - 2022-06-14
### Commit Hashes
- backend: 47d0467fd9d3ab566dd63ee5d22aad604782074c
- web-app: 692493ef8c4e60a00dbf8772e98681cdcac1e939

### Fixed
- Navbar buttons click area fixed
- Default translations file no longer has extraneous comma
- API security fix
- Missing uploadfile directory, now created if missing
- Invalid Diaeresis characters now being replaced in Unit Uploader
- Upgrade version of CakePHP from 3.5.* to 3.6.* to work with PHP 7

### Added
- Images can now be zoomed in
- Shadow-banning added
- School User manager

## [1.8.8] - 2022-06-02
### Commit Hashes
- backend: 9338f14751e1ab23e2a4834286de2fdb45aea2c2

### Fixed
- Bulk Unit Uploader fixed to better handle plosive characters

## [1.8.7] - 2022-05-23
### Commit Hashes
- backend: b20e3388f5541da44208a55454226cd28f087a0f

### Fixed
- Multi-file uploader in admin interface is fixed.

## [1.8.6] - 2022-05-19
### Commit Hashes
- backend: eee9025f3ecfe721543bfef30436163044e9377a
- web-app: df70456bf8f96942bbcc8e235afd0b42cdee356e

### Added
- Now using the multilanguage e-learning architecture
- Upgraded PHP 5.6.40 to 7.2.34
- Unified all snackbar messages into a snackbar service/component

### Fixed
- Admin multi-file upload fixed
- Fixed some unit and end-to-end tests
- Fixed grammar in signup messages

## [1.8.5] - 2022-03-08
### Commit Hashes
- backend: 8fe370b1d73d6ffa4d57d82100b316f7be600601
- web-app: 34d3aa19325b176c7ec26c9c009315249c2a5b7f

### Added
- User privacy settings now contains a link to our Privacy Policy

### Fixed
- Forum post reporting improved.
- Replies can be reported without affecting their parent post.

## [1.8.4] - 2021-01-10
### Commit Hashes
- backend: no changes
- web-app: 68aeb5cd0b585a01f6f8244a3d5a751fc761937a

### Fixed
- Privacy policy added About section of Owóksape
- Virtual keyboard no longer deletes multiple characters when backspace is typed.

## [1.8.3] - 2021-11-30
### Commit Hashes
- backend: no changes
- web-app: f2e64d5bec6f43165c513950e1229d56c4ae3fc7

### Fixed
- Owóksape now works even if schools others block Facebook traffic.

## [1.8.2] - 2021
### Commit Hashes
- backend: no changes
- web-app: 2a81e680e39c05f6a36ff3c0c01673fdb062d278

### Fixed
- Clever login fixed. It wasn't logging in properly. Some user params added to token retrieval.

## [1.8.1] - 2021-10-26
- backend: 07ea88bad8407d6f7650b8dcbc3c621bada7950d
- web-app: 2a81e680e39c05f6a36ff3c0c01673fdb062d278

### Added
- Users can now login to Owóksape via Clever
- Setting clever email to clever ID if email is not provided by Clever API

## [1.8.0] - 2021-09-07
### Commit Hashes
- backend: 1e285abdcd4544fa9b4413f9b7bbf8671610164c
- web-app: 655a7505fd5802ea029bf2e2247d81769d9d2f57

### Added
- Teachers can now change and reset student passwords from the Teacher Portal

### Changed

### Fixed
- Expired token returns user to login page, instead of requiring user to log out manually.
- Popup for joining a class is fixed.


## [1.7.0] - 2021-08-26
### Commit Hashes
- backend: 342e7813c3831971d83e8744ef32fae1c124f37c
- web-app: 87d1b891145b3f8dc8eac49221050089830989c8

### Added
- Teachers can now create lessons that automatically have "All Units"
- Teachers may use arrow buttons to add/subtract students and units from menus
- Scheduled release, optional, and inactive switches are all fully implemented and also have "Bulk Changes(All Units)" switches
- Classroom tab shows student perspective even if you are a teacher so that it is clear what is locked/unlocked/scheduled
- Teacher message now has WYSIWYG (what you see is what you get) editor so you can more easily supply links or updates to classroom header.
- Teachers and Students can now log in using Clever, either from the Clever portal or with the Sign In with Clever button on our login page.
- Database
  - add custom_html column for mobile
- Add support for english-to-english and lakota-to-lakota exercises

### Changed
- Users stay logged in longer (1 year) without having to sign-out and back in again.

### Fixed
- Several progress bugs are solved and users should pick up right where they left off regardless of whether in classroom or main path.
- Improvements to progress and activity reports so they load almost instantly with current data.
- The navbar doesn't wrap with long user names anymore.
- If ResizeImages don't work, now resorting to FullUrl images
- Users can now always see text in fill-in typing exercises typing area.
- Fillin blanks no longer incorrectly added in place of English words that are the same as Lakota blanked words.
- Users can use lowercase for uppercase words and still get the correct answer
- Allowed emails was fixed, like having a plus sign
- Fillin typing fixed when hitting enter to submit


## [1.6.0] - 2020-04-22
### Commit Hashes
- backend: 18767d54ba6a217ed6024599e09581d10be1708d
- web-app: 34c67aaf5da7d37f8115703453f4549c50458838

### Added
- new reward popups
- progress navigation and progress feedback
- single click fill in blank
- social sharing
- keyboard shortcuts

### Changed
- improved audio handling. centralized audio service and audio functions
- major performance improvements
- many additional reduced clicking, user experience, and layout improvements

### Fixed
- API call duplication bugfixes
- review fixes
- forum and leaderboard fixes

## [1.5.0] - 2019-12-09
### Commit Hashes
- backend: 1e6c6851ee6b755e7ecc21a7603769338d75d778
- web-app: f5621a45795cbf1bc49523c1754cd8c797559ae6

### Added
- Optional unit backend code

### Changed
- Updated AWS Credentials
- Backend admin file naming now without hash
- Not incrementing review counter for wrong answers
- Created default custom_html lesson font

### Fixed
- Apostrophe validation
- Match the pairs missing image issue
- Issue when no unit review exists. Goes to global review
- Unit completion for match-the-pair
- Superadmin forum moderation privileges
- Issue where unit review was available before unit was completed

## [1.4.0] - 2019-10-10
### Commit Hashes
- backend: e217b70e79cd95f45bc1b952ad0f972c0b5b1e4f
- web-app: 96510745ad1f620a3c58aeed8611f3fae5880e14

### Added
- New ActivityTypes table in the database for new review algorithm
- Comments to database fields ActivityTypes, Cards, CardUnits, Exercises

### Changed
- Updated Review algorithm to use activity type percentages
- UI changes (aesthetics)
- ReferenceDictionary table change: Changed 'audio' filed 0's to NULL's. Still need to find where this field is getting set to 0 instead of NULL. This causes the error: CONSTRAINT 'FK_reference_dictionary_files' FOREIGN KEY ('audio') REFERENCES 'files' ('id') ON DELETE SET NULL ON UPDATE NO ACTION
- Changed CardType ‘verb’ to ‘word’ for all ‘verb’ cards

### Removed
- The following units from the CardUnits table because they didn’t contain any cards where include_review = ‘1’. 177, 138, 184, 203, 211, 254, 257, 259. Need to fix how cards get added in admin backend, or just have a sheet that we check

## [0.1.0] - 2019-03-07
### Commit Hashes
- backend: 0eb212c4ae78093b9ef868ca15f9cd61aeacaf4e
- web-app: 85e407efed6eab7e1b52e342e79a84e4fc8d8397
