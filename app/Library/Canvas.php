<?php


namespace App\Library;

use \GuzzleHttp\Client;
use Exception;
use RuntimeException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Psr7;

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
            Cache::put("user_" . $emplId, $user, 60);
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
            $courses = $this->getPaginatedResults("users/" . $userId . "/courses?per_page=300");
            Cache::put("courses_" . $userId, $courses, 60);
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
            Cache::put("course_" . $courseId, $course, 60);
            return $course;
        } catch (RequestException $e) {
            $msg = $e->getMessage();
            $errorMessage = 'GetCourse Error: ' . $msg;

            throw new RuntimeException($errorMessage);
        }
    }

    public function getTeachersForCourse(int $courseId): array {
        if(Cache::has("course_teachers_" . $courseId)) {
            return Cache::get("course_teachers_" . $courseId);
        }
        try {
            $result = $this->client->get("courses/" . $courseId . "/users" . "?" . http_build_query(["enrollment_type[]"=>"teacher"]) . "&" . http_build_query(["enrollment_type[]"=>"designer"]));
            $course = json_decode($result->getBody());
            Cache::put("course_teachers_" . $courseId, $course, 60);
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


    public function getTerms() : array {
        if(Cache::has("terms")) {
            return Cache::get("terms");
        }
        try {
            $result = $this->client->get("accounts/1/terms");
            $terms = json_decode($result->getBody());
            Cache::put("terms", $terms->enrollment_terms, 60);
            return $terms->enrollment_terms;
        } catch (RequestException $e) {
            $msg = $e->getMessage();
            $errorMessage = 'GetTerms Error: ' . $msg;
            throw new RuntimeException($errorMessage);
        }
    }

    public function getAccount(int $accountId) : object {
        if(Cache::has("account_" . $accountId)) {
            return Cache::get("account_" . $accountId);
        }
        try {
            $result = $this->client->get("accounts/" . $accountId);
            $account = json_decode($result->getBody());
            Cache::put("account_" . $accountId, $account, 60);
            return $account;
        } catch (RequestException $e) {
            $msg = $e->getMessage();
            $errorMessage = 'GetAccount Error: ' . $msg;
            throw new RuntimeException($errorMessage);
        }
    }

    public function getAdminsForSubaccount(int $subAccountId, bool $recursive): array {
        if(Cache::has("subaccount_" . $subAccountId)) {
            return Cache::get("subaccount_" . $subAccountId);
        }

        try {
            $account = $this->getAccount($subAccountId);
        }
        catch (RuntimeException $e) {
            // this might fail due to perms
            return [];
        }

        try {
            $url = "accounts/" . $subAccountId . "/admins?per_page=30";
            $results = $this->getPaginatedResults($url);
            
            if($recursive) {
                
                $parent = $account->parent_account_id;
                if($parent) {
                    $results = array_merge($results, $this->getAdminsForSubaccount($parent, $recursive));
                    Cache::put("subaccount_" . $subAccountId, $results, 60);
                }
            }
            
            return $results;
            
        }
        catch (RequestException $e) {
            $msg = $e->getMessage();
            $errorMessage = 'GetSubAccount Error: ' . $msg;
            throw new RuntimeException($errorMessage);
        }

    }

    private function getPaginatedResults($url): array {
        $pagination = true;
        $results = [];
        while($pagination == true) {
            $result = $this->client->get($url);
            $results = array_merge(json_decode($result->getBody()), $results);
            if($parsed = Psr7\Header::parse($result->getHeader('Link'))) {
                $linkHeaders = $this->parseLinkHeader($parsed);
                if($linkHeaders["current"] == $linkHeaders["last"] || !isset($linkHeaders["next"])) {
                    $pagination = false;
                }
                else {
                    $url = $linkHeaders["next"];
                }
            }
        }
        return $results;
    }

    private function parseLinkHeader(array $headers) {
        $paginationHeaders = [];
        foreach ($headers as $header) {
            $paginationHeaders[$header["rel"]] = str_replace(["<",">", $this->client->getConfig('base_uri')],'',$header[0]);
        }
        return $paginationHeaders;
    }
}
