<?php
// dao/LoanApplicationDAO.php
require_once 'apis/dao/Database.php';

class LoanApplicationDAO {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function createApplication($data) {
        try {
            $sql = "INSERT INTO loan_applications (
                        lead_id, name, email, mobile, income_source, 
                        income, pincode, dob, pan, aadhaar,credit_score, 
                        loan_amount,publisher_id, merchant_id,application_id,status
                    ) VALUES (
                        :lead_id, :name, :email, :mobile, :income_source,
                        :income, :pincode, :dob, :pan, :aadhaar, :credit_score,
                        :loan_amount, :cid , :aid, :application_id, 'new'
                    )";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':lead_id' => 'LOAN' . time() . rand(1000, 9999),
                ':name' => $data['name'],
                ':email' => $data['email'],
                ':mobile' => $data['mobile'],
                ':income_source' => $data['income_source'],
                ':income' => $data['income'],
                ':pincode' => $data['pincode'],
                ':dob' => $data['dob'],
                ':pan' => $data['pan'],
                ':aadhaar' => $data['aadhaar'],
                ':credit_score' => $data['credit_score'],
                ':loan_amount' => $data['loan_amount'],
                ':cid' => $data['cid'],
                ':application_id' => $data['application_id'],
                ':aid' => $data['aid']
            ]);
            
            $last_id =  $this->db->lastInsertId();
	    $loan_app = $this->getApplicationbyID($last_id);
	    return $loan_app['lead_id'];

        } catch (PDOException $e) {
            error_log("Create Application Error: " . $e->getMessage());
            throw new Exception("Failed to create application");
        }
    }
    public function getApplicationbyID($Id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM loan_applications WHERE id = ?");
            $stmt->execute([$Id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Application Error: " . $e->getMessage());
            throw new Exception("Failed to retrieve application");
        }
    }
    
    public function getApplication($leadId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM loan_applications WHERE lead_id = ?");
            $stmt->execute([$leadId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Application Error: " . $e->getMessage());
            throw new Exception("Failed to retrieve application");
        }
    }
    
    public function updateStatus($leadId, $status) {
        try {
	    //update doc_upload status=1
	   
            $stmt1 = $this->db->prepare(
                "UPDATE loan_applications SET doc_uploaded = ? WHERE lead_id = ?"
            );
            $stmt1->execute([1, $leadId]);
            $stmt = $this->db->prepare(
                "UPDATE loan_applications SET status = ? WHERE lead_id = ?"
            );
            return $stmt->execute([$status, $leadId]);
        } catch (PDOException $e) {
            error_log("Update Status Error: " . $e->getMessage());
            throw new Exception("Failed to update application status");
        }
    }

	public function updateStatusbyApp($status , $remark , $application_id) {
        try {
            //update doc_upload status=1

            $stmt1 = $this->db->prepare(
                "UPDATE loan_applications SET status = ? , remark= ? WHERE application_id = ?"
            );
             $stmt1->execute([$status, $remark, $application_id]);
        } catch (PDOException $e) {
            error_log("Update Status Error: " . $e->getMessage());
            throw new Exception("Failed to update application status");
        }
    }

     public function updateCreditScore($score,  $lead_id) {
        try {
            //update doc_upload status=1

            $stmt1 = $this->db->prepare(
                "UPDATE loan_applications SET credit_score = ?  WHERE lead_id = ?"
            );
             $stmt1->execute([$score, $lead_id]);
        } catch (PDOException $e) {
            error_log("Update Status Error: " . $e->getMessage());
            throw new Exception("Failed to update application status");
        }
    }

   public function getApplications($aid, $cid) {
        try {
            
            // Get applications
            $sql = "SELECT 
                        lead_id,
                        name,
                        email,
                        loan_amount,
                        status,
                        created_at,
                        updated_at
                    FROM loan_applications 
                    WHERE merchant_id = ? and publisher_id= ?
                    ORDER BY created_at DESC";
                    
            $params[] = $limit;
            $params[] = $offset;
            $params = array();
	    $params[] =$aid; 
	    $params[] =$cid; 
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return [
                'applications' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            ];
        } catch (PDOException $e) {
            error_log("Error in getApplications: " . $e->getMessage());
            throw new Exception("Failed to fetch applications");
        }
    }

	public function listApplications($cid, $page=1) {
        try {

            // Get applications
            $sql = "SELECT * 
                    FROM loan_applications 
                    WHERE publisher_id= ?
                    ORDER BY created_at DESC ";

            $params[] = $limit;
            $params[] = $offset;
            $params = array();
            $params[] =$cid;
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return [
                'applications' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            ];
        } catch (PDOException $e) {
            error_log("Error in getApplications: " . $e->getMessage());
            throw new Exception("Failed to fetch applications");
        }
    }

}
