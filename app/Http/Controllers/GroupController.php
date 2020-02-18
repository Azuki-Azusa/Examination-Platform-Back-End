<?php

namespace App\Http\Controllers;

use App\Group;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function createGroup(Request $request)
    {
        $name = $request->input('group.name');
        $description = $request->input('group.description');
        $cookie = $this->getCookie($request);
        $group = new Group();
        $data = $group->createGroup($name, $description, $cookie);
        return $this->req($data);
    }

    public function getGroups(Request $request)
    {
        $cookie = $this->getCookie($request);
        $group = new Group();
        $data = $group->getGroups($cookie);
        return $this->req($data);
    }

    public function editGroup(Request $request)
    {
        $cookie = $this->getCookie($request);
        $name = $request->input('group.name');
        $description = $request->input('group.description');
        $id = $request->input('group.id');
        $group = new Group();
        $data = $group->editGroup($id, $name, $description, $cookie);
        return $this->req($data);
    }

    public function getGroup(Request $request, $id)
    {
        $cookie = $this->getCookie($request);
        $group = new Group();
        $data = $group->getGroup($id, $cookie);
        return $this->req($data);
    }

}
