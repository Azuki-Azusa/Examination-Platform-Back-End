<?php

namespace App\Http\Controllers;

use App\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function addQuestion(Request $request)
    {
        $cookie = $this->getCookie($request);
        $paper_id = $request->input('paper_id');
        $num = $request->input('question.num');
        $question = $request->input('question.question');
        $answer = $request->input('question.answer');
        $points = $request->input('question.points');
        $type = $request->input('question.type');
        $questionObject = new Question();
        $data = $questionObject->addQuestion($paper_id, $type, $num, $points, $question, $answer, $cookie);
        return $this->req($data);
    }

    public function removeQuestion(Request $request)
    {
        $cookie = $this->getCookie($request);
        $id = $request->input('question_id');
        $question = new Question();
        $data = $question->removeQuestion($id, $cookie);
        return $this->req($data);
    }
}
