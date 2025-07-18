export enum OptionType {
    Audio,
    Image,
    Text,
}

export enum CardDataType {
    Language = "language",
    Audio = "audio",
    Image = "image",
    Video = "video",
    English = "english",
}

export enum AnswerType {
    NONE,
    CORRECT,
    INCORRECT,
}

export type ApiResponseData = {
    status: boolean;
    message: string;
    results: any;
};

export type ApiResponse = {
    data: ApiResponseData;
    message: string;
};

export enum ForbidenResponseReasons {
    AGREEMENTS_NOT_ACCEPTED = "You must accept the agreements to proceed.",
    USER_NOT_PUBLICLY_ACCESSIBLE = "User is not publicly accessible due to age.",
    CANNOT_TAMPER_WITH_OTHERS_DATA = "You cannot tamper with other users' data.",
}

export enum ReviewType {
    PATH = "path",
    LEVEL = "level",
    UNIT = "unit",
}

export const Routes = {
    About: "about",
    AboutPrivacy: "about/privacy",
    AboutTerms: "about/terms",
    AccessibilitySettings: "accessibility-settings",
    AccountSettings: "account-settings",
    AddFriends: "add-friends",
    ChangePassword: "change-password",
    Classroom: "classroom",
    ContactUs: "contact-us",
    Dashboard: "dashboard",
    FindFriends: "find-friends",
    ForgotPassword: "forgot-password",
    ForumPostDetails: "forum-post-details",
    Leaderboard: "leader-board",
    LearningPath: "learning-path",
    LearningSettings: "learning-settings",
    LearningSpeed: "learning-speed",
    LessonsAndExercises: "lessons-and-exercises",
    Login: "",
    NotificationsSettings: "notifications-settings",
    PageNotFound: "page-not-found",
    PostsByUser: "posts-by-user",
    PrivacySettings: "privacy-settings",
    ProfileSettings: "profile-settings",
    Progress: "progress",
    PublicProfile: "profile",
    Register: "register",
    Review: "review",
    SpreadTheWord: "spread-the-word",
    StartLearning: "start-learning",
    TeacherAdmin: "teacher-admin",
    TeacherClassrooms: "teacher-classrooms",
    TeacherDashboard: "teacher-dashboard",
    TeacherLessons: "teacher-lessons",
    UnderConstruction: "under-construction",
    Village: "village",
} as const;

export type RegistrationData = {
    name: string;
    dob: string;
    email: string;
    password: string;
    repassword: string;
};

export type SiteLoginData = {
    email: string;
    password: string;
    type: string;
};
