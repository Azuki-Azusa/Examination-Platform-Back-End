<?php

namespace App;

use App\Maker;
use App\Paper;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    public function addQuestion($paper_id, $type, $num, $points, $question, $answer, $session)
    {
        $maker_id = $this->getIdBySession($session);
        
        // type非法
        if ($type < 0 || $type > 3) {
            $data = array('errcode' => 1, 'errmsg' => 'Illegal type.');
            return $data;
        }

        // 该Maker无该Paper
        $paper = new Paper();
        if (!$paper->own($maker_id, $paper_id)) {
            $data = array('errcode' => 1, 'errmsg' => 'Have not this paper.');
            return $data;
        }

        // 有使用该考卷的考试已开始
        if (!$paper->canEdited($paper_id)) {
            $data = array('errcode' => 1, 'errmsg' => 'This paper can\'t be edited because used by exam after beginning.');
            return $data;
        }

        // 该Paper有相同题号num
        if ($this->hasSameNum($paper_id, $num)) {
            $data = array('errcode' => 1, 'errmsg' => 'Same num exists.');
            return $data;
        }

        
        if ($type == 0) $answer = (string)$answer;
        else if ($type == 1) $answer = implode(",", $answer);

        $this->insert(
            [
            'paper_id' => $paper_id, 
            'type' => $type,
            'num' => $num,
            'points' => $points,
            'question' => json_encode($question, JSON_UNESCAPED_UNICODE),
            'answer' => $answer
            ]
        );
        $paper->addPoints($paper_id, $points);
        $data = array('errcode' => 0, 'errmsg' => '添加成功.');
        return $data;
    }

    public function removeQuestion($id, $session)
    {
        $maker_id = $this->getIdBySession($session);
        $question = $this->find($id);
        
        // 不存在该Question
        if (!$question) {
            $data = array('errcode' => 1, 'errmsg' => 'Illegal QuestionID.');
            return $data;
        }

        $paper_id = $question->paper_id;
        $points = $question->points;
        $paper = new Paper();

        // 该Question不属于该Maker
        if (!$paper->own($maker_id, $paper_id)) {
            $data = array('errcode' => 1, 'errmsg' => 'Illegal QuestionID.');
            return $data;
        }

        // 有使用该考卷的考试已开始
        if (!$paper->canEdited($id)) {
            $data = array('errcode' => 1, 'errmsg' => 'This paper can\'t be edited because used by exam after beginning.');
            return $data;
        }

        $paper->addPoints($paper_id, -$points);
        $this->destroy($id);
        $data = array('errcode' => 0, 'errmsg' => '删除成功.');
        return $data;
    }

    public function getQuestionsByPaperId($paper_id)
    {
        $questions = $this->where('paper_id', '=', $paper_id)->get();
        $data = array();
        foreach ($questions as $question) {
            $object = array(
                'id' => $question->id,
                'question' => json_decode($question->question),
                'points' => $question->points,
                'num' => $question->num,
                'type' => $question->type
            );
            array_push($data, $object);
        }
        return $data;
    }
    
    public function hasSameNum($paper_id, $num)
    {
        return $this->where([
            ['paper_id', '=', $paper_id],
            ['num', '=', $num]
        ])->exists();
    }
    
    public function getIdBySession($session)
    {
        $maker = new Maker();
        return $maker->getIdBySession($session);
    }
}
