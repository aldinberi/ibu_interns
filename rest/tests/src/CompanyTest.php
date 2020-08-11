<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__."/../../app/utils/ModelValidator.php";

require_once 'app/dao/CompanyDao.php';

/**
 * Sample test class which showcases some basic aspects of PHPUnit.
 * This test file can be removed, or edited to suit your project needs.
 */
class CompanyTest extends TestCase {
    private $dao;

    public function setUp() {
        $this->dao = new CompanyDao();
    }


    // public function testCompanyInsert() {
    //     try{
    //         $example_company = [
    //             "name" => "Aldin Inc",
    //             "address" => "Brcko",
    //             "email" => "aldin_kovacevic@aldinsolutions.com",
    //             "phone" => "3495892995932",
    //             "website" => "www.aldinsolutions.com",
    //             "status" => "APPROVED"
    //         ];
            
    //         $this->dao->insert($example_company);
    //         $this->assertTrue(true);

    //     }catch(Exception $e){
    //         $this->fail();
    //     }

    // }

    public function testBasicCompanyInfo(){
        $companies = $this->dao->get_all();
        $this->assertNotEmpty($companies);

        foreach ($companies as $company){
            $this->assertArrayHasKey("id", $company);
            $this->assertNotEmpty($company["id"]);
            $this->assertArrayHasKey("name", $company);
            $this->assertNotEmpty($company["name"]);
            $this->assertArrayHasKey("status", $company);
            $this->assertNotEmpty($company["status"]);
        } 
        
    }

    public function testIfReturnsRealCompanies(){
        $companies = $this->dao->get_all();
            $example_company = [
                "id" => 2,
                "name" => "Burch",
                "status" => "PENDING"
            ];
            $this->assertEquals($example_company["id"], $companies[0]["id"]);
            $this->assertEquals($example_company["name"], $companies[0]["name"]);
            $this->assertEquals($example_company["status"], $companies[0]["status"]);
        }

    public function testIfReturnsRightCompany(){
        $company = $this->dao->get_by_id(8);
        $example_company = [
            "id" => 8,
            "name" => "TribeOS",
            "address" => "Francuske revolucije bb",
            "email" => "tribeos@gmail.com",
            "phone" => "033234567",
            "website" => "www.tribeos.com",
            "status" => "APPROVED",
            "contact"=> "Dino K",
            "contact_email" => "dino.keco@gmail.com"
         ];
         $this->assertEquals($example_company, $company);
    }

    public function testUpdateCompany(){
        try{
            $example_company = [
                "id" => 42,
                "name" => "Aldin Inc",
                "address" => "Brcko",
                "email" => "aldin_kovacevic@aldinsolutions.com",
                "phone" => "3495892995932",
                "website" => "www.aldinsolutions.com",
                "status" => "APPROVED"
            ];
            
            $this->dao->update($example_company);
            $this->assertTrue(true);

        }catch(Exception $e){
            $this->fail();
        }

    }



    public function tearDown() {
        $this->dao = NULL;
    }
}