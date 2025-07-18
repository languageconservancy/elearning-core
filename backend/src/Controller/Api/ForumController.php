<?php

namespace App\Controller\Api;

use Cake\ORM\Query;
use App\Lib\UtilLibrary;
use Cake\Log\Log;

class ForumController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Mail');
    }

    /**
     * Get the list of forums.
     *
     * @param array $data The request data.
     * @param int $data['user_id'] The user ID.
     * @param int $data['path_id'] The path ID.
     * @param int $data['level_id'] The level ID.
     * @param int $data['unit_id'] The unit ID.
     * @param string $data['labelType'] The label type.
     * @return void
     */

    public function getForum()
    {
        $data = $this->request->getData();

        // Path forums, where level and unit are null
        $byPathId = [
            'OR' => [
                'path_id IS' => $data['path_id'] ?? null,
                'path_id IS NULL'
            ],
            'AND' => [
                'unit_id IS NULL',
                'level_id IS NULL'
            ]
        ];

        $response = $this->getForums($byPathId);

        // Specific conditions based on provided data
        if (!empty($data['level_id'])) {
            if (empty($data['unit_id'])) {
                $response = array_merge($response, $this->getForumsByLevel(
                    $data['user_id'], $data['path_id'] ?? null, $data['level_id'], $data['labelType']
                ));
            } else {
                $byLevelAndUnitId = [
                    'level_id' => $data['level_id'],
                    'unit_id' => $data['unit_id']
                ];
                $response = array_merge($response, $this->getForums($byLevelAndUnitId));
            }
        }

        // Add Reported Posts for specific roles
        if (!empty($data['user_id'])) {
            $userAccess = $this->getUserById($data['user_id']);
            if (in_array($userAccess[0]->role['role'], [UtilLibrary::ROLE_MODERATOR_STR, UtilLibrary::ROLE_SUPERADMIN_STR])) {
                $response[] = [
                    "id" => null,
                    "path_id" => null,
                    "level_id" => null,
                    "unit_id" => null,
                    "title" => "Reported Posts",
                    "subtitle" => null,
                    "created" => null,
                    "modified" => null
                ];
            }
        }

        $this->sendApiData(true, 'Forums returned successfully.', $response);
    }

    /**
     * Get forums based on condition.
     *
     * @param array $condition The condition for fetching forums.
     * @return array The forums.
     */
    private function getForums(array $condition): array
    {
        return $this->getForumsTable()->find()->where($condition)->toArray();
    }

    /**
     * Get forums by level condition.
     *
     * @param array $data The request data.
     * @param int $data['unit_id'] The unit ID.
     * @param int $data['level_id'] The level ID.
     * @param int $data['labelType'] The label type.
     * @return array The forums.
     */
    private function getForumsByLevel(int $userId, int $pathId, int $levelId, string $labelType): array
    {
        $response = [];

        if ($labelType === 'unitfetch') {
            $response = $this->getForumsOfAttemptedUnits($pathId, $levelId, $userId);
        } else {
            $numLevelsAttempted = $this->getUserActivitiesTable()
                ->find('all', ['conditions' => ['user_id' => $userId, 'level_id' => $levelId]])
                ->count();

            if ($numLevelsAttempted > 0) {
                $condition = ['level_id' => $levelId, 'unit_id IS' => null];
                $response = array_merge($response, $this->getForums($condition));

                $idsOfAttemptedUnits = $this->getIdsOfAttemptedUnits($userId, null);
                if (!empty($idsOfAttemptedUnits)) {
                    $unitForums = $this->getForums(['level_id' => $levelId, 'unit_id IN' => $idsOfAttemptedUnits]);
                    if (!empty($unitForums)) {
                        $response[0]['unitList'] = $unitForums;
                    }
                }
            }
        }

        return $response;
    }

    /**
     * Get forums by unit fetch condition.
     *
     * @param array $data The request data.
     * @return array The forums.
     */
    private function getForumsOfAttemptedUnits(int $pathId, int $levelId, int $userId): array
    {
        $response = [];
        $unitIds = getUnitIdsOfForumsForPathAndLevel($pathId, $levelId);

        if (!empty($unitIds)) {
            $idsOfAttemptedUnits = $this->getIdsOfAttemptedUnits($userId, $unitIds);
            if (!empty($idsOfAttemptedUnits)) {
                $unitCondition = ['level_id' => $levelId, 'unit_id IN' => $idsOfAttemptedUnits];
                $response = $this->getForums($unitCondition);
            }
        }

        return $response;
    }

    private function getUnitIdsOfForumsForPathAndLevel(int $pathId, int $levelId): array
    {
        $condition = [
            'path_id' => $pathId,
            'level_id' => $levelId,
            'unit_id IS NOT' => null
        ];

        return $this->getForumsTable()
            ->find('list', ['keyField' => 'id', 'valueField' => 'unit_id'])
            ->where($condition)
            ->toArray();
    }

    /**
     * Get completed units for a user.
     *
     * @param int $userId ID of the user.
     * @param array|null $unitIds The unit IDs to check against.
     * @return array Array of unit IDs corresponding to the units the user has attempted and have a forum.
     */
    private function getIdsOfAttemptedUnits(int $userId = null, array $unitIds = null): array
    {
        $conditions = ['user_id IS' => $userId];
        if ($unitIds !== null) {
            $conditions['unit_id IN'] = $unitIds;
        }
        $unitsUserHasAttempted = $this->getUserActivitiesTable()
            ->find('list', ['keyField' => 'id', 'valueField' => 'unit_id'])
            ->where($conditions)
            ->toArray();

        return array_unique(array_values($unitsUserHasAttempted));
    }

    public function createPost()
    {
        $data = $this->request->getData();
        $dataElement['forum_id'] = $data['forum_id'];
        $dataElement['user_id'] = $data['user_id'];
        $dataElement['title'] = $data['title'] ?? '';
        $dataElement['content'] = $data['content'];

        $type = $data['type'];
        if ($type != 'create') {
            $dataElement['parent_id'] = $data['parent_id'];
        }

        // Flag posts with banned words
        $this->shadowBanCheck($dataElement);

        $post = $this->getForumPostsTable()->newEmptyEntity();
        $postdata = $this->getForumPostsTable()->patchEntity($post, $dataElement);

        if ($forumPosts = $this->getForumPostsTable()->save($postdata)) {
            /*Add point for forum */
            $point = $this->getBonusPointByKey('User social point per Post or Reply');
            $activityData = array();
            $activityData['user_id'] = $data['user_id'];
            $activityData['activity_type'] = 'forumpost';
            $activityData['forumpost_id'] = $forumPosts['id'];
            $activityData['social_score'] = $point;
            $activity = $this->getUserActivitiesTable()->newEmptyEntity();
            $activityPatch = $this->getUserActivitiesTable()->patchEntity($activity, $activityData);
            $this->getUserActivitiesTable()->save($activityPatch);
            $this->updatePointByUserId($data['user_id']);
            if ($type != 'create') {
                $this->sendApiData(true, 'Reply sent successfully.', array());
            } else {
                $this->sendApiData(true, 'Post Save successfully.', array());
            }
        } else {
            $errors = array_values(array_values($postdata->getErrors()));
            foreach ($errors as $key => $err) {
                foreach ($err as $key1 => $err1) {
                    $message = $err1;
                }
            }
            $this->sendApiData(false, $message, array());
        }
    }

    /**
     * If the post does not pass the banned words check,
     * flag is for shadow banning. The argument is passed in by reference,
     * so the original variable is modified.
     * &$post {Array} - Post passed by reference.
     */
    private function shadowBanCheck(&$post)
    {
        if (
            $this->getBannedWordsTable()->presentInText($post['title'])
            || $this->getBannedWordsTable()->presentInText($post['content'])
        ) {
            $post['flag_id'] = 5;
        }
    }

    public function shareRecordingAudioInForum()
    {
        $data = $this->request->getData();

        // Prepare the data for the new forum post
        $dataElement = [
            'user_id' => $data['user_id'],
            'title' => $data['title'],
            'content' => $data['content'],
            'audio' => $data['audio_id']
        ];

        // Find the forum by unit_id
        $forum = $this->getForumsTable()->find()
            ->where(['unit_id' => $data['unit_id']])
            ->first();
        if (empty($forum)) {
            $this->sendApiData(false, 'Forum not found for unit ID ' . $data['unit_id'] . '.', array());
        }
        $dataElement['forum_id'] = $forum['id'];

        // Create a new forum post
        $post = $this->getForumPostsTable()->newEmptyEntity();
        $postData = $this->getForumPostsTable()->patchEntity($post, $dataElement);

        // Save the new forum post
        if ($this->getForumPostsTable()->save($postData)) {
            // Add points for forum
            $point = $this->getBonusPointByKey('User social point per Post or Reply');

            // Prepare the activity data
            $activityData = [
                'user_id' => $data['user_id'],
                'activity_type' => 'forumpost',
                'forumpost_id' => $postData->id,
                'social_score' => $point
            ];

            // Create and save the user activity
            $activity = $this->getUserActivitiesTable()->newEmptyEntity();
            $activityPatch = $this->getUserActivitiesTable()->patchEntity($activity, $activityData);
            $this->getUserActivitiesTable()->save($activityPatch);

            // Update user points
            $this->updatePointByUserId($data['user_id']);

            // Send success response
            $this->sendApiData(true, 'Recording Share Successfully.', array());
        } else {
            // Send failure response
            $this->sendApiData(false, 'Recording share faliure.Please try again', array());
        }
    }

    public function editPost()
    {
        $data = $this->request->getData();
        $dataElement = array();
        $dataElement['title'] = $data['title'];
        $dataElement['content'] = $data['content'];
        $id = $data['post_id'];

        // Flag posts with banned words
        $this->shadowBanCheck($dataElement);

        $forumPost = $this->getForumPostsTable()->get($id, ['contain' => []]);
        $forumPost = $this->getForumPostsTable()->patchEntity($forumPost, $dataElement);
        if ($this->getForumPostsTable()->save($forumPost)) {
            if (isset($data['user_id']) && $data['user_id'] != '') {
                $user = $this->getUserById($data['user_id']);
                if (
                    ($data['user_id'] != $forumPost['user_id']) ||
                    ($user[0]['role']['role'] == UtilLibrary::ROLE_MODERATOR_STR ||
                    $user[0]['role']['role'] == UtilLibrary::ROLE_SUPERADMIN_STR) &&
                    !empty($data['status']) &&
                    $data['status'] == 'A'
                ) {
                    $updatedata = array();
                    $updatedata['flag_id'] = null;
                    $updateforumPost = $this->getForumPostsTable()->patchEntity($forumPost, $updatedata);
                    $this->getForumPostsTable()->save($updateforumPost);
                    $this->getForumFlagsTable()->deleteAll(['post_id' => $id]);
                }
            }
            $this->sendApiData(true, 'Post saved successfully.', array());
        } else {
            $this->sendApiData(false, 'Post save failure.', array());
        }
    }

    public function deletePost()
    {
        $data = $this->request->getData();
        $id = $data['post_id'];
        $c = $this->getForumPostsTable()->find()->where(['id' => $id])->count();
        if ($c != 0) {
            $forumPost = $this->getForumPostsTable()->get($id);


            if ($this->getForumPostsTable()->delete($forumPost)) {
                $this->sendApiData(true, 'Post deleted successfully.', array());
            } else {
                $this->sendApiData(false, 'Post deletion failure.', array());
            }
        } else {
            $this->sendApiData(false, 'Post deletion failure.', array());
        }
    }

    /**
     * Get the list of posts for a forum.
     *
     * @param array $data The request data.
     * @param int $data['user_id'] The user ID.
     * @param int $data['forum_id'] The forum ID.
     * @param int $data['page'] The page number.
     * @param string $data['q'] The search query.
     * @param int $data['timestamp_offset'] The timestamp offset.
     * @param string $data['type'] The type of post.
     * @return void
     */
    public function getPostList()
    {
        $data = $this->request->getData();
        $forumAttemptIds = $this->getForumIdAccessByUserId($data['user_id']);

        // Get settings
        $settings = $this->getSitesettingsTable()->getPrefixedKeys("setting_");

        $limit = 15;
        if (!empty($data['page'])) {
            $page = $data['page'];
        } else {
            $page = 1;
        }

        $response = array();
        $condition = array('is_hide' => 'N', 'parent_id IS' => null);
        if (isset($data['forum_id']) && $data['forum_id'] != '') {
            $forum_ids = explode(",", $data['forum_id']);
            $ids = array_intersect($forumAttemptIds, $forum_ids);
            if (!empty($ids)) {
                $condition['forum_id IN'] = $ids;
            } else {
                $condition['forum_id IS'] = null;
            }
        } else {
            $condition['forum_id IN'] = $forumAttemptIds;
        }
        $timestampOffset = $data['timestamp_offset'];
        if (!empty($data['q'])) {
            $condition['OR'] = array(
                'ForumPosts.title LIKE' => '%' . $data['q'] . '%',
                'ForumPosts.content LIKE' => '%' . $data['q'] . '%'
            );
        }
        $order = array('sticky');
        $userId = null;
        $order['ForumPosts.created'] = 'desc';
        if (isset($data['user_id']) && $data['user_id'] != '') {
            $userId = $data['user_id'];
            $f = $this->getFriends($userId);
            if (count($f) > 0) {
                $FIds = '';
                foreach ($f as $fdetails) {
                    $FIds .= $fdetails['id'] . ',';
                }
                $order[] = 'FIELD(ForumPosts.user_id, ' . trim($FIds, ",") . ') desc';
            }
            $condition['AND'] = array('OR' => array(
                'flag_id IS' => null,
                'ForumPosts.user_id' => $data['user_id']
            ));
            if (isset($data['type']) && $data['type'] == 'postbyuser') {
                $condition['ForumPosts.user_id'] = $data['user_id'];
            }
        } else {
            $condition['flag_id IS'] = null;
        }

        if ($settings['setting_minors_can_access_village'] === '1') {
            $condition['Users.approximate_age >='] = RegionPolicy::selfConsentMinAge();
        } else {
            $condition['Users.approximate_age >='] = RegionPolicy::adultMinAge();
        }

        $query = $this->getForumPostsTable()->find()
            ->contain([
                'Users' => ['Usersetting'],
                'ForumFlags',
                'audioDetails',
                'ChildForumPosts' => function (Query $query) use ($userId, $settings) {
                    $childForumPostsConditions = [
                        'OR' => [
                            'ChildForumPosts.flag_id IS' => null,
                            'ChildForumPosts.user_id' => $userId
                        ],
                        'is_hide' => 'N'
                    ];
                    if ($settings['setting_minors_can_access_village'] === '1') {
                        $childForumPostsConditions['Users.approximate_age >='] = RegionPolicy::selfConsentMinAge();
                    } else {
                        $childForumPostsConditions['Users.approximate_age >='] = RegionPolicy::adultMinAge();
                    }
                    return $query->contain(['Users' => ['Usersetting']])
                        ->where($childForumPostsConditions);
                }
            ])
            ->where($condition)
            ->select([
                'ForumPosts.id',
                'ForumPosts.title',
                'ForumPosts.content',
                'ForumPosts.entry_time',
                'ForumPosts.user_id',
                'ForumPosts.forum_id',
                'ForumPosts.parent_id',
                'ForumPosts.sticky',
                'ForumPosts.created',
                'ForumPosts.audio',
                'Users.id',
                'Users.name',
                'Users.role_id',
                'Usersetting.id',
            ])
            ->order($order);

        $this->paginate = [
            'page' => $page,
            'limit' => $limit
        ];

        $post = $this->paginate($query)->toArray();

        foreach ($post as $p) {
            $p['entry_time'] = $this->getUserPostTime($p['entry_time'], $timestampOffset, 'F d, Y');
            $p['user']['badge'] = $this->getBadgeByUser($p['user']['id']);
        }
        $response['items'] = $post;

//        print_r($condition);
//        die;
        $pageinfo = array();
        $totalcount = $this->getForumPostsTable()->find()
            ->contain(['Users' => ['Usersetting']])
            ->where($condition)
            ->count();
        $pageinfo['totalcount'] = $totalcount;
        $pageinfo['currentpage'] = $page;
        $pageinfo['totalpage'] = ceil($totalcount / $limit);
        $response['pageinfo'] = $pageinfo;
        $flagReasons = $this->getForumFlagReasonsTable()->find('list', ['keyField' => 'id', 'valueField' => 'reason'])->toArray();
        $response['report_flag'] = array_values($flagReasons);
        $this->sendApiData(true, 'Post return Successfully.', $response);
    }

    private function getFriends($userId)
    {
        $id = $userId;
        $users = $this->getFriendsTable()->find('all', ['contain' => ['User', 'Friend']])
            ->where(['OR' => [
                ['friend_id =' => $userId], ['user_id =' => $userId]]])
            ->toArray();
        $friends = array();
        foreach ($users as $u) {
            if ($u->user->id != $userId) {
                $friends[] = $u->user;
            }
            if ($u->friend->id != $userId) {
                $friends[] = $u->friend;
            }
        }
        return $friends;
    }

    private function getUserPostTime($TimeStr, $timestampOffset, $format)
    {
        $servertimestamp = strtotime($TimeStr);
        $timestampdiff = $timestampOffset;
        $usertimestamp = $servertimestamp + $timestampdiff;
        $usertime = date($format, $usertimestamp);
        return $usertime;
    }

    public function getReplyPostList()
    {
        $data = $this->request->getData();
        $friend_Id = $data['friend_id'];
        $user_id = $data['user_id'];

        // Get settings
        $settings = $this->getSitesettingsTable()->getPrefixedKeys("setting_");

        $forumAttemptIdsByUser = $this->getForumIdAccessByUserId($user_id);
        $forumAttemptIdsByFriend = $this->getForumIdAccessByUserId($friend_Id);
        $ForumDiff = array_diff($forumAttemptIdsByFriend, $forumAttemptIdsByUser);
        $limit = 15;
        if (!empty($data['page'])) {
            $page = $data['page'];
        } else {
            $page = 1;
        }
        $response = array();
        $condition = array(
            'ForumPosts.is_hide' => 'N',
            'ForumPosts.flag_id IS' => null,
            'ForumPosts.parent_id IS NOT' => null,
            'ForumPosts.forum_id IN' => $forumAttemptIdsByUser,
            'ForumPosts.user_id' => $friend_Id);
        $timestampOffset = $data['timestamp_offset'];
        if (!empty($data['q'])) {
            $condition['OR'] = array(
                'ForumPosts.title LIKE' => '%' . $data['q'] . '%',
                'ForumPosts.content LIKE' => '%' . $data['q'] . '%'
            );
        }

        if ($settings['setting_minors_can_access_village'] === '1') {
            $condition['Users.approximate_age >='] = RegionPolicy::selfConsentMinAge();
        } else {
            $condition['Users.approximate_age >='] = RegionPolicy::adultMinAge();
        }

        $order = array('ForumPosts.sticky');
        $order['ForumPosts.created'] = 'desc';
        $f = $this->getFriends($user_id);
        if (!empty($f)) {
            $FIds = '';
            foreach ($f as $fdetails) {
                $FIds .= $fdetails['id'] . ',';
            }
            $order[] = 'FIELD(ForumPosts.user_id, ' . trim($FIds, ",") . ') desc';
        }

        $query = $this->getForumPostsTable()->find()
            ->contain([
                'Users' => ['Usersetting'],
                'ForumFlags',
                'audioDetails',
                'ParentForumPosts' => ['Users' => ['Usersetting']]
            ])
            ->where($condition)
            ->order($order);

        $this->paginate = [
            'page' => $page,
            'limit' => $limit
        ];
        $post = $this->paginate($query)->toArray();

        foreach ($post as $p) {
            $p['entry_time'] = $this->getUserPostTime($p['entry_time'], $timestampOffset, 'F d, Y');
        }
        $response['items'] = $post;

        $pageinfo = array();
        $totalcount = $this->getForumPostsTable()->find('all', ['conditions' => $condition])->count();
        $pageinfo['totalcount'] = $totalcount;
        $pageinfo['currentpage'] = $page;
        $pageinfo['totalpage'] = ceil($totalcount / $limit);
        $response['pageinfo'] = $pageinfo;


        if (!empty($ForumDiff)) {
            $alertmessage = 'Some forum repley are not listed as you are not completed the unit.';
        } else {
            $alertmessage = '';
        }
        $response['alertmessage'] = $alertmessage;

        $this->sendApiData(true, 'Post return Successfully.', $response);
    }

    //general function for get user friend by id. param id,

    public function getFlagPostList()
    {
        $data = $this->request->getData();
        $timestampOffset = $data['timestamp_offset'];
        $limit = 15;
        if (!empty($data['page'])) {
            $page = $data['page'];
        } else {
            $page = 1;
        }
        $response = array();
        $condition = array('is_hide' => 'N', 'flag_id IS NOT' => null);
        if (!empty($data['forum_id'])) {
            $condition['forum_id'] = $data['forum_id'];
        }
        if (!empty($data['q'])) {
            $condition['OR'] = array(
                'ForumPosts.title LIKE' => '%' . $data['q'] . '%',
                'ForumPosts.content LIKE' => '%' . $data['q'] . '%'
            );
        }
        $order = array('sticky', 'ForumPosts.created' => 'desc');

        $query = $this->getForumPostsTable()->find()
            ->contain([
                'Users' => ['Usersetting'],
                'ForumFlags',
                'audioDetails',
                'ChildForumPosts' => function (Query $query) {
                    return $query->contain(['Users' => ['Usersetting']]);
                }
            ])
            ->where($condition)
            ->order($order);

        $this->paginate = [
            'page' => $page,
            'limit' => $limit
        ];

        $post = $this->paginate($query);
        foreach ($post as $p) {
            $p['entry_time'] = $this->getUserPostTime($p['entry_time'], $timestampOffset, 'F d, Y');
            $p['user']['badge'] = $this->getBadgeByUser($p['user']['id']);
        }
        $response['items'] = $post;
//        print_r($response);
//        die;
        $pageinfo = array();
        $totalcount = $this->getForumPostsTable()->find('all', ['conditions' => $condition])->count();
        $pageinfo['totalcount'] = $totalcount;
        $pageinfo['currentpage'] = $page;
        $pageinfo['totalpage'] = ceil($totalcount / $limit);
        $response['pageinfo'] = $pageinfo;
        $Reasons = $this->getForumFlagReasonsTable()->find('list', ['keyField' => 'id', 'valueField' => 'reason'])->toArray();
        $response['report_flag'] = array_values($Reasons);
        $this->sendApiData(true, 'Post return Successfully.', $response);
    }

    public function getPost()
    {
        $data = $this->request->getData();
        $postId = $data['post_id'];
        $userId = $data['user_id'];
        $timestampOffset = $data['timestamp_offset'];
        $postDetails = $this->getForumPostsTable()->get($postId, [
            'contain' => [
                'Users' => ['Usersetting'],
                'Forums',
                'audioDetails',
                'ChildForumPosts' => function (Query $query) use ($userId) {
                    return $query->contain(['Users' => ['Usersetting']])
                        ->where([
                            'OR' => [
                                'ChildForumPosts.flag_id IS' => null,
                                'ChildForumPosts.user_id' => $userId
                            ],
                            'is_hide' => 'N'
                        ]);
                }
            ],
            'conditions' => [
                'OR' => [
                    'flag_id IS' => null,
                    'ForumPosts.user_id' => $userId
                ],
                'is_hide' => 'N'
            ]
        ]);
        foreach ($postDetails->child_forum_posts as $p) {
            $p->entry_time = $this->getUserPostTime($p->entry_time, $timestampOffset, 'F d, Y');
            $p->user->badge = $this->getBadgeByUser($p->user->id);
        }
        /** increase Counter* */
        $Counter = $this->getForumPostViewersTable()->find()->where(['user_id' => $userId, 'post_id' => $postId])->count();
        if ($Counter == 0) {
            $datacounterarray = array('user_id' => $userId, 'post_id' => $postId);
            $counterpost = $this->getForumPostViewersTable()->newEmptyEntity();
            $counterpostdata = $this->getForumPostViewersTable()->patchEntity($counterpost, $datacounterarray);
            $this->getForumPostViewersTable()->save($counterpostdata);
        }
        $postDetails->entry_time = $this->getUserPostTime($postDetails->entry_time, $timestampOffset, 'F d, Y');
        $postDetails->user->badge = $this->getBadgeByUser($postDetails->user->id);
        $this->sendApiData(true, 'Post return Successfully.', $postDetails);
    }

    public function flagAPost()
    {
        $data = $this->request->getData();
        $postId = $data['post_id'];
        $flag = $this->getForumFlagsTable()->newEmptyEntity();
        $postdata = $this->getForumFlagsTable()->patchEntity($flag, $data);
        if ($requestEntry = $this->getForumFlagsTable()->save($postdata)) {
            //assignflagNo
            $postDetails = $this->getForumPostsTable()->get($postId);
            $postDetails->flag_id = $requestEntry['id'];
            $this->getForumPostsTable()->save($postDetails);
            $this->sendApiData(true, 'Post flagged successfully.', array());
        } else {
            $this->sendApiData(false, 'Flag request error. Please try again.', array());
        }
    }

    public function getFlagReasons()
    {
        $response = array();
        $flagReasons = $this->getForumFlagReasonsTable()->find(
            'list',
            ['keyField' => 'id', 'valueField' => 'reason']
        )
            ->where(['reason !=' => 'shadow banned'])
            ->toArray();
        $response['flag_reasons'] = array_values($flagReasons);
        $this->sendApiData(true, 'Post return Successfully.', $response);
    }
}
