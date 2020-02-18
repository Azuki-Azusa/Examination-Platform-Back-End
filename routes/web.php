<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix('maker')->group(function () {
    Route::post('/register', 'MakerController@register');
    Route::get('/login/{email}/{password}', 'MakerController@login');

    Route::post('/createexam', 'ExamController@createExam')->middleware('checkMakerCookie');
    Route::get('/grouppaper', 'ExamController@getGroupAndPaper')->middleware('checkMakerCookie');
    Route::get('/tobestarted/exams', 'ExamController@getTobestartedExams')->middleware('checkMakerCookie');
    Route::get('/inprogress/exams', 'ExamController@getInprogressExams')->middleware('checkMakerCookie');
    Route::get('/finished/exams', 'ExamController@getFinishedExams')->middleware('checkMakerCookie');
    Route::get('/report/exams', 'ExamController@getReportExams')->middleware('checkMakerCookie');
    Route::get('/getexam/{id}', 'ExamController@getExam')->middleware('checkMakerCookie');
    Route::post('/editexam', 'ExamController@editExam')->middleware('checkMakerCookie');
    Route::post('/remove', 'ExamController@removeExam')->middleware('checkMakerCookie');

    Route::post('/createpaper', 'PaperController@createPaper')->middleware('checkMakerCookie');
    Route::get('/papers', 'PaperController@getPapers')->middleware('checkMakerCookie');
    Route::post('/editpaper', 'PaperController@editPaper')->middleware('checkMakerCookie');
    Route::get('/getpaper/{id}', 'PaperController@getPaper')->middleware('checkMakerCookie');

    Route::post('/creategroup', 'GroupController@createGroup')->middleware('checkMakerCookie');
    Route::get('/candidatesgroup', 'GroupController@getGroups')->middleware('checkMakerCookie');
    Route::post('/editgroup', 'GroupController@editGroup')->middleware('checkMakerCookie');
    Route::get('/getgroup/{id}', 'GroupController@getGroup')->middleware('checkMakerCookie');

    Route::post('/addquestion', 'QuestionController@addQuestion')->middleware('checkMakerCookie');
    Route::post('/removequestion', 'QuestionController@removeQuestion')->middleware('checkMakerCookie');

    Route::post('/addcandidate', 'CandidateController@addCandidate')->middleware('checkMakerCookie');
    Route::post('/removecandidate', 'CandidateController@removeCandidate')->middleware('checkMakerCookie');

});

Route::prefix('candidate')->group(function () {
    Route::get('/login/{candidate_id}/{candidate_pw}', 'CandidateController@login');
    Route::get('/getinfo', 'CandidateController@getInfo')->middleware('checkCandidateCookie');
    Route::get('/getexam', 'CandidateController@getExam')->middleware('checkCandidateCookie');
});

Route::get('/{session}', 'MakerController@getID');

Route::fallback(function () {
    $req = array('errcode' => 404, 'errmsg' => "不存在的api", 'data' => null);
    echo json_encode($req, JSON_UNESCAPED_UNICODE);
});

