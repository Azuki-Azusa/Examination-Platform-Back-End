<?php

namespace App\Http\Controllers;

use App\Paper;
use Illuminate\Http\Request;

class PaperController extends Controller
{
    // maker create paper
    public function createPaper(Request $request)
    {
        $name = $request->input('paper.name');
        $description = $request->input('paper.description');
        $cookie = $this->getCookie($request);
        $paper = new Paper();
        $data = $paper->createPaper($name, $description, $cookie);
        return $this->req($data);
    }

    public function getPapers(Request $request)
    {
        $cookie = $this->getCookie($request);
        $paper = new Paper();
        $data = $paper->getPapers($cookie);
        return $this->req($data);
    }

    public function editPaper(Request $request)
    {
        $cookie = $this->getCookie($request);
        $name = $request->input('paper.name');
        $description = $request->input('paper.description');
        $id = $request->input('paper.id');
        $paper = new Paper();
        $data = $paper->editPaper($id, $name, $description, $cookie);
        return $this->req($data);
    }

    
    public function getPaper(Request $request, $id)
    {
        $cookie = $this->getCookie($request);
        $paper = new Paper();
        $data = $paper->getPaper($id, $cookie);
        return $this->req($data);
    }
    

}
