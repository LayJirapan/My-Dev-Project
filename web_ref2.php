<?php
/*
 * Hi Mavell CSMS API
 *
 *
 * 	@since March 2021
 * 	@access public
 * 	Mgnt = 5, Mavell Admin = 4, W/H Staff =3, FG Staff = 2,
 *	Org ASC / Dealer, = 1 , Customer = 0
 *
 */
require_once('config.main.php');
header('Content-Type: application/json');
require_once('function.php');
require "vendor/autoload.php";

if ( basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"]) ) {

// This interface require authentication inside "some" actions
if(!isset($_REQUEST['a'])) echoJson(8000, 'No User Action Request');
// Check the action request
$action = trim($_REQUEST['a']);

if($action != 'page_init' && !isset($_POST['uuid'])){
	// if no bypass signin
	$dbh = new \PDO('mysql:dbname='.MYSQL_BASENAME.';host='.MYSQL_HOST.';charset=utf8mb4', MYSQL_USER, MYSQL_PASS);
	$auth = new \Delight\Auth\Auth($dbh);
	echoChkLogin();
}


$global_perm = [];
// $_SESSION['auth_roles'] = 0; // DEBUG : only debug profile

switch ($action) {


	/**
	 *	Page Initializing
	 *	Init 24/05/2021
	 */
	case 'page_init':
		// By-pass mobile app signin
		$bypass_uid = -1;
		if(!isset($_SESSION['auth_user_id']) && isset($_POST['uuid']) && isset($_POST['token'])){
			dbCon();
			// Get user by uuid and jwt to sign in
			$db->where('c.uuid',$_POST['uuid'])->where('c.jwt',@$_POST['token'])->orderBy('c.updated_at','DESC');
			$db->join('client_user c', 'c.user_id=u.id');
			$db->join('technician t', 't.user_id=u.id', 'left');
			$user = $db->getOne('users u', 'u.avatar, u.id, c.allow_noti, u.roles_mask, u.sc_id, t.type as t_type');
			// echo $db->getLastQuery().'<br/>'; // DEBUG
			if(isset($user['id']) && $user['id'] > 0){

				if($user['roles_mask'] == 1 && $user['t_type'] == 1){
					$_SESSION['auth_sc_id'] = -1; // -1 is Technicain (EWC)
				}else if($user['roles_mask'] == 1 && @$user['t_type'] != 1 && @$user['sc_id'] > 0){
					$_SESSION['auth_sc_id'] = $user['sc_id'];
				}else if($user['roles_mask'] > 1){
					$_SESSION['auth_sc_id'] = -2;// -2 is admin non-asc
				}else{
					$_SESSION['auth_sc_id'] = 0; // unknown
				}


				$_SESSION['auth_user_id'] = $user['id'];
				$_SESSION['auth_roles'] = $user['roles_mask'] > 0 ? $user['roles_mask'] : 0;
				if(isset($user['avatar']) && $user['avatar'] != '') $_SESSION['auth_avatar'] = $user['avatar'];
				$bypass_uid = $user['id'];
			}
		}// by-pass app signin
		echoJson(0, ['my_user' => [
			'username' => $_SESSION['auth_username'],
			'id' => $_SESSION['auth_user_id'],
			'avatar' => @$_SESSION['auth_avatar']//PUBLIC_DOMAIN.APP_PATH.ARCHIVE_DIR_NAME.'/profile/'.$_SESSION['auth_user_id'].'.jpg'
		],'bypass_uid' => $bypass_uid]);
		break;


	/*
	*	Send push fcm individually
	*	(by token fcm or user id)
	*	Init 08/10/2020
	*/
	case 'send_push_fcm':
		if((!isset($_POST['token']) || $_POST['token'] == '') && !isset($_POST['user_id'])) echoJson(2, 'User / Token Requried');
		else if(!isset($_POST['title']) || $_POST['title'] == '') echoJson(5, 'Title Requried');
		else if(!isset($_POST['message']) || $_POST['message'] == '') echoJson(5, 'Message Requried');
		dbCon();
		if(@$_POST['user_id'] > 0) $tk = getUserDeviceTokens($_POST['user_id']);
		else $tk = trim($_POST['token']);
		echoJson(0, fcmSendByTokens($tk,
			"Hi Mavell - ".$_POST['title'] , $_POST['message']));
		break;

	/*
	*	Send push fcm to user
	*	(by token fcm or user id)
	*	Init 10/07/2021
	*/
	case 'send_push_fcm_by_user_id':
		if(!isset($_POST['user_id']) || $_POST['user_id'] == '') echoJson(2, 'User ID Requried');
		else if(!isset($_POST['message']) || $_POST['message'] == '') echoJson(3, 'Message Requried');
		dbCon();
		$res = setNoti($_POST['user_id'], 0, 0,  $_POST['message'], @$_POST['title']);
		echoJson(0, $res);
		break;

	/*
	*	Send push fcm Service Center
	*	(by token fcm or user id)
	*	Init 12/11/2020
	*/
	case 'send_push_fcm_by_org':
		if(!isset($_POST['sc_id']) || $_POST['sc_id'] == '') echoJson(2, 'Org ID Requried');
		else if(!isset($_POST['title']) || $_POST['title'] == '') echoJson(3, 'Title Requried');
		else if(!isset($_POST['message']) || $_POST['message'] == '') echoJson(4, 'Message Requried');
		dbCon();

		$users = $db->where('u.sc_id', $_POST['sc_id'])
			->where('(c.tokenFCM IS NOT NULL OR c.tokenFCM != "")')
			->join('users u', 'c.user_id=u.id', 'left')
			->get('client_user c', 2000, 'c.tokenFCM');

		$tks = [];
		if(count($users) > 0){
			$tks = array_map(function($u){ return $u['tokenFCM']; }, $users);
		}

		echoJson(0, fcmSendByTokens($tks,
			$_POST['title'] , $_POST['message']));
		break;

	/**
	 *	User list
	 *	Init 08/10/2020
	 */
	case 'user_list':
	case 'user_detail':
		dbCon();
		chkProfilePermit('user.html');
		if(isset($_POST['user_id']) && $_POST['user_id'] > 0){
			$db->where('u.id', $_POST['user_id']);
		}
		if(isset($_POST['role']) && $_POST['role'] > -1){
			$db->where('u.roles_mask', $_POST['role']);
		}
		if(isset($_POST['keyword']) && $_POST['keyword'] != ''){
			$q = trim($_POST['keyword']);
			$db->where('(u.id LIKE "%'.$q.'%" OR u.email LIKE "%'.$q.'%"'.
			  ' OR u.fullname LIKE "%'.$q.'%" OR u.phone LIKE "%'.$q.'%"'.
				' OR u.personid LIKE "%'.$q.'%" OR u.city LIKE "%'.$q.'%"'.
				' OR c.customer_code LIKE "%'.$q.'%" OR c.zone LIKE "%'.$q.'%"'.
				' OR c.firstname LIKE "%'.$q.'%" OR c.lastname LIKE "%'.$q.'%"'.
				' OR c.phone LIKE "%'.$q.'%" OR c.remark LIKE "%'.$q.'%"'.
				' OR t.technician_code LIKE "%'.$q.'%" OR t.firstname LIKE "%'.$q.'%"'.
			  ' OR t.lastname LIKE "%'.$q.'%" OR t.zone LIKE "%'.$q.'%"'.
			')');
		}
		$db->orderBy('u.last_login','DESC')
			->groupBy('u.email')
			->join('customer c','u.id=c.user_id','left')
			->join('technician t', 'u.id=t.user_id', ($_SESSION['auth_roles'] <= 1 && $_SESSION['auth_sc_id'] != -1 ? '' : 'left') );
		if($_SESSION['auth_roles'] <= 1 && $_SESSION['auth_sc_id'] != -1) { // match sc
			$db->where('(t.sc_id = '.$_SESSION['auth_sc_id'].' OR t.user_id = '.$_SESSION['auth_user_id'].')');
		}
		$list = $db->get('users u', 2000,
				(!isset($_POST['select'])
					? 'u.id,u.username,u.email,u.avatar,u.fullname,u.roles_mask,u.last_login,u.verified,u.status,u.registered,u.social_type,u.social_id'
					: 'u.username,u.email,u.id,u.social_type,u.social_id')
				.',CONCAT_WS("",c.firstname," ",c.lastname) as customer, c.customer_id, t.technician_id, CONCAT_WS("",t.firstname," ",t.lastname, " - ", t.technician_code, " [", t.zone,"]") as technician'
			);
		echoJson(0, $action == 'user_detail' ? @$list[0]: $list);
		break;

	/**
	 *	Saving user
	 *	Init 10/10/2020
	 */
	case 's_user':
		dbCon();
		if(!isset($_POST['id']) || $_POST['id'] == '') echoJson(2, 'User ID Requried');
		else if(!isset($_POST['username']) || $_POST['username'] == '') echoJson(3, 'Username Requried');
		if(isset($_POST['email']) && $_POST['email'] != '' && validateEmail($_POST['email'])){
			$email = $_POST['email'];
		}else $email = '';

		chkProfilePermit('user.html');
		$data = $_POST;
		emptyNum($data['status']);
		emptyNum($data['verified']);
		emptyNum($data['resettable'],1);
		emptyNum($data['roles_mask']);
		emptyNum($data['registered']);
		emptyNum($data['last_login']);
		emptyNum($data['force_logout']);
		emptyNum($data['sc_id']);

		if($_SESSION['auth_roles'] <= 1 && $_SESSION['auth_sc_id'] > 0) { // match sc
			$data['sc_id'] = $_SESSION['auth_sc_id'];
		}

		// If contain new password
		if(isset($data['password']) && $data['password'] != ''){
			$newhash = \password_hash(trim($data['password']), \PASSWORD_DEFAULT);
			$data['password'] = $newhash;
		}

		unset($data['id'],$data['customer_id'],$data['technician_id']);
		if($_POST['id'] == 'new'){
			$exist = $db->where('email', $data['email'])->getValue('users', 'id');
			if($exist) echoJson(2, 'Email ถูกใช้งานแล้ว ไม่สามารถใช้ซ้ำกับผู้ใชงานคนอื่นได้');

			$data['registered'] = time();
			$id = $db->insert('users', $data);
		}else{
			$db->where('u.id',$_POST['id']);
			$updated = $db->update('users u', $data);
			$id = ($updated) ? $_POST['id'] :0;
		}
		// Update user id to customer
		$data = ['user_id' => $id, 'updated_at' => date('Y-m-d H:i:s')];
		if($email != '') $data['email'] = $_POST['email'];
		// print_r($data);

		if($id > 0 && isset($_POST['customer_id']) && $_POST['customer_id'] != '') {
			$db->where('customer_id', $_POST['customer_id'])->update('customer', $data);
		}
		// Update user id to technician
		if($id > 0 && isset($_POST['technician_id']) && $_POST['technician_id'] != '') {
			$db->where('technician_id', $_POST['technician_id'])->update('technician', $data);
		}
		// echo $db->getLastQuery().'<br/>'; // DEBUG
		echoJson(0, $id);
		break;

	/**
	 *	News list
	 *	Init 30/03/2021
	 */
	case 'news_list':
		dbCon();
		chkProfilePermit('news.html');
		if(isset($_POST['keyword']) && $_POST['keyword'] != ''){
			$q = trim($_POST['keyword']);
			$db->where('(p.title LIKE "%'.$q.'%" OR p.subtitle LIKE "%'.$q.'%"'.
			  ' OR p.picture LIKE "%'.$q.'%" OR p.detail_html LIKE "%'.$q.'%"'.
			  ' OR p.action_value LIKE "%'.$q.'%" OR p.tail_value LIKE "%'.$q.'%"'.
			')');
		}
		if(isset($_POST['news_id']) && $_POST['news_id'] > 0) $db->where('p.news_id', $_POST['news_id']);
		$db->orderBy('p.updated_at','DESC')->groupBy('p.news_id');
		$list = $db->get('news p', 50,'p.*');
		echoJson(0, $list);
		break;

	/**
	 *	Saving News
	 *	Init 09/10/2020
	 */
	case 's_news':
		dbCon();
		if(!isset($_POST['id']) || $_POST['id'] == '') echoJson(2, 'Promo ID Requried');
		chkProfilePermit('news.html','Y');
		$data = $_POST;
		unset($data['id']);
		$data['updated_at'] = date('Y-m-d H:i:s');

		if($_POST['id'] == 'new'){
			$data['created_at'] = date('Y-m-d H:i:s');
			$id = $db->insert('news', $data);
		}else{
			$db->where('p.news_id',$_POST['id']);
			$updated = $db->update('news p', $data);
			$id = ($updated) ? $_POST['id'] :0;
		}

		// Rename if uploaded cover
		if(isset($_SESSION['_newscf'])){
			$path = ROOT_PATH.ARCHIVE_FILE.'news/';
			$type = strtolower(substr(strrchr($_SESSION['_newscf'],'.'),1));
			$new_fn = 'cover_'.$id.'_'.date('Hi').'.'.$type;
			if(rename($path.$_SESSION['_newscf'], $path.$new_fn)){
				unset($_SESSION['_newscf']);
				$db->where('p.news_id',$_POST['id']);
				$db->update('news p', ['picture' => PUBLIC_DOMAIN.APP_PATH.ARCHIVE_DIR_NAME.'/news/'.$new_fn]);
			}
		}
		// echo $db->getLastQuery().'<br/>'; // DEBUG
		echoJson(0, $id);
		break;

	/**
	 *	Upload news cover
	 *	Init 04/10/2021
	 */
	case 'news_cover_pic_ul':
		// print_r($_FILES);
		if(!isset($_FILES) || !isset($_FILES['upload_file'])) echoJson(2, 'ข้อมูลไม่ครบถ้วน');
    if($_FILES['upload_file']['tmp_name'] && $_FILES['upload_file']['size'] > 0){
			if($_FILES['upload_file']['size'] > 2097152) echoJson(4,'ขนาดไฟล์ใหญ่เกิน 2Mb');
      $type = strtolower(substr(strrchr($_FILES['upload_file']['name'],'.'),1));
			$flname = '_tmp_'.$_SESSION['ssUID'].time().'.'.$type; // just into temp untill save
			$path = ROOT_PATH.ARCHIVE_FILE.'news/'.$filename;
      $located_file = $path.$flname;
      $result = move_uploaded_file($_FILES['upload_file']['tmp_name'], $located_file);
			$_SESSION['_newscf'] = $flname;
			echoJson(0, array('file'=> PUBLIC_DOMAIN.APP_PATH.ARCHIVE_DIR_NAME.'/news/'.$flname, 'result' => $result,'filename'=> $flname));
    }else echoJson(4,'No such upload file');
		break;

	/**
	 *	Product Model list
	 *	Init 30/03/2021
	 */
	case 'product_model_list':
		dbCon();
		chkProfilePermit('product.html');
		if(isset($_POST['product_id']) && $_POST['product_id'] > 0) $db->where('p.product_id', $_POST['product_id']);
		if(isset($_POST['select'])) $db->orderBy('p.product_id','ASC');
		else $db->orderBy('p.updated_at','DESC');
		$list = $db->get('product_model p', 500, isset($_POST['select']) ? 'p.product_id, p.product_code, p.model_name, p.model_indoor, p.model_outdoor' : 'p.*');
		echoJson(0, $list);
		break;

	/**
	 *	Product Model Save
	 *	Init 30/03/2021
	 */
	case 's_product_model':
		if(!isset($_POST['id']) || $_POST['id'] == '') echoJson(2, 'Product ID Requried');
		dbCon();
		chkProfilePermit('product.html', 'Y');
		$data = $_POST;
		unset($data['id'], $data['category']);
		$data['updated_at'] = date('Y-m-d H:i:s');
		$data['model_indoor'] =  trim(str_replace(' ', '', $data['model_indoor']));
		$data['model_outdoor'] = trim(str_replace(' ', '', $data['model_outdoor']));
		if($_POST['id'] == 'new'){
			$data['created_at'] = date('Y-m-d H:i:s');
			$id = $db->insert('product_model', $data);
		}else{
			$db->where('p.product_id',$_POST['id']);
			$updated = $db->update('product_model p', $data);
			$id = ($updated) ? $_POST['id'] :0;
		}
		// echo $db->getLastQuery().'<br/>'; // DEBUG
		echoJson(0, $id);
		break;

	/**
	 *	Product Type list
	 *	Init 14/11/2022
	 */
	case 'product_type_list':
		dbCon();
		chkProfilePermit('product_type.html');
		echoJson(0, $db->get('product_type p', 100, 'p.*'));
		break;

	/**
	 *	Product Type Save
	 *	Init 14/11/2022
	 */
	case 's_product_type':
		if(!isset($_POST['id']) || $_POST['id'] == '') echoJson(2, 'Product ID Requried');
		dbCon();
		chkProfilePermit('product_type.html', 'Y');
		$data = $_POST;
		unset($data['id']);
		if($_POST['id'] == 'new'){
			$id = $db->insert('product_type', $data);
		}else{
			$db->where('p.product_type_id',$_POST['id']);
			$updated = $db->update('product_type p', $data);
			$id = ($updated) ? $_POST['id'] :0;
		}
		// echo $db->getLastQuery().'<br/>'; // DEBUG
		echoJson(0, $id);
		break;

	/**
	 *	System Policy list
	 *	Init 30/03/2021
	 */
	case 'policy_list':
		dbCon();
		chkProfilePermit('policy.html');
		if(isset($_POST['policy_id']) && $_POST['policy_id'] > 0) $db->where('s.policy_id', $_POST['policy_id']);
		$db->orderBy('p.updated_at','DESC');
		$list = $db->get('policy p', 100,'p.*');
		echoJson(0, $list);
		break;

	/**
	 *	System Policy Save
	 *	Init 30/03/2021
	 */
	case 's_policy':
		dbCon();
		if(!isset($_POST['id']) || $_POST['id'] == '') echoJson(2, 'Policy ID Requried');
		chkProfilePermit('policy.html', 'Y');
		$data = $_POST;
		unset($data['id']);
		$data['updated_at'] = date('Y-m-d H:i:s');
		if($_POST['id'] == 'new'){
			$data['created_at'] = date('Y-m-d H:i:s');
			$id = $db->insert('policy', $data);
		}else{
			$db->where('p.policy_id',$_POST['id']);
			$updated = $db->update('policy p', $data);
			$id = ($updated) ? $_POST['id'] :0;
		}
		// echo $db->getLastQuery().'<br/>'; // DEBUG
		echoJson(0, $id);
		break;

	/**
	 *	Technician list
	 *	Init 31/03/2021
	 */
	case 'technician_list':
		dbCon();
		chkProfilePermit('technician.html');
		if(isset($_POST['keyword']) && $_POST['keyword'] != ''){
			$q = trim($_POST['keyword']);
			$db->where('(t.technician_code LIKE "%'.$q.'%" OR t.zone LIKE "%'.$q.'%"'.
			  ' OR t.name_title LIKE "%'.$q.'%" OR t.firstname LIKE "%'.$q.'%"'.
			  ' OR t.lastname LIKE "%'.$q.'%" OR t.phone LIKE "%'.$q.'%"'.
			  ' OR t.email LIKE "%'.$q.'%" OR t.contact_address LIKE "%'.$q.'%"'.
			  ' OR t.remark LIKE "%'.$q.'%" OR u.username LIKE "%'.$q.'%"'.
			')');
		}
		if(isset($_POST['technician_id']) && $_POST['technician_id'] > 0) $db->where('t.technician_id', $_POST['technician_id']);
		if(isset($_POST['user_id']) && $_POST['user_id'] > 0) $db->where('t.user_id', $_POST['user_id']);
		$db->orderBy('t.updated_at','DESC')
			->join('service_center sc', 'sc.sc_id=t.sc_id', 'left')
			->join('users u', 'u.id=t.user_id', 'left');
		if($_SESSION['auth_roles'] <= 1 && $_SESSION['auth_sc_id'] != -1) { // match sc
			$db->where('(t.sc_id = '.$_SESSION['auth_sc_id'].' OR t.user_id = '.$_SESSION['auth_user_id'].')');
		}
		$list = $db->get('technician t', 1000,'t.*,sc.sc_code, sc.title as sc,u.username');
		echoJson(0, $list);
		break;

	/**
	 *	Customer list
	 *	Init 29/04/2021
	 */
	case 'customer_list':
		dbCon();
		chkProfilePermit('customer.html');
		if(isset($_POST['keyword']) && $_POST['keyword'] != ''){
			$q = trim($_POST['keyword']);
			$db->where('(t.customer_code LIKE "%'.$q.'%" OR t.zone LIKE "%'.$q.'%"'.
			  ' OR t.name_title LIKE "%'.$q.'%" OR t.firstname LIKE "%'.$q.'%"'.
			  ' OR t.lastname LIKE "%'.$q.'%" OR t.phone LIKE "%'.$q.'%"'.
			  ' OR t.email LIKE "%'.$q.'%" OR t.contact_address LIKE "%'.$q.'%"'.
			  ' OR t.remark LIKE "%'.$q.'%" OR u.username LIKE "%'.$q.'%"'.
			')');
		}
		if(isset($_POST['customer_id']) && $_POST['customer_id'] > 0) $db->where('t.customer_id', $_POST['customer_id']);
		if(isset($_POST['user_id']) && $_POST['user_id'] > 0) $db->where('t.user_id', $_POST['user_id']);
		$db->orderBy('t.updated_at','DESC')
			->join('users u', 'u.id=t.user_id', 'left');
		$list = $db->get('customer t', 100,'t.*,u.username');
		echoJson(0, $list);
		break;

	/**
	 *	Customer Save
	 *	Init 29/04/2021
	 */
	case 's_customer':
		dbCon();
		if(!isset($_POST['id']) || $_POST['id'] == '') echoJson(2, 'Technician ID Requried');
		chkProfilePermit('customer.html','Y');
		$data = $_POST;
		unset($data['id']);
		$data['updated_at'] = date('Y-m-d H:i:s');
		if($_POST['id'] == 'new'){
			$data['created_at'] = date('Y-m-d H:i:s');
			$id = $db->insert('customer', $data);
		}else{
			$db->where('t.technician_id',$_POST['id']);
			$updated = $db->update('customer t', $data);
			$id = ($updated) ? $_POST['id'] :0;
		}
		// echo $db->getLastQuery().'<br/>'; // DEBUG
		echoJson(0, $id);
		break;

	/**
	 *	Technician Save
	 *	Init 31/03/2021
	 */
	case 's_technician':
		dbCon();
		if(!isset($_POST['id']) || $_POST['id'] == '') echoJson(2, 'Technician ID Requried');
		chkProfilePermit('technician.html','Y');
		$data = $_POST;
		unset($data['id']);
		$data['updated_at'] = date('Y-m-d H:i:s');
		if($_POST['id'] == 'new'){
			$data['created_at'] = date('Y-m-d H:i:s');
			$id = $db->insert('technician', $data);
		}else{
			$db->where('t.technician_id',$_POST['id']);
			$updated = $db->update('technician t', $data);
			$id = ($updated) ? $_POST['id'] :0;
		}
		// echo $db->getLastQuery().'<br/>'; // DEBUG
		echoJson(0, $id);
		break;


	/**
	 *	Product S/N list
	 *	Init 31/03/2021
	 */
	case 'product_serial_list':
		dbCon();
		chkProfilePermit('serial.html');
		if(isset($_POST['serial_id']) && $_POST['serial_id'] > 0) $db->where('p.serial_id', $_POST['serial_id']);
		if(isset($_POST['product_id']) && $_POST['product_id'] > 0) $db->where('p.product_id', $_POST['product_id']);
		if(isset($_POST['serial_no']) && $_POST['serial_no'] != '') $db->where('p.serial_no LIKE "%'.trim($_POST['serial_no']).'%"');
		if(isset($_POST['lot_no']) && $_POST['lot_no'] != '') $db->where('p.lot_no LIKE "%'.$_POST['lot_no'].'%"');
		if(isset($_POST['line']) && $_POST['line'] != '') $db->where('p.line', $_POST['line'],'like');
		if(isset($_POST['mfd']) && $_POST['mfd'] != '') $db->where('p.mfd', $_POST['mfd'],'like');
		if(isset($_POST['myear']) && $_POST['myear'] != '') $db->where('YEAR(p.mfd)', $_POST['myear']);
		if(isset($_POST['date_start']) && $_POST['date_start'] != '' && isset($_POST['date_end']) && $_POST['date_end'] != ''){
			$db->where('DATE(p.mfd) BETWEEN "'.$_POST['date_start'].'" AND "'.$_POST['date_end'].'"');
		}
		if(isset($_POST['sn_part']) && $_POST['sn_part'] != ''){
			$q = trim($_POST['sn_part']);
			$db->where('(p.sn_remote LIKE "%'.$q.'%" OR p.sn_sup LIKE "%'.$q.'%"'.
			  ' OR p.sn_pipe LIKE "%'.$q.'%" OR p.sn_test LIKE "%'.$q.'%"'.
				' OR p.sn_prop LIKE "%'.$q.'%" OR p.sn_set LIKE "%'.$q.'%"'.
				' OR p.sn_evaporator LIKE "%'.$q.'%" OR p.sn_motor_indoor LIKE "%'.$q.'%"' .
				' OR p.sn_pcb_indoor LIKE "%'.$q.'%" OR p.sn_display_assembly LIKE "%'.$q.'%"' .
				' OR p.sn_compressor LIKE "%'.$q.'%" OR p.sn_capacitor_compressor LIKE "%'.$q.'%"' .
				' OR p.sn_magnetic LIKE "%'.$q.'%" OR p.sn_condenser LIKE "%'.$q.'%"' .
				' OR p.sn_pcb_outdoor LIKE "%'.$q.'%" OR p.sn_motor_outdoor LIKE "%'.$q.'%"' .
			  ' OR p.remark LIKE "%'.$q.'%"'.
			')');
		}
		if(isset($_POST['keyword']) && $_POST['keyword'] != ''){
			$q = trim($_POST['keyword']);
			$db->where('(p.serial_no LIKE "%'.$q.'%" OR p.lot_no LIKE "%'.$q.'%"'.
			  ' OR p.mfd LIKE "%'.$q.'%" OR p.model_code_opt LIKE "%'.$q.'%"'.
			  ' OR p.remark LIKE "%'.$q.'%" OR p.age_day LIKE "%'.$q.'%"'.
			')');
		}
		$db->orderBy('p.updated_at','DESC')
			->join('product_model m','m.product_id=p.product_id','LEFT');
		$list = $db->withTotalCount()->get('product_serial p', isset($_POST['limit']) ? $_POST['limit'] : 50,
			'p.*,m.product_code,m.model_name,m.model_indoor,m.model_outdoor,m.product_type');
			// echo $db->getLastQuery().'<br/>'; // DEBUG
		echoJson(0, ['list' => $list, 'count' => $db->totalCount]);
		break;

	/**
	 *	Number of Product S/N
	 *	Init 31/03/2021
	 */
	case 'product_serial_count':
		dbCon();
		chkProfilePermit('serial.html');
		$noSN = $db->getValue('product_serial p', 'COUNT(*)');
		echoJson(0, ['no_sn' => $noSN]);
		break;

	/**
	 *	Product S/N Save
	 *	Init 31/03/2021
	 */
	case 's_product_serial':
		if(!isset($_POST['id']) || $_POST['id'] == '') echoJson(2, 'Serial ID Requried');
		dbCon();
		chkProfilePermit('serial.html', 'Y');
		$data = $_POST;
		unset($data['id']);
		$data['updated_at'] = date('Y-m-d H:i:s');
		if($_POST['id'] == 'new'){
			$data['created_at'] = date('Y-m-d H:i:s');
			$id = $db->insert('product_serial', $data);
		}else{
			$db->where('p.serial_id',$_POST['id']);
			$updated = $db->update('product_serial p', $data);
			$id = ($updated) ? $_POST['id'] :0;
		}
		// echo $db->getLastQuery().'<br/>'; // DEBUG
		echoJson(0, $id);
		break;

	/**
	 *	FG Product S/N on The Line
	 *	Init 12/09/2021
	 */
	case 's_product_serial_fg_single':
		if(!isset($_POST['serial_no']) || $_POST['serial_no'] == '') echoJson(0, ['id' => 0, 'inx' => -1, 'error' => 'ไม่พบ Serial No']);
		dbCon();
		chkProfilePermit('serial.html', 'Y');
		$snid = $db->where('serial_no', trim(str_replace(' ', '', $_POST['serial_no'])), 'like')->getValue('product_serial','serial_id');

		$data = $_POST;
		unset($data['inx']);
		$data['updated_at'] = date('Y-m-d H:i:s');
		if($snid > 0){
			$db->where('p.serial_id', $snid);
			$updated = $db->update('product_serial p', $data);
			$id = ($updated) ? $_POST['id'] :0;
		}else{
			$data['created_at'] = date('Y-m-d H:i:s');
			$snid = $db->insert('product_serial', $data);
		}
		// echo $db->getLastQuery().'<br/>'; // DEBUG
		echoJson(0, ['id' => $snid, 'inx' => @$_POST['inx']]);
		break;

	/**
	 *	Product S/N Bulk save
	 *	Init 10/07/2021
	 */
	case 's_product_serial_fg_bulk':
		if(!isset($_POST['product_id']) || $_POST['product_id'] == '') echoJson(2, 'product_id requried');
		if(!isset($_POST['lot_no']) || $_POST['lot_no'] == '') echoJson(3, 'lot_no requried');
		if(!isset($_POST['mfd']) || $_POST['mfd'] == '') echoJson(4, 'mfd requried');
		if(!isset($_POST['serials']) || $_POST['serials'] == '') echoJson(5, 'serials requried');
		dbCon();
		chkProfilePermit('fg_form.html', 'Y');
		$ids = [];
		$sr = json_decode($_POST['serials'],true);

		foreach ($sr as $key => $v) {
			if($v['delete'] == 1) continue;

			$data = array(
				'mfd' => $_POST['mfd'],
				'product_id' => $_POST['product_id'],
				'lot_no' => $_POST['lot_no'],
				'line' => $_POST['line'],
				'serial_no' => $v['model'],
				// 'remark' => 'REMOTE:'.$v['remote']."\n".'SUP:'.$v['sup']
			);

			if(isset($v['remote']) && $v['remote'] != '') $data['sn_remote'] = $v['remote'];
			if(isset($v['sup']) && $v['sup'] != '') $data['sn_sup'] = $v['sup'];
			if(isset($v['pipe']) && $v['pipe'] != '') $data['sn_pipe'] = $v['pipe'];
			if(isset($v['test']) && $v['test'] != '') $data['sn_test'] = $v['test'];
			if(isset($v['prop']) && $v['prop'] != '') $data['sn_prop'] = $v['prop'];
			if(isset($v['set']) && $v['set'] != '') $data['sn_set'] = $v['set'];
			// indoor add new
			if(isset($v['sn_evaporator']) && $v['sn_evaporator'] != '') $data['sn_evaporator'] = $v['sn_evaporator'];
			if(isset($v['sn_motor_indoor']) && $v['sn_motor_indoor'] != '') $data['sn_motor_indoor'] = $v['sn_motor_indoor'];
			if(isset($v['sn_pcb_indoor']) && $v['sn_pcb_indoor'] != '') $data['sn_pcb_indoor'] = $v['sn_pcb_indoor'];
			if(isset($v['sn_display_assembly']) && $v['sn_display_assembly'] != '') $data['sn_display_assembly'] = $v['sn_display_assembly'];
			// outdoor add new
			if(isset($v['sn_compressor']) && $v['sn_compressor'] != '') $data['sn_compressor'] = $v['sn_compressor'];
			if(isset($v['sn_capacitor_compressor']) && $v['sn_capacitor_compressor'] != '') $data['sn_capacitor_compressor'] = $v['sn_capacitor_compressor'];
			if(isset($v['sn_magnetic']) && $v['sn_magnetic'] != '') $data['sn_magnetic'] = $v['sn_magnetic'];
			if(isset($v['sn_condenser']) && $v['sn_condenser'] != '') $data['sn_condenser'] = $v['sn_condenser'];
			if(isset($v['sn_pcb_outdoor']) && $v['sn_pcb_outdoor'] != '') $data['sn_pcb_outdoor'] = $v['sn_pcb_outdoor'];
			if(isset($v['sn_motor_outdoor']) && $v['sn_motor_outdoor'] != '') $data['sn_motor_outdoor'] = $v['sn_motor_outdoor'];

			
			// print_r($data);exit;

			$data['updated_at'] = date('Y-m-d H:i:s');
			$exist_id = $db->where('serial_no',$v['model'])->getValue('product_serial', 'serial_id');

			if($exist_id >0 ){
				$db->where('p.serial_id',$_POST['id']);
				$updated = $db->update('product_serial p', $data);
				$id = ($updated) ? $exist_id : 0;
			}else{
				$data['created_at'] = date('Y-m-d H:i:s');
				$id = $db->insert('product_serial', $data);
			}
			if($id > 0) $ids[] = $id;
		} // end foreach
		// echo $db->getLastQuery().'<br/>'; // DEBUG
		echoJson(0, array('ids' => $ids));
		break;

	/**
	 *	Product S/N list
	 *	Init 01/04/2021
	 */
	case 'service_rating_list':
		dbCon();
		chkProfilePermit('rating.html');
		if(isset($_POST['rating_id']) && $_POST['rating_id'] > 0) $db->where('r.rating_id', $_POST['rating_id']);
		if(isset($_POST['user_id']) && $_POST['user_id'] > 0) $db->where('r.user_id', $_POST['user_id']);
		$db->orderBy('r.updated_at','desc')
			->join('customer c','c.customer_id=r.customer_id','left');
		if($_SESSION['auth_roles'] <= 1 && $_SESSION['auth_sc_id'] != -1) { // match sc
			$db->join('service_request sr', 'sr.register_id=r.register_id')
				->join('technician t', 't.technician_id=sr.technician_id')
				->where('(t.sc_id = '.$_SESSION['auth_sc_id'].' OR t.user_id = '.$_SESSION['auth_user_id'].')');
		}
		$list = $db->get('service_rating r', 100,'r.*, CONCAT_WS("",c.firstname," ",c.lastname) as customer');
		echoJson(0, $list);
		break;

	/**
	 *	Service Rating Save
	 *	Init 01/04/2021
	 */
	case 's_service_rating':
		dbCon();
		if(!isset($_POST['id']) || $_POST['id'] == '') echoJson(2, 'Serial ID Requried');
		chkProfilePermit('rating.html', 'Y');
		$data = $_POST;
		unset($data['id']);
		$data['updated_at'] = date('Y-m-d H:i:s');
		if($_POST['id'] == 'new'){
			$data['created_at'] = date('Y-m-d H:i:s');
			$id = $db->insert('service_rating', $data);
		}else{
			$db->where('p.rating_id',$_POST['id']);
			$updated = $db->update('service_rating p', $data);
			$id = ($updated) ? $_POST['id'] :0;
		}
		// echo $db->getLastQuery().'<br/>'; // DEBUG

		// Sending notification
		$data = $db->where('service_id', $_GET['service_id'])->getOne('service_request', 'service_id,technician_id,user_id');
		if(isset($data['service_id']) && $data['service_id'] > 0 ){
			$pk = 'SR'.fillZero($data['service_id'],8);
			$txt = 'มีการให้คะแนนการให้บริการ #'.$pk. ' ('.$_POST['rate_star'].'/5)';
			setNoti($data['user_id'], $data['service_id'], 2,  $txt);
			setNoti($data['technician_id'], $data['service_id'], 2,  $txt);
		}
		echoJson(0, $id);
		break;

	/**
	 *	Service Center list
	 *	Init 29/03/2021
	 */
	case 'sc_list':
		dbCon();
		chkProfilePermit('sc_list.html');
		if(isset($_POST['keyword']) && $_POST['keyword'] != ''){
			$q = trim($_POST['keyword']);
			$db->where('(p.short_name LIKE "%'.$q.'%" OR p.title LIKE "%'.$q.'%"'.
			  ' OR p.contact_name1 LIKE "%'.$q.'%" OR p.contact_name2 LIKE "%'.$q.'%"'.
			  ' OR p.contact_name3 LIKE "%'.$q.'%" OR p.contact_name4 LIKE "%'.$q.'%"'.
			  ' OR p.contact_name5 LIKE "%'.$q.'%" OR p.latlng LIKE "%'.$q.'%"'.
			  ' OR p.zone LIKE "%'.$q.'%" OR p.address LIKE "%'.$q.'%"'.
			')');
		}
		if($_SESSION['auth_roles'] <= 1 && $_SESSION['auth_sc_id'] != -1) { // match sc
			$db->where('p.sc_id', $_SESSION['auth_sc_id']);
		}
		$db->orderBy('p.updated_at','desc')->groupBy('p.sc_id');
		$list = $db->get('service_center p', 500,'p.*');
		echoJson(0, $list);
		break;

	/**
	 *	Service Center search (select2)
	 *	Init 29/04/2021
	 */
	case 'sc_query':
		dbCon();
		if(!isset($_GET['q']) || $_GET['q'] == '') echoJson(2, 'Search keyword requried');
		chkProfilePermit('sc_list.html');
		$q = trim($_GET['q']);
		$db->where('(c.sc_code LIKE "%'.$q.'%" OR c.title LIKE "%'.$q.'%"'.
		  ' OR c.short_name LIKE "%'.$q.'%" OR c.contact_name1 LIKE "%'.$q.'%"'.
		  ' OR c.contact_name2 LIKE "%'.$q.'%" OR c.contact_name3 LIKE "%'.$q.'%"'.
		  ' OR c.contact_name4 LIKE "%'.$q.'%" OR c.contact_name5 LIKE "%'.$q.'%"'.
		  ' OR c.zone LIKE "%'.$q.'%" OR c.sc_type LIKE "%'.$q.'%"'.
			' OR c.email LIKE "%'.$q.'%" OR c.remark LIKE "%'.$q.'%"'.
		')');
		if($_SESSION['auth_roles'] <= 1) { // match sc
			$db->where('p.sc_id', $_SESSION['auth_sc_id']);
		}
		$db->orderBy('c.updated_at','desc')
			->groupBy('c.sc_id');
		$list = $db->get('service_center c', 50,'c.*');
		// echo $db->getLastQuery().'<br/>'; // DEBUG
		echoJson(0, $list);
		break;

	/**
	 *	Saving Service Center
	 *	Init 29/03/2021
	 */
	case 's_sc':
		dbCon();
		if(!isset($_POST['id']) || $_POST['id'] == '') echoJson(2, 'Service Center ID Requried');
		chkProfilePermit('sc_list.html', 'Y');
		$data = $_POST;
		unset($data['id']);
		$data['updated_at'] = date('Y-m-d H:i:s');
		if($_POST['id'] == 'new'){
			$data['created_at'] = date('Y-m-d H:i:s');
			$id = $db->insert('service_center', $data);
		}else{
			if($_SESSION['auth_roles'] <= 1) { // match sc
				$db->where('p.sc_id', $_SESSION['auth_sc_id']);
			}else{
				$db->where('p.sc_id',$_POST['id']);
			}
			$updated = $db->update('service_center p', $data);
			$id = ($updated) ? $_POST['id'] :0;
		}

		$db->orderBy('p.zone','asc')->groupBy('p.sc_id');
		$list = $db->get('service_center p', 500,'p.title, p.address, p.phone, p.latLng');
		$val = 'FID,name,address,chips,crackers,blackbeanc,blackbeans,speltpasta,tortillas,adzuki beans,pintobeans,popcorn,milledprod,products,latitude,longitude,addrtype,addrlocat,index'."\n";
		foreach ( $list as $k => $v ) {
			// print_r($v);
	    if(isset($v['latLng']) && $v['latLng'] != '') {
				$ll = explode(',',addslashes(str_replace(' ','',$v['latLng'])));
				if(isset($ll[0]) && $ll[0] != '') $ll[0] = number_format((float)$ll[0], 6);
				if(isset($ll[1]) && $ll[1] != '') $ll[1] = number_format((float)$ll[1], 6);
				// print_r($ll);echo "\n";
				if(isset($ll[0]) && isset($ll[1])) $val .= $k.',"'.str_replace(',',';',trim($v['title'])).'","'.str_replace(',',';',trim($v['address'])).'",1,1,0,1,1,0,1,1,1,0,"'
					.str_replace(',',';',($v['phone'])).'",'.$ll[0].','.$ll[1].',,,""'."\n";
			}
		}
		file_put_contents('service_center.csv', $val);

		// echo $db->getLastQuery().'<br/>'; // DEBUG
		echoJson(0, $id);
		break;

	/**
	 *	Store list
	 *	Init 01/06/2022
	 */
	case 'store_list':
		dbCon();
		chkProfilePermit('stores.html');
		if(isset($_POST['keyword']) && $_POST['keyword'] != ''){
			$q = trim($_POST['keyword']);
			$db->where('(p.name LIKE "%'.$q.'%" OR p.phone LIKE "%'.$q.'%")');
		}
		$db->orderBy('p.updated_at','desc');
		$list = $db->get('serivice_dealer p', 1000,'p.*');
		echoJson(0, $list);
		break;

	/**
	 *	Saving Store
	 *	Init 01/06/2022
	 */
	case 's_store':
		dbCon();
		if(!isset($_POST['id']) || $_POST['id'] == '') echoJson(2, 'Dealer ID Requried');
		chkProfilePermit('stores.html', 'Y');
		$data = $_POST;
		unset($data['id']);
		$data['updated_at'] = date('Y-m-d H:i:s');
		if($_POST['id'] == 'new'){
			$data['created_at'] = date('Y-m-d H:i:s');
			$id = $db->insert('serivice_dealer', $data);
		}else{
			$db->where('p.sc_id',$_POST['id']);
			$updated = $db->update('serivice_dealer p', $data);
			$id = ($updated) ? $_POST['id'] : 0;
		}
		echoJson(0, $id);
		break;

	/**
	 *	Service Request list
	 *	Init 08/04/2021
	 */
	case 'serv_req_list':
	case 'serv_req_for_repair':
	case 'serv_req_for_history':
	case 'pending_repair_list':
	case 'schedule_repair_list': // Init 21/04/2021
	case 'service_job_list': // Init 22/04/2021
		dbCon();
		if($action == 'serv_req_list') chkProfilePermit('service_request.html');
		else if($action == 'serv_req_for_repair') chkProfilePermit('service_fix.html');
		else if($action == 'serv_req_for_history') chkProfilePermit('service_history.html');

		$add_fields = '';
		if(isset($_POST['service_id']) && $_POST['service_id'] != '') $db->where('r.service_id', (int) filter_var($_POST['service_id'],FILTER_SANITIZE_NUMBER_INT) );
		if(isset($_POST['search_range']) && $_POST['search_range'] != ''){
			$dt = explode(' - ', $_POST['search_range']);
			$db->where('DATE(r.created_at) BETWEEN "'.$dt[0].'" AND "'.$dt[1].'"' );
		}
		if(isset($_POST['search_year']) && $_POST['search_year'] != '')
			$db->where('YEAR(r.created_at)',$_POST['search_year']);

		if(isset($_POST['search_schedule']) && $_POST['search_schedule'] != ''){
			$dt = explode(' - ', $_POST['search_schedule']);
			$db->where('(r.datetime_service1 BETWEEN "'.$dt[0].'" AND "'.$dt[1].'" OR '.
				'r.datetime_service2 BETWEEN "'.$dt[0].'" AND "'.$dt[1].'" OR '.
				'r.datetime_service3 BETWEEN "'.$dt[0].'" AND "'.$dt[1].'" OR '.
				'r.datetime_service4 BETWEEN "'.$dt[0].'" AND "'.$dt[1].'" OR '.
				'r.datetime_service5 BETWEEN "'.$dt[0].'" AND "'.$dt[1].'" OR '.
			')' );
		}
		if(isset($_POST['status']) && $_POST['status'] != '')
			$db->where(is_array($_POST['status']) ? 'r.status IN ('.implode(',',$_POST['status']).')' : 'r.status = '.$_POST['status'] );
		else $db->where('r.status > -1');
		if(isset($_POST['customer_name']) && $_POST['customer_name'] != '') $db->where('(c.firstname LIKE "%'.trim($_POST['customer_name']).'%" OR c.lastname LIKE "%'.trim($_POST['customer_name']).'%")');
		if(isset($_POST['serial']) && $_POST['serial'] != '') $db->where('(r.indoor_sn LIKE "%'.trim($_POST['serial']).'%" OR r.outdoor_sn LIKE "%'.trim($_POST['serial']).'%")');
		if(isset($_POST['customer_phone']) && $_POST['customer_phone'] != '') $db->where('c.phone LIKE "%'.trim($_POST['customer_phone']).'%"');
		if(isset($_POST['customer_email']) && $_POST['customer_email'] != '') $db->where('c.email LIKE "%'.trim($_POST['customer_email']).'%"');
		if(isset($_POST['service_type']) && $_POST['service_type'] != '') $db->where('r.service_type LIKE "%'.trim($_POST['service_type']).'%"');
		if(isset($_POST['requester_name']) && $_POST['requester_name'] != '') $db->where('r.requester_name LIKE "%'.trim($_POST['requester_name']).'%"');
		if(isset($_POST['requester_phone']) && $_POST['requester_phone'] != '') $db->where('r.requester_phone LIKE "%'.trim($_POST['requester_phone']).'%"');
		if(isset($_POST['product_model']) && $_POST['product_model'] != '') $db->where('r.product_model', $_POST['product_model'] );
		if(isset($_POST['error_code']) && $_POST['error_code'] != '') $db->where('r.error_code', $_POST['error_code'] );
		if(isset($_POST['problem_keyword']) && $_POST['problem_keyword'] != '') $db->where('r.description LIKE "%'.$_POST['problem_keyword'].'%"' );

		if($action == 'pending_repair_list'){
			$db->where('r.service_type', 'repair')
				->where('r.status IN (2,3,7)'); // on service and waiting a part
			$add_fields = '';
		}else if($action == 'schedule_repair_list'){
			$db->where('r.service_type', 'repair')
				->where('r.status IN (2,3,7)'); // on service and waiting a part
			$add_fields = '';
		}else if($action == 'service_job_list'){
			$db->where('r.status IN (4,5,6)'); // job done, complete or cancel
      $db->join('claim cl', 'cl.service_id = r.service_id','left')
				->join('service_rating sr', 'sr.service_id = r.service_id and sr.status=1','left');
			$add_fields = ',cl.expense_cost,cl.claim_id,cl.date_claim,sr.rate_star,sr.comment';
		}
		$db->orderBy('r.updated_at','desc');
		$db->groupBy('r.service_id')
			->join('technician t', 't.technician_id=r.technician_id',
				(($_SESSION['auth_roles'] <= 1 && $_SESSION['auth_sc_id'] != -1) ? '' : 'left'))
			->join('customer c', 'c.customer_id=r.customer_id', 'left');

		if($_SESSION['auth_roles'] <= 1 && $_SESSION['auth_sc_id'] != -1) { // match sc
			$db->where('(t.sc_id = '.$_SESSION['auth_sc_id'].' OR t.user_id = '.$_SESSION['auth_user_id'].')');
		}
		// Join Cause in detail
		if($action == 'serv_req_for_repair'){
			if(isset($_POST['error_cause']) && $_POST['error_cause'] != '') $db->where('rd.detail LIKE \'%error_cause":"'.utf8_unicode(trim($_POST['error_cause'])).'%\'');
			if(isset($_POST['tbl_cause']) && $_POST['tbl_cause'] != '') $db->where('rd.detail LIKE \'%tbl_cause":"'.utf8_unicode(trim($_POST['tbl_cause'])).'%\'');
			$db->join('service_request_detail rd', 'rd.service_id=r.service_id AND timeline_type LIKE "served"', 'left');

			// Search seller
			if(isset($_POST['seller']) && $_POST['seller'] != '') {
				$db->where('so.seller LIKE "%'.trim($_POST['seller']).'%"');
			}
// 05AUG24 ITPP -- BEGIN
			$db->join('stock_out_detail sod', 'sod.ref = r.indoor_sn', 'left');
			$db->join('stock_out_detail sod2', 'sod2.ref = r.outdoor_sn', 'left');
// 05AUG24 ITPP: $db->join('stock_out_detail sod', 'sod.ref = r.indoor_sn OR sod.ref = r.outdoor_sn', 'left');
// 05AUG24 ITPP -- END
			$db->join('stock_out so', 'sod.stock_out_id=so.stock_out_id', 'left');
// 05AUG24 ITPP -- BEGIN
			$db->join('product_serial s2', 's2.serial_no = r.indoor_sn', 'left');
			$db->join('product_serial s', 's.serial_no = r.outdoor_sn', 'left');
// 05AUG24 ITPP: $db->join('product_serial s', 's.serial_no = r.indoor_sn OR s.serial_no = r.outdoor_sn', 'left');
// 05AUG24 ITPP -- END
			// $db->join('product_serial s', 's.serial_id = r.serial_id OR s.serial_id = r.serial_id2', 'left');
		}
// 05AUG24 ITPP -- BEGIN
/*
		$list = $db->get('service_request r', 3000,
			($action == 'serv_req_for_repair'? 'rd.detail,DATE(so.datetime_pickup) as date_pickup,so.stock_out_id,so.seller,s.lot_no,s.line,s.sn_remote,s.sn_pipe,s.sn_test,s.sn_prop,s.sn_set,s.mfd,' : '').
			'r.*, CONCAT_WS("",t.firstname," ",t.lastname) as technician, t.technician_code, t.email as technician_email, t.phone as technician_phone, CONCAT_WS("",c.firstname," ",c.lastname) as customer'.$add_fields
		);
*/		
		$list = $db->get('service_request r', 3000,
 			($action == 'serv_req_for_repair'? 'rd.detail,DATE(so.datetime_pickup) as date_pickup,so.stock_out_id,so.seller,s.lot_no,s.line,s2.sn_remote,s.sn_pipe,s.sn_test,s.sn_prop,s.sn_set,s.mfd,' : '').
			'r.*, CONCAT_WS("",t.firstname," ",t.lastname) as technician, t.technician_code, t.email as technician_email, t.phone as technician_phone, CONCAT_WS("",c.firstname," ",c.lastname) as customer'.$add_fields
		);
// 05AUG24 ITPP -- END

		// Added claim sheet count
		// if($action == 'serv_req_for_repair'){
		// 	foreach ($list as $key => $v) {
		// 		$db->where('`service_id`', $v['service_id']);
		// 		$db->where('`detail` LIKE \'%"claim":%\'');
		// 		$cc = $db->getValue('service_request_detail', 'COUNT(*)');
		// 		$list[$key]['claim'] = @$cc > 0 ? true : false;
		// 	}
		// }
		// echo $db->getLastQuery().'<br/>'; // DEBUG
		echoJson(0, $list);
		break;

	/**
	 *	Service request search (select2)
	 *	Init 16/04/2021
	 */
	case 'serv_req_query':
		dbCon();
		if(!isset($_GET['q']) || $_GET['q'] == '') echoJson(2, 'Search keyword requried');
		$q = trim($_GET['q']);
		$db->where('(c.firstname LIKE "%'.$q.'%" OR c.lastname LIKE "%'.$q.'%"'.
		  ' OR c.phone LIKE "%'.$q.'%" OR c.email LIKE "%'.$q.'%"'.
		  ' OR c.remark LIKE "%'.$q.'%" OR c.customer_code LIKE "%'.$q.'%"'.
		  ' OR r.service_id LIKE "%'.$q.'%" OR r.product_model LIKE "%'.$q.'%"'.
		  ' OR r.other_model LIKE "%'.$q.'%" OR r.description LIKE "%'.$q.'%"'.
			' OR r.requester_name LIKE "%'.$q.'%" OR r.requester_phone LIKE "%'.$q.'%"'.
		')');
		$db->orderBy('r.updated_at','desc')
			->groupBy('r.service_id')
			->join('customer c', 'c.customer_id=r.customer_id', 'left')
			->join('technician t', 't.technician_id=r.technician_id',
				($_SESSION['auth_roles'] <= 1 && $_SESSION['auth_sc_id'] != -1) ? '' : 'left');

		if($_SESSION['auth_roles'] <= 1 && $_SESSION['auth_sc_id'] != -1) { // match sc
			$db->where('(t.sc_id = '.$_SESSION['auth_sc_id'].' OR t.user_id = '.$_SESSION['auth_user_id'].')');
		}
		$list = $db->get('service_request r', 100,'r.service_id, CONCAT_WS("",c.firstname," ",c.lastname) as customer, c.customer_id');
		// echo $db->getLastQuery().'<br/>'; // DEBUG
		echoJson(0, $list);
		break;


	/**
	 *	Stock Pickup search (select2)
	 *	Init 16/04/2021
	 */
	case 'pickup_query':
		dbCon();
		if(!isset($_GET['q']) || $_GET['q'] == '') echoJson(2, 'Search keyword requried');
		chkProfilePermit('stock_picking.html');
		$q = trim($_GET['q']);
		$db->where('(c.firstname LIKE "%'.$q.'%" OR c.lastname LIKE "%'.$q.'%"'.
		  ' OR c.phone LIKE "%'.$q.'%" OR c.email LIKE "%'.$q.'%"'.
		  ' OR c.remark LIKE "%'.$q.'%" OR c.customer_code LIKE "%'.$q.'%"'.
			' OR p.stock_out_id LIKE "%'.$q.'%" OR p.ref LIKE "%'.$q.'%"'.
		  ' OR p.remark LIKE "%'.$q.'%"'.
		')');
		$db->orderBy('p.updated_at','desc')
			->where('p.out_type', 'PICKUP')
			->groupBy('p.stock_out_id')
			->join('customer c', 'c.customer_id=p.customer_id', 'left');
		$list = $db->get('stock_out p', 100,'p.stock_out_id, CONCAT_WS("",c.firstname," ",c.lastname) as customer, c.customer_id');
		// echo $db->getLastQuery().'<br/>'; // DEBUG
		echoJson(0, $list);
		break;

	/**
	 *	Customer search (select2)
	 *	Init 16/04/2021
	 */
	case 'customer_query':
		dbCon();
		if(!isset($_GET['q']) || $_GET['q'] == '') echoJson(2, 'Search keyword requried');
		$q = trim($_GET['q']);
		$db->where('(c.firstname LIKE "%'.$q.'%" OR c.lastname LIKE "%'.$q.'%"'.
			' OR c.phone LIKE "%'.$q.'%" OR c.email LIKE "%'.$q.'%"'.
			' OR c.remark LIKE "%'.$q.'%" OR c.customer_code LIKE "%'.$q.'%"'.
		')');
		$db->orderBy('c.updated_at','desc')->groupBy('c.customer_id');
		$list = $db->get('customer c', 100,'c.customer_id, CONCAT_WS("",c.firstname," ",c.lastname," - ",c.email,"") as customer, c.customer_id');
		// echo $db->getLastQuery().'<br/>'; // DEBUG
		echoJson(0, $list);
		break;

	/**
	 *	Technician search (select2)
	 *	Init 19/04/2021
	 */
	case 'technician_query':
		dbCon();
		if(!isset($_GET['q']) || $_GET['q'] == '') echoJson(2, 'Search keyword requried');
		$q = trim($_GET['q']);
		$db->where('(t.firstname LIKE "%'.$q.'%" OR t.lastname LIKE "%'.$q.'%"'.
			' OR t.phone LIKE "%'.$q.'%" OR t.email LIKE "%'.$q.'%"'.
			' OR t.zone LIKE "%'.$q.'%" OR t.technician_code LIKE "%'.$q.'%"'.
			' OR t.remark LIKE "%'.$q.'%"'.
		')');
		$db->orderBy('t.updated_at','desc');
		$list = $db->get('technician t', 100,'t.technician_id, CONCAT_WS("",t.firstname," ",t.lastname, " - ", t.technician_code, " [", t.zone,"]") as technician');
		// echo $db->getLastQuery().'<br/>'; // DEBUG
		echoJson(0, $list);
		break;


	/**
	 *	Service Request with Job Repair
	 *	Init 13/04/2021
	 */
	case 'serv_req_detail':
		dbCon();
		if(!isset($_POST['service_id']) || $_POST['service_id'] == '') echoJson(2, 'Service ID Requried');
		chkProfilePermit('service_request.html');

		$db->where('r.service_id', $_POST['service_id']);
		if($_SESSION['auth_roles'] <= 1 && $_SESSION['auth_sc_id'] != -1) { // match sc
			$db->join('technician t', 't.technician_id=r.technician_id', 'left')
				->where('(t.sc_id = '.$_SESSION['auth_sc_id'].' OR t.user_id = '.$_SESSION['auth_user_id'].' OR r.technician_id = '.$_SESSION['auth_user_id'].')');
		}
		$req = $db->getOne('service_request r','r.*');
		$query = $db->getLastQuery();

		// Customer Profile
		$db->where('c.customer_id', @$req['customer_id']);
		$db->join('users u', 'u.id=c.user_id','left');
		$cus = $db->getOne('customer c','c.*,u.username,u.avatar');

		// Technicain Profile
		$tech = ($req['technician_id'] > 0) ? $db->where('t.technician_id',$req['technician_id'])
			->join('users u','t.user_id=u.id','left')
			->join('service_center s','s.sc_id=t.sc_id','left')
			->getOne('technician t','CONCAT_WS("",s.title," #",s.sc_code) as sc_title, u.username,t.sc_id,t.user_id,t.technician_code,t.type,t.firstname,t.lastname,t.phone,t.email,t.technician_id') : [];

		$warr = getWarranty($req['serial_id'], $req['indoor_sn'], $req['outdoor_sn'], true);

		$db->where('c.service_id', $_POST['service_id'])
			->groupBy('d.claim_did')
			->join('claim c','c.claim_id=d.claim_id','left')
			->join('stock_out_detail o','o.claim_did=d.claim_did','left')
			->join('stock_item s','s.item_id=d.item_id','left');
		$claim_sheet = $db->get('claim_detail d', 1000,'d.*,c.scheduled_at, o.stock_out_did, o.stock_out_id, s.qty as qty_remain');

		// Indoor Manufactor Info // + W/H OUT
		if(isset($req['indoor_sn'])) $sin = $db->where('s.serial_no',$req['indoor_sn'],'like')
			->orderBy('so.datetime_pickup,so.stock_out_id','desc')
			->join('stock_out_detail sod', '(sod.ref=s.serial_no OR sod.serial_id=s.serial_id)', 'left')
			->join('stock_out so', 'sod.stock_out_id=so.stock_out_id', 'left')
			->getOne('product_serial s','s.*,so.stock_out_id,so.datetime_pickup,so.remark as rmso,so.seller,so.updated_at as updatedso');
		else $sin = [];

		// Indoor Manufactor Info// + W/H OUT
		if(isset($req['outdoor_sn'])) $sout = $db->where('s.serial_no',$req['outdoor_sn'],'like')
			->orderBy('so.datetime_pickup,so.stock_out_id','desc')
			->join('stock_out_detail sod', '(sod.ref=s.serial_no OR sod.serial_id=s.serial_id)', 'left')
			->join('stock_out so', 'sod.stock_out_id=so.stock_out_id', 'left')
			->getOne('product_serial s','s.*,so.stock_out_id,so.datetime_pickup,so.remark as rmso,so.seller,so.updated_at as updatedso');
		else $sout = [];

		echoJson(0, [
			'serv' => $req,
			'customer' => $cus,
			'warranty' => $warr,
			'serial_in' => $sin,
			'serial_out' => $sout,
			'tech' => $tech,
			'claim_sheet' => $claim_sheet, // claim detail sheet
			'debug' => $query,
			'file_path' => PUBLIC_DOMAIN.APP_PATH.ARCHIVE_DIR_NAME.'/']);
		break;

	/**
	 *	Service Request detail of timeline
	 *	Init 26/04/2021
	 */
	case 'serv_req_detail_timeline':
		dbCon();
		if(!isset($_POST['service_id']) || $_POST['service_id'] == '') echoJson(2, 'Service ID Requried');
		chkProfilePermit('service_request.html');

		$db->where('d.service_id', $_POST['service_id'])
			->orderBy('d.updated_at', 'DESC')
			->join('technician t', 't.technician_id=d.technician_id', 'left')
			->join('users u', 'u.id=d.user_id', 'left');
		$dl = $db->get('service_request_detail d', 500,'d.*, CONCAT_WS("",t.firstname," ",t.lastname, " - [", t.technician_code,"]", " [", t.zone,"]") as technician, u.username, u.fullname');

		$db->where('r.service_id', $_POST['service_id'])
			->where('r.status', 1)
			->join('customer c','c.customer_id=r.customer_id','left');
		$rate = $db->get('service_rating r', 500,'r.*,CONCAT_WS("",c.firstname," ",c.lastname) as customer, c.phone, c.email');

		echoJson(0, [
			'detail' => $dl,
			'reviewed' => $rate,
			'file_path' => PUBLIC_DOMAIN.APP_PATH.ARCHIVE_DIR_NAME.'/']);
		break;

	/**
	 *	Service Request Save
	 *	Init 08/04/2021
	 */
	case 's_service_request':
		dbCon();
		if(!isset($_POST['id']) || $_POST['id'] == '') echoJson(2, 'Service ID Requried');
		chkProfilePermit('service_request.html', 'Y');
		$data = $_POST;
		if(isset($_POST['datetime_service1']) && $_POST['datetime_service1'] == '') unset($data['datetime_service1']);
		if(isset($_POST['datetime_service2']) && $_POST['datetime_service2'] == '') unset($data['datetime_service2']);
		if(isset($_POST['datetime_service3']) && $_POST['datetime_service3'] == '') unset($data['datetime_service3']);
		if(isset($_POST['datetime_service4']) && $_POST['datetime_service4'] == '') unset($data['datetime_service4']);
		if(isset($_POST['datetime_service5']) && $_POST['datetime_service5'] == '') unset($data['datetime_service5']);
		if(@$_POST['status'] == 4 || @$_POST['status'] == 6) $data['date_completed'] = date('Y-m-d H:i:s');

		unset($data['id']);
		$data['updated_at'] = date('Y-m-d H:i:s');
		if($_POST['id'] == 'new'){
			$data['created_at'] = date('Y-m-d H:i:s');
			$id = $db->insert('service_request', $data);
		}else{
			$db->where('r.service_id', $_POST['id']);
			$updated = $db->update('service_request r', $data);
			$id = ($updated) ? $_POST['id'] :0;

			// Sending notification
			$data = $db->where('service_id', $_GET['service_id'])->getOne('service_request', 'service_id,technician_id,user_id');
			if(isset($data['service_id']) && $data['service_id'] >0 ){
				$pk = 'SR'.fillZero($data['service_id'],8);
				$txt = 'มีอัปเดตใบขอรับบริการ #'.$pk;
				$rx_id = $_SESSION['auth_user_id'] != $data['technician_id']
				 ? $data['technician_id'] : $data['user_id'];
				setNoti($rx_id, $data['service_id'], 2, $txt);
			}
		}
		// echo $db->getLastQuery().'<br/>'; // DEBUG
		echoJson(0, $id);
		break;

	/**
	 *	Service Request Status Save
	 *	Init 26/04/2021
	 */
	case 's_service_request_status':
		dbCon();
		if(!isset($_GET['service_id']) || $_GET['service_id'] == '') echoJson(2, 'Service ID Requried');
		if(!isset($_POST['value']) || $_POST['value'] == '') echoJson(2, 'Status Requried');
		chkProfilePermit('service_request.html', 'Y');
		$data['status'] = $_POST['value'];
		if($data['status'] == 4 || $data['status'] == 6) $data['date_completed'] = date('Y-m-d H:i:s');

		$data['updated_at'] = date('Y-m-d H:i:s');
		$db->where('r.service_id', $_GET['service_id']);
		$updated = $db->update('service_request r', $data);
		$id = ($updated) ? $_POST['value'] :0;

		// Sending notification
		$data = $db->where('r.service_id', $_GET['service_id'])->getOne('service_request r');
		if($id > 0 && isset($data['requester_email']) && $data['requester_email'] != ''){
			// print_r($data);
			$arr_status_txt = ['รอนัดหมาย', 'รอเข้าให้บริการ','รอนัดหมายเพื่อเข้าให้บริการอีกครั้ง', 'กำลังให้บริการ', 'ซ่อมงานเสร็จ', 'ยกเลิกงานโดยศูนย์บริการ', 'ปิดงานโดยสมบูรณ์', 'รออะไหล่'];
			$txt = 'ปรับปรุงสถานะของการขอรับบริการ #SR'.fillZero($data['service_id'],8).' เป็น `'.@$arr_status_txt[$_POST['value']].'`';
			// To requester
			mailSerReq($pk, $data, '<h3>'.$txt.'</h3> ขอบพระคุณ');
			setNoti($data['customer_id'], $data['service_id'], 2,  $txt);
			// To Staff
			$rx_id = $_SESSION['auth_user_id'] != $data['technician_id']
			 ? $data['technician_id'] : $data['user_id'];
			setNoti($rx_id, $data['service_id'], 2, $txt);
		}
		// echo $db->getLastQuery().'<br/>'; // DEBUG
		echoJson(0, $id);
		break;

	/**
	 *	Service Request Remark Save
	 *	Init 11/05/2021
	 */
	case 's_service_request_remark':
		dbCon();
		if(!isset($_GET['service_id']) || $_GET['service_id'] == '') echoJson(2, 'Service ID Requried');
		if(!isset($_POST['value']) || $_POST['value'] == '') echoJson(2, 'Note Requried');
		chkProfilePermit('service_request.html', 'Y');
		$rm = $_POST['value'];
		$data['r.staff_note'] = $rm;
		$data['r.updated_at'] = date('Y-m-d H:i:s');
		$db->where('r.service_id', $_GET['service_id']);
		$updated = $db->update('service_request r', $data);
		$id = ($updated) ? $_GET['service_id'] :0;
		// echo $db->getLastQuery().'<br/>'; // DEBUG

		// Sending notification
		$data = $db->where('r.service_id', $id)->getOne('service_request r', 'r.service_id,r.technician_id,r.user_id');
		if(isset($data['service_id']) && $data['service_id'] > 0){
			$txt = 'Remark ใบขอรับบริการ : '.trim(preg_replace('/\s+/', ' ', $rm));
			// To Staff
			$rx_id = $_SESSION['auth_user_id'] != $data['technician_id']
			 ? $data['technician_id'] : $data['user_id'];
			setNoti($rx_id, $data['service_id'], 2, $txt);
		}
		echoJson(0, $id);
		break;

	/**
	 *	Service Request Detail Remove for all type of timeline
	 *	Init 26/04/2021
	 */
	case 'x_ss_tl':
		dbCon();
		if(!isset($_POST['pk_id']) || $_POST['pk_id'] == 0) echoJson(2, 'Service ID Requried');
		chkProfilePermit('service_request.html', 'Y');
		$db->where('sr_detail_id', $_POST['pk_id']);
		$updated = $db->delete('service_request_detail');
		$id = ($updated) ? $_POST['pk_id'] :0;
		echoJson(0, $id);
		break;


	/**
	 *	Service Request - Comment Save
	 *	Init 23/04/2021
	 */
	case 's_ss_comment':
		if(!isset($_POST['service_id']) || $_POST['service_id'] == '') echoJson(2, 'Service ID Requried');
		if(!isset($_POST['comment_txt']) || $_POST['comment_txt'] == '') echoJson(3, 'Comment Requried');
		dbCon();
		chkProfilePermit('service_request.html', 'Y');
		$data = array();
		$data['service_id'] = $_POST['service_id'];
		$data['detail'] = $_POST['comment_txt'];
		$data['timeline_type'] = 'comment';
		$data['technician_id'] = @$_SESSION['auth_user_id'];
		$data['user_id'] = @$_SESSION['auth_user_id'];
		$data['created_at'] = date('Y-m-d H:i:s');
		$data['updated_at'] = date('Y-m-d H:i:s');
		$id = $db->insert('service_request_detail', $data);
		// echo $db->getLastQuery().'<br/>'; // DEBUG

		$db->where('service_id', $_POST['service_id'])
			->update('service_request', ['updated_at' => date('Y-m-d H:i:s')]);

		// Sending notification
		$data = $db->where('r.service_id', $_POST['service_id'])->getOne('service_request r', 'r.service_id,r.technician_id,r.user_id');
		if(isset($data['service_id']) && $data['service_id'] > 0){
			$txt = 'มีความเห็นภายในใบขอรับบริการ #SR'.fillZero($data['service_id'],8);
			setNoti( $data['user_id'] != $_SESSION['auth_user_id']
				? $data['user_id']
				: $data['technician_id'], $data['service_id'], 2, $txt);
		}
		echoJson(0, $id);
		break;

	/**
	 *	Service Request - Save the Schedule 1-5
	 *	Init 24/04/2021
	 */
	case 's_ss_appoint':
		if(!isset($_POST['service_id']) || $_POST['service_id'] == '') echoJson(2, 'Service ID Requried');
		if(!isset($_POST['datetime_service1']) &&
			!isset($_POST['datetime_service2']) &&
			!isset($_POST['datetime_service3']) &&
			!isset($_POST['datetime_service4']) &&
			!isset($_POST['datetime_service5'])
			) echoJson(2, 'Appointment Date Requried');
		dbCon();
		chkProfilePermit('service_request.html', 'Y');
		$data = array();
		$sid = $_POST['service_id'];

		$data['service_id'] = $_POST['service_id'];
		if(isset($_POST['datetime_service1'])) {$data['datetime_service1'] = $_POST['datetime_service1']; $scat = 1;}
		if(isset($_POST['datetime_service2'])) {$data['datetime_service2'] = $_POST['datetime_service2']; $scat = 2;}
		if(isset($_POST['datetime_service3'])) {$data['datetime_service3'] = $_POST['datetime_service3']; $scat = 3;}
		if(isset($_POST['datetime_service4'])) {$data['datetime_service4'] = $_POST['datetime_service4']; $scat = 4;}
		if(isset($_POST['datetime_service5'])) {$data['datetime_service5'] = $_POST['datetime_service5']; $scat = 5;}
		if(isset($_POST['technician_id'])) $data['technician_id'] = $_POST['technician_id'];

		$data['status'] = 1; // รอเข้าให้บริการ
		$data['updated_at'] = date('Y-m-d H:i:s');
		$res = $db->where('service_id', $sid)
			->update('service_request', $data);
		$id = ($res) ? $sid : 0;

		// Save note to timeline
		$tname = (isset($_POST['technician_id']))
			? $db->where('t.technician_id', $_POST['technician_id'])->getValue('technician t', 'CONCAT_WS("",t.firstname," ",t.lastname, " [", t.technician_code,"]")')
			: '(ไม่พบชื่อช่าง)';
		$data = array();
		$data['service_id'] = $sid;
		$note = 'บันทึกนัดหมายเข้าให้บริการ ครั้งที่ '.$scat. '<br/>ชื่อช่างผู้ให้บริการ : '.$tname.'<br/>วัน/เวลา : '.@$_POST['datetime_service'.$scat];
		$data['detail'] = $note;
		$data['timeline_type'] = 'appointed';
		$data['user_id'] = @$_SESSION['auth_user_id'];
		if(isset($_POST['technician_id'])) $data['technician_id'] = $_POST['technician_id'];
		$data['schedule_at'] = $scat;
		$data['created_at'] = date('Y-m-d H:i:s');
		$data['updated_at'] = date('Y-m-d H:i:s');
		$db->insert('service_request_detail', $data);

		// Sending email notification to technician & customer
		$pk = 'SR'.fillZero($id,8);
		$data = $db->where('r.service_id', $id)
			->join('technician t', 't.technician_id=r.technician_id','left')
			->getOne('service_request r', 'r.*, CONCAT_WS("",t.firstname," ",t.lastname) as technician, t.technician_id, t.email as technician_email');
		// print_r($data);
		$txt = 'คุณได้รับการบันทึกนัดหมายงานขอรรับบริการ #'.$pk;
		if(isset($data['requester_email']) && $data['requester_email'] != ''){
			mailSerReq($pk, $data, '<h3>'.$note.'</h3> ขอบพระคุณ', false); // customer
			setNoti($data['user_id'], $data['service_id'], 2, $txt); // customer
		}

		if(isset($data['technician_email']) && $data['technician_email'] != ''){
			mailSerReq($pk, $data, '<h3>'.$note.'</h3> ขอบพระคุณ', true); // tech
			setNoti($data['technician_id'], $data['service_id'], 2, $txt);
		}

		// echo $db->getLastQuery().'<br/>'; // DEBUG
		echoJson(0, $id);
		break;

	/**
	 *	Service Request - Served detail save
	 *	Init 24/04/2021
	 */
	case 's_ss_served':
		if(!isset($_POST['service_id']) || $_POST['service_id'] == '') echoJson(2, 'Service ID Requried');
		if(!isset($_POST['detail_json']) || $_POST['detail_json'] == '') echoJson(3, 'Detail Requried');
		if(!isset($_POST['schedule_at']) || $_POST['schedule_at'] == '') echoJson(4, 'Schedule Number Requried');
		dbCon();
		chkProfilePermit('service_request.html', 'Y');
		$data = array();
		$data['service_id'] = $_POST['service_id'];
		$data['detail'] = json_encode($_POST['detail_json']);
		$data['timeline_type'] = 'served';
		$data['served_start'] = @$_POST['served_start'];
		$data['served_end'] = @$_POST['served_end'];
		if(isset($_POST['technician_id'])) $data['technician_id'] = $_POST['technician_id'];
		$data['schedule_at'] = @$_POST['schedule_at'];
		$data['user_id'] = @$_SESSION['auth_user_id'];
		$data['created_at'] = date('Y-m-d H:i:s');
		$data['updated_at'] = date('Y-m-d H:i:s');
		$id = $db->insert('service_request_detail', $data);
		// echo $db->getLastQuery().'<br/>'; // DEBUG

		$db->where('service_id', $_POST['service_id'])
			->update('service_request', ['updated_at' => date('Y-m-d H:i:s')]);

		// Sending notification to staff
		$data = $db->where('r.service_id', $_POST['service_id'])->getOne('service_request r');
		if(@$data['service_id'] > 0){
			$pk = 'SR'.fillZero($data['service_id'],8);
			$txt = 'มีบันทึกงานให้บริการ #'.$pk;
			setNoti( $data['user_id'] != $_SESSION['auth_user_id']
				? $data['user_id']
				: $data['technician_id'], $data['service_id'], 2, $txt);
		}
		echoJson(0, $id);
		break;

		/**
		 *	Upload Attachement to Served by technician
		 *	Init 27/04/2021
		 */
		case 'serv_req_served_file':
			// print_r($_FILES);
	    if(!isset($_FILES) || !isset($_FILES['attach_photo'])) echoJson(2, 'ข้อมูลไม่ครบถ้วน');
	    $key = genRandStr(4);
	    $filename = @'at_'.$key.'_'.hyphenize($_FILES['attach_photo']['name']);
	    $_SESSION['_file_name_'] = $filename;
			$save_file_path = ROOT_PATH.ARCHIVE_FILE.'served/'.$filename;
	    move_uploaded_file($_FILES['attach_photo']['tmp_name'], $save_file_path);
	    echoJson(0, ['file' => PUBLIC_DOMAIN.APP_PATH.ARCHIVE_DIR_NAME.'/served/'.$filename]);
	    break;

	/**
	 *	Service Request - Repair detail save
	 *	Init 25/04/2021
	 */
	case 's_ss_fixed':
		if(!isset($_POST['service_id']) || $_POST['service_id'] == '') echoJson(2, 'Service ID Requried');
		if(!isset($_POST['detail_json']) || $_POST['detail_json'] == '') echoJson(3, 'Detail Requried');
		if(!isset($_POST['schedule_at']) || $_POST['schedule_at'] == '') echoJson(4, 'Schedule Number Requried');
		dbCon();
		chkProfilePermit('service_request.html', 'Y');
		$data = array();
		$data['service_id'] = $_POST['service_id'];
		$data['detail'] = json_encode($_POST['detail_json']);
		$data['timeline_type'] = 'fixed';
		if(isset($_POST['technician_id'])) $data['technician_id'] = $_POST['technician_id'];
		$data['schedule_at'] = @$_POST['schedule_at'];
		$data['user_id'] = @$_SESSION['auth_user_id'];
		$data['created_at'] = date('Y-m-d H:i:s');
		$data['updated_at'] = date('Y-m-d H:i:s');
		$id = $db->insert('service_request_detail', $data);

		// Save the claim
		if($id > 0 && isset($_POST['detail_json']['claim'])){
			$data = [
				'claim_id' => 'new',
				'service_id' => $data['service_id'],
				'scheduled_at' => @$_POST['schedule_at'],
				'technician_id' => @$_SESSION['auth_user_id'],
				'date_claim' => date('Y-m-d')
			];
			// ค่าซ่อม
			if($_POST['repair_cost'] > 0) $_POST['detail_json']['claim'][] = [
				"item_code" => "001","qty"=> "1","cost"=> $_POST['repair_cost'],"qty_unit"=> '-',
				"item_type"=> 'ค่าซ่อม',"total"=> $_POST['repair_cost'],"item_text"=> 'ค่าซ่อม '.$_POST['repair_txt']];
			// ค่าบริการ
			if($_POST['service_cost'] > 0) $_POST['detail_json']['claim'][] = [
				"item_code" => "001","qty"=> "1","cost"=> $_POST['service_cost'],"qty_unit"=> '-',
				"item_type"=> 'ค่าบริการ',"total"=> $_POST['service_cost'],"item_text"=> 'ค่าบริการ'];
			// ชดเช่ยค่าเดินทาง
			if($_POST['transport_cost'] > 0) $_POST['detail_json']['claim'][] = [
				"item_code" => "001","qty"=> "1","cost"=> $_POST['transport_cost'],"qty_unit"=> '-',
				"item_type"=> 'ชดเช่ยค่าเดินทาง',"total"=> $_POST['transport_cost'],"item_text"=> 'ชดเช่ยค่าเดินทาง : '.$_POST['transport_cost_txt'].' กม.'];
			// อื่นๆ
			if($_POST['other_cost'] > 0) $_POST['detail_json']['claim'][] = [
				"item_code" => "001","qty"=> "1","cost"=> $_POST['other_cost'],"qty_unit"=> '-',
				"item_type"=> 'อื่นๆ',"total"=> $_POST['other_cost'],"item_text"=> 'อื่นๆ : '.$_POST['other_cost_txt'].' กม.'];


			$claim_id = saveClaim($data, $_POST['detail_json']['claim']);
		}

		// Sending notification to staff
		$data = $db->where('r.service_id', $_POST['service_id'])->getOne('service_request r');
		if($id > 0  && @$data['service_id'] > 0){
			$pk = 'SR'.fillZero($data['service_id'],8);
			$txt = 'มีบันทึกงานซ่อมคำขอฯ #'.$pk;
			setNoti( $data['user_id'] != $_SESSION['auth_user_id']
				? $data['user_id']
				: $data['technician_id'], $data['service_id'], 2, $txt);
		}
		// echo $db->getLastQuery().'<br/>'; // DEBUG
		echoJson(0, $id);
		break;

		/**
		 *	Claim Save
		 *	Init 19/04/2021
		 */
		case 's_claim':
			dbCon();
			if(!isset($_POST['id']) || $_POST['id'] == '') echoJson(2, 'Claim ID Requried');
			chkProfilePermit('claim.html', 'Y');
			$data = $_POST;
			$data['claim_id'] = $data['id'];
			unset($data['id'],$data['detail']);
			$id = saveClaim($data, $_POST['detail']);
			echoJson(0, $id);
			break;

		/**
		 *	Change status to `ready to service`
		 *	Init 20/04/2021
		 */
		case 'b_ready_service':
			dbCon();
			if(!isset($_POST['pk_id']) || $_POST['pk_id'] == '') echoJson(2, 'Service ID Requried');
			chkProfilePermit('report_repair.html', 'Y');
			$db->where('r.service_id', $_POST['pk_id']);
			$updated = $db->update('service_request r', ['status' => 2]); // 2 = รอนัดหมายเพื่อเข้าให้บริการอีกครั้ง
			$id = ($updated) ? $_POST['id'] :0;
			echoJson(0, $id);
			break;

		/**
		 *	Change status to cancelled by SC
		 *	Init 20/04/2021
		 */
		case 'c_serv_req':
			dbCon();
			if(!isset($_POST['pk_id']) || $_POST['pk_id'] == '') echoJson(2, 'Service ID Requried');
			chkProfilePermit('report_repair.html', 'Y');
			$db->where('r.service_id', $_POST['pk_id']);
			$updated = $db->update('service_request r', ['status' => 5]); // 5 = ยกเลิกงานโดยศูนย์บริการ
			$id = ($updated) ? $_POST['id'] :0;
			echoJson(0, $id);
			break;

	/**
	 *	Stock Pickup Save
	 *	Init 16/04/2021
	 */
	case 's_stock_picking':
		dbCon();
		if(!isset($_POST['id']) || $_POST['id'] == '') echoJson(2, 'Stock Out ID Requried');
		chkProfilePermit('stock_receive.html', 'Y');
		$data = $_POST;
		$data['stock_out_id'] = $_POST['id'];
		unset($data['detail']);
		$id = saveStockPickup($data, $_POST['detail']);
		echoJson(0, $id);
		break;

	/**
	 *	Remove a stock receive
	 *	Init 26/07/2021
	 */
	case 'x_stock_receive':
		dbCon();
		if(!isset($_POST['pk_id']) || $_POST['pk_id'] == 0) echoJson(2, 'ID Requried');
		chkProfilePermit('stock_receive.html', 'Y');
		$db->where('stock_in_id', $_POST['pk_id']);
		$del = $db->delete('stock_in_detail');
		if($del) {
			$db->where('stock_in_id', $_POST['pk_id']);
			$del2 = $db->delete('stock_in');
		}
		$id = ($del && $del2) ? $_POST['pk_id'] :0;
		echoJson(0, $id);
		break;

	/**
	 *	Remove a stock picking
	 *	Init 26/07/2021
	 */
	case 'x_stock_picking':
		dbCon();
		if(!isset($_POST['pk_id']) || $_POST['pk_id'] == 0) echoJson(2, 'ID Requried');
		chkProfilePermit('stock_receive.html', 'Y');
		$db->where('stock_out_id', $_POST['pk_id']);
		$updated = $db->update('stock_out', array('status' => 0));
		$db->where('stock_out_id', $_POST['pk_id'])
			->update('stock_out_detail', array('mark' => 1));
		$id = ($updated) ? $_POST['pk_id'] :0;
		echoJson(0, $id);
		break;

	/**
	 *	Stock Pickup in Service Request Detail (after a claim)
	 *	Init 02/05/2021
	 */
	case 's_stock_out_by_serv_detail':
		dbCon();
		if(!isset($_POST['stock_out_id']) || $_POST['stock_out_id'] == '') echoJson(2, 'Stock Out ID Requried');
		if(!isset($_POST['service_id']) || $_POST['service_id'] == '') echoJson(2, 'Service ID Requried');
		if(!isset($_POST['to_out']) || !is_array($_POST['to_out'])) echoJson(3, 'Not found item to pickup');
		chkProfilePermit('service_request.html', 'Y');
		$data = $_POST;
		unset($data['to_out']);
		$id = saveStockPickup($data, $_POST['to_out']);
		echoJson(0, $id);
		break;



	/**
	 *	Stock Receive Save
	 *	Init 16/04/2021
	 */
	case 's_stock_receive':
		dbCon();
		if(!isset($_POST['id']) || $_POST['id'] == '') echoJson(2, 'Receive ID Requried');
		if(!isset($_POST['in_type']) || $_POST['in_type'] == '') echoJson(2, 'STOCK-IN TYPE Requried');
		chkProfilePermit('stock_receive.html', 'Y');
		$data = $_POST;
		emptyNum($data['stock_out_id']);
		emptyNum($data['sc_id']);
		emptyNum($data['customer_id']);
		emptyNum($data['status']);
		if($data['date_act'] == '') unset($data['date_act']);
		$data['stock_in_id'] = $_POST['id'];
		unset($data['id'],$data['detail']);
		$id = saveStockReturn($data['in_type'], $data, $_POST['detail']);
		echoJson(0, $id);
		break;


	/**
	 *	Service Request Tracking (3=on work)
	 *	Init 12/04/2021
	 */
	case 'serv_req_tracking':
		dbCon();
		chkProfilePermit('service_tracking.html');
		if(isset($_POST['search_text']) && strlen($_POST['search_text']) > 1) {
			$q = trim($_POST['search_text']);
			$db->where('(r.product_model LIKE "%'.$q.'%" OR r.device_cateory LIKE "%'.$q.'%" '
				.'OR r.indoor_sn LIKE "%'.$q.'%" OR r.outdoor_sn LIKE "%'.$q.'%" '
				.'OR r.description LIKE "%'.$q.'%" OR r.requester_name LIKE "%'.$q.'%" '
				.'OR r.requester_phone LIKE "%'.$q.'%" OR r.requester_email LIKE "%'.$q.'%" '
				.'OR r.requester_org_name LIKE "%'.$q.'%" OR r.address_no LIKE "%'.$q.'%" '
				.'OR r.address_building LIKE "%'.$q.'%" OR r.address_road LIKE "%'.$q.'%" '
				.'OR r.address_subdistrict LIKE "%'.$q.'%" OR r.address_district LIKE "%'.$q.'%" '
				.'OR r.address_province LIKE "%'.$q.'%" OR r.address_postcode LIKE "%'.$q.'%" '
				.'OR t.firstname LIKE "%'.$q.'%" OR t.lastname LIKE "%'.$q.'%")');
		}

		$dateQuery = '(CURDATE() = DATE(r.datetime_service1)'
			.' OR CURDATE() = DATE(r.datetime_service2)'
			.' OR CURDATE() = DATE(r.datetime_service3)'
			.' OR CURDATE() = DATE(r.datetime_service4)'
			.' OR CURDATE() = DATE(r.datetime_service5))';
		$db->orderBy('r.updated_at','desc')->groupBy('r.service_id')
			->where($dateQuery)
			->where('r.status IN (1,2,3,4,5)') // on process
			->join('technician t', 't.technician_id=r.technician_id',
				($_SESSION['auth_roles'] <= 1 && $_SESSION['auth_sc_id'] != -1) ? '' : 'left');

		if($_SESSION['auth_roles'] <= 1 && $_SESSION['auth_sc_id'] != -1) { // match sc
			$db->where('(t.sc_id = '.$_SESSION['auth_sc_id'].' OR t.user_id = '.$_SESSION['auth_user_id'].')');
		}
		$list = $db->get('service_request r', 100,'r.*, CONCAT_WS("",t.firstname," ",t.lastname, " [", t.technician_code,"]", " [", t.zone,"]") as technician');
		// Get technician list
		foreach ($list as $inx => $v) {
			$db->where('g.technician_id', $v['technician_id'])->orderBy('g.updated_at');
			$last = $db->getOne('service_track_gps g','g.latlng,g.updated_at');
			$list[$inx]['tech_latlng'] = @$last['latlng'];
			$list[$inx]['tech_loc_at'] = @$last['updated_at'];
		}

		// Last Position (incompleted) 24/07/2021
		$db->groupBy('t.technician_id')
		->join('users u', 'u.id=g.user_id')
		->join('technician t', 't.technician_id=u.id');
		$track = $db->get('service_track_gps g', 500,'g.*, CONCAT_WS("",t.firstname," ",t.lastname) as technician, t.technician_id');
		echoJson(0, array('list' => $list, 'track' => $track));
		break;

	/**
	 *	Warranty Register list
	 *	Init 10/04/2021
	 */
	case 'product_register_list':
		dbCon();
		chkProfilePermit('warranty_check.html');
		if(isset($_POST['status']) && $_POST['status'] != ''){
			$db->where(is_array($_POST['status']) ? 'r.status IN ('.implode(',',$_POST['status']).')' : 'r.status = '.$_POST['status'] );
		}
		if(isset($_POST['search_range']) && $_POST['search_range'] != ''){
			$dt = explode(' - ', $_POST['search_range']);
			$db->where('DATE(r.created_at) BETWEEN "'.$dt[0].'" AND "'.$dt[1].'"' );
		}
		if(isset($_POST['customer_name']) && $_POST['customer_name'] != ''){
			$db->where('r.customer_name LIKE "%'.$_POST['customer_name'].'%"' );
		}
		if(isset($_POST['customer_phone']) && $_POST['customer_phone'] != ''){
			$db->where('r.customer_phone LIKE "%'.$_POST['customer_phone'].'%"' );
		}
		if(isset($_POST['product_model']) && $_POST['product_model'] != ''){
			$db->where('(r.product_model LIKE "%'.$_POST['product_model'].'%" OR r.other_model LIKE "%'.$_POST['product_model'].'%")' );
		}
		if(isset($_POST['serial']) && $_POST['serial'] != ''){
			$db->where('(r.indoor_sn LIKE "%'.$_POST['serial'].'%" OR r.outdoor_sn LIKE "%'.$_POST['serial'].'%")' );
		}
		if(isset($_POST['keyword']) && $_POST['keyword'] != ''){
			$q = trim($_POST['keyword']);
			$db->where('(l.date_claim LIKE "%'.$q.'%" OR l.expense_cost LIKE "%'.$q.'%"'.
			  ' OR l.item_no LIKE "%'.$q.'%" OR l.remark LIKE "%'.$q.'%"'.
			')');
		}
		if(isset($_POST['register_id']) && $_POST['register_id'] != "") $db->where('r.register_id', (int) filter_var($_POST['register_id'],FILTER_SANITIZE_NUMBER_INT) );

		$db->orderBy('r.updated_at','desc')->groupBy('r.register_id')
			->join('product_serial s','s.serial_id=r.serial_id','left');
		// if($_SESSION['auth_roles'] <= 1 && $_SESSION['auth_sc_id'] != -1) { // match sc
		// 	$db->join('technician t','t.technician_code=r.technician_code')
		// 		->where('t.sc_id', $_SESSION['auth_sc_id']);
		// }
		$list = $db->get('product_register r',
			isset($_POST['limit']) ? (int)$_POST['limit'] : 50,
			'r.*,s.mfd,s.lot_no');
		echoJson(0, $list);
		break;

	/**
	 *	Number of Product Registers
	 *	Init 28/10/2021
	 */
	case 'product_register_count':
		dbCon();
		chkProfilePermit('warranty_check.html');
		$cc = $db->getValue('product_register', 'COUNT(*)');
		echoJson(0, ['no_reg' => $cc]);
		break;

	/**
	 *	Service Petty Cash
	 *	Init 13/09/2021
	 */
	case 'service_pettycash_list':
		dbCon();
		chkProfilePermit('service_pettycash.html');
		if(isset($_POST['search_range']) && $_POST['search_range'] != ''){
			$dt = explode(' - ', $_POST['search_range']);
			$db->where('DATE(p.date_issue) BETWEEN "'.$dt[0].'" AND "'.$dt[1].'"' );
		}

		if(isset($_POST['category']) && $_POST['category'] != '')$db->where('p.category', $_POST['category'],'like');

		if(isset($_POST['keyword']) && $_POST['keyword'] != ''){
			$q = trim($_POST['keyword']);
			$db->where('(p.item LIKE "%'.$q.'%" OR p.cost LIKE "%'.$q.'%"'.
			')');
		}
		if(isset($_POST['filter_id']) && $_POST['filter_id'] != ""){
			$db->where('p.sptc_id', (int) filter_var($_POST['filter_id'],FILTER_SANITIZE_NUMBER_INT) );
		}

		$db->orderBy('p.updated_at','desc')->join('users u','u.id=p.user_id','left');
		$list = $db->withTotalCount()->get('service_pettycash p',
			isset($_POST['limit']) ? (int)$_POST['limit'] : 50,
			'p.*,u.username,u.fullname,u.avatar');
			// echo $db->getLastQuery().'<br/>'; // DEBUG
		echoJson(0, ['list' => $list, 'count' => $db->totalCount]);
		break;


		/**
		 *	Service Petty Cash Save
		 *	Init 13/09/2021
		 */
		case 's_service_pettycash':
			if(!isset($_POST['id']) || $_POST['id'] == '') echoJson(2, 'Service Pettycash ID Requried');
			else if(!isset($_POST['item']) || $_POST['item'] == '') echoJson(3, 'Item Requried');
			else if(!isset($_POST['cost']) || $_POST['cost'] == 0) echoJson(4, 'Cost Requried');
			else if(!isset($_POST['date_issue']) || $_POST['date_issue'] == '') echoJson(5, 'Issued Date Requried');
			dbCon();
			chkProfilePermit('service_pettycash.html', 'Y');
			$data = $_POST;
			$data['unit'] = 'บาท';

			unset($data['id']);
			$data['updated_at'] = date('Y-m-d H:i:s');
			if($_POST['id'] == 'new'){
				$data['created_at'] = date('Y-m-d H:i:s');
				$id = $db->insert('service_pettycash', $data);
			}else{
				$db->where('p.sptc_id', $_POST['id']);
				$updated = $db->update('service_pettycash p', $data);
				$id = ($updated) ? $_POST['id'] :0;
			}
			// echo $db->getLastQuery().'<br/>'; // DEBUG
			echoJson(0, $id);
			break;


 	/**
 	 *	Warranty Register Save
 	 *	Init 10/04/2021
 	 */
	case 's_product_register':
		if(!isset($_POST['id']) || $_POST['id'] == '') echoJson(2, 'Service ID Requried');
		dbCon();
		chkProfilePermit('warranty_check.html', 'Y');
		$data = $_POST;
		if(isset($_POST['date_installed']) && $_POST['date_installed'] == '') unset($data['date_installed']);

		unset($data['id']);

		// Get User Id
		if(isset($data['customer_phone']) && $data['customer_phone'] != ''){
			$db->where('phone', trim($data['customer_phone']), 'like')
				->where('phone_validated', 1);
			$uid = $db->getValue('users','id');
			if($uid > 0) $data['user_id'] = $uid;
		}

		$data['updated_at'] = date('Y-m-d H:i:s');
		if($_POST['id'] == 'new'){
			$data['created_at'] = date('Y-m-d H:i:s');
			$id = $db->insert('product_register', $data);
		}else{
			$db->where('r.register_id', $_POST['id']);
			$updated = $db->update('product_register r', $data);
			$id = ($updated) ? $_POST['id'] :0;

			// Send mail to customer
			if($id > 0){
				$data = $db->where('r.register_id', $id)->getOne('product_register r');
				$pk = 'RG'.fillZero($id,8);
				if(isset($data['customer_email']) && $data['customer_email'] != ''){
					// print_r($data);
					$arr_status_txt = ['รอตรวจสอบ', 'รายการถูกต้อง', 'รายการไม่ถูกต้อง', 'ยกเลิกรายการ'];
					$txt = 'เจ้าหน้าที่ได้ตรวจสอบการลงทะเบียนของท่านแล้ว `'.@$arr_status_txt[$data['status']].'`';
					mailWarranty($pk, $data, '<h3>'.$txt.'</h3>');
					setNoti($data['customer_id'], $data['register_id'], 1, $txt);
				}
			}
		}
		// echo $db->getLastQuery().'<br/>'; // DEBUG
		echoJson(0, $id);
		break;

	/**
	 *	Remove User Register Products, and return `reward point`
	 *	Init 22/07/2021
	 *	Modify 21/11/2022, 25/02/2023
	 */
	 case 'x_register_prod':
		if(!isset($_POST['pk_id']) || $_POST['pk_id'] == '') echoJson(2, 'Product Register ID requied');
		dbCon();
		chkProfilePermit('warranty_check.html', 'Y');

		// IF Technicain reward!
		$db->where('r.register_id', $_POST['pk_id'])
			->join('reward rw', 'r.serial_id=rw.serial_id AND rw.status = 1','left');
		$rw = $db->getOne('product_register r', 'rw.reward_id,rw.user_id,rw.point_taken,rw.technician_id,r.register_id,rw.status as rw_status');
		$rw['point_taken'] = (float) $rw['point_taken'];
		if(@$rw['point_taken'] > 0 && $rw['reward_id'] > 0){
			// Check point if already user, avoid a deficition (pts < 0)
			$pts = getPoints('', $rw['user_id'], true);
			if($pts['balance'] - (float) $rw['point_taken'] < 0)
				echoJson(time(), 'คะแนนของผู้ใช้งานคนนี้ไม่เพียงพอในการเรียกคะแนนคืน หรืออาจจะถูกใช้งานแลกของรางวัลไปแล้ว คะแนนเรียกคืน '.$rw['point_taken'].'คะแนน'."\n".'(ปัจจุบันมี '.$pts['balance'].' คะแนน )',$rw);

			// Deduct point suddenly, if reject will be withdrawal
			$log_id = rewardRecord($rw['user_id'], $rw['point_taken'] * (-1),
				['redeem_id' => -1, 'technician_id' => $rw['technician_id'], 'approved' => true] // redeem_id = -1 meant cancel
			);

			if(empty($log_id) || $log_id == 0){
				echoJson(time(), 'ไม่สามารถหักคะแนนคืนได้', $log_id);
			}else{
				// Cancel REWARD
				$db->where('reward_id', $rw['reward_id'])->update('reward',
					['status' => 3, 'updated_at' => date('Y-m-d H:i:s')]); // 3 = ยกเลิกสะสม, global.js
			}

		}else if(@$rw['register_id'] == 0) echoJson(5, 'ไม่พบรายการลงทะเบียน', $rw);

		$db->where('register_id', $_POST['pk_id']);
		$res = $db->delete('product_register');

			// Cancel logging if not deleted
			if($res == false && $log_id > 0){
				$db->where('rw_point_id', $log_id)->update('reward_point_history',
					['status' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
			}
			if($res == false && $rw['reward_id'] > 0){
				$db->where('reward_id', $rw['reward_id'])->update('reward',
					['status' => $rw['rw_status'], 'updated_at' => date('Y-m-d H:i:s')]);
			}
		echoJson(0, $log_id);
		break;

	/**
	 *	Dashboard of Hi Mavell Page
	 * 	init: 10/04/2021
	 */
	case 'get_dashboard':
		dbCon();
		chkProfilePermit('dashboard.html');
		$arr = array('static' => []);
		if($_SESSION['auth_roles'] == 0) echoJson(2, 'Only admin allowed');

		// No. of New User Today
		if($_SESSION['auth_roles'] >= 4) {
			$arr['static']['noNewNser'] = $db->where('DATE(FROM_UNIXTIME(u.registered)) = CURDATE()')->getValue('users u', 'COUNT(*)');
		}else $arr['static']['noNewNser'] = 0;

		// No. of Signed In Today
		if($_SESSION['auth_roles'] >= 4) {
			$arr['static']['noSignedIn'] = $db->where('DATE(FROM_UNIXTIME(u.last_login)) = CURDATE()')->getValue('users u', 'COUNT(*)');
		}else $arr['static']['noSignedIn'] = 0;

		// No. of ASC's Signed In Today
		if($_SESSION['auth_roles'] >= 4) {
			$arr['static']['noASCSignnedIn'] = $db->where('DATE(FROM_UNIXTIME(u.last_login)) = CURDATE()')
				->where('u.roles_mask', 1)
				->getValue('users u', 'COUNT(*)');
		}else $arr['static']['noASCSignnedIn'] = 0;

		$dateQuery = '(CURDATE() = DATE(r.datetime_service1)'
			.' OR CURDATE() = DATE(r.datetime_service2)'
			.' OR CURDATE() = DATE(r.datetime_service3)'
			.' OR CURDATE() = DATE(r.datetime_service4)'
			.' OR CURDATE() = DATE(r.datetime_service5))';

		// No. of Today's Job On Service
		if($_SESSION['auth_roles'] >= 4) {
			$arr['static']['noOnWork'] = $db->where($dateQuery)
				->where('r.status IN (1,2,3)')
				->getValue('service_request r', 'COUNT(*)');
		}else $arr['static']['noOnWork'] = 0;

		// No. of Rating
		if($_SESSION['auth_roles'] >= 4) {
			$arr['static']['noRating'] = $db->where('DATE(r.created_at) = CURDATE()')
				->where('r.status', 1)->getValue('service_rating r', 'COUNT(*)');
		}else $arr['static']['noRating'] = 0;

		// No. of Today Job Service
		for($i = 0; $i<=6; $i++){
			if($_SESSION['auth_roles'] >= 4) {
				$arr['static']['noStatus'.$i] = $db->where($dateQuery)
					->where('r.status', $i)->getValue('service_request r', 'COUNT(*)');
			}else $arr['static']['noStatus'.$i] = 0;
			// echo $db->getLastQuery();exit;
		}

		// my current profile
		$arr['roles_mask'] = $_SESSION['auth_roles'];

		echoJson(0, $arr);
		break;

	/**
	 *	Dashboard of Job Service Request
	 * 	init: 13/04/2021
	 */
	case 'get_dashboard_service':
		dbCon();
		chkProfilePermit('service_dashboard.html');
		$arr = array('static' => []);
		if($_SESSION['auth_roles'] == 0) echoJson(2, 'Only admin allowed');

		$dateQuery = '(( CURDATE() - INTERVAL 7 DAY ) <= DATE(r.datetime_service1)'
			.' OR ( CURDATE() - INTERVAL 7 DAY ) <= DATE(r.datetime_service2)'
			.' OR ( CURDATE() - INTERVAL 7 DAY ) <= DATE(r.datetime_service3)'
			.' OR ( CURDATE() - INTERVAL 7 DAY ) <= DATE(r.datetime_service4)'
			.' OR ( CURDATE() - INTERVAL 7 DAY ) <= DATE(r.datetime_service5))';

		// No. of Today Job Service
		for($i = 0; $i<=6; $i++){
			if($_SESSION['auth_roles'] == 1) { // match sc
				$db->join('technician t','t.technician_id=r.technician_id')
					->where('t.sc_id', $_SESSION['auth_sc_id']);
			}
			$arr['static']['noStatus'.$i] = $db->where($dateQuery)
				->where('r.service_type', 'repair')
				->where('r.status', $i)->getValue('service_request r', 'COUNT(*)');
			// echo $db->getLastQuery();exit;
		}

		// No. of Rating
		if($_SESSION['auth_roles'] == 1) { // match sc
			$db->join('technician t','t.technician_id=r.technician_id')
				->where('t.sc_id', $_SESSION['auth_sc_id']);
		}
		$arr['static']['noStatusOver3d'] =
			$db->where('DATE(r.created_at) < CURDATE() - INTERVAL 3 DAY')
				->where('r.service_type', 'repair')
				->where('r.status IN (0,1,2,3)')
				->getValue('service_request r', 'COUNT(*)');

		// List of Recent Jobs
		if($_SESSION['auth_roles'] == 1) { // match sc
			$db->join('technician t','t.technician_id=r.technician_id')
				->where('t.sc_id', $_SESSION['auth_sc_id']);
		}
		$arr['list_recents'] = $db->orderBy('r.created_at','DESC')
			->where('r.status',0)
			->get('service_request r', 20,'r.service_id,r.requester_name,r.requester_phone');


		// List of Service on Working
		if($_SESSION['auth_roles'] == 1) { // match sc
			$db->join('technician t','t.technician_id=r.technician_id')
				->where('t.sc_id', $_SESSION['auth_sc_id']);
		}
		$arr['list_on_work'] = $db->orderBy('r.updated_at','DESC')
			->where('r.service_type', 'repair')
			->where('r.status',3) // on work
			->where($dateQuery)
			->get('service_request r', 20,'r.service_id,r.status,r.created_at');

		// List of Over 3 days
		if($_SESSION['auth_roles'] == 1) { // match sc
			$db->join('technician t','t.technician_id=r.technician_id')
				->where('t.sc_id', $_SESSION['auth_sc_id']);
		}
		$arr['list_delay_3d'] = $db->orderBy('r.created_at','ASC')
			->where('DATE(r.created_at) < CURDATE() - INTERVAL 3 DAY')
			->where('r.service_type', 'repair')
			->where('r.status IN (0,1,2,3)')
			->get('service_request r', 20,'r.service_id,r.status,r.created_at');

		// my current profile
		$arr['roles_mask'] = $_SESSION['auth_roles'];
		echoJson(0, $arr);
		break;

	/**
	 *	Dashboard of Welcome Page
	 * 	init: 10/04/2021, 27/05/2021
	 */
	case 'get_welcome':
		dbCon();
		chkProfilePermit('welcome.html');
		$arr = array('static' => []);
		// if($_SESSION['auth_roles'] == 0) echoJson(2, 'Only admin allowed');

		// No. of New User Today
		if($_SESSION['auth_roles'] >= 4) {
			$arr['static']['noNewNser'] = $db->where('DATE(u.registered) = CURDATE()')
				->getValue('users u', 'COUNT(*)');
		}else $arr['static']['noNewNser'] = 0;

		// No. of New Technicians
		if($_SESSION['auth_roles'] >= 4) {
			$arr['static']['noNewTech'] = $db->where('DATE(t.created_at) = CURDATE()')
			->getValue('technician t', 'COUNT(*)');
		}else $arr['static']['noNewTech'] = 0;

		// No. of Product Register Record
		if($_SESSION['auth_roles'] >= 4) {
			$arr['static']['noNewProdReg'] = $db->where('DATE(r.created_at) = CURDATE()')->getValue('product_register r', 'COUNT(*)');
		}else $arr['static']['noNewProdReg'] = 0;

		// No. of Service Register Record
		if($_SESSION['auth_roles'] >= 4) {
			$arr['static']['noNewRequest'] = $db->where('DATE(s.created_at) = CURDATE()')->getValue('service_request s', 'COUNT(*)');
		}else $arr['static']['noNewRequest'] = 0;

		// Last 3 news
		$arr['news'] = $db->orderBy('n.updated_at', 'DESC')
			->where('n.status', 1)
			->get('news n', 3, 'n.title,n.subtitle,n.news_id,n.picture,n.updated_at');

		// 30 days history
		if($_SESSION['auth_roles'] > 0) {
			$arr['month_log'] = array('newUsers' => [], 'newRequest' => [], 'newProdReg' => []);
			$start    = (new DateTime(date('Y-m-d', strtotime('today - 30 days'))));
	    $end      = (new DateTime(date('Y-m-d') ));//->modify('first day of next month');
			$dateQuery = ' BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE()';

			$arr['month_query']['newUsers'] = $db->where('DATE(FROM_UNIXTIME(registered))'.$dateQuery)->groupBy('DATE(FROM_UNIXTIME(registered))')->get('users', 31, 'COUNT(*) as nn,DATE(FROM_UNIXTIME(registered)) as date');
			$arr['month_query']['newProdReg'] = $db->where('DATE(r.created_at)'.$dateQuery)->groupBy('DATE(r.created_at)')->get('product_register r', 31, 'COUNT(*) as nn,DATE(r.created_at) as date');
			$arr['month_query']['newRequest'] = $db->where('DATE(s.created_at)'.$dateQuery)->groupBy('DATE(s.created_at)')->get('service_request s', 31, 'COUNT(*) as nn,DATE(s.created_at) as date');

			foreach ($arr['month_query']['newUsers'] as $key => $val) {
				$arr['month_query']['newUsers_key'][$val['date']]  = $val['nn'] != null && $val['nn'] > 0 ? $val['nn'] : 0;
			}
			foreach ($arr['month_query']['newProdReg'] as $key => $val) {
				$arr['month_query']['newProdReg_key'][$val['date']]  = $val['nn'] != null && $val['nn'] > 0 ? $val['nn'] : 0;
			}
			foreach ($arr['month_query']['newRequest'] as $key => $val) {
				$arr['month_query']['newRequest_key'][$val['date']]  = $val['nn'] != null && $val['nn'] > 0 ? $val['nn'] : 0;
			}
			$arr['month_query']['newUsers'] = @$arr['month_query']['newUsers_key'];
			$arr['month_query']['newProdReg'] = @$arr['month_query']['newProdReg_key'];
			$arr['month_query']['newRequest'] = @$arr['month_query']['newRequest_key'];
			unset($arr['month_query']['newUsers_key'], $arr['month_query']['newProdReg_key'], $arr['month_query']['newRequest_key']);
		}

		$interval = DateInterval::createFromDateString('1 day');
    $period_month   = new DatePeriod($start, $interval, $end);
    foreach ($period_month as $dt) {
			// echo $dt->format("Y-m-d") . "<br>\n"; continue; // debug
			$arr['month_log']['newUsers'][] = isset($arr['month_query']['newUsers'][$dt->format("Y-m-d")])? $arr['month_query']['newUsers'][$dt->format("Y-m-d")] : 0; //rand(10,100);
			$arr['month_log']['newProdReg'][] = isset($arr['month_query']['newProdReg'][$dt->format("Y-m-d")]) ? $arr['month_query']['newProdReg'][$dt->format("Y-m-d")] : 0;
			$arr['month_log']['newRequest'][] = isset($arr['month_query']['newRequest'][$dt->format("Y-m-d")]) ? $arr['month_query']['newRequest'][$dt->format("Y-m-d")] : 0;
		};

		// My current profile
		$arr['userName'] = @$_SESSION['auth_username'];
		if($_SESSION['auth_roles'] > 0 && $_SESSION['auth_sc_id'] > 0 ) {
			$sc = $db->where('sc_id', $_SESSION['auth_sc_id'])
				->getOne('service_center', 'title,address');
			$arr['dealerName'] = $sc['title'];
			$arr['addrInfo'] = $sc['address'];
		}
		$arr['roles_mask'] = $_SESSION['auth_roles'];

		echoJson(0, $arr);
		break;

	/**
	 *	List Of Stock Item
	 *	Init 15/04/2021
	 */
	case 'item_list':
	case 'stock_item_list':
		dbCon();
		if($action == 'stock_item_list') chkProfilePermit('stock_part.html');
		else if($action == 'item_list') chkProfilePermit('part.html');
		if(isset($_POST['part_type']) && $_POST['part_type'] > 0) $db->where('i.part_type', (int) $_POST['part_type']);
		if(isset($_POST['item_id']) && $_POST['item_id'] > 0) $db->where('i.item_id', (int) filter_var($_POST['item_id'],FILTER_SANITIZE_NUMBER_INT) );
		if(isset($_POST['search_range']) && $_POST['search_range'] != ''){
			$dt = explode(' - ', $_POST['search_range']);
			$db->where('DATE(i.updated_at) BETWEEN "'.$dt[0].'" AND "'.$dt[1].'"' );
		}
		if(isset($_POST['item_name']) && $_POST['item_name'] != '') $db->where('i.item_name LIKE "%'.$_POST['item_name'].'%"' );
		if(isset($_POST['item_code']) && $_POST['item_code'] != '') $db->where('i.item_code LIKE "%'.$_POST['item_code'].'%"');
		$db->orderBy('i.updated_at','desc')
			->join('product_model p', 'p.product_id=i.part_type','left');
		$list = $db->get('stock_item i', 500,'i.*, p.model_name as part_type_name');
		echoJson(0, $list);
		break;


	/**
	 *	Part search (select2)
	 *	Init 25/04/2021
	 */
	case 'part_query':
		dbCon();
		if(!isset($_GET['q']) || $_GET['q'] == '') echoJson(2, 'Search keyword requried');
		chkProfilePermit('part.html');
		$q = trim($_GET['q']);
		$db->where('(p.item_code LIKE "%'.$q.'%" OR p.item_name LIKE "%'.$q.'%"'.
			' OR p.remark LIKE "%'.$q.'%"'.
		')');
		$db->orderBy('p.updated_at','desc')->where('p.status', 1);
		$list = $db->get('stock_item p', 100,'p.item_id, p.item_code, p.cost, p.qty, p.qty_unit, p.part_type, CONCAT_WS("",p.item_code," ",p.item_name," - ",p.qty ," ", p.qty_unit,"") as item, p.product_id, p.updated_at, p.part_type');
		// echo $db->getLastQuery().'<br/>'; // DEBUG
		echoJson(0, $list);
		break;

	/**
	 *	S/N search (select2)
	 *	Init 02/05/2021
	 */
	case 'serial_query':
		dbCon();
		if(!isset($_GET['q']) || $_GET['q'] == '') echoJson(2, 'Search keyword requried');
		chkProfilePermit('serial.html');
		$q = trim($_GET['q']);
		$db->where('(s.serial_no LIKE "%'.$q.'%" OR s.lot_no LIKE "%'.$q.'%"'.
			' OR s.remark LIKE "%'.$q.'%"'.
		')');
		$db->orderBy('s.updated_at','desc');
		$list = $db->get('product_serial s', 10,'CONCAT_WS("",s.serial_no," (Lot ",s.lot_no,") ",s.model_code_opt) as item, s.model_code_opt, s.serial_id, s.serial_no, s.updated_at');

		// Check avoid duplication pickup
		if(isset($_GET['picking']) && $_GET['picking'] == 1){
			$new_list = [];
			foreach ($list as $key => $sn) {
				$did = $db->where('o.ref', $sn['serial_no'], 'like')->where('o.mark', 0)
						->getValue('stock_out_detail o','o.stock_out_did');
				if($did == 0){
					$new_list[] = $list[$key];
				}
				// echo $db->getLastQuery().'<br/>'; // DEBUG
			}
			$list = $new_list;
		}
		echoJson(0, $list);
		break;

	/**
	 *	Stock Item Save
	 *	Init 15/04/2021
	 */
	case 's_stock_item':
		dbCon();
		if(!isset($_POST['id']) || $_POST['id'] == '') echoJson(2, 'Service ID Requried');
		chkProfilePermit('part.html', 'Y');
		$data = $_POST;
		unset($data['id']);
		$data['updated_at'] = date('Y-m-d H:i:s');
		if($_POST['id'] == 'new'){
			$data['created_at'] = date('Y-m-d H:i:s');
			$id = $db->insert('stock_item', $data);
		}else{
			$db->where('i.item_id', $_POST['id']);
			$updated = $db->update('stock_item i', $data);
			$id = ($updated) ? $_POST['id'] :0;
		}
		// echo $db->getLastQuery().'<br/>'; // DEBUG
		echoJson(0, $id);
		break;

	/**
	 *	Card Of Stock Item (part)
	 *	Init 15/04/2021
	 */
	case 'stock_card':
	case 'stock_remain_list':
		dbCon();
		if($action == 'stock_card') chkProfilePermit('stock_card.html');
		else if($action == 'stock_remain_list') chkProfilePermit('stock_remain.html');
		// if(isset($_POST['status']) && $_POST['status'] != ''){
		// 	$db->where(is_array($_POST['status']) ? 'r.status IN ('.implode(',',$_POST['status']).')' : 'r.status = '.$_POST['status'] );
		// }
		// if(isset($_POST['service_type']) && $_POST['service_type'] != ''){
		// 	$db->where('r.service_type',$_POST['service_type'] );
		// }
		// if($action == 'stock_card'){
			// $db->groupBy('i.item_id');
		// }
		$db->orderBy('i.updated_at','desc')
			->join('stock_item i', 'i.item_id=h.item_id','left');
		$list = $db->get('stock_item_history h', 500,'i.*,h.*');
		echoJson(0, $list);
		break;

	/**
	 *	Card Of Stock Item for product
	 *	Init 23/04/2021
	 */
	case 'stock_card_product':
		dbCon();
		chkProfilePermit('report_stock_card.html');
		// if(isset($_POST['status']) && $_POST['status'] != ''){
		// 	$db->where(is_array($_POST['status']) ? 'r.status IN ('.implode(',',$_POST['status']).')' : 'r.status = '.$_POST['status'] );
		// }
		// if(isset($_POST['service_type']) && $_POST['service_type'] != ''){
		// 	$db->where('r.service_type',$_POST['service_type'] );
		// }
		$db->orderBy('m.product_type','asc')
			->groupBy('m.product_type','desc')
			->join('stock_item i', 'i.item_id=h.item_id','left')
			->join('product_model m', 'm.product_id=i.product_id','left');
		$list = $db->get('stock_item_history h', 1000,'h.*,i.item_code,i.item_name,i.qty,i.qty_unit,i.remark,m.product_id,m.product_code,m.product_type,m.model_name,m.model_indoor,m.model_outdoor');
		echoJson(0, $list);
		break;

	/**
	 *	Last 10 items has a movment / QTY Traffic
	 *	Init 18/04/2021
	 */
	case 'stock_traffic_top10':
		dbCon();
		chkProfilePermit('stock_remain.html');
		$db->orderBy('i.updated_at','desc')
			->groupBy('item_id, SIGN(qty_adjust)')
			->join('stock_item i', 'i.item_id=h.item_id','left');
		$tf = $db->get('stock_item_history h', 500,'SUM(h.qty_adjust) as val, i.item_id, i.item_code, i.item_name');
		echoJson(0, ['traffic' => $tf]);
		break;

	/**
	 *	Stock Item Aging
	 *	Init 18/04/2021
	 */
	case 'stock_aging':
		dbCon();
		chkProfilePermit('stock_aging.html');
		if(isset($_POST['search_range']) && $_POST['search_range'] != ''){
			$dt = explode(' - ', $_POST['search_range']);
			$db->where('DATE(s.mfd) BETWEEN "'.$dt[0].'" AND "'.$dt[1].'"' );
		}
		if(isset($_POST['year']) && $_POST['year'] != ''){
			$db->where('YEAR(s.mfd)',$_POST['year'] );
		}
		$db->orderBy('s.updated_at','desc')
			->groupBy('s.mfd,m.product_code')
			->join('product_model m','s.product_id=m.product_id','left');
		$list = $db->get('product_serial s', 1000,'count(s.serial_id) as qty,m.product_code,m.product_id,m.model_name,s.mfd,DATEDIFF(CURDATE(),s.mfd) as aging, s.age_day');
		echoJson(0, $list);
		break;

	/**
	 *	linked to service requests and technician
	 *	Init 19/04/2021
	 */
	case 'claim_list':
		dbCon();
		chkProfilePermit('claim.html');
		if(isset($_POST['status']) && $_POST['status'] != ''){
			$db->where(is_array($_POST['status']) ? 'r.status IN ('.implode(',',$_POST['status']).')' : 'r.status = '.$_POST['status'] );
		}
		if(isset($_POST['search_range']) && $_POST['search_range'] != ''){
			$dt = explode(' - ', $_POST['search_range']);
			$db->where('DATE(l.created_at) BETWEEN "'.$dt[0].'" AND "'.$dt[1].'"' );
		}
		if(isset($_POST['service_type']) && $_POST['service_type'] != ''){
			$db->where('r.service_type',$_POST['service_type'] );
		}
		if(isset($_POST['service_type']) && $_POST['service_type'] != ''){
			$db->where('r.service_type',$_POST['service_type'] );
		}
		if(isset($_POST['keyword']) && $_POST['keyword'] != ''){
			$q = trim($_POST['keyword']);
			$db->where('(l.date_claim LIKE "%'.$q.'%" OR l.expense_cost LIKE "%'.$q.'%"'.
			  ' OR l.item_no LIKE "%'.$q.'%" OR l.remark LIKE "%'.$q.'%"'.
			')');
		}
		if(isset($_POST['service_id']) && $_POST['service_id'] != "") $db->where('l.service_id', (int) filter_var($_POST['service_id'],FILTER_SANITIZE_NUMBER_INT) );
		if(isset($_POST['claim_id']) && $_POST['claim_id'] != "") $db->where('l.claim_id', (int) filter_var($_POST['claim_id'],FILTER_SANITIZE_NUMBER_INT) );

		$db->orderBy('l.updated_at','desc')
			->groupBy('l.claim_id')
			->join('technician t', 't.technician_id=l.technician_id', ($_SESSION['auth_roles'] <= 1 && $_SESSION['auth_sc_id'] != -1 ? '' : 'left'))
			->join('service_request r', 'r.service_id=l.service_id', 'left');
		if($_SESSION['auth_roles'] <= 1 && $_SESSION['auth_sc_id'] != -1) { // match sc
				$db->where('t.sc_id', $_SESSION['auth_sc_id']);
		}
		$list = $db->get('claim l', 1000,'l.*, CONCAT_WS("",t.firstname," ",t.lastname, " - ", t.technician_code,"]", " [", t.zone,"]") as technician, r.requester_name, r.requester_phone, r.requester_email');
		// echo $db->getLastQuery().'<br/>'; // DEBUG
		echoJson(0, $list);
		break;

	/**
	 *	Claim Sheet
	 *	Init 30/04/2021
	 */
	case 'claim_detail':
		if(!isset($_POST['claim_id']) || $_POST['claim_id'] == 0) echoJson(2, 'Claim ID Requried');
		dbCon();
		chkProfilePermit('claim.html');
		$db->where('d.claim_id', $_POST['claim_id']);
		$db->orderBy('d.sort_order','asc');
		$list = $db->get('claim_detail d', 1000,'d.*');
		echoJson(0, $list);
		break;

	/**
	 *	Service Request Detail Remove for all type of timeline
	 *	Init 26/04/2021
	 */
	case 'x_claim_detail':
		dbCon();
		if(!isset($_POST['pk_id']) || $_POST['pk_id'] == 0) echoJson(2, 'Service ID Requried');
		chkProfilePermit('service_request.html', 'Y');
		$db->where('claim_did', $_POST['pk_id']);
		$updated = $db->delete('claim_detail');
		$id = ($updated) ? $_POST['id'] :0;
		echoJson(0, $id);
		break;

	/**
	 *	Pickup List
	 *	Init 16/04/2021
	 */
	case 'stock_pickup_list':
		dbCon();
		chkProfilePermit('stock_picking.html');
		if(isset($_POST['stock_control_search']) && $_POST['stock_control_search'] != '') $db->where('h.stock_control', $_POST['stock_control_search'],'like');
		if(isset($_POST['ref_search']) && $_POST['ref_search'] != '') $db->where('h.ref', $_POST['ref_search'],'like');
		if(isset($_POST['stock_out_id']) && $_POST['stock_out_id'] != '') $db->where('h.stock_out_id', '%'. (int) filter_var($_POST['stock_out_id'],FILTER_SANITIZE_NUMBER_INT).'%' , 'LIKE');
		if(isset($_POST['date_start']) && $_POST['date_start'] != '' && isset($_POST['date_end']) && $_POST['date_end'] != ''){
			$db->where('DATE(h.datetime_pickup) BETWEEN "'.$_POST['date_start'].'" AND "'.$_POST['date_end'].'"');
		}
		// Search Products
		$filtered_prod = false;
		if(isset($_POST['product_search']) && $_POST['product_search'] != ''){
				$db->join('stock_out_detail d', 'd.stock_out_id=h.stock_out_id');
				$db->join('product_serial s', 's.serial_id=d.serial_id'); // must join to filter
				$q = trim($_POST['product_search']);
				$db->where('(s.serial_no LIKE "'.$q.'%" OR s.model_code_opt LIKE "%'.$q.'%"'.
				  ' OR s.remark LIKE "%'.$q.'%"'.
				')');
				$filtered_prod = true;
		}
		// Search Part Items
		if(isset($_POST['item_search']) && $_POST['item_search'] != ''){
				if(!$filtered_prod) $db->join('stock_out_detail d', 'd.stock_out_id=h.stock_out_id');
				$db->join('stock_item p', 'p.item_id=d.item_id'); // must join to filter
				$q = trim($_POST['item_search']);
				$db->where('(p.item_code LIKE "%'.$q.'%" OR p.item_name LIKE "%'.$q.'%"'.
				  ' OR p.remark LIKE "%'.$q.'%"'.
				')');
		}
		$db->orderBy('h.updated_at','desc')
			// ->where('h.out_type', 'PICKUP')
			->join('service_request r', 'r.service_id=h.service_id','left')
			->join('customer c', 'c.customer_id=r.customer_id','left')
			->join('users u', 'u.id=h.user_id_created', 'left');
		$list = $db->get('stock_out h', 500,'h.*, CONCAT_WS("",c.firstname," ",c.lastname) as customer, u.username as username_created');
		echoJson(0, $list);
		break;


	/**
	 *	Picking Sheet
	 *	Init 13/05/2021
	 */
	case 'picking_detail':
		if(!isset($_POST['stock_out_id']) || $_POST['stock_out_id'] == 0) echoJson(2, 'Stock OUT ID Requried');
		dbCon();
		chkProfilePermit('stock_picking.html');
		$db->where('d.stock_out_id', $_POST['stock_out_id'])
			->orderBy('d.sort_order','asc')
			->join('stock_item i','d.item_id=i.item_id','left')
			->join('product_serial s','s.serial_id=d.serial_id','left');
		$list = $db->get('stock_out_detail d', 1000,'d.*,i.item_code,i.item_name,i.part_type,s.serial_no,s.model_code_opt');
		echoJson(0, $list);
		break;

	/**
	 *	Picking Detail Remove
	 *	Init 13/05/2021
	 */
	case 'x_picking_detail':
		dbCon();
		if(!isset($_POST['stock_out_did']) || $_POST['stock_out_did'] == 0) echoJson(2, 'Stock Out Detail ID Requried');
		if(!isset($_POST['stock_out_id']) || $_POST['stock_out_id'] == 0) echoJson(3, 'Stock Out ID Requried');
		chkProfilePermit('stock_picking.html', 'Y');
		$updated = $db->where('stock_out_did', $_POST['stock_out_did'])->delete('stock_out_detail');
		if($updated){
			recountStockOut($_POST['stock_out_id']);
		}
		$id = ($updated) ? $_POST['id'] :0;
		echoJson(0, $id);
		break;


	/**
	 *	Remove user record
	 *	MSA supports (check in `login_authentication`)
	 *  @POST pk_id
	 *	Init 11/09/2021
	 */
	case 'x_user':
		// if($_SESSION['PROFILE_ID'] != 4) echoJson(1003, 'ต้องเป็น Super Admin เท่านั้น '.$_SESSION['PROFILE_ID']);
		if(!isset($_POST['pk_id'])) echoJson(1002, 'PK ID Requried');
		dbCon();
		chkProfilePermit('main/user.html', 'can_edit');
		$db->where('id', $_POST['pk_id']);
		$db->delete('users') ? echoJson(0) : echoJson(1016, 'Could not remove a user account.');
		break;


	/**
	 *	Return Sheet : join both part or product
	 *	Init 14/05/2021
	 */
	case 'return_detail':
		if(!isset($_POST['stock_in_id']) || $_POST['stock_in_id'] == 0) echoJson(2, 'Stock IN ID Requried');
		dbCon();
		chkProfilePermit('stock_receive.html');
		$db->where('d.stock_in_id', $_POST['stock_in_id'])
			->orderBy('d.sort_order','asc')
			->join('stock_item i','d.item_id=i.item_id','left')
			->join('product_serial s','s.serial_id=d.serial_id','left');
		$list = $db->get('stock_in_detail d', 1000,'d.*,i.item_code,i.item_name,i.part_type,s.serial_no,s.model_code_opt');
		echoJson(0, $list);
		break;

	/**
	 *	Return Detail Remove
	 *	Init 14/05/2021
	 */
	case 'x_return_detail':
		dbCon();
		if(!isset($_POST['pk_id']) || $_POST['pk_id'] == 0) echoJson(2, 'Service ID Requried');
		chkProfilePermit('return.html', 'Y');
		$stock_in_id = $db->where('stock_in_did', $_POST['pk_id'])->getValue('stock_in_detail', 'stock_in_id');
		$db->where('stock_in_did', $_POST['pk_id']);
		$del = $db->delete('stock_in_detail');
		if($del & $stock_in_id > 0 ){
			recountStockIn($stock_in_id);
		}
		$id = ($del) ? $_POST['pk_id'] :0;
		echoJson(0, $id);
		break;

	/**
	 *	Receive List
	 *	Init 17/04/2021
	 */
	case 'stock_receive_list':
	case 'return_list':
	case 'stock_return_list':
		dbCon();
		if($action == 'stock_receive_list') chkProfilePermit('stock_return.html');
		else if($action == 'return_list') chkProfilePermit('return.html');
		if(isset($_POST['stock_in_id']) && $_POST['stock_in_id'] != '') $db->where('i.stock_in_id', (int) filter_var($_POST['stock_in_id'],FILTER_SANITIZE_NUMBER_INT).'' , 'LIKE');
		if(isset($_POST['doc_search']) && $_POST['doc_search'] != '') {
			$q = trim($_POST['doc_search']);
			$db->where('(i.ref LIKE "%'.$q.'%" OR i.rec_doc_id LIKE "%'.$q.'%")');
		}


		if(isset($_POST['sender_search']) && $_POST['sender_search'] != '')  $db->where('i.sender', '%'.trim($_POST['sender_search']).'%','like');
		if(isset($_POST['place_search']) && $_POST['place_search'] != '')  $db->where('i.place', '%'.trim($_POST['place_search']).'%','like');
		if(isset($_POST['dept_search']) && $_POST['dept_search'] != '')  $db->where('i.dept', '%'.trim($_POST['dept_search']).'%','like');
		if(isset($_POST['date_start']) && $_POST['date_start'] != '' && isset($_POST['date_end']) && $_POST['date_end'] != ''){
			$db->where('DATE(i.datetime_return) BETWEEN "'.$_POST['date_start'].'" AND "'.$_POST['date_end'].'"');
		}
		// Search Products
		$filtered_prod = false;
		if(isset($_POST['product_search']) && $_POST['product_search'] != ''){
				$db->join('stock_in_detail d', 'd.stock_in_id=i.stock_in_id');
				$db->join('product_serial s', 's.serial_id=d.serial_id'); // must join to filter
				$q = trim($_POST['product_search']);
				$db->where('(s.serial_no LIKE "'.$q.'%" OR s.model_code_opt LIKE "%'.$q.'%"'.
				  ' OR s.remark LIKE "%'.$q.'%"'.
				')');
				$filtered_prod = true;
		}

		// Search Part Items
		if(isset($_POST['item_search']) && $_POST['item_search'] != ''){
				if(!$filtered_prod) $db->join('stock_in_detail d', 'd.stock_in_id=i.stock_in_id', 'left');
				$db->join('stock_item p', 'p.item_id=d.item_id'); // must join to filter
				$q = trim($_POST['item_search']);
				$db->where('(p.item_code LIKE "%'.$q.'%" OR p.item_name LIKE "%'.$q.'%"'.
				  ' OR p.remark LIKE "%'.$q.'%"'.
				')');
		}
		$db->orderBy('i.updated_at','desc')->orderBy('i.stock_in_id')
			// ->where('i.in_type', 'RETURN') // RECEIVE
			// ->join('stock_out o', 'i.stock_out_id=o.stock_out_id','left')
			->join('users u', 'u.id=i.user_id_created', 'left');
		$list = $db->get('stock_in i', isset($_POST['limit']) ? $_POST['limit'] : 500,'i.*, u.username as username_created');
		// echo $db->getLastQuery().'<br/>'; // DEBUG
		echoJson(0, $list);
		break;


	/**
	 *	Stock Item Save
	 *	Init 15/04/2021
	 */
	case 's_stock_item':
		dbCon();
		if(!isset($_POST['id']) || $_POST['id'] == '') echoJson(2, 'Service ID Requried');
		chkProfilePermit('stock_part.html', 'Y');
		$data = $_POST;
		unset($data['id']);
		$data['updated_at'] = date('Y-m-d H:i:s');
		if($_POST['id'] == 'new'){
			$data['created_at'] = date('Y-m-d H:i:s');
			$id = $db->insert('stock_item', $data);
		}else{
			$db->where('i.item_id', $_POST['id']);
			$updated = $db->update('stock_item i', $data);
			$id = ($updated) ? $_POST['id'] :0;
		}
		// echo $db->getLastQuery().'<br/>'; // DEBUG
		echoJson(0, $id);
		break;


	/**
	 *	Stock Item History Save
	 *	Init 15/04/2021
	 */
	case 's_stock_history':
		dbCon();
		if(!isset($_POST['id']) || $_POST['id'] == '') echoJson(2, 'Service ID Requried');
		chkProfilePermit('stock_card.html', 'Y');
		$data = $_POST;
		unset($data['id']);
		$data['updated_at'] = date('Y-m-d H:i:s');
		if($_POST['id'] == 'new'){
			$data['created_at'] = date('Y-m-d H:i:s');
			$id = $db->insert('stock_item_history', $data);
		}else{
			$db->where('h.item_log_id', $_POST['id']);
			$updated = $db->update('stock_item_history h', $data);
			$id = ($updated) ? $_POST['id'] :0;
		}
		// echo $db->getLastQuery().'<br/>'; // DEBUG
		echoJson(0, $id);
		break;

	/**
	 *	User Notifications (Inbox)
	 *	Init 09/05/2021
	 */
	case 'my_noti':
		dbCon();
		$db->orderBy('n.created_at','desc')
			->where('n.user_id', $_SESSION['auth_user_id']);
		$list = $db->get('user_notification n', 200,'n.*');
		echoJson(0, $list);
		break;

	/**
	 *	Mark read notifcaitons (Inbox)
	 *	Init 09/05/2021
	 */
	case 'noti_mark_read':
		dbCon();
		if(!isset($_POST['ids']) || $_POST['ids'] == '') echoJson(0, false);
		$db->orderBy('n.created_at','desc')
		// ->where('n.user_id', $_SESSION['auth_user_id'])
			->where('n.noti_id IN ('.implode(',',$_POST['ids']).')');
		$res = $db->update('user_notification n', ['status' => 1]);
		echoJson(0, $res ? true : false);
		break;


	/**
	 *	Chat Recent List
   *  @REQUEST : -
	 *	Init 20/05/2021
	 */
	case 'chat_recents' :
		dbCon();
		chkProfilePermit('chat.html');
		$meID = $_SESSION['auth_user_id'];

		if(isset($_POST['search_text']) && strlen($_POST['search_text']) > 1) {
			$q = trim($_POST['search_text']);
			$db->where('(tx.username LIKE "%'.$q.'%" OR rx.username LIKE "%'.$q.'%")');
		}

		$db->groupBy('tx.id') // unique
			->orderBy('c.time', 'desc')
			// ->where('(c.userIDTx = '.$meID.' OR c.userIDRx = '.$meID.')')
			->join('users rx','rx.id=c.userIDRx','left')
			->join('users tx','tx.id=c.userIDTx','left');
		if($_SESSION['auth_roles'] <= 1 && $_SESSION['auth_sc_id'] != -1) { // match sc
			$db->where('(c.userIDTx = '.$_SESSION['auth_user_id'].' OR c.userIDRx = '.$_SESSION['auth_user_id'].')');
		}
		$rec = $db->get('chat c', 100,'c.*,tx.username as tx_name, rx.username as rx_name');
		// echo $db->getLastQuery().'<br/>'; // DEBUG

		echoJson(0, [
			'recents'=> $rec,
			'me_uid'=> $meID,
			'me_name'=> @$_SESSION['auth_username'],
			'file_path' => PUBLIC_DOMAIN.APP_PATH.ARCHIVE_DIR_NAME.'/avatar/'
		]);
		break;

	/**
	 *	Chat Initialising + load chat history for 10
   *  @REQUEST : fuid
	 *	Init 20/05/2021
	 */
	case 'chat_start_room' :
		if(!isset($_POST['fuid'])) echoJson(1,'User ID Required');
		dbCon();
		chkProfilePermit('chat.html');
		$meUID = $_SESSION['auth_user_id'];
		$fUID = $_POST['fuid'];
		// $_SESSION['userIDRx'] = $fUID;

		// $db->where('(( userIDTx= '.$meUID.' AND userIDRx= '.$fUID.') OR ( userIDRx= '.$fUID.' AND userIDTx = '.$meUID.'))')
		$db->where('(userIDRx = '.$fUID.' OR userIDTx = '.$fUID.')')
			->orderBy('time', 'desc');
		$msg = $db->get('chat',16,'cmID, userIDTx, userIDRx, room_type, txt, status, `time`');

		$db->where('u.id' , $fUID)
			->join('customer c','u.id=c.user_id','left');
		$user = $db->get('users u', 16 ,'u.username, CONCAT_WS("",c.firstname," ",c.lastname) as customer, c.customer_id');
		echoJson(0, ['msg' => $msg, 'user' => $user]);
		break;


	// Chat's sent a message - user sent a message to server via AJAX
	// MSA Supports
	case 'chat_send' :
		if(!isset($_POST['message'])) echoJson(6002,'Message Required');
		require_once('cls/phpUserAgent.php');
		$userIDTx = $_SESSION['auth_user_id'];
		$userIDRx = isset($_POST['fuid']) ? $_POST['fuid']: $_SESSION['userIDRx'];
		dbCon();
		chkProfilePermit('chat.html');

		$userAgent = new phpUserAgent();
		$agent = $userAgent->getBrowserName().'|'.$userAgent->getBrowserVersion().'|'.$userAgent->getOperatingSystem().'|'.$userAgent->getEngine();

		$cmid = $db->insert('chat', array(
			'userIDTx'=>$userIDTx,
			'userIDRx'=>$userIDRx,
			'room_type'=>1,
			'txt'=>$_POST['message'],
			'status'=>'1',
			'time'=>date('Y-m-d H:i:s'),
			'agent'=>$agent,
			'sessionID'=>session_id(),
			// 'sc_id'=> $_SESSION['auth_sc_id'], // MSA
			'ip'=>isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']));

		if($cmid > 0) {
			echoJson(0, array('cmID'=>$cmid, 'record_time'=>date('Y-m-d H:i:s')));

			// Push message
			if($userIDRx > 0 && $uid > 0){
				$tk = getUserDeviceTokens($userIDRx);
				$fcm_id = fcmSendByTokens($tk, "Hi Mavell - New Message" , substr($_POST['message'], 160));
			}
		}else echoJson(6003, 'Could not send message');
		break;

	// Chat set of read ACKs and set of new messages
	// *	Init 20/05/2021
	case 'chat_update' :
		if(!isset($_POST['fuid'])) echoJson(6001,'User ID /Profile ID Required');
		else if(!isset($_POST['last_cmID'])) echoJson(6004,'Last Chat Message ID Required');
		$userIDTx = $_SESSION['auth_user_id'];
		$userIDRx = isset($_POST['fuid']) ? $_POST['fuid']: $_SESSION['userIDRx'];

		dbCon();
		chkProfilePermit('chat.html');
		$db->orderBy('time', 'desc')
			->where('userIDRx',$userIDTx)
			->where('userIDTx',$userIDRx)
			->where('cmID', $_POST['last_cmID'], '>');
		$new_msg = $db->get('chat', 60,'cmID, userIDTx, userIDRx, room_type, txt, status,time');
		echoJson(0, array('read'=>[], 'msg_rx'=>$new_msg, 'fuid'=>$_POST['fuid']));
		break;


	/**
	 *	My registered Product List (Warranty)
	 *	Init 24/05/2021
	 */
	case 'my_registered':
		dbCon();
		$db->where('r.technician_id', $_SESSION['auth_user_id']);
		$db->orderBy('r.updated_at','desc')->groupBy('r.register_id')
			->join('product_serial s','s.serial_id=r.serial_id','left');
		$list = $db->get('product_register r', 100,'r.*,s.mfd,s.lot_no');
		echoJson(0, $list);
		break;

	/**
	 *	My service request list
	 *	Init 25/05/2021
	 */
	case 'my_serv_req':
		dbCon();
		$db->where('r.technician_id', $_SESSION['auth_user_id']);
		$db->orderBy('r.updated_at','desc')
			->groupBy('r.service_id')
			->join('technician t', 't.technician_id=r.technician_id', 'left');
		$list = $db->get('service_request r', 1000,'r.*, CONCAT_WS("",t.firstname," ",t.lastname, " - ", t.technician_code) as technician, t.technician_id');
		echoJson(0, $list);
		break;

	/**
	 *	Report Service Request KPI
	 *	Init 26/06/2021
	 */
	case 'report_kpi':
		dbCon();
		chkProfilePermit('report_kpi.html');
		$arr = array();
		// $db->where('r.technician_id', $_SESSION['auth_user_id']);

		$year = (isset($_POST['search_year']) && $_POST['search_year'] != '') ? $_POST['search_year'] : date('Y') ;

		// Total Serv Req
		$db->where('YEAR(r.created_at)',$year);
		$arr['srTotal'] = $db->groupBy('YEAR(r.created_at), MONTH(r.created_at)')
			->get('service_request r', 15,'COUNT(r.service_id) as cc, MONTH(r.created_at) as mm, YEAR(r.created_at) as yy');

		// Repeat Service Visiting (Second time)
		$db->where('YEAR(r.created_at)',$year);
		$arr['srSecond'] = $db->groupBy('YEAR(r.created_at), MONTH(r.created_at)')
			->where('r.datetime_service2 IS NOT NULL AND r.datetime_service2 != ""')
			->get('service_request r', 15,'COUNT(r.service_id) as cc, MONTH(r.created_at) as mm, YEAR(r.created_at) as yy');

		// Service completed in 3 days
		$db->where('YEAR(r.created_at)',$year);
		$arr['srIn3d'] = $db->groupBy('YEAR(r.created_at), MONTH(r.created_at)')
			->where('r.date_completed IS NOT NULL AND DATEDIFF(r.created_at, r.date_completed) <= 3')
			->get('service_request r', 15,'COUNT(r.service_id) as cc, MONTH(r.created_at) as mm, YEAR(r.created_at) as yy');

		// Service completed in same day
		$db->where('YEAR(r.created_at)',$year);
		$arr['srIn1d'] = $db->groupBy('YEAR(r.created_at), MONTH(r.created_at)')
			->where('r.date_completed IS NOT NULL AND DATEDIFF(r.created_at, r.date_completed) <= 1')
			->get('service_request r', 15,'COUNT(r.service_id) as cc, MONTH(r.created_at) as mm, YEAR(r.created_at) as yy');

		// Service completed in one time
		$db->where('YEAR(r.created_at)',$year);
		$arr['srIn1T'] = $db->groupBy('YEAR(r.created_at), MONTH(r.created_at)')
			->where('r.datetime_service2 IS NULL')->where('r.status', 6)
			->get('service_request r', 15,'COUNT(r.service_id) as cc, MONTH(r.created_at) as mm, YEAR(r.created_at) as yy');
		// echo $db->getLastQuery().'<br/>'; // DEBUG
		echoJson(0, $arr);
		break;


	/**
	 *	Reward Item list
	 *	Init 06/12/2021
	 */
	case 'reward_item_list':
		dbCon();
		chkProfilePermit('reward_item.html');
		if(isset($_POST['rw_item_id']) && $_POST['rw_item_id'] > 0) $db->where('p.rw_item_id', $_POST['rw_item_id']);
		if(isset($_POST['select'])) $db->orderBy('p.item_code','ASC');
		else $db->orderBy('p.item_code','ASC')->orderBy('p.point_require','DESC');
		$list = $db->get('reward_item p', 1000, isset($_POST['select']) ? 'p.rw_item_id,p.item_title,p.item_code' : 'p.*');
		echoJson(0, $list);
		break;

	/**
	 *	Reward Item Save
	 *	Init 06/12/2021
	 */
	case 's_reward_item':
		if(!isset($_POST['id']) || $_POST['id'] == '') echoJson(2, 'Product ID Requried');
		dbCon();
		chkProfilePermit('reward_item.html', 'Y');
		$data = $_POST;
		unset($data['id']);
		$data['user_id'] = $_SESSION['auth_user_id'];
		$data['updated_at'] = date('Y-m-d H:i:s');
		$data['item_code'] =  trim(str_replace(' ', '', $data['item_code']));
		$data['point_require'] =  (float) $data['point_require'];
		if($_POST['id'] == 'new'){
			$data['created_at'] = date('Y-m-d H:i:s');
			$id = $db->insert('reward_item', $data);
		}else{
			$db->where('p.rw_item_id',$_POST['id']);
			$updated = $db->update('reward_item p', $data);
			$id = ($updated) ? $_POST['id'] :0;
		}

		// Rename if uploaded cover
		if(isset($_SESSION['_rwitemcf'])){
			$path = ROOT_PATH.ARCHIVE_FILE.'/reward/';
			$type = strtolower(substr(strrchr($_SESSION['_rwitemcf'],'.'),1));
			$new_fn = 'reward_item_'.$id.'_'.date('Hi').'.'.$type;
			if(rename($path.$_SESSION['_rwitemcf'], $path.$new_fn)){
				unset($_SESSION['_rwitemcf']);
				$db->where('i.rw_item_id',$_POST['id']);
				$db->update('reward_item i', ['picture' => PUBLIC_DOMAIN.APP_PATH.ARCHIVE_DIR_NAME.'/reward/'.$new_fn]);
			}
		}
		// echo $db->getLastQuery().'<br/>'; // DEBUG
		echoJson(0, $id);
		break;

	/**
	 *	Upload Picture Cover
	 *	Init 24/12/2021
	 */
	case 'reward_item_cover_pic_ul':
		// print_r($_FILES);
		if(!isset($_FILES) || !isset($_FILES['upload_file'])) echoJson(2, 'ข้อมูลไม่ครบถ้วน');
    if($_FILES['upload_file']['tmp_name'] && $_FILES['upload_file']['size'] > 0){
			if($_FILES['upload_file']['size'] > 2097152) echoJson(4,'ขนาดไฟล์ใหญ่เกิน 2Mb');
      $type = strtolower(substr(strrchr($_FILES['upload_file']['name'],'.'),1));
			$flname = '_tmp_'.$_SESSION['ssUID'].time().'.'.$type; // just into temp untill save
			$path = ROOT_PATH.ARCHIVE_FILE.'reward/'.$filename;
      $located_file = $path.$flname;
      $result = move_uploaded_file($_FILES['upload_file']['tmp_name'], $located_file);
			$_SESSION['_rwitemcf'] = $flname;
			echoJson(0, array('file'=> PUBLIC_DOMAIN.APP_PATH.ARCHIVE_DIR_NAME.'/reward/'.$flname,
				'result' => $result, 'filename'=> $flname));
    }else echoJson(4,'No such upload file');
		break;


	/**
	 *	Warranty Register list
	 *	Init 10/04/2021
	 */
	case 'reward_list':
		dbCon();
		chkProfilePermit('reward.html');
		if(isset($_POST['status']) && $_POST['status'] != ''){
			$db->where(is_array($_POST['status']) ? 'r.status IN ('.implode(',',$_POST['status']).')' : 'r.status = '.$_POST['status'] );
		}
		if(isset($_POST['search_range']) && $_POST['search_range'] != ''){
			$dt = explode(' - ', $_POST['search_range']);
			$db->where('DATE(r.date_request) BETWEEN "'.$dt[0].'" AND "'.$dt[1].'"' );
		}
		if(isset($_POST['product_model']) && $_POST['product_model'] != ''){
			$db->where('(r.product_model LIKE "%'.$_POST['product_model'].'%" OR r.other_model LIKE "%'.$_POST['product_model'].'%")' );
		}
		if(isset($_POST['serial']) && $_POST['serial'] != '')$db->where('(r.product_sn LIKE "%'.$_POST['serial'].'%")');
		if(isset($_POST['reward_id']) && $_POST['reward_id'] != "") $db->where('r.reward_id', (int) filter_var($_POST['reward_id'],FILTER_SANITIZE_NUMBER_INT) );
		if(isset($_POST['technician_id']) && $_POST['technician_id'] > 0) $db->where('r.technician_id', $_POST['technician_id']);
		if(isset($_POST['technician_name']) && $_POST['technician_name'] != '') $db->where('(t.firstname LIKE "%'.$_POST['technician_name'].'%" OR t.lastname LIKE "%'.$_POST['technician_name'].'%")' );
		if(isset($_POST['technician_phone']) && $_POST['technician_phone'] != '') $db->where('t.phone LIKE "%'.$_POST['technician_phone'].'%"' );
		if(isset($_POST['product_id']) && $_POST['product_id'] > 0) $db->where('i.product_id', $_POST['product_id']);
		if(isset($_POST['reward_id']) && $_POST['reward_id'] != "") $db->where('r.reward_id', (int) filter_var($_POST['reward_id'],FILTER_SANITIZE_NUMBER_INT) );

		$db->orderBy('r.updated_at','desc')->groupBy('r.reward_id');
		$db->join('product_model p','r.product_id=r.product_id','left');
		$db->join('technician t','t.technician_id=r.technician_id', 'left');
		if($_SESSION['auth_roles'] <= 1 && $_SESSION['auth_sc_id'] != -1) { // match sc
			$db->where('t.sc_id', $_SESSION['auth_sc_id']);
		}
		$list = $db->get('reward r',
			isset($_POST['limit']) ? (int)$_POST['limit'] : 50,
			'r.*,CONCAT_WS("",t.name_title, t.firstname," ",t.lastname) as technician_name, t.technician_code, t.phone as technician_phone');
			// echo $db->getLastQuery().'<br/>'; // DEBUG

		echoJson(0, ['list' => $list, 'file_path' => PUBLIC_DOMAIN.APP_PATH.ARCHIVE_DIR_NAME.'/reward/']);
		break;

	/**
	 *	Number of Point Reward
	 *	Init 06/12/2021
	 */
	case 'reward_count':
		dbCon();
		chkProfilePermit('reward.html');
		$cc = $db->getValue('reward', 'COUNT(*)');
		echoJson(0, ['no_reward' => $cc]);
		break;

	/**
 	 *	Point Reward Request Re-Save, Approved and Rejected
 	 *	Init 06/12/2021
 	 */
	case 's_reward':
		if(!isset($_POST['id']) || $_POST['id'] == 0) echoJson(2, 'Reward ID Requried');
		dbCon();
		chkProfilePermit('reward.html', 'Y');
		$data = $_POST;
		unset($data['id']);
		$data['updated_at'] = date('Y-m-d H:i:s');
		$updated = $db->where('r.reward_id', $_POST['id'])->update('reward r', $data);
		$id = ($updated) ? $_POST['id'] :0;

		// Send mail to redeemer
		if($id > 0){
			$db->join('technician t', 't.technician_id=r.technician_id', 'left')
				->join('service_center s','s.sc_id=t.sc_id','left')
				->where('r.reward_id', $id);
			$data = $db->getOne('reward r', 'r.*, CONCAT_WS("",s.title," #",s.sc_code) as sc_title,t.firstname, t.lastname, t.technician_code, t.phone, t.email');
			$pk = 'RG'.fillZero($id,8);

		  //  **** Point added for approval or not approval (backout)
			$log_id = rewardRecord($data['user_id'], $data['point_taken'],
				['reward_id' => $id, 'technician_id' => $data['technician_id'],
					'approved' => $data['status'] == 1]);


			if($log_id && isset($data['email']) && validateEmail($data['email'])){
				// print_r($data);
				$arr_status_txt = ['รอตรวจสอบ', 'รายการถูกต้อง', 'รายการไม่ถูกต้อง', 'ยกเลิกรายการ'];
				$txt = 'เจ้าหน้าที่ได้ตรวจสอบรายการสะสมคะแนน `'.@$arr_status_txt[$data['status']].'`';
				mailRewardReq($pk, $data, '<h3>'.$txt.'</h3>');
				setNoti($data['technician_id'], $id, 5, $txt);
			}
		}
		// echo $db->getLastQuery().'<br/>'; // DEBUG
		echoJson(0, $id);
		break;

	/**
	 *	Point Reward Redeem list
	 *	Init 06/12/2021
	 */
	case 'reward_redeem_list':
		dbCon();
		chkProfilePermit('reward_redeem.html');
		if(isset($_POST['status']) && $_POST['status'] != ''){
			$db->where(is_array($_POST['status']) ? 'r.status IN ('.implode(',',$_POST['status']).')' : 'r.status = '.$_POST['status'] );
		}
		if(isset($_POST['search_range']) && $_POST['search_range'] != ''){
			$dt = explode(' - ', $_POST['search_range']);
			$db->where('DATE(r.created_at) BETWEEN "'.$dt[0].'" AND "'.$dt[1].'"' );
		}
		if(isset($_POST['technician_name']) && $_POST['technician_name'] != ''){
			$db->where('(t.firstname LIKE "%'.$_POST['technician_name'].'%" OR t.lastname LIKE "%'.$_POST['technician_name'].'%")');
		}
		if(isset($_POST['technician_phone']) && $_POST['technician_phone'] != ''){
			$db->where('t.phone LIKE "%'.$_POST['technician_phone'].'%"' );
		}
		if(isset($_POST['item_search']) && $_POST['item_search'] != ''){
			$db->where('(r.item_code LIKE "%'.$_POST['item_search'].'%" OR r.item_title LIKE "%'.$_POST['item_search'].'%")');
		}
		if(isset($_POST['reward_category_id']) && $_POST['reward_category_id'] > -1){
			$db->join('reward_item i','i.rw_item_id=r.rw_item_id','left');
			$db->where('i.reward_category_id', $_POST['reward_category_id']  );
		}
		if(isset($_POST['reward_id']) && $_POST['reward_id'] != "") $db->where('r.reward_id', (int) filter_var($_POST['reward_id'],FILTER_SANITIZE_NUMBER_INT) );

		$db->orderBy('r.updated_at','desc')->groupBy('r.redeem_id');
		$db->join('technician t','t.technician_id=r.technician_id', 'left');
		$db->join('reward_item i','i.rw_item_id=r.rw_item_id', 'left');
		$db->join('address a','a.add_id=r.address_id', 'left');
		if($_SESSION['auth_roles'] <= 1 && $_SESSION['auth_sc_id'] != -1) { // match sc
			$db->where('t.sc_id', $_SESSION['auth_sc_id']);
		}
		$list = $db->get('reward_redeem r',
			isset($_POST['limit']) ? (int)$_POST['limit'] : 50,
			'r.redeem_id,r.item_title,r.item_code,r.rw_item_id,r.qty,r.point_used,r.status,r.user_id,r.technician_id,r.address_id,r.created_at as rr_created,r.updated_at as rr_updated ,CONCAT_WS("",t.name_title, t.firstname," ",t.lastname) as technician_name, t.technician_code, t.phone as technician_phone, i.reward_category_id, i.remark as item_remark, a.*');
		echoJson(0, $list);
		break;

	/**
 	 *	Redeem Re-Save
 	 *	Init 06/12/2021
 	 */
	case 's_reward_redeem':
		if(!isset($_POST['id']) || $_POST['id'] == 0) echoJson(2, 'Redeem ID Requried');
		dbCon();
		chkProfilePermit('reward_redeem.html', 'Y');

		// Save modified `address`
		if(isset($_POST['address']) && isset($_POST['address_id']) && $_POST['address_id'] > 0){
			if(!isset($_POST['address']['house_no']) || $_POST['address']['house_no'] == '') echoJson(3, 'House No. Required');
			if(!isset($_POST['address']['subdistrict']) || $_POST['address']['subdistrict'] == '') echoJson(5, 'Subdistrict Required');
			if(!isset($_POST['address']['district']) || $_POST['address']['district'] == '') echoJson(6, 'District Required');
			if(!isset($_POST['address']['province']) || $_POST['address']['province'] == '') echoJson(7, 'Province Required');
			if(!isset($_POST['address']['postcode']) || $_POST['address']['postcode'] == '') echoJson(8, 'Phone Required');
			if(!isset($_POST['address']['phone']) || $_POST['address']['phone'] == '') echoJson(8, 'Phone Required');
			$data = $_POST['address'];
			$data['updated_at'] = date('Y-m-d H:i:s');
			$add_updated = $db->where('add_id', $_POST['address_id'])
				->update('address', $data);
		}

		$data = $_POST;
		unset($data['id'],$data['address']);
		$data['updated_at'] = date('Y-m-d H:i:s');
		$db->where('r.redeem_id', $_POST['id']);
		$updated = $db->update('reward_redeem r', $data);
		$id = ($updated) ? $_POST['id'] :0;
		// echo $db->getLastQuery().'<br/>'; // DEBUG

		// Send mail to customer and logging
		if($id > 0){
			$db->join('technician t', 't.technician_id=r.technician_id', 'left')
				->join('service_center s','s.sc_id=t.sc_id','left')
				->where('r.redeem_id', $id);
			$data = $db->getOne('reward_redeem r', 'r.*, CONCAT_WS("",s.title," #",s.sc_code) as sc_title,t.firstname, t.lastname, t.technician_code, t.phone, t.email');
			$pk = 'RD'.fillZero($id,8);

			//  **** Point re-added for rejected (0) or cancelled (4) (readd last records)
		 $log_id = rewardRecord($data['user_id'], $data['point_used'] * (-1), [
				'redeem_id' => $id, 'technician_id' => $data['technician_id'],
				'approved' => $data['status'] == 0 || $data['status'] == 4 ? false : true
			]);

			if($log_id > 0 && isset($data['email']) && $data['email'] != ''){
				// print_r($data);
				$arr_status_txt = ['ไม่สำเร็จ', 'แลกสำเร็จ', 'กำลังจัดของ', 'จัดส่งแล้ว', 'ยกเลิก'];
				$txt = 'เจ้าหน้าที่ได้ตรวจสอบการแลกคะแนนแล้ว `'.@$arr_status_txt[$data['status']].'`';
				// mailWarranty($pk, $data, '<h3>'.$txt.'</h3>');
				setNoti($data['technician_id'], $data['redeem_id'], 6, $txt);
			}
		}
		// echo $db->getLastQuery().'<br/>'; // DEBUG
		echoJson(0, $id);
		break;


	/**
	 *	Number of Point Reward Transaction
	 *	Init 06/12/2021
	 */
	case 'reward_redeem_count':
		dbCon();
		chkProfilePermit('reward_redeem.html');
		$cc = $db->getValue('reward_redeem', 'COUNT(*)');
		echoJson(0, ['no_rw' => $cc]);
		break;


	/**
	 *	Reward Point Log Save
	 *	Init 22/12/2021
	 */
	case 's_reward_log':
		if(!isset($_POST['id']) || $_POST['id'] == '') echoJson(2, 'Reward Point ID Requried');
		dbCon();
		chkProfilePermit('reward_history.html', 'Y');
		$data['user_id'] = $_SESSION['auth_user_id'];
		$data['updated_at'] = date('Y-m-d H:i:s');
		$data['balance'] =  (float) $_POST['balance'];
		$data['point'] =  (float) $_POST['point'];
		$data['status'] =  (int) $_POST['status'];
		$db->where('l.rw_point_id',$_POST['id']);
		$updated = $db->update('reward_point_history l', $data);
		$id = ($updated) ? $_POST['id'] :0;
		// echo $db->getLastQuery().'<br/>'; // DEBUG
		echoJson(0, $id);
		break;

	/**
	 *	Get Reward / Redeem point history
	 *	Init 22/12/2021
	 */
	 case 'reward_point_log':
		dbCon();
		chkProfilePermit('reward_history.html');
		if(isset($_POST['search_range']) && $_POST['search_range'] != ''){
			$dt = explode(' - ', $_POST['search_range']);
			$db->where('DATE(l.created_at) BETWEEN "'.$dt[0].'" AND "'.$dt[1].'"' );
		}
		if(isset($_POST['technician']) && $_POST['technician'] != ''){
			$db->where('(t.technician_id = "'.$_POST['technician'].'" OR t.firstname LIKE "%'.$_POST['technician'].'%" OR t.lastname LIKE "%'.$_POST['technician'].'%" OR t.phone LIKE "%'.$_POST['technician'].'%" OR t.technician_code LIKE "%'.$_POST['technician'].'%" OR t.email LIKE "'.$_POST['technician'].'")' );
		}
		if(isset($_POST['item_search']) && $_POST['item_search'] != ''){
			$db->where('(rw.product_model LIKE "%'.$_POST['item_title'].'%" OR rw.product_sn LIKE "%'.$_POST['item_search'].'%" OR r.item_code LIKE "%'.$_POST['item_search'].'%" OR r.item_title LIKE "%'.$_POST['item_search'].'%")' );
		}
		if(isset($_POST['reward_category_id']) && $_POST['reward_category_id'] > -1){
			$db->join('reward_item i','i.rw_item_id=r.rw_item_id','left');
			$db->where('i.reward_category_id', $_POST['reward_category_id']  );
		}
		if(isset($_POST['reward_id']) && $_POST['reward_id'] != "") $db->where('l.reward_id', (int) filter_var($_POST['reward_id'],FILTER_SANITIZE_NUMBER_INT) );
		if(isset($_POST['redeem_id']) && $_POST['redeem_id'] != "") $db->where('l.redeem_id', (int) filter_var($_POST['redeem_id'],FILTER_SANITIZE_NUMBER_INT) );

		$db->orderBy('l.created_at','desc')->groupBy('l.rw_point_id')
			->join('reward_redeem r','r.redeem_id=l.redeem_id','left')
			->join('reward rw','rw.reward_id=l.reward_id','left');
		$db->join('technician t', 't.technician_id=l.technician_id','left');//'(t.technician_id=r.technician_id OR t.technician_id=rw.technician_id)', 'left');
		$list = $db->get('reward_point_history l',
			isset($_POST['limit']) ? (int)$_POST['limit'] : 50,
			'l.*,r.item_title,r.item_code,r.status as redeem_status,rw.status as reward_status,'.
			'rw.product_model,rw.product_sn,CONCAT_WS("",t.name_title, t.firstname," ",t.lastname) as technician_name, '.
			't.technician_code, t.phone as technician_phone');
		echoJson(0, ['list' => $list]);
		break;


	/**
	 *	Cancel Reward and return `point`
	 *	Init 21/12/2022, Modified 26/02/2023
	 */
	case 'x_reward':
		if(!isset($_POST['pk_id']) || $_POST['pk_id'] == 0) echoJson(2, 'PK Requried');
		chkProfilePermit('reward.html', 'Y');
		dbCon();
		$rw = $db->where('reward_id', $_POST['pk_id'])->where('status', 1)->getOne('reward', 'reward_id,user_id,point_taken,technician_id');
		if($rw['reward_id'] > 0){
			$pts = getPoints('', $rw['user_id'],true);

			if($pts['balance'] - (float) $rw['point_taken'] < 0)
				echoJson(time(), 'คะแนนของผู้ใช้งานคนนี้ไม่เพียงพอในการเรียกคะแนนคืน หรืออาจจะถูกใช้งานแลกของรางวัลไปแล้ว คะแนนเรียกคืน '.$rw['point_taken'].'คะแนน'."\n".'(ปัจจุบันมี '.$pts['balance'].' คะแนน )',$rw);

			// Deduct point suddenly, if reject will be withdrawal
			$log_id = rewardRecord($rw['user_id'], $rw['point_taken'] * (-1),
				['redeem_id' => -1, 'technician_id' => $rw['technician_id'], 'approved' => true] // redeem_id = -1 meant cancel
			);

			if(empty($log_id) || $log_id == 0){
				echoJson(time(), 'ไม่สามารถบันทึกคะแนนคืนได้', $log_id);
			}else{
				$db->where('reward_id', $rw['reward_id'])->update('reward',
					['status' => 3, 'updated_at' => date('Y-m-d H:i:s')]); // 3 = ยกเลิกสะสม, global.js
				echoJson(0, $log_id);
			}
		}else echoJson(time(), 'ไม่พบรายการสะสม', $rw);
		break;

	/**
	 *	Cancel Redeemtion
	 *	Init 26/02/2023
	 */
	case 'x_redeem':
		if(!isset($_POST['pk_id']) || $_POST['pk_id'] == 0) echoJson(2, 'PK Requried');
		chkProfilePermit('reward_redeem.html', 'Y');
		dbCon();
		$rw = $db->where('redeem_id', $_POST['pk_id'])->where('status', 1)->getOne('reward_redeem', 'redeem_id,user_id,point_used,qty,technician_id');
		if($rw['redeem_id'] > 0){

			// Increase point suddenly, if reject will be withdrawal
			$log_id = rewardRecord($rw['user_id'], $rw['point_used'],
				['reward_id' => -1, 'technician_id' => $rw['technician_id'], 'approved' => true] // reward_id = -1 meant cancel
			);

			if(empty($log_id) || $log_id == 0){
				echoJson(time(), 'ไม่สามารถบันทึกคะแนนได้', $log_id);
			}else{

				$db->where('redeem_id', $rw['redeem_id'])->update('reward_redeem',
					['status' => 4, 'updated_at' => date('Y-m-d H:i:s')]); // 4 = ยกเลิกแลก, global.js
				echoJson(0, $log_id);
			}
		}else echoJson(time(), 'ไม่พบรายการแลกรางวัล', $rw);
		break;


	/**
	 *	Global Remove (x_)
	 *	Init 07/08/2021
	 */
	case 'x_news':
	case 'x_customer':
	case 'x_item':
	case 'x_product_serial':
	case 'x_service_pettycash':
	case 'x_serv_req':
	case 'x_product':
	case 'x_product_type':
	case 'x_reward_item':
	case 'x_claim':
	case 'x_sc':
	case 'x_store':
		if(!isset($_POST['pk_id']) || $_POST['pk_id'] == 0) echoJson(2, 'PK Requried');
		$pk = (int) $_POST['pk_id'];
		$tbl = '';
		dbCon();

		if($action == 'x_serv_req') {
			chkProfilePermit('service_request.html', 'Y');
			$res = $db->where('service_id', $pk)
				->update('service_request',
				['status' => -1, 'updated_at' => date('Y-m-d H:i:s')]);
			echoJson(0, ($res) ? $_POST['pk_id'] :0); exit;
		}else if($action == 'x_news') {
			chkProfilePermit('news.html', 'Y');
			$db->where('news_id', $pk);
			$tbl = 'news';
		}else if($action == 'x_customer') {
			chkProfilePermit('customer.html', 'Y');
			$db->where('customer_id', $pk);
			$tbl = 'customer';
		}else if($action == 'x_stock') {
			chkProfilePermit('part.html', 'Y');
			$db->where('item_id', $pk);
			$tbl = 'stock_item';
		}else if($action == 'x_product_serial') {
			chkProfilePermit('serial.html', 'Y');
			$db->where('serial_id', $pk);
			$tbl = 'product_serial';
		}else if($action == 'x_service_pettycash') {
			chkProfilePermit('service_pettycash.html', 'Y');
			$db->where('sptc_id', $pk);
			$tbl = 'service_pettycash';
		}else if($action == 'x_product') {
			chkProfilePermit('product.html', 'Y');
			$db->where('product_id', $pk);
			$tbl = 'product_model';
		}else if($action == 'x_product_type') {
			chkProfilePermit('product_type.html', 'Y');
			$db->where('product_type_id', $pk);
			$tbl = 'product_type';
		}else if($action == 'x_reward_item') {
			chkProfilePermit('reward_item.html', 'Y');
			$db->where('rw_item_id', $pk);
			$tbl = 'reward_item';
		}else if($action == 'x_claim') {
			chkProfilePermit('claim.html', 'Y');
			$db->where('claim_id', $pk);
			$tbl = 'claim';
		}else if($action == 'x_sc') {
			chkProfilePermit('sc_list.html', 'Y');
			$db->where('sc_id', $pk);
			$tbl = 'service_center';
		}else if($action == 'x_store') {
			chkProfilePermit('stores.html', 'Y');
			$db->where('dealer_id', $pk);
			$tbl = 'serivice_dealer';
		}
		$res = $db->delete($tbl);
		$id = ($res) ? $_POST['pk_id'] :0;
		echoJson(0, $id);
		break;


	/**
	 *	Batch recheck service request to retreive `serial_id` by serial number
	 *	Init 06/09/2021
	 */
	case 'batch_register_find_serials':
			dbCon();
			$list = $db->where('r.serial_id IS NULL') // IS NULL
				->orderBy('r.updated_at','DESC')
				->get('product_register r', 1000,'indoor_sn,outdoor_sn,register_id');
			// echo '<pre>';
			foreach ($list as $key => $v) {
				$sid = $db->where('serial_no','%'.$v['indoor_sn'],'like')->getValue('product_serial','serial_id');
				$sid2 = $db->where('serial_no','%'.$v['indoor_sn'],'like')->getValue('product_serial','serial_id');
				if(!isset($sid) || $sid == 0){
					$sid = $sid2;
				}
				$res = $db->where('register_id', $v['register_id'])
					->update('product_register', ['serial_id' => $sid ?? 0, 'serial_id2' => $sid2 ?? NULL] );
				$sid = '';$sid2 = '';
				echo $res.' => '.$db->getLastQuery()."\n"; // DEBUG
				// print_r($res);
			}// end foreach
		break;

	/**
	 *	Batch recheck product register to retreive `serial_id` by serial number
	 *	Init 06/09/2021
	 */
	case 'batch_serv_req_find_serials':
			dbCon();
			$list = $db->where('r.serial_id IS NULL')
				->orderBy('r.updated_at','DESC')
				->get('service_request r', 1000,'indoor_sn,outdoor_sn,service_id');
			foreach ($list as $key => $v) {
				$sid = $db->where('serial_no','%'.$v['indoor_sn'],'like')->getValue('product_serial','serial_id');
				$sid2 = $db->where('serial_no','%'.$v['indoor_sn'],'like')->getValue('product_serial','serial_id');
				if(!isset($sid) || $sid == 0){
					$sid = $sid2;
				}
				$res = $db->where('service_id', $v['service_id'])
				->update('service_request', [
					'serial_id' => $sid ?? 0,
					'serial_id2' => $sid2 ?? NULL
				]);
				$sid = '';$sid2 = '';
				echo $res.' => '.$db->getLastQuery()."\n"; // DEBUG
				// print_r($res);
			}// end foreach
		break;

		/**
		 *	Batch count a number of item/product to stock_out
		 *	Init 29/09/2021
		 */
		case 'batch_stock_out_count_item':
				dbCon();
				$list = $db//->where('no_sn',0)->where('no_it',0)
					->orderBy('updated_at','DESC')
					->get('stock_out', 5000,'stock_out_id');
				foreach ($list as $key => $v) {
					recountStockOut($v['stock_out_id']);
					echo $res.' => '.$db->getLastQuery()."\n"; // DEBUG
					// print_r($res);
				}// end foreach
			break;

		/**
		 *	Batch re-write empty product model (opt) to serial number
		 *	Init 15/10/2021
		 */
		case 'batch_model_opt_serials':
				dbCon();
				$list = $db->where('s.product_id > 0 AND s.model_code_opt IS NULL')
					->orderBy('s.updated_at','DESC')
					->get('product_serial s', 1000,'s.serial_id, s.product_id, s.line');

				foreach ($list as $key => $v) {
					if($v['line'] == 'INDOOR' || $v['line'] == 'indoor'){
						$model = $db->where('product_id', $v['product_id'])->getValue('product_model','model_indoor');
						$db->where('serial_id', $v['serial_id'])->update('product_serial', ['model_code_opt' => $model]);
					}else if($v['line'] == 'OUTDOOR' || $v['line'] == 'outdoor'){
						$model = $db->where('product_id', $v['product_id'])->getValue('product_model','model_outdoor');
						$db->where('serial_id', $v['serial_id'])->update('product_serial', ['model_code_opt' => $model]);
					}

					echo $res.' => '.$db->getLastQuery()."\n"; // DEBUG
					// print_r($res);
				}// end foreach
			break;


	case '__test_fcm':
		dbCon();
			$a = setNoti(20, 10, 2, "Welcome to Starbuck Coffee WG") ;
			print_r($a);
		break;

	case '__test_otp':
		$res = sendOTP('0999199564', '998999');
		print_r($res);
		break;

	case '__test_email':
		dbCon();
		// $data = $db->where('r.service_id', 124) // = nortop.sr
			// ->getOne('service_request r');
		// $r = mailSerReq('SR'.fillZero($data['service_id'],8), $data, '<h3>TEST SEND MAIL</h3> ขอบพระคุณ', false);
		// print_r($r);
		sendmail('test sending', '<p><strong><span style="font-size: 16pt;">ยินดีต้อนรับสู่เว็บไซต์และแอปพลิเคชัน SILPA</span></strong></p><p><strong>ขอบคุณสำหรับการลงทะเบียนเพื่อใช้บริการของ SILPA</strong></p><p><strong>โปรดยืนยันที่อยู่อีเมล์ของคุณภายใน 24 ชั่วโมง เพื่อเริ่มรับชมข่าวสารในวงการศิลปะ</strong></p><p><strong>สามารถร่วมชมผลงานศิลปะและส่งกำลังใจให้กับศิลปินที่คุณชื่นชอบ</strong></p><p><strong>พร้อมรับสิทธิพิเศษมากมาย สุดพิเศษสำหรับท่านที่ทำการยืนยันอีเมลแล้วเท่านั้น!</strong></p>' ,'nortop.sr@gmail.com', 'Nortop Sri');
		break;

		//****************************************************************

	default: echoJson(9000);
}

} // end of base file check

//////////////////////////////////////////////////////////////





function saveClaim(&$data, &$detail = []){
	global $db;
	$data['updated_at'] = date('Y-m-d H:i:s');
	$data['user_id_created'] = isset($data['user_id_created']) && $data['user_id_created'] != ''
		? $data['user_id_created'] :  @$_SESSION['auth_user_id'];
	$data['status'] = 1;
	if(!isset($data['date_claim'])) $data['date_claim'] = date('Y-m-d');
	if($data['claim_id'] == 'new'){
		unset($data['claim_id']);
		$data['created_at'] = date('Y-m-d H:i:s');
		$cid = $db->insert('claim', $data);
	}else{
		$db->where('c.claim_id', $data['claim_id']);
		$updated = $db->update('claim c', $data);
		$cid = ($updated) ? $data['claim_id'] :0;
	}
	// print_r($detail);

	if($cid > 0 && is_array($detail)){
		$i = 1;
		$total_claim = 0;
		$db->where('claim_id', $cid)->delete('claim_detail'); // reset
		foreach ($detail as $key => $v) {
			// print_r($v);
			if(!is_array($v)) continue;
			$did = $db->insert('claim_detail', [
				'claim_id' => $cid,
				'item_id' => @$v['item_id'],
				'sort_order' => $i,
				'item_code' => @$v['item_code'],
				'item_name' => @$v['item_text'],
				'item_type' => @$v['item_type'],
				'qty' => $v['qty'],
				'qty_unit' => $v['qty_unit'],
				'cost' => $v['cost'],
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s')
			]);
			$i++;
			$total_claim += (float)$v['qty']*(float)$v['cost'];
		} // end foreach
		if($total_claim > 0){
			$db->where('claim_id', $cid)->update('claim',
				['expense_cost' => $total_claim, 'item_no' => $i]);
		}

	}
	// echo $db->getLastQuery().'<br/>'; // DEBUG
	return $cid;
}

function saveStockPickup(&$data, &$detail = []){
	global $db;
	unset($data['id']);
	$data['updated_at'] = date('Y-m-d H:i:s');
	$data['out_type'] = 'PICKUP';
	emptyNum($data['service_id']);

	$data['user_id_created'] = isset($data['user_id_created']) && $data['user_id_created'] > 0
		? $data['user_id_created'] :  @$_SESSION['auth_user_id'];
	$data['status'] = 1;
	if(!isset($data['datetime_pickup'])) $data['datetime_pickup'] = date('Y-m-d');

	if($data['stock_out_id'] == 'new'){
		unset($data['stock_out_id']);
		$data['created_at'] = date('Y-m-d H:i:s');
		$sid = $db->insert('stock_out', $data);
	}else{
		$db->where('stock_out_id', $data['stock_out_id']);
		$updated = $db->update('stock_out', $data);
		// echo $db->getLastQuery().";\n"; // DEBUG
		$sid = ($updated) ? $data['stock_out_id'] :0;
	}
	// print_r($detail);

	if($sid > 0 && is_array($detail)){
		$i = 1;
		$db->where('stock_out_id', $sid)->delete('stock_out_detail'); // reset
		foreach ($detail as $key => $v) {
			// print_r($v);
			if(!is_array($v)) continue;

			// If Product
			$sn = (isset($v['serial_id']) && $v['serial_id'] > 0)
				? $db->where('serial_id', $v['serial_id'])->getValue('product_serial','serial_no')
				: NULL;

			$did = $db->insert('stock_out_detail', [
				'stock_out_id' => $sid,
				'claim_did' => isset($v['claim_did']) && $v['claim_did'] > 0 ? $v['claim_did'] : 0,
				'item_id' => isset($v['item_id']) && $v['item_id'] > 0 ? $v['item_id'] : 0,
				'serial_id' => isset($v['serial_id']) && $v['serial_id'] > 0 ? $v['serial_id'] : 0,
				'ref' => $sn,
				'mark' => 0,
				'sort_order' => $i,
				'qty' => $v['qty'],
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s')
			]);
			// echo $db->getLastQuery().'<br/>'; // DEBUG
			$i++;
			if(@$v['item_id'] > 0) {
				stockUpdate($v['item_id'], 0 - abs((float) $v['qty']));
			}

		} // end foreach

		// Update total
		recountStockOut($stock_out_id = 0);

	}
	// echo $db->getLastQuery().'<br/>'; // DEBUG
	return $sid;
}

function saveStockReturn($type, &$data, &$detail = []){
	global $db;
	$data['in_type'] = $type;
	$data['updated_at'] = date('Y-m-d H:i:s');
	$data['user_id_created'] = isset($data['user_id_created']) && $data['user_id_created'] != ''
		? $data['user_id_created'] :  @$_SESSION['auth_user_id'];
	if(!isset($data['datetime_return'])) $data['datetime_return'] = date('Y-m-d');
	$data['status'] = 1;
	if($data['stock_in_id'] == 'new'){
		unset($data['stock_in_id']);
		$data['created_at'] = date('Y-m-d H:i:s');
		$sid = $db->insert('stock_in', $data);
	}else{
		$db->where('p.stock_in_id', $data['stock_in_id']);
		$updated = $db->update('stock_in p', $data);
		$sid = ($updated) ? $data['stock_in_id'] :0;
	}

	if($sid > 0 && is_array($detail)){
		$i = 1; $no_sn = 0; $no_it = 0;
		$db->where('stock_in_id', $sid)->delete('stock_in_detail'); // reset
		foreach ($detail as $key => $v) {
			// print_r($v);
			if(!is_array($v)) continue;

			$did = $db->insert('stock_in_detail', [
				'stock_in_id' => $sid,
				'stock_out_did' => isset($v['stock_out_did']) && $v['stock_out_did'] > 0 ? $v['stock_out_did'] : 0,
				'item_id' => isset($v['item_id']) && $v['item_id'] > 0 ? $v['item_id'] : 0,
				'serial_id' => isset($v['serial_id']) && $v['serial_id'] > 0 ? $v['serial_id'] : 0,
				'sort_order' => $i,
				'qty' => $v['qty'],
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s')
			]);
			$i++;
			if(@$v['serial_id'] > 0) $no_sn++;
			else if(@$v['item_id'] > 0) {
				stockUpdate($v['item_id'], $v['qty']);
				$no_it++;
			}

			// Update stock out mark to `unlock`
			if($did > 0 && @$v['serial_id'] > 0){
				$sn = $db->where('serial_id', $v['serial_id'])->getValue('product_serial','serial_no');
				$db->where('(`ref` like "'.$sn.'" OR `serial_id`='.$v['serial_id'].')')
					->update('stock_out_detail', ['mark' => 1]);
			}
			// echo $db->getLastQuery().";\n"; // DEBUG
		} // end foreach

		// Update total
		$db->where('p.stock_in_id', $data['stock_in_id'])
			->update('stock_in p', ['no_it' => $no_it, 'no_sn' => $no_sn]);

	}
	return $sid;
}


// for search in mysql unicode
 function utf8_unicode($str) {
    $unicode = array();
    $values = array();
    $lookingFor = 1;

    for ($i = 0; $i < strlen( $str ); $i++ ) {
        $thisValue = ord( $str[ $i ] );
        if ( $thisValue < ord('A') ) {
            // exclude 0-9
            if ($thisValue >= ord('0') && $thisValue <= ord('9')) {
                 // number
                 $unicode[] = chr($thisValue);
            }
            else {
                 $unicode[] = '%'.dechex($thisValue);
            }
        } else {
            if ( $thisValue < 128) {
                $unicode[] = $str[ $i ];
            } else {
                if ( count( $values ) == 0 ) {
                    $lookingFor = ( $thisValue < 224 ) ? 2 : 3;
                }
                $values[] = $thisValue;
                if ( count( $values ) == $lookingFor ) {
                    $number = ( $lookingFor == 3 ) ?
                        ( ( $values[0] % 16 ) * 4096 ) + ( ( $values[1] % 64 ) * 64 ) + ( $values[2] % 64 ):
                        ( ( $values[0] % 32 ) * 64 ) + ( $values[1] % 64 );
                    $number = dechex($number);
                    $unicode[] = (strlen($number)==3)? "\\\\\\\\u0".$number: "\\\\\\\\u".$number; // MySQL, there is a second layer of escaping https://stackoverflow.com/a/13327605/5561377
                    $values = array();
                    $lookingFor = 1;
                } // if
            } // if
        }
    } // for
		// echo implode("",$unicode);
    return implode("",$unicode);
}
