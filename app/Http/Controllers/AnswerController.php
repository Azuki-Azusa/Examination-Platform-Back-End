<?php

namespace App\Http\Controllers;

use App\Answer;
use Illuminate\Http\Request;

class AnswerController extends Controller
{
    public function commit(Request $request)
    {
        $cookie = $this->getCookie2($request);
        $exam_id = $request->exam_id;
        $answers = $request->answers;
        $answer = new Answer();
        $data = $answer->commmit($exam_id, $answers, $cookie);
        return $this->req($data);
    }

    public function correctAll(Request $request)
    {
        $cookie = $this->getCookie($request);
        $exam_id = $request->exam_id;
        $answer = new Answer();
        $data = $answer->correctAll($exam_id, $cookie);
        return $this->req($data);
    }

    public function getRandomAnswer(Request $request, $question_id, $exam_id)
    {
        $cookie = $this->getCookie($request);
        $answer = new Answer();
        $data = $answer->getRandomAnswer($question_id, $exam_id, $cookie);
        return $this->req($data);
    }

    public function correct(Request $request)
    {
        $cookie = $this->getCookie($request);
        $answer_id = $request->answer_id;
        $points = $request->points;
        $answer = new Answer();
        $data = $answer->correct($answer_id, $points, $cookie);
        return $this->req($data);
    }
}
