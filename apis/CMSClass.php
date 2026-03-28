<?php
#set_include_path('/var/www/html/');
require_once 'utils/dbutils.php';

class CMSClass{
	private $table = 'findipay_leads';

	public function __construct() {
    		$this->conn = DBUtils::getConn('fintracms') ;
	}


	public function createRecord($data){
		$table = $this->table;
		$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  		$sql = "INSERT INTO $table (name, agentid, publisher, productid,  email, phone, pincode, created, modified)
  			VALUES (:name , :agentid ,:publisher , :productid,  :email , :phone , :pincode, :created, :modified)";

		$stmt = $this->conn->prepare($sql);

		$name = $data['customer']['name'];
		$agentid = $data['agentId'];
		$publisher = $data['publisher'];
		$productid = $data['productid'];
		$email = $data['customer']['emailId'];
		$phone = $data['customer']['mobileNo'];
		$pincode = $data['customer']['pincode'];
		$created =  date("Y-m-d H:i:s");
		$modified = date("Y-m-d H:i:s");


		$stmt->bindParam(':name', $name);
		$stmt->bindParam(':agentid', $agentid);
		$stmt->bindParam(':publisher', $publisher);
		$stmt->bindParam(':productid', $productid);
		$stmt->bindParam(':email', $email);
		$stmt->bindParam(':phone', $phone);
		$stmt->bindParam(':pincode', $pincode);
		$stmt->bindParam(':created', $created);
		$stmt->bindParam(':modified', $modified);


		$stmt->execute();
		$id = $this->conn->lastInsertId();
		return array('clickid' => $id , 'created'=> $created );
	}


	public function updateRecord($data){

		return;
	}

	public function testcreate(){
		$data= array();

		$data['name'] = 'rahul Saxena';
		$data['agentid']= 'testagent';
		$data['productid']  = 'cc-1';
		$data['email'] = 'email@email.com';
		$data['phone'] = '916775566';
		$data['pincode'] = '560066';
		$id = $this->createRecord($data);
		echo "hi $id";
		
	}
}

	#$c = new CMSClass();
	#$c->testcreate();
?>
