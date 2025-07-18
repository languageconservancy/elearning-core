<?php

namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\EventInterface;
use Exception;
use App\Model\Table\ActivityTypesTable;
use App\Model\Table\BannedWordsTable;
use App\Model\Table\BonusPointsTable;
use App\Model\Table\CardTable;
use App\Model\Table\CardcardgroupTable;
use App\Model\Table\CardgroupTable;
use App\Model\Table\CardgrouptypeTable;
use App\Model\Table\CardtypeTable;
use App\Model\Table\CardUnitsTable;
use App\Model\Table\ClassroomLevelUnitsTable;
use App\Model\Table\ClassroomsTable;
use App\Model\Table\ClassroomUsersTable;
use App\Model\Table\ClassStudentsTable;
use App\Model\Table\ContentsTable;
use App\Model\Table\DictionaryTable;
use App\Model\Table\EmailcontentsTable;
use App\Model\Table\ExercisesTable;
use App\Model\Table\ExerciseCustomOptionsTable;
use App\Model\Table\ExerciseoptionsTable;
use App\Model\Table\FilesTable;
use App\Model\Table\ForumsTable;
use App\Model\Table\ForumFlagsTable;
use App\Model\Table\ForumPostsTable;
use App\Model\Table\ForumFlagReasonsTable;
use App\Model\Table\ForumPostViewersTable;
use App\Model\Table\FriendsTable;
use App\Model\Table\GlobalFiresTable;
use App\Model\Table\GradesTable;
use App\Model\Table\InflectionsTable;
use App\Model\Table\LearningpathsTable;
use App\Model\Table\LearningspeedTable;
use App\Model\Table\LessonFrameBlocksTable;
use App\Model\Table\LessonFramesTable;
use App\Model\Table\LessonsTable;
use App\Model\Table\LevelsTable;
use App\Model\Table\LevelUnitsTable;
use App\Model\Table\PasswordresetTable;
use App\Model\Table\PathlevelTable;
use App\Model\Table\PointReferencesTable;
use App\Model\Table\ProgressTimersTable;
use App\Model\Table\RecordingAudiosTable;
use App\Model\Table\ReviewCountersTable;
use App\Model\Table\ReviewQueuesTable;
use App\Model\Table\ReviewVarsTable;
use App\Model\Table\RolesTable;
use App\Model\Table\SchoolLevelsTable;
use App\Model\Table\SchoolRolesTable;
use App\Model\Table\SchoolsTable;
use App\Model\Table\SchoolUsersTable;
use App\Model\Table\SitesettingsTable;
use App\Model\Table\UnitdetailsTable;
use App\Model\Table\UnitFiresTable;
use App\Model\Table\UnitsTable;
use App\Model\Table\UserActivitiesTable;
use App\Model\Table\UserimagesTable;
use App\Model\Table\UserLevelBadgesTable;
use App\Model\Table\UserPointsTable;
use App\Model\Table\UserprogressTable;
use App\Model\Table\UserSettingsTable;
use App\Model\Table\UsersTable;
use App\Model\Table\UserUnitActivitiesTable;
use App\Model\Table\WordlinksTable;

class AppController extends Controller
{
    protected ?ActivityTypesTable $ActivityTypes = null;
    protected ?BannedWordsTable $BannedWords = null;
    protected ?BonusPointsTable $BonusPoints = null;
    protected ?CardTable $Card = null;
    protected ?CardcardgroupTable $Cardcardgroup = null;
    protected ?CardgroupTable $CardGroups = null;
    protected ?CardgrouptypeTable $CardGroupTypes = null;
    protected ?CardtypeTable $Cardtype = null;
    protected ?CardUnitsTable $CardUnits = null;
    protected ?ClassroomLevelUnitsTable $ClassroomLevelUnits = null;
    protected ?ClassroomsTable $Classrooms = null;
    protected ?ClassroomUsersTable $ClassroomUsers = null;
    protected ?ClassStudentsTable $ClassStudents = null;
    protected ?ContentsTable $Contents = null;
    protected ?DictionaryTable $Dictionary = null;
    protected ?EmailcontentsTable $Emailcontents = null;
    protected ?ExercisesTable $Exercises = null;
    protected ?ExerciseCustomOptionsTable $ExerciseCustomOptions = null;
    protected ?ExerciseoptionsTable $Exerciseoptions = null;
    protected ?FilesTable $Files = null;
    protected ?ForumsTable $Forums = null;
    protected ?ForumFlagsTable $ForumFlags = null;
    protected ?ForumPostsTable $ForumPosts = null;
    protected ?ForumFlagReasonsTable $ForumFlagReasons = null;
    protected ?ForumPostViewersTable $ForumPostViewers = null;
    protected ?FriendsTable $Friends = null;
    protected ?GlobalFiresTable $GlobalFires = null;
    protected ?GradesTable $Grades = null;
    protected ?InflectionsTable $Inflections = null;
    protected ?LearningpathsTable $Learningpaths = null;
    protected ?LearningspeedTable $Learningspeed = null;
    protected ?LessonFrameBlocksTable $LessonFrameBlocks = null;
    protected ?LessonFramesTable $LessonFrames = null;
    protected ?LessonsTable $Lessons = null;
    protected ?LevelsTable $Levels = null;
    protected ?LevelUnitsTable $LevelUnits = null;
    protected ?PasswordresetTable $Passwordreset = null;
    protected ?PathlevelTable $Pathlevel = null;
    protected ?PointReferencesTable $PointReferences = null;
    protected ?ProgressTimersTable $ProgressTimers = null;
    protected ?RecordingAudiosTable $RecordingAudios = null;
    protected ?ReviewCountersTable $ReviewCounters = null;
    protected ?ReviewQueuesTable $ReviewQueues = null;
    protected ?ReviewVarsTable $ReviewVars = null;
    protected ?RolesTable $Roles = null;
    protected ?SchoolLevelsTable $SchoolLevels = null;
    protected ?SchoolRolesTable $SchoolRoles = null;
    protected ?SchoolsTable $Schools = null;
    protected ?SchoolUsersTable $SchoolUsers = null;
    protected ?SitesettingsTable $Sitesettings = null;
    protected ?UnitdetailsTable $Unitdetails = null;
    protected ?UnitFiresTable $UnitFires = null;
    protected ?UnitsTable $Units = null;
    protected ?UserActivitiesTable $UserActivities = null;
    protected ?UserimagesTable $Userimages = null;
    protected ?UserLevelBadgesTable $UserLevelBadges = null;
    protected ?UserPointsTable $UserPoints = null;
    protected ?UserprogressTable $Userprogress = null;
    protected ?UsersettingsTable $UserSettings = null;
    protected ?UsersTable $Users = null;
    protected ?UserUnitActivitiesTable $UserUnitActivities = null;
    protected ?WordlinksTable $Wordlinks = null;

    /**
     * @throws Exception
     */
    public function initialize(): void
    {
        parent::initialize();

        // Load components relevant to both Api and Admin prefixes
        $this->loadComponent('Flash');
        if ($this->request->getParam('controller') !== 'Social') {
            $this->loadComponent('Authentication.Authentication');
        }
    }

    protected function getTableInstance(string $name)
    {
        return $this->getTableLocator()->get($name);
    }

    protected function getActivityTypesTable(): ActivityTypesTable
    {
        if ($this->ActivityTypes === null) {
            $this->ActivityTypes = $this->getTableLocator()->get('ActivityTypes');
        }
        return $this->ActivityTypes;
    }

    protected function getBannedWordsTable(): BannedWordsTable
    {
        if ($this->BannedWords === null) {
            $this->BannedWords = $this->getTableLocator()->get('BannedWords');
        }
        return $this->BannedWords;
    }

    protected function getBonusPointsTable(): BonusPointsTable
    {
        if ($this->BonusPoints === null) {
            $this->BonusPoints = $this->getTableLocator()->get('BonusPoints');
        }
        return $this->BonusPoints;
    }

    protected function getCardTable(): CardTable
    {
        if ($this->Card === null) {
            $this->Card = $this->getTableLocator()->get('Card');
        }
        return $this->Card;
    }

    protected function getCardcardgroupTable(): CardcardgroupTable
    {
        if ($this->Cardcardgroup === null) {
            $this->Cardcardgroup = $this->getTableLocator()->get('Cardcardgroup');
        }
        return $this->Cardcardgroup;
    }

    protected function getCardgroupTable(): CardgroupTable
    {
        if ($this->CardGroups === null) {
            $this->CardGroups = $this->getTableLocator()->get('Cardgroup');
        }
        return $this->CardGroups;
    }

    protected function getCardgrouptypeTable(): CardgrouptypeTable
    {
        if ($this->CardGroupTypes === null) {
            $this->CardGroupTypes = $this->getTableLocator()->get('Cardgrouptype');
        }
        return $this->CardGroupTypes;
    }

    protected function getCardtypeTable(): CardtypeTable
    {
        if ($this->Cardtype === null) {
            $this->Cardtype = $this->getTableLocator()->get('Cardtype');
        }
        return $this->Cardtype;
    }

    protected function getCardUnitsTable(): CardUnitsTable
    {
        if ($this->CardUnits === null) {
            $this->CardUnits = $this->getTableLocator()->get('CardUnits');
        }
        return $this->CardUnits;
    }

    protected function getClassroomLevelUnitsTable(): ClassroomLevelUnitsTable
    {
        if ($this->ClassroomLevelUnits === null) {
            $this->ClassroomLevelUnits = $this->getTableLocator()->get('ClassroomLevelUnits');
        }
        return $this->ClassroomLevelUnits;
    }

    protected function getClassroomsTable(): ClassroomsTable
    {
        if ($this->Classrooms === null) {
            $this->Classrooms = $this->getTableLocator()->get('Classrooms');
        }
        return $this->Classrooms;
    }

    protected function getClassroomUsersTable(): ClassroomUsersTable
    {
        if ($this->ClassroomUsers === null) {
            $this->ClassroomUsers = $this->getTableLocator()->get('ClassroomUsers');
        }
        return $this->ClassroomUsers;
    }

    protected function getClassStudentsTable(): ClassStudentsTable
    {
        if ($this->ClassStudents === null) {
            $this->ClassStudents = $this->getTableLocator()->get('ClassStudents');
        }
        return $this->ClassStudents;
    }

    protected function getContentsTable(): ContentsTable
    {
        if ($this->Contents === null) {
            $this->Contents = $this->getTableLocator()->get('Contents');
        }
        return $this->Contents;
    }

    protected function getDictionaryTable(): DictionaryTable
    {
        if ($this->Dictionary === null) {
            $this->Dictionary = $this->getTableLocator()->get('Dictionary');
        }
        return $this->Dictionary;
    }

    protected function getEmailcontentsTable(): EmailcontentsTable
    {
        if ($this->Emailcontents === null) {
            $this->Emailcontents = $this->getTableLocator()->get('Emailcontents');
        }
        return $this->Emailcontents;
    }

    protected function getExercisesTable(): ExercisesTable
    {
        if ($this->Exercises === null) {
            $this->Exercises = $this->getTableLocator()->get('Exercises');
        }
        return $this->Exercises;
    }

    protected function getExerciseCustomOptionsTable(): ExerciseCustomOptionsTable
    {
        if ($this->ExerciseCustomOptions === null) {
            $this->ExerciseCustomOptions = $this->getTableLocator()->get('ExerciseCustomOptions');
        }
        return $this->ExerciseCustomOptions;
    }

    protected function getExerciseoptionsTable(): ExerciseoptionsTable
    {
        if ($this->Exerciseoptions === null) {
            $this->Exerciseoptions = $this->getTableLocator()->get('Exerciseoptions');
        }
        return $this->Exerciseoptions;
    }

    protected function getFilesTable(): FilesTable
    {
        if ($this->Files === null) {
            $this->Files = $this->getTableLocator()->get('Files');
        }
        return $this->Files;
    }

    protected function getForumsTable(): ForumsTable
    {
        if ($this->Forums === null) {
            $this->Forums = $this->getTableLocator()->get('Forums');
        }
        return $this->Forums;
    }

    protected function getForumFlagsTable(): ForumFlagsTable
    {
        if ($this->ForumFlags === null) {
            $this->ForumFlags = $this->getTableLocator()->get('ForumFlags');
        }
        return $this->ForumFlags;
    }

    protected function getForumPostsTable(): ForumPostsTable
    {
        if ($this->ForumPosts === null) {
            $this->ForumPosts = $this->getTableLocator()->get('ForumPosts');
        }
        return $this->ForumPosts;
    }

    protected function getForumFlagReasonsTable(): ForumFlagReasonsTable
    {
        if ($this->ForumFlagReasons === null) {
            $this->ForumFlagReasons = $this->getTableLocator()->get('ForumFlagReasons');
        }
        return $this->ForumFlagReasons;
    }

    protected function getForumPostViewersTable(): ForumPostViewersTable
    {
        if ($this->ForumPostViewers === null) {
            $this->ForumPostViewers = $this->getTableLocator()->get('ForumPostViewers');
        }
        return $this->ForumPostViewers;
    }

    protected function getFriendsTable(): FriendsTable
    {
        if ($this->Friends === null) {
            $this->Friends = $this->getTableLocator()->get('Friends');
        }
        return $this->Friends;
    }

    protected function getGlobalFiresTable(): GlobalFiresTable
    {
        if ($this->GlobalFires === null) {
            $this->GlobalFires = $this->getTableLocator()->get('GlobalFires');
        }
        return $this->GlobalFires;
    }

    protected function getGradesTable(): GradesTable
    {
        if ($this->Grades === null) {
            $this->Grades = $this->getTableLocator()->get('Grades');
        }
        return $this->Grades;
    }

    protected function getInflectionsTable(): InflectionsTable
    {
        if ($this->Inflections === null) {
            $this->Inflections = $this->getTableLocator()->get('Inflections');
        }
        return $this->Inflections;
    }

    protected function getLearningpathsTable(): LearningpathsTable
    {
        if ($this->Learningpaths === null) {
            $this->Learningpaths = $this->getTableLocator()->get('Learningpaths');
        }
        return $this->Learningpaths;
    }

    protected function getLearningspeedTable(): LearningspeedTable
    {
        if ($this->Learningspeed === null) {
            $this->Learningspeed = $this->getTableLocator()->get('Learningspeed');
        }
        return $this->Learningspeed;
    }

    protected function getLessonFrameBlocksTable(): LessonFrameBlocksTable
    {
        if ($this->LessonFrameBlocks === null) {
            $this->LessonFrameBlocks = $this->getTableLocator()->get('LessonFrameBlocks');
        }
        return $this->LessonFrameBlocks;
    }

    protected function getLessonFramesTable(): LessonFramesTable
    {
        if ($this->LessonFrames === null) {
            $this->LessonFrames = $this->getTableLocator()->get('LessonFrames');
        }
        return $this->LessonFrames;
    }

    protected function getLessonsTable(): LessonsTable
    {
        if ($this->Lessons === null) {
            $this->Lessons = $this->getTableLocator()->get('Lessons');
        }
        return $this->Lessons;
    }

    protected function getLevelsTable(): LevelsTable
    {
        if ($this->Levels === null) {
            $this->Levels = $this->getTableLocator()->get('Levels');
        }
        return $this->Levels;
    }

    protected function getLevelUnitsTable(): LevelUnitsTable
    {
        if ($this->LevelUnits === null) {
            $this->LevelUnits = $this->getTableLocator()->get('LevelUnits');
        }
        return $this->LevelUnits;
    }

    protected function getPathlevelTable(): PathlevelTable
    {
        if ($this->Pathlevel === null) {
            $this->Pathlevel = $this->getTableLocator()->get('Pathlevel');
        }
        return $this->Pathlevel;
    }

    protected function getPasswordresetTable(): PasswordresetTable
    {
        if ($this->Passwordreset === null) {
            $this->Passwordreset = $this->getTableLocator()->get('Passwordreset');
        }
        return $this->Passwordreset;
    }

    protected function getPointReferencesTable(): PointReferencesTable
    {
        if ($this->PointReferences === null) {
            $this->PointReferences = $this->getTableLocator()->get('PointReferences');
        }
        return $this->PointReferences;
    }

    protected function getProgressTimersTable(): ProgressTimersTable
    {
        if ($this->ProgressTimers === null) {
            $this->ProgressTimers = $this->getTableLocator()->get('ProgressTimers');
        }
        return $this->ProgressTimers;
    }

    protected function getRecordingAudiosTable(): RecordingAudiosTable
    {
        if ($this->RecordingAudios === null) {
            $this->RecordingAudios = $this->getTableLocator()->get('RecordingAudios');
        }
        return $this->RecordingAudios;
    }

    protected function getReviewCountersTable(): ReviewCountersTable
    {
        if ($this->ReviewCounters === null) {
            $this->ReviewCounters = $this->getTableLocator()->get('ReviewCounters');
        }
        return $this->ReviewCounters;
    }

    protected function getReviewQueuesTable(): ReviewQueuesTable
    {
        if ($this->ReviewQueues === null) {
            $this->ReviewQueues = $this->getTableLocator()->get('ReviewQueues');
        }
        return $this->ReviewQueues;
    }

    protected function getReviewVarsTable(): ReviewVarsTable
    {
        if ($this->ReviewVars === null) {
            $this->ReviewVars = $this->getTableLocator()->get('ReviewVars');
        }
        return $this->ReviewVars;
    }

    protected function getRolesTable(): RolesTable
    {
        if ($this->Roles === null) {
            $this->Roles = $this->getTableLocator()->get('Roles');
        }
        return $this->Roles;
    }

    protected function getSchoolLevelsTable(): SchoolLevelsTable
    {
        if ($this->SchoolLevels === null) {
            $this->SchoolLevels = $this->getTableLocator()->get('SchoolLevels');
        }
        return $this->SchoolLevels;
    }

    protected function getSchoolsTable(): SchoolsTable
    {
        if ($this->Schools === null) {
            $this->Schools = $this->getTableLocator()->get('Schools');
        }
        return $this->Schools;
    }

    protected function getSchoolRolesTable(): SchoolRolesTable
    {
        if ($this->SchoolRoles === null) {
            $this->SchoolRoles = $this->getTableLocator()->get('SchoolRoles');
        }
        return $this->SchoolRoles;
    }

    protected function getSchoolUsersTable(): SchoolUsersTable
    {
        if ($this->SchoolUsers === null) {
            $this->SchoolUsers = $this->getTableLocator()->get('SchoolUsers');
        }
        return $this->SchoolUsers;
    }

    protected function getSitesettingsTable(): SitesettingsTable
    {
        if ($this->Sitesettings === null) {
            $this->Sitesettings = $this->getTableLocator()->get('Sitesettings');
        }
        return $this->Sitesettings;
    }

    protected function getUnitdetailsTable(): UnitdetailsTable
    {
        if ($this->Unitdetails === null) {
            $this->Unitdetails = $this->getTableLocator()->get('Unitdetails');
        }
        return $this->Unitdetails;
    }

    protected function getUnitFiresTable(): UnitFiresTable
    {
        if ($this->UnitFires === null) {
            $this->UnitFires = $this->getTableLocator()->get('UnitFires');
        }
        return $this->UnitFires;
    }

    protected function getUnitsTable(): UnitsTable
    {
        if ($this->Units === null) {
            $this->Units = $this->getTableLocator()->get('Units');
        }
        return $this->Units;
    }

    protected function getUserActivitiesTable(): UserActivitiesTable
    {
        if ($this->UserActivities === null) {
            $this->UserActivities = $this->getTableLocator()->get('UserActivities');
        }
        return $this->UserActivities;
    }

    protected function getUserimagesTable(): UserimagesTable
    {
        if ($this->Userimages === null) {
            $this->Userimages = $this->getTableLocator()->get('Userimages');
        }
        return $this->Userimages;
    }

    protected function getUserLevelBadgesTable(): UserLevelBadgesTable
    {
        if ($this->UserLevelBadges === null) {
            $this->UserLevelBadges = $this->getTableLocator()->get('UserLevelBadges');
        }
        return $this->UserLevelBadges;
    }

    protected function getUserPointsTable(): UserPointsTable
    {
        if ($this->UserPoints === null) {
            $this->UserPoints = $this->getTableLocator()->get('UserPoints');
        }
        return $this->UserPoints;
    }

    protected function getUserprogressTable(): UserprogressTable
    {
        if ($this->Userprogress === null) {
            $this->Userprogress = $this->getTableLocator()->get('Userprogress');
        }
        return $this->Userprogress;
    }

    protected function getUserSettingsTable(): UsersettingsTable
    {
        if ($this->UserSettings === null) {
            $this->UserSettings = $this->getTableLocator()->get('Usersettings');
        }
        return $this->UserSettings;
    }

    protected function getUsersTable(): UsersTable
    {
        if ($this->Users === null) {
            $this->Users = $this->getTableLocator()->get('Users');
        }
        return $this->Users;
    }

    protected function getUserUnitActivitiesTable(): UserUnitActivitiesTable
    {
        if ($this->UserUnitActivities === null) {
            $this->UserUnitActivities = $this->getTableLocator()->get('UserUnitActivities');
        }
        return $this->UserUnitActivities;
    }

    protected function getWordlinksTable(): WordlinksTable
    {
        if ($this->Wordlinks === null) {
            $this->Wordlinks = $this->getTableLocator()->get('Wordlinks');
        }
        return $this->Wordlinks;
    }

    /**
     * @return object|null
     */
    protected function getAuthUser(): ?object
    {
        if (empty($this->Authentication)) {
            return null;
        }

        return $this->Authentication->getResult()->getData();
    }

    public function extractObjErrorMsgs(\Cake\Datasource\EntityInterface $entity): string
    {
        $errorMsgs = '';
        $errors = array_values($entity->getErrors());
        foreach ($errors as $key => $err) {
            foreach ($err as $key1 => $err1) {
                $errorMsgs .= $err1 . " ";
            }
        }
        return $errorMsgs;
    }
}
