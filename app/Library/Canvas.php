<?php


namespace App\Library;

use \GuzzleHttp\Client;
use Exception;
use RuntimeException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Cache;


class Canvas
{
    public $canvasId = null;
    public $enrollmentTerm = null;
    
    
    public function __construct(string $canvasToken=null, string $canvasURL=null, int $canvasId = null) {
        if(!$canvasToken) {
            throw new \Exception('A Canvas Token must be Specified');
        }

        if(!$canvasURL) {
            throw new \Exception('A Canvas URL must be Specified');
        }

        if(!$canvasId) {
            throw new \Exception('A Canvas ID must be Specified');
        }
        $this->canvasId = $canvasId;


        $this->client = new Client(
            [
                'headers' => 
                [
                    'Authorization' => 'Bearer ' . $canvasToken
                ],
                'base_uri' => $canvasURL . "/api/v1/"
            ]);
    }

    public function findUser(string $user) : array  {

        try {
            $result = $this->client->get("accounts/" . $this->canvasId . "/users?search_term=" . $user);
            return json_decode($result->getBody());
        } catch (RequestException $e) {
            $msg = $e->getMessage();
            $errorMessage = 'FindUser Error: ' . $msg;

            throw new RuntimeException($errorMessage);
        }
    }

    public function getUser(int $emplId) : object  {
        if(Cache::has("user_" . $emplId)) {
            return Cache::get("user_" . $emplId);
        }

        try {
            $result = $this->client->get("users/sis_user_id:" . $emplId);
            $user = json_decode($result->getBody());
            Cache::put("user_" . $emplId, $user, 600);
            return $user;
        } catch (RequestException $e) {
            $msg = $e->getMessage();
            $errorMessage = 'GetUser Error: ' . $msg;

            throw new RuntimeException($errorMessage);
        }
    }

    public function getUserCourses(int $userId) : array {

        if(Cache::has("courses_" . $userId)) {
            return Cache::get("courses_" . $userId);
        }

        try {
            $result = $this->client->get("users/" . $userId . "/courses?per_page=300");
            $courses = json_decode($result->getBody());
            Cache::put("courses_" . $userId, $courses, 600);
            return $courses;
        } catch (RequestException $e) {
            $msg = $e->getMessage();
            $errorMessage = 'GetUserCourses Error: ' . $msg;

            throw new RuntimeException($errorMessage);
        }
    }

    public function getCourse(int $courseId) : object {
        if(Cache::has("course_" . $courseId)) {
            return Cache::get("course_" . $courseId);
        }
        try {
            $result = $this->client->get("courses/" . $courseId . "?" . http_build_query(["include[]"=>"course_image"]));
            $course = json_decode($result->getBody());
            Cache::put("course_" . $courseId, $course, 600);
            return $course;
        } catch (RequestException $e) {
            $msg = $e->getMessage();
            $errorMessage = 'GetCourse Error: ' . $msg;

            throw new RuntimeException($errorMessage);
        }

    }

    public function createContentMigration(int $targetCourseId, int $sourceCourseId) : object{

        try {
            $result = $this->client->post("courses/" . $targetCourseId . "/content_migrations", [
                "form_params" => [
                    "migration_type" => "course_copy_importer",
                    "settings[source_course_id]" => $sourceCourseId
                ]
            ]);
            return json_decode($result->getBody());
        } catch (RequestException $e) {
            $msg = $e->getMessage();
            $errorMessage = 'Create Migration Error: ' . $msg;

            throw new RuntimeException($errorMessage);
        }
    }

    public function getMigrationProgress(int $progressId) {
        try {
            $result = $this->client->get("progress/" . $progressId);
            return json_decode($result->getBody());
        } catch (RequestException $e) {
            $msg = $e->getMessage();
            $errorMessage = 'getMigrationProgress Error: ' . $msg;

            throw new RuntimeException($errorMessage);
        }
    }
}
