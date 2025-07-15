<?php
/*
 * Mavell CSMS API
 *
 *
 * @since 1.0.0
 * @access public
 *
 */
require_once('vendor/autoload.php');
require_once('config.main.php');
require_once('function.php');
use DocxMerge\DocxMerge;

$dbh = new \PDO('mysql:dbname='.MYSQL_BASENAME.';host='.MYSQL_HOST.';charset=utf8mb4', MYSQL_USER, MYSQL_PASS);
$auth = new \Delight\Auth\Auth($dbh);
echoChkLogin();

// Check the action request
if(!isset($_REQUEST['a'])) echoJson(90, 'No action');
$action = trim($_REQUEST['a']);

switch ($action) {
	//////////////////////////////////////////////////////////////////////////////////////////////////

  /**
	 *	Service Request Form Generator
	 */
  case 'form_service_request':
		if(!isset($_POST['service_id'])) echoJson(2, 'Service ID Requried');
		dbCon();
		chkProfilePermit('service_request.html', 'can_edit');
		$phpWord = new \PhpOffice\PhpWord\PhpWord();
		$templateProcessor =  $phpWord->loadTemplate('doctpl/serv_req.docx');

    $id = $_POST['service_id'];
    $field = [];
    $report_key = array(
      'pk','created_at','sc_title','sc_code','sc_phone','technician_name','sc_address','sc_phone',
      'requester_title','requester_name','requester_phone','address_no',
      'address_moo','address_soi','address_building','address_road',
      'address_subdistrict','address_district','address_province','address_postcode','address_latlng',
      'request_latlng','product_type_txt','tbl_fcu','tbl_con','product_model',
      'outdoor_sn','indoor_sn','served_detail','error_code_txt','part_list','total_cost',
      'served_start','served_end','technician_phone','technician_code',
      'served_error_code','cause_broken','report_repair','part_list',
      'product_model1','product_model2', 'in_warr', 'out_warr',
      'v1','v2','v3','v4','v5','v6','v7','v8',
      'v9','v10','v11','v12','v13','v14','v15','v16',
      'v17','v18','v19','room_size',
      'repair_cost','repair_txt','service_cost','service_txt','transport_cost','transport_cost_txt','other_cost','other_cost_txt',
      'tbl_cause1','tbl_cause2','tbl_cause3','tbl_cause4','cause_txt'
    );
    $field['pk'] = 'SR'.fillZero($id,8);

		$req = $db->where('r.service_id', $id)->getOne('service_request r','r.*');

		$tech = ($req['technician_id'] > 0)
      ? $db->where('t.technician_id',$req['technician_id'])
  			->join('users u','t.user_id=u.id','left')
  			->join('service_center s','s.sc_id=t.sc_id','left')
  			->getOne('technician t','s.title as sc_title, s.sc_code, s.address as sc_address, '.
          's.phone as sc_phone, u.username,t.sc_id,t.user_id,t.technician_code, '.
          'CONCAT_WS("",t.name_title,t.firstname," ",t.lastname) as technician_name,t.phone as technician_phone')
      : [];

    // ------ Served Table ------
    $served = $db->where('d.service_id', $id)
      ->where('d.timeline_type', 'served')
      ->orderBy('d.updated_at','DESC')
      ->getOne('service_request_detail d','d.served_start,d.served_end,d.detail');

    if(isset($served['detail'])){
      $served = array_merge($served, json_decode($served['detail'], true));
      unset($served['detail']);
    }

    $served['created_at'] = isset($served['created_at']) ? date("d-m-Y", strtotime($served['created_at'])) : '';
    $served['served_start'] = isset($served['served_start']) ? $served['served_start'] : '';
    $served['served_end'] = isset($served['served_end']) ? $served['served_end'] : '';
    $served['cause_broken'] = isset($served['cause_broken']) ? $served['cause_broken'] : '';
    $served['served_detail'] = isset($served['served_detail']) ? $served['served_detail'] : '';
    $served['tbl_fcu'] = @$served['tbl_fcu'] == 1 ? '✓' : ' ';
    $served['tbl_con'] = @$served['tbl_condensing'] == 1 ? '✓' : ' ';
    $served['in_warr'] = @$served['valid_warranty'] == 'yes' ? '✓' : ' ';
    $served['out_warr'] = @$served['valid_warranty'] == 'no' ? '✓' : ' ';
    $served['warranty_id'] = isset($served['warranty_id']) ? $served['warranty_id'] : '';
    $served['served_error_code'] = isset($served['error_code']) ? $served['error_code'] : '';
    $served['report_repair'] = isset($served['report_repair']) ? $served['report_repair'] : '';

    $served['tbl_cause1'] = @$served['tbl_cause'] == 'การติดตั้ง' ? '✓' : '[ ]';
    $served['tbl_cause2'] = @$served['tbl_cause'] == 'การประกอบ' ? '✓' : '[ ]';
    $served['tbl_cause3'] = @$served['tbl_cause'] == 'คุณภาพของวัตถุดิบ' ? '✓' : '[ ]';
    $served['tbl_cause4'] = @$served['tbl_cause'] == 'อื่นๆ' ? '✓' : '[ ]';

    $served['v1'] = isset($served['tbl_volt_pre']) ? $served['tbl_volt_pre'] : '';
    $served['v2'] = isset($served['tbl_volt_post']) ? $served['tbl_volt_post'] : '';
    $served['v3'] = isset($served['tbl_amp_pre']) ? $served['tbl_amp_pre'] : '';
    $served['v4'] = isset($served['tbl_amp_post']) ? $served['tbl_amp_post'] : '';
    $served['v5'] = isset($served['tbl_term_remote_pre']) ? $served['tbl_term_remote_pre'] : '';
    $served['v6'] = isset($served['tbl_term_remote_post']) ? $served['tbl_term_remote_post'] : '';
    $served['v7'] = isset($served['tbl_psil_pre']) ? $served['tbl_psil_pre'] : '';
    $served['v8'] = isset($served['tbl_psil_post']) ? $served['tbl_psil_post'] : '';
    $served['v9'] = isset($served['tbl_psih_pre']) ? $served['tbl_psih_pre'] : '';
    $served['v10'] = isset($served['tbl_psih_post']) ? $served['tbl_psih_post'] : '';
    $served['v11'] = isset($served['tbl_fcu_out_pre']) ? $served['tbl_fcu_out_pre'] : '';
    $served['v12'] = isset($served['tbl_fcu_out_post']) ? $served['tbl_fcu_out_post'] : '';
    $served['v13'] = isset($served['tbl_fcu_in_pre']) ? $served['tbl_fcu_in_pre'] : '';
    $served['v14'] = isset($served['tbl_fcu_in_post']) ? $served['tbl_fcu_in_post'] : '';
    $served['v15'] = isset($served['tbl_cdu_out_pre']) ? $served['tbl_cdu_out_pre'] : '';
    $served['v16'] = isset($served['tbl_cdu_out_post']) ? $served['tbl_cdu_out_post'] : '';
    $served['v17'] = isset($served['tbl_cdu_in_pre']) ? $served['tbl_cdu_in_pre'] : '';
    $served['v18'] = isset($served['tbl_cdu_in_post']) ? $served['tbl_cdu_in_post'] : '';
    $served['v19'] = isset($served['pipe_length']) ? $served['pipe_length'] : '';
    $served['room_size'] = isset($served['room_size']) ? $served['room_size'] : '';
    $served['cause_txt'] = isset($served['cause_other_txt']) ? $served['cause_other_txt'] : '___________';

		$field['product_type_txt'] = @$req['product_type'] > -2 ? productTypeTxt($req['product_type']) : '';

    // Product Model Indoor (re-call from table)
    $field['product_model1'] = isset($served['indoor_sn']) && $served['indoor_sn'] != ''
      ? $db-where('serial_no', $served['indoor_sn'])->getValue('product_serial','model_code_opt')
      : $served['product_model'];

    // Product Model Outdoor (re-call from table)
    $field['product_model2'] = isset($served['outdoor_sn']) && $served['outdoor_sn'] != ''
      ? $db-where('serial_no', $served['outdoor_sn'])->getValue('product_serial','model_code_opt')
      : 'N/A';

    // ------ Fixed Table ------
    $fixed = $db->where('d.service_id', $id)
      ->where('d.timeline_type', 'fixed')
      ->orderBy('d.updated_at','DESC')
      ->getOne('service_request_detail d','d.detail');

    // echo $db->getLastQuery().'<br/>'; // DEBUG

    $field['part_list'] = '';
    if(isset($fixed['detail'])){
      $fixed = json_decode($fixed['detail'], true);
      // print_r($fixed);
      if(isset($fixed['claim'])) foreach ($fixed['claim'] as $key => $v) {
        $field['part_list'] .= $v['item_text'].' ฿'.$v['cost'].'x'.$v['qty'].' '.$v['qty_unit'].' / ';
      }
      unset($fixed['detail']);
    }else $fixed = [];
    $field['repair_cost'] = isset($fixed['repair_cost']) ? $fixed['repair_cost'] : '';
    $field['repair_txt'] = isset($fixed['repair_txt']) ? $fixed['repair_txt'] : '';
    $field['service_cost'] = isset($fixed['service_cost']) ? $fixed['service_cost'] : '';
    $field['service_txt'] = isset($fixed['service_txt']) ? $fixed['service_txt'] : '';
    $field['transport_cost'] = isset($fixed['transport_cost']) ? $fixed['transport_cost'] : '';
    $field['transport_cost_txt'] = isset($fixed['transport_cost_txt']) ? $fixed['transport_cost_txt'] : '';
    $field['other_cost_txt'] = isset($fixed['other_cost_txt']) ? $fixed['other_cost_txt'] : '';
    $field['other_cost'] = isset($fixed['other_cost']) ? $fixed['other_cost'] : '';
    $field['total_cost'] = isset($fixed['total_cost']) ? $fixed['total_cost'] : '';


    $field = array_merge($field, $served, $req, $tech);

		// Put Variables
		foreach ($report_key as $key) {
			if(!is_array($value)){
				$templateProcessor->setValue($key, (isset($field[$key]) && $field[$key] !='' ? $field[$key] : '') ); // ✓
			}
		}

		$temp_file = '_report/form_serv_req_'.$id.'_'.date('Ymd').'.docx';
		$templateProcessor->saveAs($temp_file);

    // $phpWord = new \PhpOffice\PhpWord\PhpWord();
	  //  \PhpOffice\PhpWord\Settings::setPdfRendererPath('vendor/mpdf/mpdf');
	  //  \PhpOffice\PhpWord\Settings::setPdfRendererName('MPDF');

    // $phpWord = \PhpOffice\PhpWord\IOFactory::load($temp_file);
    // header('Content-Type: application/pdf');
    // header('Content-Disposition: attachment;filename="form_serv_req_'.$id.'_'.date('Ymd').'.pdf"');
    // header('Cache-Control: max-age=0');
        // $phpWord->save('php://output', 'PDF');
    // $phpWord->save('test.pdf', 'PDF');
		echoJson(0, ['file' => PUBLIC_DOMAIN.APP_PATH.'/api/'.$temp_file]);
		break;

	//////////////////////////////////////////////////////////////////////////////////////////////////
	default: echoJson(9000);
}

function mergeNRm($file_list,$target_file){
	$dm = new DocxMerge();
	// print_r($file_list); // for debug
	$dm->merge($file_list , $target_file );

	// Removed All temp docx
	for($i=0; $i<$k; $i++){
		@unlink($file_list[$i]);
	}
	// Temp from merged file
	foreach (glob("dm*.tmp") as $filename) {
		@unlink($filename);
	}
}
