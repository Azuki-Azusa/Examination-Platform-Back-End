<?php

namespace App;

use App\Exam;
use App\Question;
use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{

    public $timestamps = false;

    public function commmit($exam_id, $answers, $session)
    {
        $candidate_id = $this->getIdBySession($session);

        $candidate = new Candidate();
        $candidate = $candidate->find($candidate_id);
        $group_id = $candidate->group_id;

        $exam = new Exam();
        $exam = $exam->find($exam_id);
        if (!$exam || $exam->group_id != $group_id) {
            $data = array('errcode' => 1, 'errmsg' => 'Illegal exam_id.');
            return $data;
        }
        foreach ($answers as $answer) {
            $r = $this->insertAnswer($answer, $exam, $candidate);
            if ($r == 1) {
                $data = array('errcode' => 1, 'errmsg' => 'Illegal para.');
                return $data;
            }
        }
        $data = array('errcode' => 0, 'errmsg' => '提交成功');
        return $data;
    }

    public function getIdBySession($session)
    {
        return session($session, 'default');
    }

    public function insertAnswer($answer, $exam, $candidate)
    {
        $question_id = $answer['question_id'];
        $answer = $answer['answer'];

        // Question非法 or Candidate不属于Exam or Question不属于Exam
        $question = new Question();
        $question = $question->find($question_id);
        if (!$question || 
            $question->paper_id != $exam->paper_id ||
            $candidate->group_id != $exam->group_id) 
        {
            return 1;
        }
        
        if (is_array($answer)) $answer = implode(",", $answer);
        else if (is_int($answer)) $answer = (string)$answer;

        if ($this->answerExists($exam->id, $question_id, $candidate->id)) {
            $answerobj = $this->where([
                ['exam_id', '=', $exam->id],
                ['question_id', '=', $question_id],
                ['candidate_id', '=', $candidate->id]
            ])->first();
            $answerobj->answer = $answer;
            $answerobj->save();
        }
        else {
            $this->insert([
                'question_id' => $question_id,
                'exam_id' => $exam->id,
                'candidate_id' => $candidate->id,
                'answer' => $answer
            ]);
        }
        return 0;
    }

    public function correctAll($exam_id, $session)
    {
        $maker_id = $this->getIdBySession($session);

        $examObj = new Exam();
        $exam = $examObj->find($exam_id);
        if (!$exam || $exam->state != 0 || $examObj->getStateOfExam($exam) != 2) {
            $data = array('errcode' => 1, 'errmsg' => 'Exam state error.');
            return $data;
        }
        if ($exam->maker_id != $maker_id) {
            $data = array('errcode' => 1, 'errmsg' => 'Exam Maker error.');
            return $data;
        }

        $exam->state = 1;
        $exam->save();

        $questionObj = new Question();
        $questions = $questionObj->where('paper_id', $exam->paper_id)->get();

        $exam->state = 1;
        $exam->save();

        foreach ($questions as $question) {
            $type = $question->type;
            if ($type != 3) {
                $correctAnswer = $question->answer;
                $points = $question->points;
                $question_id = $question->id;
                $this->correctAnswers($question_id, $exam->id, $correctAnswer, $points);
            }
        }

        $data = array('errcode' => 0, 'errmsg' => '批改完成');
        return $data;

    }

    public function answerExists($exam_id, $question_id, $candidate_id)
    {
        return $this->where([
            ['exam_id', '=', $exam_id],
            ['question_id', '=', $question_id],
            ['candidate_id', '=', $candidate_id]
        ])->exists();
    }

    public function correctAnswers($question_id, $exam_id, $answer, $points)
    {
        $this->where([
            ['question_id', '=', $question_id],
            ['exam_id', '=', $exam_id],
        ])->update(['state' => 1]);
        $this->where([
            ['question_id', '=', $question_id],
            ['exam_id', '=', $exam_id],
            ['answer', '=', $answer],
        ])->update(['points' => $points]);
    }

    public function getRandomAnswer($question_id, $exam_id, $session)
    {
        $maker_id = $this->getIdBySession($session);

        $examObj = new Exam();
        $exam = $examObj->find($exam_id);
        if (!$exam || $exam->state == 2 || $examObj->getStateOfExam($exam) != 2) {
            $data = array('errcode' => 1, 'errmsg' => 'Exam state error.');
            return $data;
        }
        if ($exam->maker_id != $maker_id) {
            $data = array('errcode' => 1, 'errmsg' => 'Exam Maker error.');
            return $data;
        }

        $questionObj = new Question();
        $question = $questionObj->find($question_id);
        if (!$question || $question->type != 3) {
            $data = array('errcode' => 1, 'errmsg' => 'Questions_id error.');
            return $data;
        }

        if ($this->where([
            ['exam_id', '=', $exam_id],
            ['question_id', '=', $question_id],
            ['state', '=', 0]
        ])->doesntExist()) {
            $data = array('errcode' => 1, 'errmsg' => 'Questions_id error.');
            return $data;
        }

        $answer = $this->where([
            ['exam_id', '=', $exam_id],
            ['question_id', '=', $question_id],
            ['state', '=', 0]
        ])->first();

        $data = array('answer_id' => $answer->id, 'answer' => $answer->answer);
        return $data;

    }

    public function correct($answer_id, $points, $session)
    {
        $maker_id = $this->getIdBySession($session);

        $examObj = new Exam();
        $answer = $this->find($answer_id);
        if (!$answer) {
            $data = array('errcode' => 1, 'errmsg' => 'answer_id error.');
            return $data;
        }
        $exam_id = $answer->exam_id;
        $exam = $examObj->find($exam_id);
        if (!$exam || $exam->state == 2 || $examObj->getStateOfExam($exam) != 2) {
            $data = array('errcode' => 1, 'errmsg' => 'Exam state error.');
            return $data;
        }
        if ($exam->maker_id != $maker_id) {
            $data = array('errcode' => 1, 'errmsg' => 'Exam Maker error.');
            return $data;
        }

        $questionObj = new Question();
        $question = $questionObj->find($answer->question_id);
        if ($points > $question->points) {
            $data = array('errcode' => 1, 'errmsg' => 'Points error.');
            return $data;
        }
        $answer->state = 1;
        $answer->points = $points;
        $answer->save();
        $data = array('errcode' => 0, 'errmsg' => '批改成功.');
        return $data;
    }
}
