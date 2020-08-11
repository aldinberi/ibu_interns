<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__."/../../app/utils/ModelValidator.php";

require_once 'app/dao/UserDao.php';

/**
 * Sample test class which showcases some basic aspects of PHPUnit.
 * This test file can be removed, or edited to suit your project needs.
 */
class UserTest extends TestCase {
    private $dao;

    public function setUp() {
        $this->dao = new UserDao();
    }


    // public function testUserInsert() {
    //     try{
    //         $example_user = [
    //             "name" => "Amer Hašak",
    //             "password" => "1234",
    //             "email" => "amerresa@gmail.com",
    //             "type" => "COMPANY_USER",
    //         ];
            
    //         $this->dao->insert($example_user);
    //         $this->assertTrue(true);

    //     }catch(Exception $e){
    //         $this->fail();
    //     }

    // }

    public function testBasicUserInfo(){
        $users = $this->dao->get_all();
        $this->assertNotEmpty($users);
    
        foreach ($users as $user){
            $this->assertArrayHasKey("id", $user);
            $this->assertNotEmpty($user["id"]);
            $this->assertArrayHasKey("name", $user);
            $this->assertNotEmpty($user["name"]);
            $this->assertArrayHasKey("email", $user);
            $this->assertNotEmpty($user["email"]);
            $this->assertArrayHasKey("type", $user);
            $this->assertNotEmpty($user["type"]);
        } 
        
    }

    public function testIfReturnsRealCustomers(){
        $users = $this->dao->get_all();
            $example_user = [
                "name" => "aldin",
                "year" => "Third",
                "email" => "saka@outlook.com",
                "type" => "INTERN",
            ];
            $this->assertEquals($example_user["name"], $users[0]["name"]);
            $this->assertEquals($example_user["year"], $users[0]["year"]);
            $this->assertEquals($example_user["email"], $users[0]["email"]);
            $this->assertEquals($example_user["type"], $users[0]["type"]);
        }

    public function testIfReturnsRightUser(){
        $user = $this->dao->get_by_id(1);
        $example_user = [
            "id" => 1,
            "name" => "aldin",
            "year" => "Third",
            "email" => "saka@outlook.com",
            "type" => "INTERN",
        ];
        $this->assertEquals($example_user["id"], $user["id"]);
        $this->assertEquals($example_user["name"], $user["name"]);
        $this->assertEquals($example_user["email"], $user["email"]);
        $this->assertEquals($example_user["type"], $user["type"]);
    }

    public function testUpdateUser(){
        try{
            $example_user = [
                "id" => 23,
                "name" => "Admir Kekić",
                "year" => "Third",
                "email" => "saka@outlook.com",
                "type" => "INTERN",
            ];
            
            $this->dao->update($example_user);
            $this->assertTrue(true);

        }catch(Exception $e){
            $this->fail();
        }

    }



    public function tearDown() {
        $this->dao = NULL;
    }
}