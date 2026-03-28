<?php

require_once 'utils/dbutils.php';
require_once 'utils/utils.php';
require_once 'User.php';


class Auth{

	public function createUser($params){
		//check if user exists
		$user = new User();
		if($user->checkIfUserExists($params)){
			return $this->userExistsMsg();
		}
		$user->createUser($params);
		return $this->userCreatedMsg();
	}	

	public function logout($params){

	}

	public function verifyUserPhone($params){
		$otp = $params['otp'];
		$phone = $params['phone'];
		//check in otps table if they match
		$query = "select otp from phone_otps where phone=:phone";
		$pdo = DBUtils::getConn();
		$st = $pdo->prepare($query);
		$st->execute(array(':phone' => $phone));

		$result = $st->fetch(PDO::FETCH_ASSOC);
		if($result['otp'] == $otp)
		{
			$this->markPhoneVerified($params);
			$user = new User();
			$user->getUser($params);
			$uuid = $user->fields['uuid'];
			//Issue a JWT token
			$token  = $this->issueJWTToken($uuid);
			return $this->otpSuccess($token);
			
		}
		return $this->otpFailed();
	}
	public function markPhoneVerified($params){
		$user = new User();
		$user->mark_phone_valid($params);
	}

	public function issueJWTToken($uuid){
		$token = Utils::encodeJWT(array('id' => $uuid));
		return $token;
	}
	public function userExistsMsg(){
		$msg = array('status' => 'failed' , 'reason' => 'Account exists');
		return $msg;
	}
	public function userCreatedMsg(){
		$msg = array('status' => 'success' , 'reason' => '');
		return $msg;
	}
	public function otpSuccess($token){
		$msg = array('status' => 'success' , 'reason' => '' , 'token' => $token);
		return $msg;
	}
	public function otpFailed(){
		$msg = array('status' => 'failed' , 'reason' => 'OTP verifcation failed.Try Again.');
		return json_encode($msg);
	}

	public function createOTP($params){
		$phone = $params['phone'];
		$otp = $params['otp'];
		$pdo = DBUtils::getConn();
                $query = "insert into phone_otps (phone,otp ) values (:value , :otp) ON DUPLICATE KEY UPDATE  otp=:otp1";
                $st = $pdo->prepare($query);
                $st->execute(array(':value' => $phone , ':otp' => $otp, ':otp1' => $otp));
	}

	public function sendOTP($params){
		$otp = rand(pow(10, 3), pow(10, 4)-1);
		$params['otp'] = $otp;
		$phone = $params['phone'];
		$this->createOTP($params);
		$msg =  ' is the OTP for Fintra';
		$msg = $otp . $msg;
		$cmd = "aws sns publish --region ap-southeast-1 --message '$msg' --phone-number $phone > /dev/null 2>/dev/null &";
		exec($cmd);
	}
}
/*
$u = new Auth();
$d['phone'] = '+919167071530';
$d['email'] = 'rahul1iitkgp@gmail.com';
$d['name'] ='rahul';

$u->sendOTP($d);

*/
?>
