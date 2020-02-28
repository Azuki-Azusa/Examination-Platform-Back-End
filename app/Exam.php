<?php

namespace App;

use App\Maker;
use App\Group;
use App\Paper;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    public $timestamps = false;
    // 创建Exam
    public function createExam($name, $startTime, $endTime, $description, $rule, $group_id, $paper_id, $session)
    {
        date_default_timezone_set("PRC");
        $startTimeStamp = strtotime($startTime);
        $endTimeStamp = strtotime($endTime);
        $maker_id = $this->getIdBySession($session);

        // Name为空
        if (empty($name)) {
            $data = array('errcode' => 1, 'errmsg' => 'Name can\'t be empty.');
            return $data;
        }

        // 有同名Exam
        if ($this->hasSameName($maker_id, $name)) {
            $data = array('errcode' => 1, 'errmsg' => 'Exam with same name exists.');
            return $data;
        }

        // 日期非法
        if (!$startTimeStamp || !$endTimeStamp || $endTimeStamp <= $startTimeStamp || $endTimeStamp <= strtotime(date("Y-m-d H:i:s"))) {
            $data = array('errcode' => 1, 'errmsg' => 'Illegal date.');
            return $data;
        }

        // Group or paper非法
        if (!$this->hasGroupAndPaper($maker_id, $group_id, $paper_id)) {
            $data = array('errcode' => 1, 'errmsg' => 'Illegal group of paper.');
            return $data;
        }
        
        $this->insert(
            [
            'maker_id' => $maker_id, 
            'name' => $name,
            'description' => $description,
            'rule' => $rule,
            'startTime' => $startTime.':00',
            'endTime' => $endTime.':00',
            'group_id' => $group_id,
            'paper_id' => $paper_id,
            ]
        );
        $data = array('errcode' => 0, 'errmsg' => '创建成功.');
        return $data;
    }

    // 获得Group与Paper列表
    public function getGroupAndPaper($session) 
    {
        $maker_id = $this->getIdBySession($session);
        $group = new Group();
        $paper = new Paper();
        $groups = $group->getGroupsByMakerId($maker_id);
        $papers = $paper->getPapersByMakerId($maker_id);
        $data = array('errcode' => 0, 'errmsg' => null, 'groups' => $groups, 'papers' => $papers);
        return $data;
    }
    
    // 获得考试列表
    public function getExams($i, $session) 
    {
        $maker_id = $this->getIdBySession($session);
        date_default_timezone_set("PRC");
        $now = date("Y-m-d H:i:s");
        switch ($i) {
            // 开始前的考试列表
            case 0:
                $exams = $this->where([
                    ['maker_id', '=', $maker_id],
                    ['startTime', '>', $now]
                ])->get();
                break;
            // 考试中的考试列表
            case 1:
                $exams = $this->where([
                    ['maker_id', '=', $maker_id],
                    ['startTime', '<=', $now],
                    ['endTime', '>', $now]
                ])->get();
                break;
            // 考试后的考试列表
            case 2:
                $exams = $this->where([
                    ['maker_id', '=', $maker_id],
                    ['endTime', '<=', $now],
                    ['state', '<>', 2]
                ])->get();
                break;
            // 批改后的考试列表
            case 3:
                $exams = $this->where([
                    ['maker_id', '=', $maker_id],
                    ['state', '=', 2]
                ])->get();
                break;
            default:
                $exams = $this->where('maker_id', '=', $maker_id);
        }
        $group = new Group();
        $paper = new Paper();
        $data = array();
        foreach ($exams as $exam) {
            $object = array(
                'id' => $exam->id,
                'name' => $exam->name, 
                'startTime' => substr($exam->startTime, 0, 16), 
                'endTime' => substr($exam->endTime, 0, 16),
                'group' => $group->getName($exam->group_id),
                'paper' => $paper->getName($exam->paper_id)
                );
            array_push($data, $object);
        }
        $data = array('errcode' => 0, 'errmsg' => null, 'exams' => $data);
        return $data;
    }

    public function getExam($id, $session) 
    {
        $maker_id = $this->getIdBySession($session);

        // 不存在该Exam
        if (!$this->own($maker_id, $id)) {
            $data = array('errcode' => 1, 'errmsg' => 'Have no this exam.');
            return $data;
        }
        
        $exam = $this->find($id);

        $group = new Group();
        $paper = new Paper();
        $groups = $group->getGroupsByMakerId($maker_id);
        $papers = $paper->getPapersByMakerId($maker_id);

        switch($this->getStateOfExam($exam)) {
            case 0: $state = '未开始'; break;
            case 1: $state = '进行中'; break;
            case 2: $state = '已结束'; break;
                
        }

        $exam = array(
            'id' => $exam->id,
            'name' => $exam->name,
            'startTime' => substr($exam->startTime,0, 16),
            'endTime' => substr($exam->endTime,0, 16),
            'description' => $exam->description,
            'rule' => $exam->rule,
            'group' => $exam->group_id,
            'paper' => $exam->paper_id,
            'state' => $state,
            'objective_state' => $exam->state
        );

        $data = array('errcode' => 0, 'errmsg' => null, 'exam' => $exam, 'groups' => $groups, 'papers' => $papers);
        return $data;
    }

    public function editeExam($id, $name, $startTime, $endTime, $description, $rule, $group_id, $paper_id, $session)
    {
        date_default_timezone_set("PRC");
        $startTimeStamp = strtotime($startTime);
        $endTimeStamp = strtotime($endTime);
        $maker_id = $this->getIdBySession($session);

        // 不存在该Exam
        if (!$this->own($maker_id, $id)) {
            $data = array('errcode' => 1, 'errmsg' => 'Have no this exam.');
            return $data;
        }

        // Name为空
        if (empty($name)) {
            $data = array('errcode' => 1, 'errmsg' => 'Name can\'t be empty.');
            return $data;
        }

        // 有同名Exam
        if ($this->hasSameName($maker_id, $name) && $name != $this->find($id)->name) {
            $data = array('errcode' => 1, 'errmsg' => 'Exam with same name exists.');
            return $data;
        }

        // 日期非法
        if (!$startTimeStamp || !$endTimeStamp || $endTimeStamp <= $startTimeStamp || $endTimeStamp <= strtotime(date("Y-m-d H:i:s"))) {
            $data = array('errcode' => 1, 'errmsg' => 'Illegal date.');
            return $data;
        }

        // Group or paper非法
        if (!$this->hasGroupAndPaper($maker_id, $group_id, $paper_id)) {
            $data = array('errcode' => 1, 'errmsg' => 'Illegal group of paper.');
            return $data;
        }

        $exam = $this->find($id);
        if ($this->getStateOfExam($exam) == 0) {
            $exam = $this->find($id);
            $exam->name = $name;
            $exam->startTime = $startTime.':00';
            $exam->endTime = $endTime.':00';
            $exam->description = $description;
            $exam->rule = $rule;
            $exam->group_id = $group_id;
            $exam->paper_id = $paper_id;
            $exam->save();
            $data = array('errcode' => 0, 'errmsg' => '编辑成功.');
        }
        else if ($this->getStateOfExam($exam) == 1) {
            $exam = $this->find($id);
            $exam->endTime = $endTime.':00';
            $exam->description = $description;
            $exam->rule = $rule;
            $exam->save();
            $data = array('errcode' => 0, 'errmsg' => '编辑成功.');
        }
        else {
            $data = array('errcode' => 1, 'errmsg' => 'Exam finished.');
        }
        return $data;

    }

    public function removeExam($exam_id, $session)
    {
        $maker_id = $this->getIdBySession($session);

        // 不存在该Exam
        if (!$this->own($maker_id, $exam_id)) {
            $data = array('errcode' => 1, 'errmsg' => 'Have no this exam.');
            return $data;
        }

        $exam = $this->find($exam_id);
        if (!$this->getStateOfExam($exam) == 0) {
            $data = array('errcode' => 1, 'errmsg' => 'Exam has started.');
            return $data;
        }

        $this->destroy($exam_id);
        $data = array('errcode' => 0, 'errmsg' => '移除成功.');
        return $data;

    }

    public function finishExam($exam_id, $session)
    {
        $maker_id = $this->getIdBySession($session);

        // 不存在该Exam
        if (!$this->own($maker_id, $exam_id)) {
            $data = array('errcode' => 1, 'errmsg' => 'Have no this exam.');
            return $data;
        }

        // Exam状态有误
        $exam = $this->find($exam_id);
        if ($this->getStateOfExam($exam) != 2) {
            $data = array('errcode' => 1, 'errmsg' => 'Exam state error.');
            return $data;
        }
        if ($exam->state == 2) {
            $data = array('errcode' => 1, 'errmsg' => 'Exam state error.');
            return $data;
        }
        // 创建records
        $candidates = DB::table('candidates')->where("group_id", $exam->group_id)->get();
        foreach($candidates as $candidate) {
            $answers = DB::table('answers')->where("candidate_id", $candidate->id)->get();
            $points = 0;
            foreach($answers as $answer) {
                $points += $answer->points;
            }
            DB::table('records')->insert([
                'candidate_id' => $candidate->id,
                'exam_id' => $exam_id,
                'points' => $points
            ]);
        }
        $exam->state = 2;
        $exam->save();
        
        $data = array('errcode' => 0, 'errmsg' => '结束成功.');
        return $data;
    }

    public function getStateOfExam($exam) {
        date_default_timezone_set("PRC");
        $now = date("Y-m-d H:i:s");
        $nowStamp = strtotime($now);
        $startTimeStamp = strtotime($exam->startTime);
        $endTimeStamp = strtotime($exam->endTime);
        if ($startTimeStamp > $nowStamp) return 0; // 开始前
        else if ($startTimeStamp <= $nowStamp && $endTimeStamp > $nowStamp)  return 1; // 进行中
        else if ($endTimeStamp <= $nowStamp) return 2; // 已结束
    }

    public function getExamsInProgressByGroupId($group_id)
    {
        date_default_timezone_set("PRC");
        $now = date("Y-m-d H:i:s");
        return $this->where([
            ['group_id', '=', $group_id],
            ['startTime', '<=', $now],
            ['endTime', '>', $now]
        ])->get();
    }

    public function getExamsByGroupId($group_id) {
        return $this->where("group_id", "=", $group_id)->get();
    }

    public function getExamsByPaperId($paper_id) {
        return $this->where("paper_id", "=", $paper_id)->get();
    }

    // 该Maker有该班组与试卷
    public function hasGroupAndPaper($maker_id, $group_id, $paper_id)
    {
        $group = new Group();
        $paper = new Paper();
        return ($group->own($maker_id, $group_id) && $paper->own($maker_id, $paper_id));
    }

    // 该Maker有同名Exam
    public function hasSameName($maker_id, $name)
    {
        return $this->where([
            ['maker_id', '=', $maker_id],
            ['name', '=', $name]
        ])->exists();
    }

    public function getIdBySession($session)
    {
        $maker = new Maker();
        return $maker->getIdBySession($session);
    }

    public function own($maker_id, $exam_id)
    {
        return $this->where([
            ['id', '=', $exam_id],
            ['maker_id', '=', $maker_id],
        ])->exists();
    }
}
