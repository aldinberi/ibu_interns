<?php
/**
 * @OA\Schema(
 * )
 */
class UserInsert {
    /**
     * @OA\Property(
     * description="Name of user",
     * default = "Name",
     * required=true
     * )
     * @var string
     */
    public $name;
     /**
     * @OA\Property(
     * description="Email of user",
     * default = "Email",
     * required=true
     * )
     * @var string
     */
    public $email;
     /**
     * @OA\Property(
     * description="Password of user",
     * default = "Email",
     * required=true
     * )
     * @var string
     */
    public $password;
     /**
     * @OA\Property(
     * description="Type of user",
     * default = "INTERN",
     * 
     * )
     * @var string
     */
    public $type;
}

/**
 * @OA\Schema(
 * )
 */
class UserPasswordUpdate {
    /**
     * @OA\Property(
     * description="Old password of account",
     * default = "string",
     * required=true
     * )
     * @var string
     */
    public $old_password;
     /**
     * @OA\Property(
     * description="New password of the user",
     * default = "string",
     * required=true
     * )
     * @var string
     */
    public $new_password;
}

/**
 * @OA\Schema(
 * )
 */
class InternApply {
    /**
     * @OA\Property(
     * description="Id of the internship",
     * default = 20,
     * required=true
     * )
     * @var int
     */
    public $internship_id;
     /**
     * @OA\Property(
     * description="Id of the intern",
     * default = 1,
     * required=true
     * )
     * @var int
     */
    public $intern_id;
         /**
     * @OA\Property(
     * description="Status of the internship",
     * default = "PENDING",
     * required=true
     * )
     * @var string
     */
    public $status;
}

/**
 * @OA\Schema(
 * )
 */
class UserSendEmail {
    /**
    * @OA\Property(
    * description="Email of user",
    * default = "Email",
    * required=true
    * )
    * @var string
    */
   public $email;
}