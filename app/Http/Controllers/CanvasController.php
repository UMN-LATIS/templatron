<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Library\Canvas;
use Auth;

class CanvasController extends Controller
{
    public $canvas = null;

    public function __construct() {
        $this->canvas = new Canvas(config("canvas.token"), config("canvas.url"), config("canvas.account"));
    }


    public function index() {
        if(!Auth::user()->emplid) {
            return abort(403);
        }

        $emplId = Auth::user()->emplid;

        $canvasUserInfo = $this->canvas->getUser($emplId);
        $userCourses = $this->canvas->getUserCourses($canvasUserInfo->id);

        $data = collect($userCourses)->filter(function($value, $key) {
            return (collect($value->enrollments)->filter(function($value, $key) {
                return $value->role == "TeacherEnrollment" || $value->role == "DesignerEnrollment";
            })->count() > 0);
        })->sortByDesc("enrollment_term_id")->values();
        return response()->json($data);

    }
    
    public function createMigration(Request $request) {
        
        $targetCourse = $request->get("selectedCourse");
        $sourceCourse = $request->get("template");
        $migrationInfo = $this->canvas->createContentMigration($targetCourse, $sourceCourse);
        return $migrationInfo->id;
    }

    public function validateUserCanMigrateCourse(Request $req, int $course) {
        $user = Auth::user()->emplid;
        $canvasUserInfo = $this->canvas->getUser($user);
        $userList = $this->canvas->getTeachersForCourse($course);
        if(collect($userList)->pluck("id")->contains($canvasUserInfo->id)) {
            return response()->json($this->canvas->getCourse($course));
        }
        else {
            // welp they're not attached to the course. We'll need to do the more expensive lookup.
            $courseInfo =  $this->canvas->getCourse($course);
            $adminsForSubaccount = $this->canvas->getAdminsForSubaccount($courseInfo->account_id, true);
            if(collect($adminsForSubaccount)->filter(function($item) { 
                return $item->role == "College/Program Assistant" || $item->role == "Instructor Course-Creation Support";
            })->pluck("user.id")->contains($canvasUserInfo->id)) {
                return response()->json($this->canvas->getCourse($course));
            }

            abort(403);
        }
    }
}
