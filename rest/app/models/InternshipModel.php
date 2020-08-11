<?php
/**
 * @OA\Schema(
 * )
 */
class InternshipInsert {
    /**
     * @OA\Property(
     * description="Title while doing the internship",
     * default = "Title",
     * required=true
     * )
     * @var string
     */
    public $title;
     /**
     * @OA\Property(
     * description="Job description of internship",
     * default = "Job description",
     * required=true
     * )
     * @var string
     */
    public $job_description;
     /**
     * @OA\Property(
     * description="The staring date of the internship",
     * default = "Start date",
     * required=true
     * )
     * @var string
     */
    public $start_date;
     /**
     * @OA\Property(
     * description="The ending date of internship",
     * default = "End date",
     * required = true
     * 
     * )
     * @var string
     */
    public $end_date;
}