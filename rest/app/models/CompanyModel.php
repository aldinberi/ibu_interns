<?php
/**
 * @OA\Schema(
 * )
 */
class CompanyInsert {
    /**
     * @OA\Property(
     * description="Name of company",
     * default = "Name",
     * required=true
     * )
     * @var string
     */
    public $name;
     /**
     * @OA\Property(
     * description="Address of company",
     * default = "Address",
     * required=true
     * )
     * @var string
     */
    public $address;
     /**
     * @OA\Property(
     * description="Email of company",
     * default = "Email",
     * required=true
     * )
     * @var string
     */
    public $email;
     /**
     * @OA\Property(
     * description="Phone of company",
     * default = "Phone",
     * required=true
     * )
     * @var string
     */
    public $phone;
     /**
     * @OA\Property(
     * description="Website of company",
     * default = "Website",
     * required=true
     * )
     * @var string
     */
    public $website;
}

/**
 * @OA\Schema(
 * )
 */
class CompanyUpdate {
    /**
     * @OA\Property(
     * description="Name of company",
     * default = "Name"
     * )
     * @var string
     */
    public $name;
     /**
     * @OA\Property(
     * description="Address of company",
     * default = "Address"
     * )
     * @var string
     */
    public $address;
     /**
     * @OA\Property(
     * description="Email of company",
     * default = "Email",
     * )
     * @var string
     */
    public $email;
     /**
     * @OA\Property(
     * description="Phone of company",
     * default = "Phone",
     * )
     * @var string
     */
    public $phone;
     /**
     * @OA\Property(
     * description="Website of company",
     * default = "Website",
     * )
     * @var string
     */
    public $website;
}