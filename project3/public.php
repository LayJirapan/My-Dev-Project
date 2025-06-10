<?php
/*
 * Mavell API
 *
 * @since 1.0.0
 * @access public
 *
 */

require_once 'vendor/autoload.php';
require_once('config.main.php');
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
require_once('function.php');


// This interface require authentication inside "some" actions
if(!isset($_REQUEST['a'])) echoJson(8000, 'No User Action Request');
// else if(!isset($_REQUEST['api_token']) || $_REQUEST['api_token'] == '') echoJson(8001, 'No User API Token');

// Check api token
// $user = $db->where('api_token', trim($_REQUEST['api_token']))->getOne('login_authentication', 'id,amr_id');
// if(!isset($user['id']) || $user['id'] == 0) echoJson(8002, 'API Token Invalid');

// Check the action request
$action = trim($_REQUEST['a']);

$app_uuid = '';
if(isset($_COOKIE['uuid']) && $_COOKIE['uuid'] != '') $app_uuid = $_COOKIE['uuid'];
else if(isset($_POST['app_uuid']) && $_POST['app_uuid'] != '') $app_uuid = $_POST['app_uuid'];

$app_token = '';
if(isset($_COOKIE['app_token']) && $_COOKIE['app_token'] != '') $app_token = $_COOKIE['app_token'];
else if(isset($_POST['app_token']) && $_POST['app_token'] != '') $app_token = $_POST['app_token'];

switch ($action) {

	/**
	 *	Product Register
	 *	Init 02/04/2021
	 */
	case 's_product_register':
		if(!isset($_POST['product_type']) || $_POST['product_model'] == '') echoJson(2, 'product type required');
		if(!isset($_POST['product_model']) || $_POST['product_model'] == '') echoJson(3, 'product model required');
		if(@$_POST['indoor_sn'] == '' && @$_POST['outdoor_sn'] == '') echoJson(4, 'Indoor หรือ Outdoor S/N required');
		if(!isset($_POST['customer_name']) || $_POST['customer_name'] == '') echoJson(6, 'Customer name required');
		if(!isset($_POST['customer_phone']) || $_POST['customer_phone'] == '') echoJson(7, 'Customer phone required');
		// if(!isset($_POST['customer_email']) || $_POST['customer_email'] == '') echoJson(8, 'Customer email required');
		if(!isset($_POST['date_purchased']) || $_POST['date_purchased'] == '') echoJson(9, 'Purchase date required');
		if(!isset($_POST['user_accept_1']) || $_POST['user_accept_1'] == '') echoJson(9, 'user accpetance 1 required');
		// if(!recatchaVerify()) echoJson(3, 'Recatcha ตรวจสอบว่าคุณเป็น Robot?');
		dbCon();
		// chkProfilePermit('policy.html', 'Y');
		$data = $_POST;
		$data['indoor_sn'] = clean($data['indoor_sn']);
		$data['outdoor_sn'] = clean($data['outdoor_sn']);
		$data['customer_phone'] = clean($data['customer_phone']);
		if(isset($_POST['seller_phone'])) $data['seller_phone'] = clean($data['seller_phone']);
		if(isset($_POST['technician_phone'])) $data['technician_phone'] = clean($data['technician_phone']);

		// Match Indoor S/N
		// $sn = matchSerailID($data['indoor_sn'],$data['outdoor_sn']); // NOT USED FOREVER ****
		// Match Indoor S/N
		$db->where('UPPER(`serial_no`) LIKE UPPER(\''.$data['indoor_sn'].'\')')->orderBy('updated_at','DESC');
		$sn = $db->getOne('product_serial','serial_id,product_id');
		$pm = []; $sn_id2 = 0;

		if(isset($sn['serial_id']) && $sn['serial_id'] > 0) {
			$data['serial_id'] = $sn['serial_id'];
			$data['product_id'] = @$sn['product_id'];
			if(@$sn['product_id'] > 0){
				$pm = $db->where('product_id',$sn['product_id'])->getOne('product_model', 'product_type,product_code,reward_pts,model_indoor');
				$data['product_type'] = $pm['product_type'];
				$data['product_model'] = $pm['product_code'];
			}
			$data['status'] = 1; // FOUND S/N, Set to already checked!
			$txt = '<h3><div style="color:green">รายการการลงทะเบียนของท่านสมบูรณ์แล้ว</div></h3> ขอบพระคุณ';

			// Match Outdoor S/N
			$db->where('UPPER(`serial_no`) LIKE UPPER(\''.$data['outdoor_sn'].'\')')->orderBy('updated_at','DESC');
			$sn_id2 = $db->getValue('product_serial','serial_id');
			if($sn_id2 > 0) $data['serial_id2'] = $sn_id2;

		}else{
			$data['status'] = 0;
			$txt = '<h3>ระบบได้รับเรื่องขอรับบริการแล้ว</h3> ภายหลังเจ้าหน้าที่ตรวจสอบข้อมูลแล้วเพื่อความถูกต้องแล้ว จะมีอีเมล์แจ้งกลับสถานะของการลงทะเบียนโดยเร็วที่สุด';
		}


		// Get User Id
		if(isset($data['customer_phone']) && $data['customer_phone'] != ''){
			$db->where('phone', trim($data['customer_phone']), 'like')
				->where('phone_validated', 1);
			$uid = $db->getValue('users','id');
			if($uid > 0) $data['user_id'] = $uid;
		} // end if

		// Check Already registered **
		if($sn_id2 > 0) $db->where('(serial_id = '.$data['serial_id'] .' OR serial_id2 = '.$sn_id2.')');
		else $db->where('serial_id', $data['serial_id']);
		$id = $db->where('status', 1)->getValue('product_register','register_id');
		if($id > 0) echoJson(8, 'รหัสผลิตภัณฑ์​ (S/N) ได้ถูกใช้ลงทะเบียนของไปแล้ว');

		$data['date_purchased'] = (isset($_POST['date_purchased']) && $_POST['date_purchased']!='') ? DateTime::createFromFormat('d/m/Y', $_POST['date_purchased'])->format('Y-m-d'):'';
		$data['date_installed'] = (isset($_POST['date_installed']) && $_POST['date_installed']!='') ? DateTime::createFromFormat('d/m/Y', $_POST['date_installed'])->format('Y-m-d'):'';
		unset($data['id'],$data['user_accept_1'],$data['product_type_txt'],$data['product_model_txt'],$data['token']);
		$data['updated_at'] = date('Y-m-d H:i:s');
		$data['created_at'] = date('Y-m-d H:i:s');
		$id = $db->insert('product_register', $data);
		// echo $db->getLastQuery().'<br/>'; // DEBUG
		$pk = 'RG'.fillZero($id,8);

		// Succeed Register into DB
		if($id > 0){
			if(isset($data['customer_email']) && validateEmail($data['customer_email'])){
				// print_r($data);
				mailWarranty($pk, $data, $txt);
			}
			// to all admin
			$admins = $db->where('roles_mask', '2')->where('status', '1')->get('users', 100, 'id');
			// print_r($admins);
			$txt = 'มีรายการบันทึกการรับประกันสินค้าใหม่ #'.$pk;
			foreach ($admins as $key => $v) setNoti($v['id'], $id, 1, $txt);
		}

		// IF OTP Succeed
		if($id > 0 && @$_SESSION['ssSuccessOTP'] == 1){
			///////////////////////////////////// REWARD //////////////////////////////////////
			$rwid = 0;
			if($data['status'] == 1 && isset($data['technician_phone']) && $data['technician_phone'] != ''
				&& isset($sn['serial_id']) && $sn['serial_id'] > 0 && $pm['reward_pts'] > 0){
				// Already reward **
				$rwid = $db->where('r.serial_id', $sn['serial_id'])
					->where('r.status NOT IN (2,3)') // ไม่ถูกต้อง, ยกเลิก
					->getValue('reward r','r.reward_id');


				// Technicain Profile
				$tech = $db->where('t.phone', $data['technician_phone'])
					->join('users u','t.user_id=u.id','left')
					->join('service_center s','s.sc_id=t.sc_id','left')
					->getOne('technician t','CONCAT_WS("",s.title," #",s.sc_code) as sc_title, u.username,t.sc_id,t.user_id,t.technician_code,t.type,t.firstname,t.lastname,t.phone,t.email,t.technician_id');
					// print_r($tech); exit;

				if($rwid == 0 && isset($tech['technician_id']) && $tech['technician_id'] > 0
					&& isset($tech['user_id']) && $tech['user_id'] > 0){

						$rwDat = [
							'user_id' => $tech['user_id'],
							'serial_id' => $sn['serial_id'],
							'register_id' => $id,
							'product_id' => $sn['product_id'],
							'product_model' => $pm['model_indoor'] ?? NULL,
							'product_sn' => $data['indoor_sn'] ?? NULL,
							'technician_id' => $tech['technician_id'],
							'created_at' => date('Y-m-d H:i:s'),
							'updated_at' => date('Y-m-d H:i:s'),
							'date_request' => date('Y-m-d'),
							'date_approve' => date('Y-m-d'), // approved by OTP
							'point_taken' => $pm['reward_pts'],
							'status' => 1, // approval
						];
						$rwid = $db->insert('reward', $rwDat);

						if($rwid > 0){
							$pk = 'RG'.fillZero($rwid,8);
							$_SESSION['ssSuccessOTP'] = 0;

							//  **** Point added for approval or not approval (backout)
							$log_id = rewardRecord($tech['user_id'], $rwDat['point_taken'],
								['reward_id' => $rwid, 'technician_id' => $tech['technician_id'],'approved' => true]);

							if($log_id > 0 && isset($tech['email']) && validateEmail($tech['email'])){
								$data['product_sn'] = $data['product_model'] ?? "";
								$data['email'] = $tech['email'];
								$data['firstname'] = $tech['firstname'];
								$data['lastname'] = $tech['lastname'];
								$data['phone'] = $tech['technician_phone'];
								$data['technician_code'] = $tech['technician_code'];
								$data['sc_title'] = $tech['sc_title'];
								$txt = 'ระบบตรวจสอบรายการสะสมคะแนน `รายการถูกต้อง`';

								mailRewardReq($pk, $data, '<h3>'.$txt.'</h3>');
								setNoti($tech['technician_id'], $rwid, 5, $txt);
							}
						}//end if reward saved
				}
			} /// end if, rewards
		} // end if OTP Succeed

		///////////////////////////////////// Store the dealer //////////////////////////////////////
		if((!isset($_POST['dealer_id']) || $_POST['dealer_id'] == 0) && isset($data['seller_name']) && $data['seller_name'] != ''){
			$dealerId = $db->where('name',$data['seller_name'],'like')->getValue('serivice_dealer', 'dealer_id');
			if($dealerId == 0) $dealerId = $db->insert('serivice_dealer', [
				'name' => $data['seller_name'],
				'phone' => $data['seller_phone'],
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s')
			]);
		}
		echoJson(0, ['id' => $id, 'pk' => $pk, 'sn' => $sn, 'dealer_id' => $dealerId, 'reward_id' => $rwid, 'rw_point_id' => $log_id]);
		break;

	/**
	 *	Service Request
	 *	Init 05/04/2021
	 */
	case 's_service_request':
		// if(!recatchaVerify()) echoJson(3, 'Recatcha ตรวจสอบว่าคุณเป็น Robot?');
		if(!isset($_POST['product_type']) || $_POST['product_type'] == '') echoJson(2, 'Product type required');
		if(!isset($_POST['product_model']) || $_POST['product_model'] == '') echoJson(3, 'Product model required');
		if(@$_POST['indoor_sn'] == '' && @$_POST['outdoor_sn'] == '') echoJson(4, 'Indoor หรือ Outdoor S/N required');
		if(!isset($_POST['requester_name']) || $_POST['requester_name'] == '') echoJson(6, 'Requester\'s name required');
		if(!isset($_POST['requester_phone']) || $_POST['requester_phone'] == '') echoJson(7, 'Requester\'s phone required');
		// if(!isset($_POST['requester_email']) || $_POST['requester_email'] == '') echoJson(8, 'Requester\'s email required');
		if(!isset($_POST['user_accept_']) || $_POST['user_accept_'] == '') echoJson(9, 'User accpetance required');
		dbCon();
		unset($_POST['room_size']);
        unset($_POST['tbl_volt_pre']);
        unset($_POST['tbl_amp_pre']);
        unset($_POST['tbl_term_remote_pre']);
        unset($_POST['tbl_psil_pre']);
        unset($_POST['tbl_fcu_out_pre']);
        unset($_POST['tbl_fcu_in_pre']);
        unset($_POST['tbl_cdu_out_pre']);
        unset($_POST['tbl_cdu_in_pre']);
        unset($_POST['pipe_length']);

		$data = $_POST;
		$data['indoor_sn'] = clean($data['indoor_sn']);
		$data['outdoor_sn'] = clean($data['outdoor_sn']);
		$data['requester_phone'] = clean($data['requester_phone']);

		// Match S/N
		emptyNum($data['serial_id']);
		emptyNum($data['product_id']);
		if(@$data['serial_id'] == 0 || @$data['serial_id'] == ''){
			unset($data['product_id'],$data['serial_id']);
			$sn = matchSerailID($data['indoor_sn'],$data['outdoor_sn']);
			if(isset($sn['serial_id']) && $sn['serial_id'] > 0) {
				// $data['serial_id'] = $sn['serial_id'];
				// if(isset($sn['product_id']) && $sn['product_id'] > 0) $data['product_id'] = $sn['product_id'];
			}
		}

		unset($data['id'],$data['user_accept_'],$data['product_type_txt'],$data['product_model_txt'],$data['token']);
		$data['updated_at'] = date('Y-m-d H:i:s');
		$data['created_at'] = date('Y-m-d H:i:s');
		if(isset($_POST['filename']) && is_array($_POST['filename'])) $data['filename'] = json_encode($data['filename']);
		$id = $db->insert('service_request', $data);
		// print_r($data);
		// echo $db->getLastQuery().'<br/>'; // DEBUG
		$pk = 'SR'.fillZero($id,8);

		if($id > 0){
			if(isset($data['requester_email']) &&  validateEmail($data['requester_email'])){
				// print_r($data);
				mailSerReq($pk, $data, '<h3>ระบบได้รับเรื่องขอรับบริการแล้ว</h3> ทางเจ้าหน้าที่จะติดต่อกลับท่านโดยเร็วที่สุด ขอบพระคุณ');
			}
			// to all admin
			$admins = $db->where('roles_mask', '2')->where('status', '1')->get('users', 100, 'id');
			$txt = 'มีรายการคำร้องขอบริการใหม่ #'.$pk;
			foreach ($admins as $key => $v) setNoti($v['id'], $id, 2, $txt);
		}

		echoJson(0, ['id' => $id, 'pk' => $pk, 'sn' => $sn]);
		break;

		//ส่งข้อมูล JSON ลง database

		case 'save_service_detail':
		global $db;
		dbCon();

		$service_id = $_POST['id'];
		$form = json_decode($_POST['detail'], true);

		if (!$form) {
			echo json_encode(['status' => 'error', 'msg' => 'JSON decode failed']);
			exit;
		}

		$detail_json = json_encode($form, JSON_UNESCAPED_UNICODE);
		$now = date('Y-m-d H:i:s');
		$data = [
			'detail' => $detail_json,
			'timeline_type' => 'appointed',
			'updated_at' => $now
		];

		try {
			$exists = $db->where('service_id', $service_id)->getValue('service_request_detail', 'COUNT(*)');

			if ($exists > 0) {
				// UPDATE
				$db->where('service_id', $service_id)->update('service_request_detail', $data);
				$action = 'update';
			} else {
				// INSERT
				$data['service_id'] = $service_id;
				$data['created_at'] = $now;
				$db->insert('service_request_detail', $data);
				$action = 'insert';
			}

			echo json_encode(['status' => 'ok', 'action' => $action]);

		} catch (Exception $e) {
			file_put_contents("error_log.txt", $e->getMessage() . "\n", FILE_APPEND); //เก็บ log
			echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
		}

		exit;
		break;




	/**
	 *	Check S/N
	 *	Init 04/04/2021
	 * 	- Accept Indoor and outdoor sn
	 */
	 case 'check_sn':
	 	if(!isset($_REQUEST['sn']) || $_REQUEST['sn'] == "" || strlen( $_REQUEST['sn']) < 4)
			echoJson(2, 'S/N Requried');
		dbCon();
		$db->orderBy('s.`updated_at`', 'DESC')->orderBy('r.`updated_at`', 'DESC')
		 	->where('s.serial_no LIKE "%'.trim($_REQUEST['sn']).'%"')
			->join('product_model p', 'p.product_id=s.product_id AND s.product_id IS NOT NULL','left')
		 	->join('product_register r', 'r.serial_id=s.serial_id AND r.serial_id IS NOT NULL','left');
		$arr = $db->getOne('product_serial s','s.serial_id,s.product_id,s.mfd,p.product_code,s.model_code_opt,p.model_name,p.product_type,r.date_installed,r.status,r.updated_at,r.technician_name,r.register_id');
			// .'r.indoor_sn,r.outdoor_sn,r.customer_title,r.customer_name,r.customer_phone,r.customer_email,r.customer_lineid,r.customer_org_name,,'
			// .'r.install_latlng,r.address_no,r.address_moo,r.address_building,r.address_road,r.address_subdistrict,r.address_district,r.address_province,r.address_postcode,'
			// .'r.seller_name,r.seller_phone,r.technician_phone,r.technician_name,r.technician_code,r.status,r.updated_at');
		// echo $db->getLastQuery().'<br/>'; // DEBUG
		// Blind Sensitive info
		if(isset($arr['customer_phone'])) $arr['customer_phone'] = substr($arr['customer_phone'],0,3)."-XXX-".substr($arr['customer_phone'],6,5);
		echoJson(0, ['info' => $arr]);
		break;

	/**
	 *	Check Product Registeredd Status
	 *	Init 28/05/2021
	 */
	 case 'check_rg_status':
	 	if(!isset($_POST['id']) || $_POST['id'] == "" || $_POST['id'] == 0)
			echoJson(2, 'Register ID Requried');
		if(!isset($_POST['token']) && !recatchaVerify($_POST['token'])) echoJson(3, 'Recatcha ตรวจสอบว่าคุณเป็น Robot?');
		dbCon();
		$db->where('r.register_id', $_REQUEST['id']);
		$status = $db->getValue('product_register r','r.status');
		echoJson(0, ['status' => $status]);
		break;

	/**
	 *	Check Service Request Status
	 *	Init 28/05/2021
	 */
	 case 'check_sr_status':
	 	if(!isset($_REQUEST['id']) || $_REQUEST['id'] == "" || $_REQUEST['id'] == 0)
			echoJson(2, 'Service ID Requried');
		if(!isset($_POST['token']) && !recatchaVerify($_POST['token'])) echoJson(3, 'Recatcha ตรวจสอบว่าคุณเป็น Robot?');
		dbCon();
		$db->where('r.service_id', $_REQUEST['id']);
		$status = $db->getValue('service_request r','r.status');
		echoJson(0, ['status' => $status]);
		break;

	/**
	 *	Get Product Model
	 *	Init 17/09/2021
	 */
	 case 'get_product_model':
	 	// if(!isset($_POST['token']) && !recatchaVerify($_POST['token'])) echoJson(3, 'Recatcha ตรวจสอบว่าคุณเป็น Robot?');
		dbCon();
		$db->join('product_type p', 'p.product_type_id=m.product_type', 'left');
		echoJson(0, ['list' => $db->get('product_model m',200,'m.product_type,p.product_type as product_type_txt, m.product_id,m.product_code,m.model_name')]);
		break;

	/**
	 *	Get Product Type
	 *	Init 14/11/2022
	 */
	 case 'get_product_type':
		dbCon();
		echoJson(0, ['list' => $db->get('product_type p',100,'p.*')]);
		break;

	/**
	 *	Upload Attachement to Service Request (Before submit)
	 *	Init 21/04/2021
	 */
	case 'serv_req_fileupload':
		// print_r($_FILES);
    if(!isset($_FILES) || !isset($_FILES['attach_photo'])) echoJson(2, 'ข้อมูลไม่ครบถ้วน');
    $key = genRandStr(4);
    $filename = @'at'.$key.'_'.hyphenize($_FILES['attach_photo']['name']);
		$save_file_path = ROOT_PATH.ARCHIVE_FILE.'public/'.$filename;
    move_uploaded_file($_FILES['attach_photo']['tmp_name'], $save_file_path);
    echoJson(0, ['file' => PUBLIC_DOMAIN.APP_PATH.ARCHIVE_DIR_NAME.'/public/'.$filename]);
    break;


	/**
	 *	S/N search (select2) (both Indoor and Outdoor)
	 *	Init 28/05/2021
	 */
	case 'sn_query':
		dbCon();
		if(!isset($_GET['q']) || $_GET['q'] == '') echoJson(2, 'Search keyword requried');
		$q = trim($_GET['q']);
		if(strlen($q) < 2) echoJson(0, []);
		$db->where('(s.serial_no LIKE "%'.$q.'%")');
			// ' OR s.indoor_sn LIKE "%'.$q.'%" OR r.outdoor_sn LIKE "%'.$q.'%"  OR r.register_id  = "'.(int) filter_var($q,FILTER_SANITIZE_NUMBER_INT).'" '.
		$db->orderBy('s.updated_at','desc')// ->groupBy('r.register_id')
			->join('product_register r', 'r.serial_id=s.serial_id','left'); //  AND r.status = 1
		$list = $db->get('product_serial s', 10,'s.product_id,s.serial_id,r.register_id, s.line, s.model_code_opt, r.register_id as id, IF(r.indoor_sn IS NULL, s.serial_no, r.indoor_sn) as indoor_sn, r.outdoor_sn');
		// echo $db->getLastQuery().'<br/>'; // DEBUG

		if(count($list) == 0) $list = [['register_id' => 0, 'indoor_sn' => $q, 'outdoor_sn' => $q, 'serial_no' => $q, 'id' => 0, 'model_code_opt' => 'ไม่พบข้อมูล']];
		echoJson(0, $list);
		break;

	/**
	 *	S/N search (select2) (both Indoor and Outdoor)
	 *	Init 28/11/2021
	 */
	case 'get_product_sn':
		dbCon();
		if(!isset($_POST['q']) || $_POST['q'] == '') echoJson(2, 'Search keyword requried');
		$q = trim($_POST['q']);
		if(strlen($q) < 4) echoJson(0, []);

		if(isset($_POST['line']) && $_POST['line'] != '') $db->where('UPPER(s.line)', strtoupper($_POST['line']));
		$result =  $db->where('(s.serial_no LIKE "'.$q.'")') // MUST EXACT THE SAME
			->orderBy('s.updated_at','desc')// ->groupBy('r.register_id')
			// ->join('product_register r', 'r.serial_id=s.serial_id','left') //  AND r.status = 1
			->join('product_register r', '(r.indoor_sn = s.serial_no OR r.outdoor_sn = s.serial_no)','left') //  AND r.status = 1
			->join('product_model m', 's.product_id=m.product_id','left') //  AND r.status = 1
			->getOne('product_serial s','s.product_id,s.serial_id,r.register_id, s.line, s.model_code_opt, r.register_id as id, IF(r.indoor_sn IS NULL, s.serial_no, r.indoor_sn) as indoor_sn, r.outdoor_sn, m.model_name, m.product_type');
		// $result['debug'] = $db->getLastQuery(); // DEBUG
		echoJson(0, $result);
		break;

	/**
	 *	Get Item for rewards (via Hi Mavll App)
	 *	Init 15/12/2021
	 */
	 case 'get_reward_items':
		// if(!isset($_POST['token']) && !recatchaVerify($_POST['token'])) echoJson(3, 'Recatcha ตรวจสอบว่าคุณเป็น Robot?');
		dbCon();
		if($app_uuid != ''){
			$pts = getPoints($app_uuid);
		}else{
			$pts =  ['balance' => 'N/A', 'expiring' => 'N/A', 'user_id' => 0];
		}

		$db->where('i.status',1);
		if(isset($_POST['sort_by']) && $_POST['sort_by'] != '') $db->orderBy('i.'.$_POST['sort_by'], 'asc');
		else $db->orderBy('i.point_require', 'asc');
		$list = $db->get('reward_item i',200,'i.rw_item_id,i.item_title, i.item_code, i.point_require, i.picture');
		echoJson(0, ['list' => $list, 'cookie' => $app_uuid , 'pts' => $pts]);
		break;

	/**
	 *	Get my addresses
	 *	Init 15/12/2021
	 */
	 case 'get_my_addr':
		if($app_uuid != ''){
			dbCon();
			$db->where('c.uuid', $app_uuid)
				->orderBy('c.updated_at','DESC')->orderBy('a.updated_at','desc')
				->where('c.status', 1)
				->where('a.for_customer', 0)
				->join('client_user c', 'c.user_id=a.user_id AND c.status =1');
			$list = $db->get('address a', 5, 'a.*');
			echoJson(0, ['list' => $list, 'uuid']);
		}else{
			echoJson(0, ['list' => [] ]);
		}
		break;

	/**
	 *	Get my address
	 *	Init 31/01/2022
	 */
	case 'get_my_address':
		if($app_uuid != ''){
			if(!isset($_POST['add_id']) || $_POST['add_id'] == '') echoJson(3, 'ไม่พบรหัสที่อยู่ในระบบ');
			dbCon();
			$db->where('c.uuid', $app_uuid)->where('a.add_id', $_POST['add_id'])
				->orderBy('c.updated_at','DESC')->orderBy('a.updated_at','desc')
				->where('c.status', 1)
				->join('client_user c', 'c.user_id=a.user_id AND c.status =1');
			$add = $db->getOne('address a', 'a.*');
			echoJson(0, ['address' => $add, 'uid' => $app_uuid]);
		}else{
			echoJson(0, ['address' => []]);
		}
		break;


	/**
	 *	Submit the reward's collect-point request
	 *	Init 15/12/2021
	 */
	case 's_reward_request':
		// if(
		// 	strlen($app_token) < 6 || strlen($app_uuid) < 6 )
		// 		echoJson(2, 'ไม่พบผู้ใช้งาน');
		if(!isset($_POST['product_id']) || $_POST['product_id'] == '') echoJson(3, 'ไม่พบรายการสินค้า');
		if(!isset($_POST['serial_id']) || $_POST['serial_id'] == '') echoJson(4, 'ไม่พบรหัสสินค้า (S/N)');
		if(!isset($_POST['product_model']) || $_POST['product_model'] == '') echoJson(5, 'ไม่พบรุ่นสินค้า');
		if(!isset($_POST['technician_phone']) || $_POST['technician_phone'] == '') echoJson(6, ',ไม่พบหมายเลขโทรศัพท์ช่างสะสมแต้ม');
		dbCon();

		// Already reward
		$exist_rw_id = $db->where('r.serial_id', $_POST['serial_id'])
			->where('r.status NOT IN (2,3)') // ไม่ถูกต้อง, ยกเลิก
			->getOne('reward r','r.reward_id');
		if($exist_rw_id > 0){
			echoJson(7, 'พบรายการสินค้าถูกแจ้งสะสมแต้มมาแล้ว');
		}

		$data = $_POST;
		$tech = $db->where('t.phone', trim($_POST['technician_phone']))
			->orderBy('t.updated_at', 'desc') // might have multiple users for same phone no.
			->join('users u', 'u.id=t.user_id AND u.roles_mask = 1','left')
			->getOne('technician t','t.*');
		if(!isset($tech['technician_id']) || $tech['technician_id'] == 0){
			echoJson(8, ',ไม่พบหมายเลขโทรศัพท์ช่างสะสมแต้ม');
		}else if(!isset($tech['user_id']) || $tech['user_id'] == 0){
			echoJson(9, ',ไม่พบข้อมูลผู้ใช้งานของช่างนี้');
		}

		$data['user_id'] = $tech['user_id'];
		$model = $db->where('product_id', $_POST['product_id'])->getOne('product_model','reward_pts');
		// $data['user_id'] = getUserIdByUUid($app_uuid);
		// $tech = ($data['user_id'] > 0) ? $db->where('t.user_id', $data['user_id'])
		// 	->join('service_center s','s.sc_id=t.sc_id','left')
		// 	->getOne('technician t','CONCAT_WS("",s.title," #",s.sc_code) as sc_title,t.sc_id,t.user_id,t.technician_code,t.type,t.firstname,t.lastname,t.phone,t.email,t.technician_id') : [];

		$data['technician_id'] = $tech['technician_id'];
		$data['created_at'] = date('Y-m-d H:i:s');
		$data['updated_at'] = date('Y-m-d H:i:s');
		$data['date_request'] = date('Y-m-d');
		$data['point_taken'] = @$model['reward_pts']; // Point will be added on approval!!!
		$data['status'] = 0; // Requested status
		unset($data['user_accept_2'],$data['user_accept'], $data['technician_phone']);
		$id = $db->insert('reward', $data);
		// print_r($data);
		// echo $db->getLastQuery().'<br/>'; // DEBUG
		$pk = 'RW'.fillZero($id,8);

		if($id > 0){
			if(isset($tech['email']) &&  validateEmail($tech['email'])){
				$data = array_merge($tech,$data);
				mailRewardReq($pk, $data, '<h3>ระบบได้รับเรื่องสะสมคะแนนของคุณแล้ว</h3> ทางเจ้าหน้าที่จะตรวจสอบโดยเร็วที่สุด ขอบพระคุณ');
			}
			// **  To all admin
			$admins = $db->where('roles_mask', '2')->where('status', '1')->get('users', 100, 'id');
			$txt = 'สะสมแต้มใหม่ #'.$pk;
			foreach ($admins as $key => $v) setNoti($v['id'], $id, 5, $txt); // 5 = สะสมคะแนน
		}
		echoJson(0, ['id' => $id, 'pk' => $pk]);
		break;

	/**
	 *	Submit the redeem request
	 *	Init 15/12/2021
	 */
	case 's_redeem':
		if(strlen($app_token) < 6 || strlen($app_uuid) < 6 )
				echoJson(2, 'ไม่พบผู้ใช้งาน');
		if(!isset($_POST['address_id']) || $_POST['address_id'] == '') echoJson(2, 'ไม่พบที่อยู่ในการจัดส่ง');
		if(!isset($_POST['rw_item_id']) || $_POST['rw_item_id'] == '') echoJson(3, 'ไม่พบรายการรางวัลที่เลือก');
		if(!isset($_POST['point_require']) || $_POST['point_require'] == '') echoJson(4, 'ไม่พบคะแนนที่ใช้แลก');
		dbCon();

		$pts = getPoints($app_uuid);

		// No point enough
		if($pts['balance'] <= 0) echoJson(5, 'ไม่พบคะแนนของคุณ');

		$data = [];
		$item = $db->where('rw_item_id', $_POST['rw_item_id'])->getOne('reward_item','*');

		if($pts['balance'] < $item['point_require']) echoJson(6, 'คะแนนของท่านไม่เพียงพอในการแลกของรางวัลนี้ '.$item['point_require'].'คะแนน<br/>(ปัจจุบันคุณมี '.$pts['balance'].' คะแนน )');

		$data['item_title'] = $item['item_title'];
		$data['item_code'] = $item['item_code'];
		$data['point_used'] = $item['point_require'];
		$data['rw_item_id'] = $item['rw_item_id'];
		$data['address_id'] = $_POST['address_id'];
		$data['status'] = 1; // 1 = Redeem succeed
		$data['qty'] = 1;
		$data['user_id'] = getUserIdByUUid($app_uuid);
		$tech = ($data['user_id'] > 0) ? $db->where('t.user_id', $data['user_id'])
			->join('service_center s','s.sc_id=t.sc_id','left')
			->getOne('technician t','CONCAT_WS("",s.title," #",s.sc_code) as sc_title,t.sc_id,t.user_id,t.technician_code,t.type,t.firstname,t.lastname,t.phone,t.email,t.technician_id') : [];

		$data['technician_id'] = $tech['technician_id'];
		$data['updated_at'] = date('Y-m-d H:i:s');
		$data['created_at'] = date('Y-m-d H:i:s');

		$id = $db->insert('reward_redeem', $data);
		// print_r($data);
		// echo $db->getLastQuery().'<br/>'; // DEBUG
		$pk = 'RD'.fillZero($id,8);
		$log_id = 0;

		if($id > 0){
			// Deduct point suddenly, if reject will be refunedded
			$log_id = rewardRecord($data['user_id'], $data['point_used'] * (-1),
				['redeem_id' => $id, 'technician_id' => $data['technician_id'], 'approved' => true]
			);

			// Send Email
			if($log_id > 0 && isset($tech['email']) && validateEmail($tech['email'])){
				$data = array_merge($tech,$data);
				// print_r($data);
				mailRewardRedeem($pk, $data, '<h3>ระบบได้รับเรื่องแลกรางวัลของคุณแล้ว</h3> ทางเจ้าหน้าที่จะตรวจสอบโดยเร็วที่สุด ขอบพระคุณ');
			}
			// **  To all admin
			if($log_id > 0){
				$admins = $db->where('roles_mask', '2')->where('status', '1')->get('users', 100, 'id');
				$txt = 'แลกของรางวัลใหม่ #'.$pk;
				foreach ($admins as $key => $v) setNoti($v['id'], $id, 6, $txt); // 6 = ตัดคะแนนแลกรางฃวัล
			}
		}
		echoJson(0, ['id' => $id, 'pk' => $pk, 'log_id' => $log_id]);
		break;

	/**
	 *	Get my history
	 *	Init 15/12/2021
	 */
	 case 'get_my_pts_log':

		if($app_uuid != ''){
			dbCon();
			$pts = getPoints($app_uuid);
			$db->where('a.user_id', $pts['user_id']);
			$db->orderBy('a.created_at','desc')
				->join('reward_redeem r','r.redeem_id=a.redeem_id','left')
				->join('reward rw','rw.reward_id=a.reward_id','left');
			$list = $db->get('reward_point_history a', 500,'a.*,r.item_title,r.item_code,r.status as redeem_status,rw.status as reward_status, rw.product_model');
		}else{
			$list = ['err_msg' => 'not found uuid'];
			$pts =  ['balance' => 'N/A', 'expiring' => 'N/A', 'user_id' => 0];
		}
		echoJson(0, ['list' => $list, 'pts' => $pts, 'user_id' => $pts['user_id']]);
		break;


	/**
	 *	Get my profile for reward page
	 *	Init 29/12/2021
	 */
	 case 'get_profile_reward':
	 if($app_uuid != '' && @$app_token != ''){
		 dbCon();
		 $db->where('c.uuid', $app_uuid)
			 ->orderBy('c.updated_at','DESC')
			 ->where('c.status', 1)
			 ->join('client_user c', 'c.user_id=t.user_id AND c.status =1')
			 ->join('service_center sc', 'sc.sc_id=t.sc_id AND c.status =1','left');
		 $profile = $db->getOne('technician t', 't.technician_id, t.technician_code,t.firstname,t.lastname,t.name_title,t.phone, t.email, sc.title as shop');

	 }else{
		 $profile = [];
	 }
	 echoJson(0, ['profile' => $profile]);
	 break;

	/**
	 *	Upload Attachment  to collect-point request (Before submit)
	 *	Init 21/12/2021
	 */
	case 'reward_request_fileupload':
		// print_r($_FILES);
    if(!isset($_FILES) || !isset($_FILES['attach_photo'])) echoJson(2, 'ข้อมูลไม่ครบถ้วน');
    $key = genRandStr(16);
    $filename = @session_id().'_'.$key.'_'.hyphenize($_FILES['attach_photo']['name']);
		$save_file_path = ROOT_PATH.ARCHIVE_FILE.'reward/'.$filename;
    move_uploaded_file($_FILES['attach_photo']['tmp_name'], $save_file_path);
    echoJson(0, ['file' => PUBLIC_DOMAIN.ARCHIVE_DIR_NAME.'/reward/'.$filename, 'filekey' => $filename]);
    break;

	/**
	 *	Save new address
	 *	Init 28/01/2022
	 */
	case 's_address_form':
		// if(
		// 	strlen($app_token) < 6 || strlen($app_uuid) < 6 )
		// 		echoJson(2, 'ไม่พบผู้ใช้งาน');
		if(!isset($_POST['house_no']) || $_POST['house_no'] == '') echoJson(3, 'ไม่พบบ้านเลขที่');
		if(@$_POST['subdistrict'] == '' || @$_POST['district'] == '' || @$_POST['province'] == '' || @$_POST['postcode'] == '') echoJson(4, 'ไม่พบตำบล / อำเภอ / จังหวัด / รหัสไปรษณีย์');
		dbCon();
		$data = $_POST;
		unset($data['add_id'],$data['app_token'],$data['app_uuid']);
		$data['user_id'] = getUserIdByUUid($app_uuid);
		$data['updated_at'] = date('Y-m-d H:i:s');
		$data['created_at'] = date('Y-m-d H:i:s');
		if(isset($_POST['add_id']) && $_POST['add_id'] != 'new'){
			$res = $db->where('add_id', $_POST['add_id'])->update('address', $data);
			$add_id = ($res) ? $_POST['add_id'] : 0;
		}
		else $add_id = $db->insert('address', $data);
		echoJson(0, ['id' => $add_id]);
		break;



	/**
	 *	Remove my address
	 *	Init 31/01/2022
	 */
	case 'x_my_address':
		if(
			strlen($app_token) < 6 || strlen($app_uuid) < 6 )
				echoJson(2, 'ไม่พบผู้ใช้งาน');
		if(!isset($_POST['add_id']) || $_POST['add_id'] == '') echoJson(3, 'ไม่พบรหัสที่อยู่ในระบบ');
		dbCon();
		$uid = getUserIdByUUid($app_uuid);
		$res = $db->where('add_id', $_POST['add_id'])->where('user_id', $uid)->delete('address');
		echoJson(0, $res);
		break;


	/**
	 *  Dealer search (select2)
	 *	Init 18/03/2022
	 */
	case 'get_service_dealer':
		dbCon();
		if(!isset($_POST['q']) || $_POST['q'] == '') echoJson(2, 'Search keyword requried');
		$q = trim($_POST['q']);
		// if(strlen($q) < 2) echoJson(0, []);

		$result =  $db->where('(s.name LIKE "%'.$q.'%" OR s.phone LIKE "'.$q.'%")')
			->get('serivice_dealer s', 20, 's.name,s.phone,s.dealer_id, "dealer" as source');

		$rt1 =  $db->where('s.seller LIKE "%'.$q.'%"')->groupBy('s.seller')
			->get('stock_out s', 20, 's.seller as name, "" as phone, 0 as dealer_id, "stock" as source');
		if($rt1 != null && count($rt1) > 0) $result = array_merge($result,$rt1);

		$rt1 =  $db->where('(s.seller_name LIKE "%'.$q.'%" OR s.seller_phone LIKE "'.$q.'%")')->groupBy('s.seller_name')
			->get('product_register s', 20, 's.seller_name as name, s.seller_phone as phone, 0 as dealer_id, "reg" as source');
			if($rt1 != null && count($rt1) > 0) $result = array_merge($result,$rt1);

		$rt1 =  $db->where('(s.title LIKE "%'.$q.'%" OR s.phone LIKE "'.$q.'%")')->groupBy('s.title')
			->get('service_center s', 20, 's.title as name, "" as phone, 0 as dealer_id, "sc" as source');
			if($rt1 != null && count($rt1) > 0) $result = array_merge($result,$rt1);
		echoJson(0, $result);
		break;


	/**
	 *	Get OTP for product register verification
	 *	Init 18/03/2022
	 */
	 case 'otp_request_register_submit':
	 	if(!isset($_SESSION['ssFailedOTP'])) $_SESSION['ssFailedOTP'] = 0;
		if(!isset($_POST['mobile']) || $_POST['mobile'] == '') echoJson(2, 'ไม่พบหมายเลขโทรศัพท์');
		if(!isset($_POST['token']) || $_POST['token'] == '') echoJson(3, 'Google Token ไม่พบ');
		dbCon();
		// echoJson(0, ['token' => '888888-39399393-33304444444','ref' => '99393']);exit;// DEBUG
		$res = sendOTP($_POST['mobile']);

			if(isset($res->code) && $res->code == '000'){
				if(isset($res->result) && isset($res->result->ref_code) && isset($res->result->token)){
					$vid = $db->insert('user_validation', [
						'phone' => trim($_POST['mobile']),
						'user_id' => -1,
						'token' => $res->result->token,
						'ref' => $res->result->ref_code,
						'created_at' => date('Y-m-d H:i:s'),
						'status' => 0,
						'updated_at' => date('Y-m-d H:i:s')
					]);
					$res->result->vid = $vid;
				}
				echoJson(0, [
					'token' => $res->result->token,
					'ref' => $res->result->ref_code
				]);
				$_SESSION['ssFailedOTP']++;


			}else{
				echoJson((int) @$res->code, @$res->detail);
			}
			break;

	/**
	 *	Submit OTP for product register verification
	 *	Init 18/03/2022
	 */
	 case 'otp_verify_register_submit':
		 if(!isset($_POST['ref']) || $_POST['ref'] == '') echoJson(4, 'ไม่พบหมายเลข Reference Code');
		 if(!isset($_POST['otp']) || $_POST['otp'] == '') echoJson(5, 'ระบบไม่ได้รับ OTP');
		 if(!isset($_POST['token']) || $_POST['token'] == '') echoJson(6, 'ระบบไม่ได้รับ Token');
		 dbCon();

		 $res = validateOTP($_POST['otp'],$_POST['ref'],$_POST['token']);
		 // print_r($res);
		 if(isset($res->code) && isset($res->result)
			 && $res->code == '000' && @$res->result->status == true){
			 // Get phone number
			 $uv = $db->where('user_id', -1)
				 ->where('token', $_POST['token'])
				 ->where('ref', $_POST['ref'])
				 ->getOne('user_validation', 'phone,user_vid');

			 if(@$uv['user_vid'] > 0) $db->where('user_vid', $uv['user_vid'])
				 ->update('user_validation', [
					 'status' => 1,
					 'updated_at' => date('Y-m-d H:i:s')
				 ]);
			 unset($_SESSION['ssFailedOTP']);
			 $_SESSION['ssSuccessOTP'] = 1;
			 echoJson(0, ['result' => $res->code, 'uid' => -1]);
		 }else if(isset($res->code) && isset($res->result)
				 && $res->code == '000'
				 && @$res->result->status == false){
			 echoJson(8, 'รหัส OTP ไม่ถูกต้อง');
		 }else{
			 echoJson(9, @$res->code .' '. @$res->detail);
		 }
		 break;


	 /**
	  *	Fix the reward history missing after reward succeed
	  *	Init 18/03/2023
	  */
	  case '__fix_reward_history_record':
		 	 dbCon();
			 $list = $db->where('r.user_id IS NOT NULL')
			 	// ->where('DATE(r.created_at) < DATE(CURDATE())')
	 			->where('r.status', 1) // ไม่ยกเลิก
				->orderBy('r.created_at','desc')
	 			->get('reward r',10000,'r.*');
				echo '<pre>';
				// print_r($list);
			foreach ($list as $k => $v) {
				$reCC = $db->where('h.reward_id', $v['reward_id'])
	 	 			->getValue('reward_point_history h', 'COUNT(h.rw_point_id)');
				echo $v['reward_id'].'->'.$reCC."\n";

				if($reCC <= 0){
					$save = [
						'reward_id' => $v['reward_id'],
						'user_id' => $v['user_id'],
						'technician_id' => $v['technician_id'],
						'point' => $v['point_taken'],
						'created_at' => $v['created_at'],
						'updated_at' => $v['updated_at'],
						'approved_user_id' => 1,
						'status' => 1,
						'balance' => 0
					];
					echo $db->insert('reward_point_history', $save);
					print_r($save);
					echo $db->getLastQuery().'<br/>'; // DEBUG
				}
			}
	 	 break;

	default: echoJson(9000, 'No action found');
}



// ************************ Rewards ******************************