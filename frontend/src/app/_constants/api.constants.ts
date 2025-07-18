import { environment } from "environments/environment";

const prefixes = {
    user: "Users/",
    settings: "Sitesetting/",
    path: "LearningPath/",
    speed: "LearningSpeed/",
    lessons: "Lessons/",
    exercises: "Exercise/",
    points: "UserPoints/",
    reviews: "Review/",
    forum: "Forum/",
    teacher: "Teacher/",
    classrooms: "Classrooms/",
};

export const User = {
    AUTH_TOKEN: environment.API + prefixes.user + "token.json",
    CAPTCHA: environment.API + prefixes.user + "captchaResponse.json",
    CHECK_EMAIL: environment.API + prefixes.user + "checkEmail.json",
    CHECK_FRIENDS: environment.API + prefixes.user + "checkFriend.json",
    DEACTIVATE_ACCOUNT: environment.API + prefixes.user + "deactivateAccount.json",
    DELETE_ACCOUNT: environment.API + prefixes.user + "deleteAccount.json",
    EMAIL_INVITE: environment.API + prefixes.user + "emailInviteFriend.json",
    FORGOT_PASSWORD: environment.API + prefixes.user + "forgotPassword.json",
    SET_FRIENDS: environment.API + prefixes.user + "addOrRemoveFriend.json",
    GET_FRIENDS: environment.API + prefixes.user + "getUsersForFriends.json",
    INVITE: environment.API + prefixes.user + "inviteFriend.json",
    LOGIN: environment.API + prefixes.user + "login.json",
    PASSWORD_TOKEN: environment.API + prefixes.user + "resetPasswordToken.json",
    RESET_PASSWORD: environment.API + prefixes.user + "resetPassword.json",
    TEACHER_PASSWORD_CHANGE: environment.API + prefixes.user + "teacherChangePassword.json",
    SIGNUP: environment.API + prefixes.user + "signup.json",
    UPDATE_SETTING: environment.API + prefixes.user + "updateUserSetting.json",
    UPDATE: environment.API + prefixes.user + "updateUser.json",
    UPLOAD_GALLERY_IMAGE: environment.API + prefixes.user + "uploadGalleryImage.json",
    GET_USERS_FRIENDS: environment.API + prefixes.user + "getUsersFriends.json",
    GET_LEADERBOARD: environment.API + prefixes.user + "getLeaderBoardData.json",
    POST_CONTACTUS: environment.API + prefixes.user + "contactUs.json",
    VALIDATE_EMAIL_TOKEN: environment.API + prefixes.user + "validateEmail.json",
    GET_USER: environment.API + prefixes.user + "getUser",
    GET_PUBLIC_USER: environment.API + prefixes.user + "getPublicUser",
    SAVE_AGREEMENTS_ACCEPTANCE: environment.API + prefixes.user + "saveAgreementsAcceptance.json",
    NOTIFY_PARENT: environment.API + prefixes.user + "notifyParent.json",
    GET_REGION_POLICY: environment.API + prefixes.user + "getRegionPolicy.json",
};

export const Settings = {
    CONSTRUCTION_MODE: environment.API + prefixes.settings + "fetchConstruction.json",
    LOGIN_IMAGE: environment.API + prefixes.settings + "fetchLink.json",
    CMS_CONTENT: environment.API + prefixes.settings + "fetchCmsContent.json",
    GET_SETTINGS: environment.API + prefixes.settings + "fetchSiteSettingsSettings.json",
    GET_FEATURES: environment.API + prefixes.settings + "fetchSiteSettingsFeatures.json",
    GET_CONTENT_BY_KEYWORD: environment.API + prefixes.settings + "fetchContentByKeyword.json",
    GET_PLATFORM_ROLES: environment.API + prefixes.settings + "fetchPlatformRoles.json",
    GET_VERSION_INFO: environment.API + prefixes.settings + "fetchVersionInfo.json",
};
export const Path = {
    FETCH: environment.API + prefixes.path + "getPath.json",
    PATH_DETAILS: environment.API + prefixes.path + "getPathDetails.json",
    UNIT_DETAILS: environment.API + prefixes.path + "getUnitDetails.json",
};

export const Speed = {
    FETCH: environment.API + prefixes.speed + "getSpeed.json",
};

export const Lessons = {
    FETCH: environment.API + prefixes.lessons + "getLessons.json",
};

export const Exercises = {
    FETCH: environment.API + prefixes.exercises + "getExercise.json",
    QUESTION_ATTEMPTED_REQUIRED_NUM_TIMES_MINUS_ONE:
        environment.API + prefixes.exercises + "questionAttemptedRequiredNumTimesMinusOne.json",
    SAVE_AUDIO: environment.API + prefixes.exercises + "saveRecordingAudioFile.json",
    EMAIL_SHARE: environment.API + prefixes.exercises + "shareRecordingAudioFileByEmail.json",
};

export const Points = {
    ADD_ACTIVITY: environment.API + prefixes.points + "addUserActivity.json",
    SET_SCORE: environment.API + prefixes.points + "getScorePerSet.json",
    UNIT_SCORE: environment.API + prefixes.points + "getUnitCompleteScore.json",
    GET_TIMER: environment.API + prefixes.points + "getProgressTimer.json",
    SET_TIMER: environment.API + prefixes.points + "addProgressTimer.json",
    RESET_PROGRESS: environment.API + prefixes.points + "resetProgressData.json",
    GLOBAL_FIRE: environment.API + prefixes.points + "getGlobalFire.json",
    REVIEW_SCORE: environment.API + prefixes.points + "getReviewScoreByUserId.json",
    GET_PROGRESS: environment.API + prefixes.points + "getProgressData.json",
};

export const Review = {
    FETCH: environment.API + prefixes.reviews + "getReviewExercise.json",
    GET_FIRE: environment.API + prefixes.reviews + "getReviewHaveOrNot.json",
};

export const Forum = {
    GET_FORUMS: environment.API + prefixes.forum + "getForum.json",
    GET_FORUM_POSTS: environment.API + prefixes.forum + "getPostList.json",
    FLAG_POST: environment.API + prefixes.forum + "FlagAPost.json",
    ADD_POST: environment.API + prefixes.forum + "createPost.json",
    SINGLE_POST: environment.API + prefixes.forum + "getPost.json",
    UPDATE_POST: environment.API + prefixes.forum + "editPost.json",
    DELETE_POST: environment.API + prefixes.forum + "deletePost.json",
    FLAG_POST_LIST: environment.API + prefixes.forum + "getFlagPostList.json",
    FORUM_SHARE: environment.API + prefixes.forum + "shareRecordingAudioInForum.json",
    GET_FLAG_REASONS: environment.API + prefixes.forum + "getFlagReasons.json",
};

export const Classrooms = {
    GET_SCHOOLS_AND_ROLES: environment.API + prefixes.classrooms + "getSchoolsAndRoles.json",
    GET_TEACHER_LEVEL_UNITS: environment.API + prefixes.classrooms + "getTeacherLevelUnits.json",
    UPDATE_CLASSROOM_DATA: environment.API + prefixes.classrooms + "updateClassroomData.json",
    DELETE_CLASSROOM: environment.API + prefixes.classrooms + "deleteClassroom.json",
    GET_CLASSROOM_DATA: environment.API + prefixes.classrooms + "getClassroomData.json",
    GET_TEACHER_LEVELS: environment.API + prefixes.classrooms + "getTeacherLevels.json",
    GET_TEACHER_CLASSROOMS: environment.API + prefixes.classrooms + "getTeacherClassrooms.json",
    GET_TEACHER_CLASSROOM_UNITS_AND_STUDENTS:
        environment.API + prefixes.classrooms + "getTeacherClassroomUnitsAndStudents.json",
    GET_STUDENT_ACTIVITIES: environment.API + prefixes.classrooms + "getStudentActivities.json",
    GET_AVAILABLE_PATHS: environment.API + prefixes.classrooms + "getAvailablePaths.json",
    GET_UNIT_CARDS: environment.API + prefixes.classrooms + "getUnitCards.json",
    ARCHIVE_CLASSROOM: environment.API + prefixes.classrooms + "archiveClassroom.json",
};

export const Teacher = {
    ADD_STUDENTS: environment.API + prefixes.teacher + "addStudents.json",
    ADD_CLASS: environment.API + prefixes.teacher + "addClass.json",
    GET_CLASS: environment.API + prefixes.teacher + "getClass.json",
    UPDATE_CLASS: environment.API + prefixes.teacher + "updateClass.json",
    GET_PROGRESS: environment.API + prefixes.teacher + "getProgressData.json",
    GET_ACTIVITIES: environment.API + prefixes.teacher + "getActivitiesData.json",
};
