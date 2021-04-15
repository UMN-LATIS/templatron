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
        Auth::user()->emplId = 2328381;

        if(!Auth::user()->emplId) {
            return abort(403);
        }

        
        $emplId = Auth::user()->emplId;

        $canvasUserInfo = $this->canvas->getUser($emplId);
        $userCourses = $this->canvas->getUserCourses($canvasUserInfo->id);

        return collect($userCourses)->filter(function($value, $key) {
            return (collect($value->enrollments)->filter(function($value, $key) {
                return $value->role == "TeacherEnrollment" || $value->role == "DesignerEnrollment";
            })->count() > 0);
        })->sortByDesc("enrollment_term_id");

    }

    
    public function createMigration(Request $request) {
        
        $targetCourse = $request->get("selectedCourse");
        $sourceCourse = $request->get("template");
        $migrationInfo = $this->canvas->createContentMigration($targetCourse, $sourceCourse);
        return $migrationInfo->id;
    }
}
