<?php

namespace App;

use App\Maker;
use App\Group;
use App\Exam;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    public function addCandidate($group_id, $name, $login_id, $password, $session)
    {
        $maker_id = $this->getIdBySession($session);

        // 该Maker无该Group
        $group = new Group();
        if (!$group->own($maker_id, $group_id)) {
            $data = array('errcode' => 1, 'errmsg' => 'Have not this group.');
            return $data;
        }

        // 有使用该Group的考试已开始
        if (!$group->canEdited($group_id)) {
            $data = array('errcode' => 1, 'errmsg' => 'This group can\'t be edited because used by exam after beginning.');
            return $data;
        }

        // 该Paper有相同题号num
        if (!$this->isUnique($login_id, $password)) {
            $data = array('errcode' => 1, 'errmsg' => '(loginid, password) is not unique.');
            return $data;
        }

        $this->insert(
            [
            'group_id' => $group_id, 
            'name' => $name,
            'login_id' => $login_id,
            'password' => $password,
            ]
        );
        $group->addNumber($group_id, 1);
        $data = array('errcode' => 0, 'errmsg' => '添加成功.');
        return $data;

    }

    public function removeCandidate($candidate_id, $session)
    {
        $maker_id = $this->getIdBySession($session);

        $candidate = $this->find($candidate_id);

        // 不存在该Candidate
        if (!$candidate) {
            $data = array('errcode' => 1, 'errmsg' => 'Illegal CandidateID.');
            return $data;
        }

        $group_id = $candidate->group_id;
        $group = new Group();

        // 该Candidate不属于该Maker
        if (!$group->own($maker_id, $group_id)) {
            $data = array('errcode' => 1, 'errmsg' => 'Illegal CandidateID.');
            return $data;
        }

        // 有使用该考卷的考试已开始
        if (!$group->canEdited($candidate_id)) {
            $data = array('errcode' => 1, 'errmsg' => 'This paper can\'t be edited because used by exam after beginning.');
            return $data;
        }

        $group->addNumber($group_id, -1);
        $this->destroy($candidate_id);
        $data = array('errcode' => 0, 'errmsg' => '删除成功.');
        return $data;
    }

    public function getCandidateByGroupId($group_id)
    {
        $candidates = $this->where('group_id', '=', $group_id)->get();
        $data = array();
        foreach ($candidates as $candidate) {
            $object = array(
                'id' => $candidate->id,
                'name' => $candidate->name,
                'login_id' => $candidate->login_id,
                'password' => $candidate->password
            );
            array_push($data, $object);
        }
        return $data;
    }

    public function login($candidate_id, $candidate_pw)
    {
        $id = $this->where([
            ['login_id', '=', $candidate_id],
            ['password', '=', $candidate_pw],
        ])->value('id');
        if ($id) {
            $session = $this->createSession($candidate_id, $candidate_pw);
            $data = array('errcode' => 0, 'session' => $session, 'errmsg' => null);
        }
        else {
            $data = array('errcode' => 1, 'errmsg' => 'login_id or password incorrect');
        }
        return $data;
    }

    public function getInfo($session)
    {
        $id = $this->getCandidateIdBySession($session);
        
        $candidate = $this->find($id);
        $name = $candidate->name;
        $group_id = $candidate->group_id;
        $group = new Group();
        $group_name = $group->getName($group_id);
        $data = array('errcode' => 0, 'errmsg' => null, 'candidate' => $name, 'group'=> $group_name);
        return $data;
    }

    public function getExam($session)
    {
        $id = $this->getCandidateIdBySession($session);

        $group_id = $this->find($id)->group_id;
        $exam = new Exam();
        $exams = $exam->getExamsByGroupId($group_id);

        $data = array();
        foreach($exams as $exam) {
            $object = array(
                'id' => $exam->id,
                'name' => $exam->name,
                'startTime' => substr($exam->startTime, 0, 16), 
                'endTime' => substr($exam->endTime, 0, 16)
            );
            array_push($data, $object);
        }
        $data = array('errcode' => 0, 'errmsg' => null, 'exams' => $data);
        return $data;
    }

    public function getIdBySession($session)
    {
        $maker = new Maker();
        return $maker->getIdBySession($session);
    }

    public function isUnique($login_id, $password)
    {
        return $this->where([
            ['login_id', '=', $login_id],
            ['password', '=', $password]
        ])->doesntExist();
    }

    public function getCandidateIdBySession($session)
    {
        return session($session, 'default');
    }

    public function createSession($login_id, $password) 
    {
        $session = md5(uniqid(microtime(true).$login_id.'asuka'.$password,true));
        session([$session => $this->getIdByIDPW($login_id, $password)]);
        return $session;
    }

    public function getIdByIDPW($login_id, $password)
    {
        return $this->where([
            ['login_id', '=', $login_id],
            ['password', '=', $password]
        ])->value('id');
    }

}
