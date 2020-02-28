<?php

namespace App\Http\Controllers;

use App\Candidate;
use Illuminate\Http\Request;

class CandidateController extends Controller
{
    public function addCandidate(Request $request)
    {
        $cookie = $this->getCookie($request);
        $group_id = $request->input('group_id');
        $name = $request->input('candidate.name');
        $login_id = $request->input('candidate.login_id');
        $password = $request->input('candidate.password');
        $candidate = new Candidate();
        $data = $candidate->addCandidate($group_id, $name, $login_id, $password, $cookie);
        return $this->req($data);
    }

    public function removeCandidate(Request $request)
    {
        $cookie = $this->getCookie($request);
        $candidate_id = $request->input('candidate_id');
        $candidate = new Candidate();
        $data = $candidate->removeCandidate($candidate_id, $cookie);
        return $this->req($data);
    }

    public function login($candidate_id, $candidate_pw)
    {
        $candidate = new Candidate();
        $data = $candidate->login($candidate_id, $candidate_pw);
        if ($data['errcode'] == 0) {
            $cookie = $data['session'];
            return response($this->req($data))->cookie('sessionCandidate', $cookie);
        }
        else {
            return $this->req($data);
        }
    }

    public function getInfo(Request $request)
    {
        $cookie = $this->getCookie2($request);
        $candidate = new Candidate();
        $data = $candidate->getInfo($cookie);
        return $this->req($data);
    }

    public function getExam(Request $request)
    {
        $cookie = $this->getCookie2($request);
        $candidate = new Candidate();
        $data = $candidate->getExam($cookie);
        return $this->req($data);
    }

    public function getQuestions(Request $request, $exam_id)
    {
        $cookie = $this->getCookie2($request);
        $candidate = new Candidate();
        $data = $candidate->getQuestions($exam_id, $cookie);
        return $this->req($data);
    }

    public function getCandidateInfo(Request $request, $exam_id)
    {
        $cookie = $this->getCookie($request);
        $candidate = new Candidate();
        $data = $candidate->getCandidateInfo($exam_id, $cookie);
        return $this->req($data);
    }
}
