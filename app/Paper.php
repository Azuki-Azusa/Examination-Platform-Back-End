<?php

namespace App;

use App\Maker;
use App\Exam;
use App\Question;
use Illuminate\Database\Eloquent\Model;

class Paper extends Model
{
    public $timestamps = false;

    public function createPaper($name, $description, $session)
    {
        $maker_id = $this->getIdBySession($session);

        // Name为空
        if (empty($name)) {
            $data = array('errcode' => 1, 'errmsg' => 'Name can\'t be empty.');
            return $data;
        }

        // 有同名Paper
        if ($this->hasSameName($maker_id, $name)) {
            $data = array('errcode' => 1, 'errmsg' => 'Paper with same name exists.');
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

    public function getPapers($session)
    {
        $maker_id = $this->getIdBySession($session);
        $data = $this->getPapersByMakerId($maker_id);
        $data = array('errcode' => 0, 'errmsg' => null, 'papers' => $data);
        return $data;
    }

    public function editPaper($id, $name, $description, $session)
    {
        $maker_id = $this->getIdBySession($session);

        // 该Maker无该Paper
        if (!$this->own($maker_id, $id)) {
            $data = array('errcode' => 1, 'errmsg' => 'Have no this paper.');
            return $data;
        }

        // Name为空
        if (empty($name)) {
            $data = array('errcode' => 1, 'errmsg' => 'Name can\'t be empty.');
            return $data;
        }

        // 有同名Paper
        if ($this->hasSameName($maker_id, $name) && $name != $this->find($id)->name) {
            $data = array('errcode' => 1, 'errmsg' => 'Paper with same name exists.');
            return $data;
        }

        /*
        // 有使用该考卷的考试已开始
        if (!$this->canEdited($id)) {
            $data = array('errcode' => 1, 'errmsg' => 'This paper can\'t be edited because used by exam after beginning.');
            return $data;
        }
        */

        // 编辑
        $paper = $this->find($id);
        $paper->name = $name;
        $paper->description = $description;
        $paper->save();
        $data = array('errcode' => 0, 'errmsg' => '编辑成功.');
        return $data;
    }

    public function getPaper($id, $session)
    {
        $maker_id = $this->getIdBySession($session);

        // 该Maker无该Paper
        if (!$this->own($maker_id, $id)) {
            $data = array('errcode' => 1, 'errmsg' => 'Have no this paper.');
            return $data;
        }

        $question = new Question();
        $paper = $this->find($id);
        $data = array(
            'id' => $paper->id,
            'name' => $paper->name,
            'description' => $paper->description,
            'total_points' => $paper->total_points,
            'questions' => $question->getQuestionsByPaperId($id)
        );
        $data = array('errcode' => 0, 'errmsg' => null, 'paper' => $data);
        return $data;
    }

    // 有使用该考卷的考试已开始
    public function canEdited($id)
    {
        $examObject = new Exam();
        $exams = $examObject->getExamsByPaperId($id);
        foreach ($exams as $exam) {
            if ($examObject->getStateOfExam($exam) <> 0) {
                return false;
            }
        }
        return true;
    }

    public function getPapersByMakerId($maker_id) {
        $papers = $this->where('maker_id', $maker_id)->get();
        $data = array();
        foreach ($papers as $paper) {
            $object = array(
                'name' => $paper->name, 
                'description' => $paper->description, 
                'total_points' => $paper->total_points,
                'id' => $paper->id);
            array_push($data, $object);
        }
        return $data;
    }

    public function addPoints($paper_id, $points) {
        $paper = $this->find($paper_id);
        $paper->total_points += $points;
        $paper->save();
    }

    public function getPoints($id) {
        return $this->find($id)->total_points;
    }

    public function getName($id) {
        return $this->find($id)->name;
    }

    // 该Maker有同名Paper
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

    // 该Maker有该Paper
    public function own($maker_id, $paper_id)
    {
        $maker = new Maker();
        return $this->where([
            ['id', '=', $paper_id],
            ['maker_id', '=', $maker_id],
        ])->exists();
    }

}
