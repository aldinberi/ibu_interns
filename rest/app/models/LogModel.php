<?php

/**
 * @OA\Schema(
 * )
 */
class LogInsert
{
    /**
     * @OA\Property(
     * description="Wokr done during the working day",
     * default = "work done",
     * required=true
     * )
     * @var string
     */
    public $work_done;
    /**
     * @OA\Property(
     * description="Date of the working happend",
     * default = "Date of log",
     * required=true
     * )
     * @var string
     */
    public $date;
    /**
     * @OA\Property(
     * description="The id the internship",
     * default = 1,
     * required=true
     * )
     * @var int
     */
    public $time;
    /**
     * @OA\Property(
     * description="Duration of work for the work",
     * default = "00:00",
     * required=true
     * )
     * @var string
     */
    public $internship_id;
    /**
     * @OA\Property(
     * description="The id of the intern",
     * default = 1,
     * required = true
     * 
     * )
     * @var int
     */
    public $intern_id;
}
