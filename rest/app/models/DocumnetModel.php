<?php
/**
 * @OA\Schema(
 * )
 */
class DocumentUpload {
    /**
     * @OA\Property(
     * description="The document to be upload in base64",
     * default = "Document in base64",
     * required=true
     * )
     * @var string
     */
    public $document;
     /**
     * @OA\Property(
     * description="Id of the user inserting the document",
     * default = "1",
     * required=true
     * )
     * @var int
     */
    public $intern_id;
     /**
     * @OA\Property(
     * description="The name of the document",
     * default = "Motivation_letter.pdf",
     * required=true
     * )
     * @var string
     */
    public $document_name;
     /**
     * @OA\Property(
     * description="Type of the document",
     * default = "CV",
     * required=true
     * )
     * @var string
     */
    public $type;
}