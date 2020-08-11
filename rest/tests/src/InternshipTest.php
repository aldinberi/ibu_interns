<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__."/../../app/utils/ModelValidator.php";

require_once 'app/dao/InternshipDao.php';

/**
 * Sample test class which showcases some basic aspects of PHPUnit.
 * This test file can be removed, or edited to suit your project needs.
 */
class InternshipTest extends TestCase {
    private $dao;

    public function setUp() {
        $this->dao = new InternshipDao();
    }


    // public function testIntershipInsert() {
    //     try{
    //         $example_internship = [
    //             "company_id" => 8,
    //             "title" => "PHP backend developer",
    //             "department_id" => 1,
    //             "job_description" => "Work on backend in PHP",
    //             "start_date" =>"2020-02-04",
    //             "end_date" => "2020-02-04",
    //             "status" => "PENDING"
    //         ];
            
    //         $this->dao->insert($example_internship);
    //         $this->assertTrue(true);

    //     }catch(Exception $e){
    //         $this->fail();
    //     }

    // }

    public function testBasicInternshipInfo(){
        $internships = $this->dao->get_all();

        $this->assertNotEmpty($internships);
    
        foreach ($internships as $internship){
            $this->assertArrayHasKey("id", $internship);
            $this->assertNotEmpty($internship["id"]);
            $this->assertArrayHasKey("title", $internship);
            $this->assertNotEmpty($internship["title"]);
            $this->assertArrayHasKey("status", $internship);
            $this->assertNotEmpty($internship["status"]);
            $this->assertArrayHasKey("department_name", $internship);
            $this->assertNotEmpty($internship["department_name"]);
            $this->assertArrayHasKey("company_name", $internship);
            $this->assertNotEmpty($internship["company_name"]);
        } 
        
    }

    public function testIfReturnsRealInternships(){
        $internships = $this->dao->get_all();
            $example_internship = [
                "id" => 20,
                "company_id" => 8,
                "title" => "PHP backend developer",
                "department_id" => 1,
                "status" => "APPROVED",
                "company_name" => "TribeOS",
                "department_name" => "IT",
                "intern_status" => "PENDING"
            ];
            $this->assertEquals($example_internship["id"], $internships[0]["id"]);
            $this->assertEquals($example_internship["company_id"], $internships[0]["company_id"]);
            $this->assertEquals($example_internship["title"], $internships[0]["title"]);
            $this->assertEquals($example_internship["department_id"], $internships[0]["department_id"]);
            $this->assertEquals($example_internship["status"], $internships[0]["status"]);
            $this->assertEquals($example_internship["company_name"], $internships[0]["company_name"]);
            $this->assertEquals($example_internship["department_name"], $internships[0]["department_name"]);
            $this->assertEquals($example_internship["intern_status"], $internships[0]["intern_status"]);
        }

    public function testIfReturnsRightIntenship(){
        $internship = $this->dao->get_by_id(20);
        $example_internship = [
            "id" => 20,
            "company_id" => 8,
            "title" => "PHP backend developer",
            "status" => "APPROVED",
            "department_name" => "IT",
        ];
        $this->assertEquals($example_internship["id"], $internship["id"]);
        $this->assertEquals($example_internship["company_id"], $internship["company_id"]);
        $this->assertEquals($example_internship["title"], $internship["title"]);
        $this->assertEquals($example_internship["status"], $internship["status"]);
        $this->assertEquals($example_internship["department_name"], $internship["department_name"]);
    }

    public function testUpdateInternship(){
        try{
            $example_internship = [
                "id" => 42,
                "company_id" => 8,
                "title" => "PHP backend developer",
                "status" => "PENDING",
            ];
            
            $this->dao->update($example_internship);
            $this->assertTrue(true);

        }catch(Exception $e){
            $this->fail();
        }

    }



    public function tearDown() {
        $this->dao = NULL;
    }
}