<?php

namespace App\Controller\Api;

class LessonsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
    }

    public function getLessons()
    {
        $data = $this->request->getData();
        if ($data['lesson_id'] != null) {
            $lessons = $this->getSingleLesson($data);
        } else {
            $lessons = $this->getLessonsTable()->find('all', ['contain' => ['Lessonframes', 'Lessonframes.LessonFrameBlocks']]);
        }


        $lessonElements = json_decode(json_encode($lessons), true);
        $lessons['assets'] = $this->getAssetUrls($lessonElements);

        $this->sendApiData(true, 'Result return successfully.', $lessons);
    }

    public function getSingleLesson($data)
    {
        $id = $data['lesson_id'];
        $user = $this->getAuthUser();
        $lesson = $this->getLessonsTable()->get($id, ['contain' => ['Lessonframes', 'Lessonframes.LessonFrameBlocks']]);
        $unitAttempt = $this->getUnitAttemptIdAndIsCompleted($data['level_id'], $data['unit_id'], $user['id']);
        $data['user_unit_activity_id'] = $unitAttempt['last_id'] ?? 1;


        $completed = false;
        if (isset($unitAttempt['percent'])) {
            $completed = $unitAttempt['percent'] >= 100 ?? false;
        }

        if ($unitAttempt['isunitComplete']) {
            $lesson['IsCompleted'] = array("status" => false);
        } else {
            $lesson['IsCompleted'] = $this->isCompleted($data);
        }
        return $lesson;
    }
}
