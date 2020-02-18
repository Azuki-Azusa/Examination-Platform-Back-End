<?php

namespace App\Http\Controllers;

use App\Exam;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    // maker create exam
    public function createExam(Request $request)
    {
        $name = $request->input('exam.name');
        $startTime = $request->input('exam.startTime');
        $endTime = $request->input('exam.endTime');
        $description = $request->input('exam.description');
        $rule = $request->input('exam.rule');
        $group_id = $request->input('exam.group');
        $paper_id = $request->input('exam.paper');
        $cookie = $this->getCookie($request);
        $exam = new Exam();
        $data = $exam->createExam($name, $startTime, $endTime,  $description, $rule, $group_id, $paper_id, $cookie);
        return $this->req($data);
    }

    public function getGroupAndPaper(Request $request)
    {
        $cookie = $this->getCookie($request);
        $exam = new Exam();
        $data = $exam->getGroupAndPaper($cookie);
        return $this->req($data);
    }

    public function getTobestartedExams(Request $request)
    {
        $cookie = $this->getCookie($request);
        $exam = new Exam();
        $data = $exam->getExams(0, $cookie);
        return $this->req($data);
    }

    public function getInprogressExams(Request $request)
    {
        $cookie = $this->getCookie($request);
        $exam = new Exam();
        $data = $exam->getExams(1, $cookie);
        return $this->req($data);
    }

    public function getFinishedExams(Request $request)
    {
        $cookie = $this->getCookie($request);
        $exam = new Exam();
        $data = $exam->getExams(2, $cookie);
        return $this->req($data);
    }

    public function getReportExams(Request $request)
    {
        $cookie = $this->getCookie($request);
        $exam = new Exam();
        $data = $exam->getExams(3, $cookie);
        return $this->req($data);
    }

    public function getExam(Request $request, $id)
    {
        $cookie = $this->getCookie($request);
        $exam = new Exam();
        $data = $exam->getExam($id, $cookie);
        return $this->req($data);
    }

    public function editExam(Request $request)
    {
        $cookie = $this->getCookie($request);
        $id = $request->input('exam.id');
        $name = $request->input('exam.name');
        $startTime = $request->input('exam.startTime');
        $endTime = $request->input('exam.endTime');
        $description = $request->input('exam.description');
        $rule = $request->input('exam.rule');
        $group_id = $request->input('exam.group');
        $paper_id = $request->input('exam.paper');
        $exam = new Exam();
        $data = $exam->editeExam($id, $name, $startTime, $endTime, $description, $rule, $group_id, $paper_id, $cookie);
        return $this->req($data);
    }

    public function removeExam(Request $request)
    {
        $cookie = $this->getCookie($request);
        $exam_id = $request->input('exam_id');
        $exam = new Exam();
        $data = $exam->removeExam($exam_id, $cookie);
        return $this->req($data);

    }
}
