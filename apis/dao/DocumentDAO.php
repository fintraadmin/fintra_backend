<?php
// dao/DocumentDAO.php

class DocumentDAO {
    private $db;
    private $s3Util;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
       // $this->s3Util = new S3Utility();
    }
    
    public function saveDocument($leadId, $documentType, $file, $filename) {
        try {
            // Upload to S3 first
            //$s3Path = $this->s3Util->uploadFile($file, $leadId, $documentType);
            
           // if (!$s3Path) {
           //     throw new Exception("Failed to upload file to S3");
           // }
            
            // Save document record
            $sql = "INSERT INTO document_uploads (
                        lead_id, document_type, s3_path, file_name
                    ) VALUES (
                        :lead_id, :document_type, :s3_path, :file_name
                    )";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':lead_id' => $leadId,
                ':document_type' => $documentType,
                ':s3_path' => $s3Path,
		':file_name' => $filename
            ]);
            
            return $result ? $this->db->lastInsertId() : false;
        } catch (PDOException $e) {
            error_log("Save Document Error: " . $e->getMessage());
            throw new Exception("Failed to save document record");
        }
    }
    
    public function getDocuments($leadId) {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM document_uploads WHERE lead_id = ?"
            );
            $stmt->execute([$leadId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Documents Error: " . $e->getMessage());
            throw new Exception("Failed to retrieve documents");
        }
    }
    
    public function isDocumentUploaded($leadId, $documentType) {
        try {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM document_uploads 
                 WHERE lead_id = ? AND document_type = ?"
            );
            $stmt->execute([$leadId, $documentType]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Check Document Error: " . $e->getMessage());
            throw new Exception("Failed to check document status");
        }
    }
    
    public function getAllRequiredDocuments($leadId) {
        $required = ['pan', 'aadhaar', 'salary', 'bank', 'address'];
        $uploaded = $this->getDocuments($leadId);
        $status = [];
        foreach ($required as $doc) {
            $status[$doc] = [
                'type' => $doc,
                'uploaded' => false,
                'path' => null
            ];
        }
        
        foreach ($uploaded as $doc) {
            if (isset($status[$doc['document_type']])) {
                $status[$doc['document_type']]['uploaded'] = true;
                $status[$doc['document_type']]['path'] = $doc['s3_path'];
            }
        }
        
        return $status;
    }
}
