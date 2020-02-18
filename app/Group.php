<?php

namespace App;

use App\Maker;
use App\Exam;
use App\Candidate;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    public $timestamps = false;
    
    public function createGroup($name, $description, $session)
    {
        $maker_id = $this->getIdBySession($session);
        
        // Name为空
        if (empty($name)) {
            $data = array('errcode' => 1, 'errmsg' => 'Name can\'t be empty.');
            return $data;
        }

        // 有同名Group
        if ($this->hasSameName($maker_id, $name)) {
            $data = array('errcode' => 1, 'errmsg' => 'Group with same name exists.');
            return $data;
        }

        // 插入
        $this->insert(
            [
            'maker_id' => $maker_id, 
            'name' => $name,
            'description' => $description,
            ]
        );
        $data = array('errcode' => 0, 'errmsg' => '创建成功.');
        return $data;
    }

    public function getGroups($session)
    {
        $maker_id = $this->getIdBySession($session);
        $data = $this->getGroupsByMakerId($maker_id);
        $data = array('errcode' => 0, 'errmsg' => null, 'group' => $data);
        return $data;
    }

    public function editGroup($id, $name, $description, $session)
    {
        $maker_id = $this->getIdBySession($session);

        // 该Maker无该Group
        if (!$this->own($maker_id, $id)) {
            $data = array('errcode' => 1, 'errmsg' => 'Have no this group.');
            return $data;
        }

        // Name为空
        if (empty($name)) {
            $data = array('errcode' => 1, 'errmsg' => 'Name can\'t be empty.');
            return $data;
        }

        // 有同名Group
        if ($this->hasSameName($maker_id, $name) && $name != $this->find($id)->name) {
            $data = array('errcode' => 1, 'errmsg' => 'Group with same name exists.');
            return $data;
        }

        /*
        // 有使用该考卷的考试已开始
        if (!$this->canEdited($id)) {
            $data = array('errcode' => 1, 'errmsg' => 'This group can\'t be edited because used by exam after beginning.');
            return $data;
        }
        */

        // 编辑
        $group = $this->find($id);
        $group->name = $name;
        $group->description = $description;
        $group->save();
        $data = array('errcode' => 0, 'errmsg' => '编辑成功.');
        return $data;
    }

    public function getGroup($id, $session)
    {
        $maker_id = $this->getIdBySession($session);

        // 该Maker无该Group
        if (!$this->own($maker_id, $id)) {
            $data = array('errcode' => 1, 'errmsg' => 'Have no this group.');
            return $data;
        }

        $candidate = new Candidate();
        $group = $this->find($id);
        $data = array(
            'id' => $group->id,
            'name' => $group->name,
            'description' => $group->description,
            'number' => $group->number,
            'candidates' => $candidate->getCandidateByGroupId($id)
        );
        $data = array('errcode' => 0, 'errmsg' => null, 'group' => $data);
        return $data;
    }

    // 有使用该考卷的考试已开始
    public function canEdited($id)
    {
        $examObject = new Exam();
        $exams = $examObject->getExamsByGroupId($id);
        foreach ($exams as $exam) {
            if ($examObject->getStateOfExam($exam) <> 0) {
                return false;
            }
        }
        return true;
    }

    public function getGroupsByMakerId($maker_id) {
        $groups = $this->where('maker_id', $maker_id)->get();
        $data = array();
        foreach ($groups as $group) {
            $object = array(
                'name' => $group->name, 
                'description' => $group->description, 
                'number' => $group->number,
                'id' => $group->id);
            array_push($data, $object);
        }
        return $data;
    }

    public function getName($id) {
        return $this->find($id)->name;
    }

    // 该Maker有同名Group
    public function hasSameName($maker_id, $name)
    {
        return $this->where([
            ['maker_id', '=', $maker_id],
            ['name', '=', $name]
        ])->exists();
    }

    public function addNumber($group_id, $number)
    {
        $group = $this->find($group_id);
        $group->number += $number;
        $group->save();
    }

    public function getIdBySession($session)
    {
        $maker = new Maker();
        return $maker->getIdBySession($session);
    }

    // 该Maker有该Group
    public function own($maker_id, $group_id)
    {
        $maker = new Maker();
        return $this->where([
            ['id', '=', $group_id],
            ['maker_id', '=', $maker_id],
        ])->exists();
    }
}
