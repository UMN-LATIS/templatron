<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Library\Canvas;
use Auth;
use Log;
use Str;

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
        if(count($targetCourse)> 1 && !Auth::user()->admin) {
            abort(403);
        }
        $sourceCourse = $request->get("template");
        $migrationInfo = [];
        foreach($targetCourse as $course) {
            Log::info("Starting migration for:" . $course . " " . $sourceCourse . " " . Auth::user()->emplid);
            $migrationInfo[] = $this->canvas->createContentMigration($course, $sourceCourse);
        }
        
        return response()->json(collect($migrationInfo)->pluck("id"));
    }

    public function validateUserCanMigrateCourse(Request $req, string $course) {
        $user = Auth::user()->emplid;
        if(!is_int($user)) {
            abort(403);
        }
        $canvasUserInfo = $this->canvas->getUser($user);

        if(Str::contains($course, ',') && !Auth::user()->admin) {
            abort(403);
        }
        $courseList = explode(",", $course);
        array_walk($courseList, function($val) { return trim($val); });
        
        $courseReturnArray = [];
        foreach($courseList as $course) {
            $userList = $this->canvas->getTeachersForCourse($course);
            if(collect($userList)->pluck("id")->contains($canvasUserInfo->id)) {
                $courseReturnArray[] = $this->canvas->getCourse($course);
            }
            else {
                // welp they're not attached to the course. We'll need to do the more expensive lookup.
                $courseInfo =  $this->canvas->getCourse($course);
                $adminsForSubaccount = $this->canvas->getAdminsForSubaccount($courseInfo->account_id, true);
                if(collect($adminsForSubaccount)->filter(function($item) { 
                    return $item->role == "College/Program Assistant" || $item->role == "Instructor Course-Creation Support";
                })->pluck("user.id")->contains($canvasUserInfo->id)) {
                    $courseReturnArray =$this->canvas->getCourse($course);
                }
            }
        }

        if(count($courseReturnArray) == 0) {
            abort(403);
        }

        return response()->json($courseReturnArray);
    }
}
