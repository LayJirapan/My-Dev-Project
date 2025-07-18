var ldat = {serv_req_query: {filename:[]}, product_model: [], product_type: []};
var gsitekey = "6LcVOJoaAAAAAOZLlQT_bF5EoKZJEXIbqm2-YOxI";
var dll = [13.754145, 100.506498]; // User denied geolocation prompt - default to Bangkok


window.initmap1 = function(tabid) {
 if(tabid == undefined ) tabid = '1'

 if(!navigator || !navigator.geolocation){
   console.log("navigator not found");
   mapAfterInit(tabid, dll[0], dll[1]);
 }else{
   navigator.geolocation.getCurrentPosition(function(position) {
    // $("#home-tab,#home2-tab").click();
    $("#placeManual2-tab").click();  // เปิดแท็บกรอกที่อยู่เอง แทน

     console.log("getting position is succeed");
     // Center on user's current location if geolocation prompt allowed
     mapAfterInit(tabid, position.coords.latitude, position.coords.longitude);
   }, function(error) {
     console.log("getting position is error");
     //error handling
        switch(error.code) {
            case error.PERMISSION_DENIED:
              //User denied the request for Geolocation.
              break;
            case error.POSITION_UNAVAILABLE:
              //Location information is unavailable.
              break;
            case error.TIMEOUT:
              //The request to get user location timed out.
              break;
            case error.UNKNOWN_ERROR:
              //An unknown error occurred.
              break;
        }
        Swal.fire("เว็บเบราว์เซอร์ของท่านได้ปิดการแสดงตำแหน่งปัจจุบันของท่าน เพื่อช่วยให้ช่วยบันทึกข้อมูลลงฟอร์มได้สะดวกและรวดเร็ว กรุณาปรับค่าเป็น Allow Location.",'','success');
     mapAfterInit(tabid, dll[0], dll[1]);
   });
 }

};

window.mapAfterInit = function(tabid, lat, lng){
  let gMap = new google.maps.Map(document.getElementById('mapCanvas'+tabid));
  var latLng = new google.maps.LatLng(lat, lng);
  gMap.setCenter(latLng);
  gMap.setZoom(11);

  let marker = new google.maps.Marker({
    position: latLng,
    title: 'สถานที่ติดตั้ง',
    map: gMap,
    draggable: false
  });
  updateMarkerPosition(tabid, latLng);

   // google.maps.event.addListener(marker, 'dragstart', function() {
   //   // updateMarkerAddress('Dragging...');
   // });

   google.maps.event.addListener(marker, 'drag', function() {
     // updateMarkerStatus('Dragging...');
     updateMarkerPosition(tabid, marker.getPosition());
   });

   // google.maps.event.addListener(marker, 'dragend', function() {
   //   // updateMarkerStatus('Drag ended');
   //   // geocodePosition(marker.getPosition());
   // });
};

window.updateMarkerPosition = function(tabid, latLng) {
  var ll = [latLng.lat(),latLng.lng()].join(',');
  console.info(ll);
  if(tabid == 2) $("input[name=request_latlng2]").val(ll);
  else $("input[name=install_latlng]").val(ll);
};


$($ => {
  "use strict";

  // Create the script tag, set the appropriate attributes
  var script = document.createElement('script');
  script.src = 'https://maps.googleapis.com/maps/api/js?key=AIzaSyB2B1lK3UAPBHg3BqNj9Mm5NTlqtpNmERQ&loading=async&callback=initmap1';
  script.async = true;
  document.head.appendChild(script);

  var q = getUrlVars();
  ldat.query = q;

  if(q.menu && q.menu != ''){
    $("#menu-"+q.menu+"-tab").click();
  }

  $.Thailand({
    database: './plugins/jquery-Thailand/db.json',
    $district: $('input[name="address_subdistrict"]'), // input ของตำบล
    $amphoe: $('input[name="address_district"]'), // input ของอำเภอ
    $province: $('input[name="address_province"]'), // input ของจังหวัด
    $zipcode: $('input[name="address_postcode"]'), // input ของรหัสไปรษณีย์
    onDataFill: function(data){
        console.info('Data Filled', data);
    },
    onLoad: function(){
        console.info('Autocomplete is ready!');
        // $('#loader, .demo').toggle();
    }
  });

  // PRODUCT TYPE
  eb.post("public", "get_product_type", {}, (d) => {
    let tpl = '<option disabled>- โปรดระบุประเภทแอร์มาเวล | Select product type -</option>';
    ldat.product_type = ['(ไม่พบข้อมูล)'];
    $.each(d.list, (k,v) => {
      tpl += `<option value="${eb.snull(v.product_type_id)}">${eb.snull(v.product_type)}</option>`;
      ldat.product_type.push(eb.snull(v.product_type));
    })
    $("#product_type").html(tpl);
  });

  // Product Model
  eb.post("public", "get_product_model", {}, (d) => {
      ldat.product_model = d.list;
      renderProdModel();
  });

  qrReaderInit();


  // Preset the site by get query
  if(ldat.query && ldat.query.sn != null && ldat.query.sn.length > 0){
    $("#qrCodeInfoSec").show();
    $("#mainMenuSec").addClass('col-lg-9','col-md-6');
    $("input[name=indoor_sn]").val(ldat.query.sn);

    $("#qSN").html(ldat.query.sn);
    eb.post("public", "check_sn", {sn: ldat.query.sn},
  		function(d){
        // console.info(d.info);
        if(d.info.mfd) $("#qMFD").html(d.info.mfd);
        $("#qRegiestered").html(d.info.register_id && d.info.register_id > 0 ? "ลงทะเบียนแล้ว" : "ยังไม่ลงทะเบียน");
        if(d.info.model_name) $("#qProductCode").html(eb.snull(d.info.model_name));
        if(d.info.date_installed) $("#qInstDate").html(eb.snull(d.info.date_installed));
        if(d.info.technician_name) $("#qTech").html(eb.snull(d.info.technician_name));

        $.each(d.info,function(k,v){
          $("input[name="+k+"]").val(v)
          if(d.info.register_id && d.info.register_id > 0){
            $("input[name="+k+"]").val(v).attr('disabled','disabled');
          }
        });
        //Resolved input
        $("#isInstaller").removeAttr('disabled','disabled');
      });
  };

  // Dealer / Shop Search
  $('#seller').select2({
    placeholder: 'พิมพ์ชื่อร้าน หรือ ตัวแทนเพื่อค้นหา',
    minimumInputLength: 3,
    ajax: {
      url: eb.api_path+eb.api_version+"/public.html?a=get_service_dealer",
      dataType: 'json',
      quietMillis: 50,
      method: "post",
      language: { inputTooShort: function () { return ''; } },
      processResults: function (d) {
        ldat.dealers = d;
        let items = [];
        $.each(d.data,function(k,v){
          items.push({text: v.name, id: v.dealer_id ? v.dealer_id : -1});
        });
        console.log(items)
        return {results: items, };
      }
    },
  }).on('select2:select', function (e) {
    var data = e.params.data;
    var p = '';
    $.each(ldat.dealers,function(k,v){
      if(v.dealer_id == data.id) {
        ldat.cur_dealer_id = v.dealer_id;
        p = v.phone;
        return false;
      }
    });
    $("#seller_phone").val(p);
    console.log('select2 seller_phone => ', p);
  });

  // Add other dealer
  $("#btnAddDealer").on('click', function(){

    let s = prompt('กรอกชื่อเต็มของร้าน/ตัวแทนจำหน่สยที่ซื้อมาให้ถูกต้อง เพื่อรักษาสิทธิ์ประโยชน์ในงานบริการ')
    if(s != null){
      var n = Math.floor((Math.random() * -100) + 1);
      var newOption = new Option(s, n, false, false);
      $('#seller').append(newOption).val(n).trigger('change');

      let p = prompt('กรอกเบอร์โทรศัพท์ติดต่อร้าน/ตัวแทนจำหน่าย เช่น 0232988899');
      $("#seller_phone").val(p);
      console.log('seller_phone => ', p);
    }
  });

  // OTP Handler
  $('#otpCode').mask('000000').on('keyup',function(){
    if(this.value.length == 6){
      var _otp = this.value;
      grecaptcha.ready(function() {
        grecaptcha.execute(gsitekey, {action: 'otp_verify_register_submit'}).then(function(token) {
          clearInterval(ldat.timerID);
        $('#btnSubmitRegister').attr('disabled','disabled').html('<i class="fa fa-spinner fa-spin fa-fw"></i> กำลังส่งข้อมูล....');

        eb.post("public", "otp_verify_register_submit", {otp: _otp, token: ldat.otp.token, ref: ldat.otp.ref},
      		function(d){
            $('#mod_otp').modal('hide');
            $('#otpCode').val(''); //reset
            ldat.register_query.token = token;
            eb.post("public", "s_product_register", ldat.register_query, function(rt){
      				if(rt.id && rt.id > 0) {
                $('#btnSubmitRegisterReset').click();
                localStorage.setItem("_mv_rg_ids", JSON.stringify(rt));
                ldat.rg_ids = rt;
                setStatusRG();
                Swal.fire('ระบบได้รับเรื่องการลงทะเบียนของท่านแล้ว','','success');
              }else{
                Swal.fire('การลงทะเบียนไม่สำเร็จ กรุณาลองใหม่','','error');
              }
              $('#btnSubmitRegister').removeAttr('disabled').html('<i class="fas fa-paper-plane mr-1"></i> ลงทะเบียน');
              // grecaptcha.reset();
      			});

            setTimeout(function(){
              $('#btnSubmitRegister').removeAttr('disabled').html('<i class="fas fa-paper-plane mr-1"></i> ลงทะเบียน');
            },6000);
          }, null, function(rt){
            Swal.fire(rt.err_msg,'','error');
          });
        });
      });
    }
  });

});


  // Preset in local storage
  ldat.rg_ids = localStorage.getItem("_mv_rg_ids");
  if(ldat.rg_ids) ldat.rg_ids = JSON.parse(ldat.rg_ids);
  if(ldat.rg_ids && ldat.rg_ids.id && ldat.rg_ids.id > 0){
    setStatusRG();
    // grecaptcha.ready(function() {
    //   grecaptcha.execute(gsitekey, {action: 'check_rg_status'}).then(function(token) {
    //   eb.post("public", "check_rg_status", {id: ldat.rg_ids.id, token: token},
    // 		function(d){
    //       if(d.status !== undefined) setStatusRG();
    //     });
    //   });
    // });
  }

  ldat.sr_ids = localStorage.getItem("_mv_sr_ids");
  if(ldat.sr_ids) ldat.sr_ids = JSON.parse(ldat.sr_ids);
  if(ldat.sr_ids && ldat.sr_ids.id && ldat.sr_ids.id > 0){
    grecaptcha.ready(function() {
      grecaptcha.execute(gsitekey, {action: 'check_rg_status'}).then(function(token) {
      eb.post("public", "check_sr_status", {id: ldat.sr_ids.id, token: token},
        function(d){
          if(d.status !== undefined) setStatusSR(d.status);
        });
      });
    });
  }



  // Tab hadler event for Main Menu
  $('#menu-tab a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
    // e.target // newly activated tab
    // e.relatedTarget // previous active tab
    // console.log(e.target.id);
    if(e.target.id == 'menu-req-tab'
      && $(e.target).data('map-loaded') === undefined){
        // Swal.fire('go map2 loading');
        initmap1(2);
        $(e.target).data('map-loaded',1);
        $.Thailand({
          database: './plugins/jquery-Thailand/db.json',
          $district: $('input[name="address_subdistrict2"]'), // input ของตำบล
          $amphoe: $('input[name="address_district2"]'), // input ของอำเภอ
          $province: $('input[name="address_province2"]'), // input ของจังหวัด
          $zipcode: $('input[name="address_postcode2"]'), // input ของรหัสไปรษณีย์
          onDataFill: function(data){
              console.info('Data Filled', data);
          },
          onLoad: function(){
              console.info('Address Autocomplete on serv req is ready!');
              // $('#loader, .demo').toggle();
          }
        });

    }else if(e.target.id == 'menu-faq-tab'
      && $(e.target).data('map-loaded') === undefined){

      // Content list into page tab
      $.getJSON( eb.api_path+"public_faq.json", function( _li ) {
          // console.log(_li);
          // FAQ Category
          var _i = 1;
        $("#faq-tabs").html("");
        $.each(_li, function(k1,v1){
          $("#faq-tabs").append('<a href="#tab'+_i+'" class="nav-link'+(_i==1 ? ' active': '')+'" data-toggle="pill" role="tab" aria-controls="tab'+_i+'" aria-selected="true">'+
              '<i class="mdi mdi-help-circle"></i> '+k1+'</a>');

          $("#faq-tab-content").append('<div class="tab-pane'+(_i==1 ? ' show active': '')+'" id="tab'+_i+'" role="tabpanel" aria-labelledby="tab'+_i+'">'+
            '<div class="accordion" id="accordion-tab-'+_i+'"></div></div>');

          // FAQ Topics
          var _j = 1;
          $.each(v1, function(k2,v2){
            // console.log(v2);
            $('#accordion-tab-'+_i).append('<div class="card">'+
                '<div class="card-header" id="accordion-tab-'+_i+'-heading-'+_j+'">'+
                    '<h5><button class="btn btn-link text-left" type="button" data-toggle="collapse" data-target="#accordion-tab-'+_i+'-content-'+_j+'" aria-expanded="false" aria-controls="accordion-tab-'+_i+'-content-'+_j+'">'+eb.nl2br(v2.title)+'</button></h5>'+
                '</div>'+
                '<div class="collapse'+(_j==1 ? ' show': '')+'" id="accordion-tab-'+_i+'-content-'+_j+'" aria-labelledby="accordion-tab-'+_i+'-heading-'+_j+'" data-parent="#accordion-tab-'+_i+'">'+
                    '<div class="card-body"><p>'+eb.nl2br(v2.detail)+'</p></div>'+
                '</div>'+
            '</div>');
            _j++;
          });
          _i++;

        });
        $(e.target).data('map-loaded',1);
      });

    }
    e.preventDefault();
    $('html,body').animate({
        scrollTop: $("#menu-tabContent").offset().top
    }, 'slow');
  })

  // Toggle Name Title
  $("#customerTitle").on('change',function(){
    if(this.value == "อื่นๆ") $("input[name=customer_title_other]").removeClass('hide');
    else $("input[name=customer_title_other]").addClass('hide');
  });
  $("#requesterTitle").on('change',function(){
    if(this.value == "อื่นๆ") $("input[name=requester_title_other2]").removeClass('hide');
    else $("input[name=requester_title_other2]").addClass('hide');
  });

  // Hide Error code & cause if not repair req
  $("#serviceType").change(function(){
    if(this.value == 'repair'){
      $("#errorCodeSec,#errorCauseSec").show();
    }else{
      $("#errorCodeSec,#errorCauseSec").hide();
    }
  });

  $(".select2").select2();

  // Search S/N for service request
  // Search S/N for service request
  $('#snQuery').on('search', () => findProductSN());
  $("#btnFindSN1").on('click', () => findProductSNInO());
  $("#btnFindSN2").on('click', () => findProductSN());


  // Reset the serv req
  $("#btnSubmitServReqReset").click(function() {
    $('#form_register input, #form_register textarea').val("");
    var _d = localStorage.getItem("_mv_sr_ids");
    if(_d && confirm('ต้องการลบสถานะงานติดตามที่บันทึกไว้บนเครื่องของท่าน?')){
      localStorage.removeItem("_mv_sr_ids");
      $("#statusServRerv").parent().addClass('hide');
    }
  });


  // Toggle ac error code by filltering the cause
  // $("#errorCode").change(function(){
  //   $("#errorCause option").hide();
  //   $("#errorCause option[data-ec="+this.value+"]").show();
  // });

  // Toggle check panel of installer
  $("#isInstaller").click(function() {
      // if(!$(this).is(":checked")){
      //   $("#installerSec").collapse('toggle');
      // }else{
      //   $("#installerSec").collapse('toggle');
      // }
      $("#toggleInstaller").click();
  });

  $('#installerSec').on('shown.bs.collapse', function () {
    // Swal.fire(1);
  });



  //Date range picker
  $('.date').datetimepicker({
      format: 'DD/MM/YYYY',
      allowInputToggle: true,
      defaultDate: moment({
      hour: 2
    }),
  });

  // 1. Save Product Register
  $('#btnSubmitRegister').on('click', function(){
    var q = $('#form_register').serializeObject();
    // console.log(q);
    q.id = ldat.id;
    var pModel= $("#productModel").select2('data'); // if disable
    q.product_model = pModel[0] ? pModel[0].text : null;

    if(ldat.cur_product_type) q.product_type = ldat.cur_product_type;
    if(q.product_type) q.product_type_txt = ldat.product_type[q.product_type];
    // console.table(q);
    if(q.customer_title == 'อื่นๆ' && q.customer_title_other != '') q.customer_title = q.customer_title_other;
    delete q.customer_title_other;

    if(q.customer_title == 'อื่นๆ' && q.customer_title_other != '') q.customer_title = q.customer_title_other;
    else if(q.indoor_sn == '') {
      Swal.fire('กรุณากรอกหมายเลขเครื่องภายใน (Fancoil) / Indoor Unit Serial No.','','warning');
      return false;
    }else if(q.outdoor_sn == '') {
      Swal.fire('หมายเลขเครื่องภายนอก (Condensing) / Outdoor Unit Serial No.','','warning');
      return false;
    }else if(q.customer_phone == q.technician_phone) {
      Swal.fire('หมายเลขโทรศัพท์ลูกค้ากับช่างไม่สามารถใช้หมายเลขเดียวกันได้','','error');
      return false;
    }else if(!q.seller_name || q.seller_name == '') {
      Swal.fire('กรุณากรอกข้อมูลร้านค้า/ตัวแทนจำหน่าย','','error');
      return false;
    }
    if(ldat.cur_dealer_id) q.dealer_id = ldat.cur_dealer_id;

    if(
      eb.isnt('รหัสสินค้า / Product Serial ID', q.serial_id)
      && eb.isnt('ประเภทสินค้า / Product Type', q.product_type)
      && eb.isnt("รุ่นสินค้า / Model ID", q.product_model)
      && eb.isnt('ชื่อและนามสกุลผู้ซื้อ / Consumer Full Name', q.customer_name)
      && eb.isnt('โทรศัพท์ผู้ซื้อ / Consumer Phone', q.customer_phone)
      // && eb.isnt('อีเมล์ผู้ซื้อ / Consumer Email', q.customer_email)
      && eb.isnt('วันที่ซื้อ / Date of Purchase', q.date_purchased)
      && eb.isnt('กรุณากดยินยอมข้อมูลส่วนบุคคลของท่าน / Please press consent of your personal information.', q.user_accept_1, "1")
     ) {

       ldat.register_query = q;
       $('#mod_otp').modal('show');

       $('#otpCode')[0].focus();
       var pNum = $("#customer_phone").val();
       if(!pNum || pNum == ''){
         Swal.fire('กรุณากรอกหมายเลขโทรศัพท์มือถือของลูกค้าเพื่อรับรหัสยืนยัน (OTP)','','error').then(() => {
           $('#mod_otp').modal('hide');
         });
         return;
       }
       $("#otpMobile").html(pNum);
       if(ldat.otp){
         showOTP();
       }else{
         grecaptcha.ready(function() {
           grecaptcha.execute(gsitekey, {action: 'otp_request_register_submit'}).then(function(token) {
           eb.post("public", "otp_request_register_submit", {mobile: pNum, token: token},
             function(d){
               ldat.otp = d;
               showOTP();
             });
           }, null, function(){
             $('#mod_otp').modal('hide');
           });
         });
       }

      // grecaptcha.ready(function() {
      //     grecaptcha.execute(gsitekey, {action: 'submit'}).then(function(token) {
      //       q.token = token;
      //       eb.post("public", "s_product_register", q, function(rt){
      // 				if(rt.id && rt.id > 0) {
      //           $('#btnSubmitRegisterReset').click();
      //           localStorage.setItem("_mv_rg_ids", JSON.stringify(rt));
      //           ldat.rg_ids = rt;
      //           setStatusRG(0);
      //           Swal.fire('ระบบได้รับเรื่องการลงทะเบียนของท่านแล้ว','','success');
      //         }else{
      //           Swal.fire('การลงทะเบียนไม่สำเร็จ กรุณาลองใหม่','','error');
      //         }
      //         $('#btnSubmitRegister').removeAttr('disabled').html('<i class="fas fa-paper-plane mr-1"></i> ลงทะเบียน');
      //         // grecaptcha.reset();
      // 			});
      //
      //       setTimeout(function(){
      //         $('#btnSubmitRegister').removeAttr('disabled').html('<i class="fas fa-paper-plane mr-1"></i> ลงทะเบียน');
      //       },6000);
      //     });
      //   });


		} // end if meter
	});

$('input[name="user_type"]').on('change', function () {
  const selected = $(this).val();
  if (selected === 'technician') {
    $('#technician-section').slideDown(); // หรือใช้ removeClass('d-none')
  } else {
    $('#technician-section').slideUp(); // หรือใช้ addClass('d-none')
  }
});

  // 2. Save Service Request
  $('#btnSubmitServReq').on('click', function(){
    // pre check to confirm for s/n empty
    let sn = $("input[name=indoor_sn2]").val();
    if(!sn || sn == ''){
      Swal.fire({
        title: 'หมายเลขเครื่อง (Serial No) '+$("#snQuery").val()+" เพื่อแจ้งต่อเจ้าหน้าที่ตรวจสอบ?",
        showCancelButton: true,
        confirmButtonText: `ยืนยัน`
      }).then((result) => {
          if (result.isConfirmed) {
            $("input[name=indoor_sn2]").val($("#snQuery").val());
            clickServReqSubmit();
          }
      });

    }else{
      clickServReqSubmit();
    }
	});


  // Upload file (Service Request)
	$('#attachPhoto')
    .change(function(e1){
      var fn = $('#attachPhoto').val();
      let fc = ldat.serv_req_query.filename.length;
      console.info(' filename : '+ fn+ ' count : '+ fc);
      if(fn.toLowerCase().match(/\.(jpg|png|gif|jpeg|bmp)/g)){
        if (this.files && this.files[0]) {
          // $("#previewFile").append("<div class='col-6'>"+
          //   "<img id='previewHolder"+fc+"' style='width:100%;'/></div>");
          let removeId = "previewWrap" + fc;
$("#previewFile").append(`
  <div class="col-6 mb-2" id="${removeId}">
    <div class="position-relative">
      <img id="previewHolder${fc}" style="width:100%; border:1px solid #ccc; border-radius:8px;"/>
      <button type="button" class="btn btn-sm btn-danger position-absolute" style="top:5px; right:5px;" onclick="removeAttachedFile(${fc})">
        <i class="fas fa-times"></i>
      </button>
    </div>
  </div>
`);


          var reader = new FileReader();
          reader.onload = function(e) {
            $('#previewHolder'+fc).attr('src', e.target.result);
          }
          reader.readAsDataURL(this.files[0]);
        } else {
          Swal.fire('select a file to see preview');
        }
      }else if(fn.toLowerCase().match(/\.(mp4|avi|mov)/g)){
        if (this.files && this.files[0]) {
          $("#previewFile").html("<video controls><source id='previewHolder' type=\"video/mp4\"></video>");
          $('#previewHolder').attr('src', URL.createObjectURL(this.files[0]));
        }
      }else{
        $("#previewFile").html("File Name : "+ fn);
      }
    })
		.attr('data-url', 'api/v1/public.html?a=serv_req_fileupload')
		.fileupload({
			dataType: 'json',
			done: function (e, data1) {
				if( data1.result.err_code == 0 && data1.result.data.file){
          ldat.serv_req_query.filename.push(data1.result.data.file);
          // servReqSubmit();
        }else{
          Swal.fire(data1.result.err_msg);
          // grecaptcha.reset();
        }
			},
			progressall: function (e, data) {
        $("#progress").removeClass("hide");
				var progress = parseInt(data.loaded / data.total * 100, 10);
				$('#progress .progress-bar').css('width', progress + '%').html(progress + '%');
			}
	});
  //.fileupload('disable');
function removeAttachedFile(index) {
  const removedFile = ldat.serv_req_query.filename[index];
  if (!removedFile) return;

  // ลบ DOM
  $("#previewWrap" + index).remove();

  // เรียกลบไฟล์จากเซิร์ฟเวอร์
  $.post("api/public.php?a=delete_uploaded_file", { filename: removedFile }, function (res) {
    if (res && res.status === "ok") {
      console.log("ลบไฟล์เรียบร้อย:", removedFile);
    } else {
      console.warn("ไม่สามารถลบไฟล์:", removedFile);
    }
  });

  // ลบชื่อไฟล์ออกจาก array
  ldat.serv_req_query.filename.splice(index, 1);
}



function clickServReqSubmit(){
  var q = $('#form_service_request').serializeObject();
  var tmp = {};
  // remove LAST character, `2`
  $.each(q, function(k,v){
    if(k.length > 0) tmp[k.slice(0,-1)] = v;
  });
  q = tmp
  q.id = ldat.id;
  q.channel = "WEB-CS";


  var ecSl2 = $("#errorCode").select2('data');
  if ($("#errorCode").val() === "error_code_group") {
  // ✅ กรณีเลือก “ขึ้น Error Code” → ใช้ค่าจาก dropdown ย่อย
  q.error_code = $("#errorCodeDynamic").val();
  q.error_code_txt = $("#errorCodeDynamic option:selected").text();
} else {
  // ✅ กรณีอื่น ๆ → ใช้ค่าจาก dropdown หลัก
  const ecSl2 = $("#errorCode").select2('data');
  q.error_code = ecSl2[0] ? ecSl2[0].id : null;
  q.error_code_txt = ecSl2[0] ? ecSl2[0].text : null;
}
delete q.error_code_dynamic;
  var pModel= $("#productModel2").select2('data'); // if disable
  q.product_model = pModel[0] ? pModel[0].text : null;
  // q.error_code = ecSl2[0] ? ecSl2[0].id : null;
  // q.error_code_txt = ecSl2[0] ? ecSl2[0].text : null;
  if(ldat.cur_product_type) q.product_type = ldat.cur_product_type;
  if(q.product_type) q.product_type_txt = ldat.product_type[q.product_type];
  // q.error_cause = $("#error_cause2 option:selected").text();
  if(q.requester_title == 'อื่นๆ' && q.requester_title_other != '') q.requester_title = q.requester_title_other;
  delete q.requester_title_other;
  console.info(q);

  // ตรวจสอบช่างผู้แจ้งขอรับบริการ เฉพาะเมื่อเลือก "ช่าง/ASC"
  if ($('input[name="user_type"]:checked').val() === 'technician') {
    if (!q.technician_name || q.technician_name.trim() === '') {
      Swal.fire('กรุณากรอกชื่อ-นามสกุลของช่างผู้แจ้งบริการ','','error');
      return;
    }
    if (!q.technician_phone || q.technician_phone.trim() === '') {
      Swal.fire('กรุณากรอกเบอร์โทรศัพท์ของช่างผู้แจ้งบริการ','','error');
      return;
    }
  }
  if(q.error_code == 0 && q.description == ""){
    Swal.fire('กรุณาระบุอาการเสีย หรือรายละเอียด / Error description','','error');return;
  }
  //error_code ย่อย
    if ($("#errorCode").val() === "error_code_group") {
    const selectedErrorCode = $("#errorCodeDynamic").val();
    if (!selectedErrorCode || selectedErrorCode === "") {
      Swal.fire("กรุณาเลือกรายการ Error Code", "", "warning");
      return;
    }
  }
  // ตรวจสอบกรณีเลือก "other" หรือ "อื่นๆ"
if (
  q.error_code === "other" ||
  q.error_code === "อื่นๆ" ||
  q.error_code_txt?.includes("โปรดระบุในรายละเอียด")
) {
  const description = q.description;

  if (!description || description.trim() === "") {
    Swal.fire("กรุณาระบุรายละเอียดเพิ่มเติม", "", "warning");
    return;
  }
}

  if (ldat.serv_req_query.filename.length === 0) {
  Swal.fire('กรุณาแนบไฟล์อย่างน้อย 1 ไฟล์ประกอบการแจ้งซ่อม','','error');
  return;
}

    // ตรวจสอบชื่อและเบอร์ผู้ขอรับบริการก่อน
  if (!q.requester_name || q.requester_name.trim() === "") {
    Swal.fire('กรุณากรอกชื่อและนามสกุลผู้ขอรับบริการ', '', 'error');
    return;
  }
  if (!q.requester_phone || q.requester_phone.trim() === "") {
    Swal.fire('กรุณากรอกเบอร์โทรศัพท์ผู้ขอรับบริการ', '', 'error');
    return;
  }
  //  บังคับกรอกข้อมูลที่อยู่ 
const addressFields = {
  address_no: 'เลขที่บ้าน',
  address_postcode: 'รหัสไปรษณีย์',
  address_subdistrict: 'แขวง/ตำบล',
  address_district: 'เขต/อำเภอ',
  address_province: 'จังหวัด'
};

for (let field in addressFields) {
  const val = q[field];
  if (!val || val.trim() === '') {
    Swal.fire(`กรุณากรอก${addressFields[field]}`, '', 'warning');
    return;
  }
}

if (
  $('input[name="user_type"]:checked').val() === "technician" &&
  (
    $("#errorCode").val() === "com_notworking" ||
    $("#errorCodeDynamic").val() === "com_notworking" ||
    $("#errorCode").val() === "com_lock" ||
    $("#errorCodeDynamic").val() === "com_lock"
  )
) {
  const voltage = q.tbl_volt_pre;
  if (!voltage || voltage === "") {
    Swal.fire("กรุณากรอกแรงดันไฟฟ้า (V) ในข้อ 2", "", "warning");
    return;
  }
}


// ✅ บังคับกรอกทุกช่องถ้าเลือก not_cold_enough แอร์เย็นไม่ฉ่ำ
if (
  $('input[name="user_type"]:checked').val() === "technician" &&
  $("#errorCode").val() === "not_cold_enough"
) {
  const requiredFields = [
    "room_size", "tbl_volt_pre", "tbl_amp_pre", "tbl_term_remote_pre",
    "tbl_psil_pre", "tbl_fcu_out_pre", "tbl_fcu_in_pre",
    "tbl_cdu_out_pre", "tbl_cdu_in_pre", "pipe_length"
  ];

  for (let key of requiredFields) {
    if (!q[key] || q[key].trim() === "") {
      Swal.fire("กรุณากรอกข้อมูลให้ครบถ้วนในรายการตรวจสอบก่อนซ่อม", "", "warning");
      return;
    }
  }
}



  if(
    eb.isnt('ประเภทสินค้า / Product Type', q.product_type)
    && eb.isnt("รุ่นสินค้า / Model ID", q.product_model)
    && eb.isnt('หมายเลขเครื่องภายใน / Indoor Unit Serial No.', q.indoor_sn)
    // && eb.isnt('หมายเลขเครื่องภายนอก / Outdoor Unit Serial No.', q.outdoor_sn)
    // && eb.isnt('อีเมล์ผู้ขอรับบริการ / Requester Email', q.requester_email)
    && eb.isnt('กรุณากดยินยอมข้อมูลส่วนบุคคลของท่าน / Please press consent of your personal information.', q.user_accept_, "1")
   ) {
    $('#btnSubmitServReq').attr('disabled','disabled').html('<i class="fa fa-spinner fa-spin fa-fw"></i> กำลังส่งข้อมูล....');

    grecaptcha.ready(function() {
        grecaptcha.execute(gsitekey, {action: 'submit'}).then(function(token) {
          var fn = ldat.serv_req_query.filename;
          ldat.serv_req_query = q;
          ldat.serv_req_query.filename = fn;
          if($('#attachPhoto').val().length > 6){
            $('#attachPhoto').fileupload('enable').trigger('change');
            // start upload file then submit form
          }else{
            servReqSubmit();
          }
        });
      });


  } // end if meter
}
// ✅ [1] Event listener เรียกใช้ฟังก์ชันเมื่อ dropdown เปลี่ยน
$("#errorCode, #errorCodeDynamic, input[name='user_type']").on("change", function () {
  toggleChecklistByErrorCode();
});

function toggleChecklistByErrorCode() {
  const userType = $('input[name="user_type"]:checked').val();
  const selectedErrorCode = $("#errorCode").val();

  // ซ่อนทั้งหมดก่อน
  $("#technicianMeasurementSection").hide();
  $("#technicianChecklist").hide();
  $("[id^='checklist-item-']").hide();

  // เงื่อนไข: com_notworking / com_lock → แสดงเฉพาะข้อ 2, 3
  if (
    userType === "technician" &&
    (selectedErrorCode === "com_notworking" || selectedErrorCode === "com_lock")
  ) {
    $("#technicianMeasurementSection").show();
    $("#technicianChecklist").show();
    $("#checklist-item-2, #checklist-item-3").show();
  }


  // เงื่อนไข: not_cold_enough → แสดงทั้งหมด
  else if (userType === "technician" && selectedErrorCode === "not_cold_enough") {
    $("#technicianMeasurementSection").show();
    $("#technicianChecklist").show();
    $("[id^='checklist-item-']").show(); // ✅ แสดงทั้งหมด
  }
}


function servReqSubmit(){
  eb.post("public", "s_service_request", ldat.serv_req_query, function(rt){
    if(rt.id && rt.id > 0) {
      // ✅ 1. เตรียม JSON จากฟอร์ม
      const allowedKeys = [
        'room_size2',
        'tbl_volt_pre2', 'tbl_amp_pre2', 'tbl_term_remote_pre2',
        'tbl_psil_pre2',
        'tbl_fcu_out_pre2', 'tbl_fcu_in_pre2',
        'tbl_cdu_out_pre2', 'tbl_cdu_in_pre2',
        'pipe_length2'
      ];

      let detail = {};
      $.each($('#form_service_request').serializeObject(), function(k, v) {
        if (allowedKeys.includes(k)) {
          detail[k.slice(0, -1)] = v;
        }
      });

      // ✅ 2. ส่ง JSON ไปเก็บ
      $.post("api/public.php?a=save_service_detail", {
        id: rt.id,
        detail: JSON.stringify(detail)
      }, function(res2) {
        if (res2.status !== 'ok') {
          console.warn('บันทึกรายละเอียดไม่สำเร็จ');
        }
      });

      // ✅ 3. รายงานผล
      localStorage.setItem("_mv_sr_ids", JSON.stringify(rt));
      ldat.sr_ids = rt;
      setStatusSR(0);
      Swal.fire('ระบบได้รับคำขอบริการของคุณแล้ว เจ้าหน้าที่จะดำเนินการติดต่อท่านโดยเร็ว');
      $("#previewFile").html('');
      ldat.serv_req_query = []; //reset
      // ✅ เคลียร์ฟอร์มเพิ่มเติม
      $('#form_service_request')[0].reset(); // ล้างฟอร์มทั้งหมด
      $("#productModel2").val(null).trigger('change'); // เคลียร์ select2 รุ่น
      $("#errorCode").val(null).trigger('change');     // เคลียร์ select2 error code
      $("input[type=radio][name=pipe_welding]").prop('checked', false); // เคลียร์ radio
      $("#statusServRerv").parent().addClass('hide'); // ซ่อนสถานะ
      $('html, body').animate({ scrollTop: 0 }, 'slow'); // เลื่อนขึ้นไปบนสุด
    } else {
      Swal.fire('Service Request Failure, please try again.');
    }

    $('#btnSubmitServReq').removeAttr('disabled').html('<i class="fas fa-paper-plane mr-1"></i> แจ้งขอรับบริการ');
    grecaptcha.reset();
  });
}


// var geocoder = new google.maps.Geocoder();

function geocodePosition(pos) {
  geocoder.geocode({
    latLng: pos
  }, function(responses){
    if (responses && responses.length > 0) {
      updateMarkerAddress(responses[0].formatted_address);
    } else {
      updateMarkerAddress('Cannot determine address at this location.');
    }
  });
}

  function setStatusRG(){
    $("#statusRegister").html(
      (ldat.rg_ids && ldat.rg_ids.pk ? ldat.rg_ids.pk + ' ' : '' )
    );
    $("#statusRegister").parent().removeClass('hide');
  }
  function setStatusSR(status){
    $("#statusServRerv").html(
      (ldat.sr_ids && ldat.sr_ids.pk ? ldat.sr_ids.pk + ' ' : '' )+
      (status > -1 ? eb.serv_req_status[status] : 'ไม่พบรายการสถานะ')
    )
    $("#statusServRerv").parent().removeClass('hide');
  }

  function renderProdModel(){
    let tpl = '<option disabled selected>- โปรดระบุรุ่น -</option>';
    $(ldat.product_model).each((k,v) => {
      tpl += '<option value="'+v.product_code+'" data-type="'+v.product_type+'">'+v.model_name+'</option>';
    });
    $("#productModel,#productModel2").html(tpl).on('select2:select', function (e) {
      var data = e.params.data;
      if(data.element){
        var type = $(data.element).attr('data-type');
        if(type != null){
          ldat.cur_product_type = type;
          $('#productTypeTxt,#productTypeTxt2').html(ldat.product_type[type]);
          console.log("productTypeTxt : " +data.id, ldat.product_type[type]);
          // $('input[name="indoor_sn"],input[name="indoor_sn2"]').val(data.id.replace("-", ""));
        }
      }
    });
  }

function loadErrorCodeGroup(product_id) {
  eb.post("public", "get_supplier_id_by_product", { product_id: product_id }, function (res) {
    const supplier_id = res.supplier_id;

    eb.post("public", "get_error_codes_by_supplier", { supplier_id: supplier_id }, function (errors) {
      let options = '<option disabled selected value="0">- โปรดระบุอาการ -</option>';
      let otherOption = ''; // <- เก็บ "อื่นๆ" แยกไว้ก่อน

      // เพิ่ม "ขึ้น Error Code" (จาก error_code_list)
      errors.forEach(e => {
        if (e.error_code === 'error_code_list') {
          options += `<option value="error_code_group">${e.error_code_prompt}</option>`;
        }
      });

      // รายการอาการเสียทั่วไป (supplier_id == null)
      errors.forEach(e => {
        if (!e.supplier_id && e.error_code !== 'error_code_list') {
          const prompt = e.error_code_prompt?.toLowerCase() || "";
          const isOther = e.error_code === 'other' || prompt.includes("อื่น") || prompt.includes("โปรดระบุ");

          if (isOther) {
            otherOption = `<option value="${e.error_code}">${e.error_code_prompt}</option>`;
          } else {
            options += `<option value="${e.error_code}">${e.error_code_prompt}</option>`;
          }
        }
      });

      // ต่อท้าย “อื่นๆ” เสมอ
      options += otherOption;

      $("#errorCode").html(options).trigger("change");
    });
  });
}


  const findProductSN = () => {
    let sn_keyword = $('#snQuery').val();
    if(sn_keyword == "") {
      Swal.fire("กรุณาพิมพ์หมายเลขเครื่องภายใน หรือ ภายนอก (Product Serial Number) แล้วกดค้นหาอีกครั้ง", '', 'warning');
      return;
    }
    $('#btnFindSN').attr('disabled','disabled').html('<i class="fa fa-spinner fa-spin fa-fw"></i> กำลังส่งข้อมูล....');
    eb.post("public", "get_product_sn", {"q" : sn_keyword}, function(d){
      console.table(d);
      if(d == null){
        Swal.fire("ไม่พบข้อมูลรายการสินค้า", '', 'error');
        $('#btnFindSN').removeAttr('disabled').html('<i class="fas fa-search"></i> ค้นหารหัสผลิตภัณฑ์');
        $('#productModel2').prop('disabled', false);

        return;
      }

      if(d.indoor_sn) {
          if((d.line && d.line == 'INDOOR')
            || (d.register_id && d.register_id > 0)
            || (d.indoor_sn != '' && (!d.outdoor_sn || d.outdoor_sn == ''))
          ) {
            $("input[name=indoor_sn2]").val(d.indoor_sn);
          }
          else $("input[name=indoor_sn2]").val(d.outdoor_sn);
      }
      if(d.outdoor_sn) $("input[name=outdoor_sn2]").val(d.outdoor_sn);
      if(d.serial_id) {
        $("input[name=serial_id2]").val(d.serial_id);
        $("#resultSN3").html('<div class="text-md badge badge-success">พบรหัสรายการสินค้านี้</div>');
      }else{
        $("#resultSN3").html('<div class="text-md badge badge-danger">ไม่พบรหัสสินค้านี้</div>');
      }
      if(d.product_id) $("input[name=product_id2]").val(d.product_id);

            if (d.product_id) {
        $("input[name=product_id2]").val(d.product_id);

        // ✅ โหลด dropdown อาการเสียหลัก
        loadErrorCodeGroup(d.product_id);
      }

      if(d.model_code_opt && d.model_code_opt !='') {
        var stringVal = '';
        $('#productModel2').find('option').each(function(){
            if($(this).is(':contains(' + d.model_code_opt + ')')){
              stringVal  = $(this).val();
            }
            if(stringVal != '') {
              $('#productModel2').val(stringVal).trigger("change");
              var ptype = $(this).attr('data-type');
              if(ptype != null){
                ldat.cur_product_type = ptype;
                $('#productTypeTxt2').html(ldat.product_type[ptype]);
                console.log('product type in model => ' + ptype);
              }
              return false;
            }
        });
        $('#productModel2').prop('disabled', true);
      }else{
        $('#productModel2').prop('disabled', false);
      }

      if(d.product_type && d.product_type > -1) {
        $("#productTypeTxt2").html(ldat.product_type[d.product_type]);
        $("input[name=product_type2]").val(d.product_type);
      };

      if(d.register_id && d.register_id > 0) {
        $("#resultSN2").html('พบรายการนี้ลงทะเบียนไว้ในระบบ #RG'+ eb.zerofill(d.register_id,8));
      }

      console.info('indoor_sn2 => ' + d.indoor_sn + '   /   outdoor_sn2 => ' + d.outdoor_sn);
      $('#btnFindSN2').removeAttr('disabled').html('<i class="fas fa-search"></i> กรุณากดค้นหารหัสผลิตภัณฑ์ที่นี่');
    }, null, function(d){
      Swal.fire(d.err_msg, '', 'warning');
      $('#btnFindSN2').removeAttr('disabled').html('<i class="fas fa-search"></i> กรุณากดค้นหารหัสผลิตภัณฑ์ที่นี่');
    });
  } // end fn

// find product sn for product register
const findProductSNInO = () => {
  let inoorSN = $('input[name="indoor_sn"]').val();
  let outdoorSN = $('input[name="outdoor_sn"]').val();
  if(inoorSN == "" && outdoorSN == "") {
    Swal.fire("กรุณาพิมพ์หมายเลขเครื่องภายใน หรือ ภายนอก (Product Serial Number) แล้วกดค้นหาอีกครั้ง", 'เพื่อใช้ในการลงทะเบียน', 'warning');
    return;
  }

  if(inoorSN == '') {
    inoorSN = outdoorSN;
    outdoorSN = '';
  }
  $('#btnFindSN1').attr('disabled','disabled').html('<i class="fa fa-spinner fa-spin fa-fw"></i> กำลังส่งข้อมูล....');

  // Indoor Finding
  eb.post("public", "get_product_sn", {"q" : inoorSN}, function(d){
    console.table(d);
    if(d == null && outdoorSN == ""){
      Swal.fire("ไม่พบข้อมูลรายการสินค้า ", '', 'error');
      resetSNRegister();
      return;
    }else if(d != null){

      if(d.indoor_sn &&d.line && d.line == 'OUTDOOR') { // swap to outdoor
          $("input[name=outdoor_sn]").val(d.indoor_sn);
          $('input[name="indoor_sn"]').val(""); // reset
      }
      if(d.serial_id) {
        $("input[name=serial_id]").val(d.serial_id);
        $("#resultSN1").html('<div class="text-md badge badge-success">พบรหัสรายการสินค้า '+d.line+'</div>');
      }
      if(d.product_id) $("input[name=product_id]").val(d.product_id);
      if(d.model_code_opt && d.model_code_opt !='') {
        var stringVal = '';
        $('#productModel').find('option').each(function(){
            if($(this).is(':contains(' + d.model_code_opt + ')')){
              stringVal  = $(this).val();
            }
            if(stringVal != '') {
              $('#productModel').val(stringVal).trigger("change");
              var ptype = $(this).attr('data-type');
              if(ptype != null){
                ldat.cur_product_type = ptype;
                $('#productTypeTxt').html(ldat.product_type[ptype]);
                console.log('product type in model 1 => ' + ptype);
              }
              return false;
            }
        });
        $('#productModel').prop('disabled', true);
      }else{
        $('#productModel').prop('disabled', false);
      }

      if(d.product_type) {
        $("#productTypeTxt").html(ldat.product_type[d.product_type]);
        $("input[name=product_type]").val(d.product_type);
      }

      if(d.register_id && d.register_id > 0) {
        $("#resultSN").html('พบรายการนี้ลงทะเบียนไว้ในระบบ #RG'+ eb.zerofill(d.register_id, 8)+'');
        Swal.fire("พบรายการลงทะเบียน ไม่สามารถลงทะเบียนซ้ำ", 'INDOOR', 'error');
        resetSNRegister();
      }
      console.info('indoor_sn => ' + d.indoor_sn);
      $('#btnFindSN1').removeAttr('disabled').html('<i class="fas fa-search"></i> ค้นหารหัสผลิตภัณฑ์');
    } //end if not d null

    if(outdoorSN != ''){
      // Outdoor Finding after Indoor
      eb.post("public", "get_product_sn", {"q" : outdoorSN}, function(d2){
        console.table(d2);
        if(d2 == null && d == null){
          Swal.fire("ไม่พบข้อมูลรายการสินค้า ", '', 'error');
          resetSNRegister();
          return;
        }else if(d2 != null){
            if(d2.indoor_sn &&d.line && d2.line == 'INDOOR') { // swap to indoor
              $("input[name=indoor_sn]").val(d2.indoor_sn);
              $('input[name="outdoor_sn"]').val(""); // reset

          }else if(d2.register_id && d2.register_id > 0) {
            $("#resultSN").html('พบรายการนี้ลงทะเบียนไว้ในระบบ #RG'+ eb.zerofill(d.register_id, 8)+'');
            Swal.fire("พบรายการลงทะเบียน ไม่สามารถลงทะเบียนซ้ำ", 'OUTDOOR', 'error');
            resetSNRegister();
          }else if(d.product_id != d2.product_id) {
            Swal.fire("รหัสสินค้าผลิตภัณฑ์ Indoor และ Outdoor ไม่ตรงรุ่นกัน ", '', 'error');
            resetSNRegister();
          }else if(d2.serial_id) {
            $("input[name=serial_id]").val(d2.serial_id);
            $("#resultSN1").append('<br/><div class="text-md badge badge-success">พบรหัสรายการสินค้า '+d2.line+'</div>');
          }
        }
        $('#btnFindSN1').removeAttr('disabled').html('<i class="fas fa-search"></i> ค้นหารหัสผลิตภัณฑ์');
      });
    }

  }, null, function(d){
    Swal.fire(d.err_msg, '', 'warning');
    $('#btnFindSN1').removeAttr('disabled').html('<i class="fas fa-search"></i> ค้นหารหัสผลิตภัณฑ์');
  });
} // end fn

const resetSNRegister = function(){
  $('input[name="outdoor_sn"],input[name="indoor_sn"],input[name="serial_id"]').val(""); // reset
  $('#btnFindSN1').removeAttr('disabled').html('<i class="fas fa-search"></i> ค้นหารหัสผลิตภัณฑ์');
}

// QR Code Reader
const qrReaderInit = function(){
  ldat.cur_el_qrr = null;
  ldat.qrr_found = false;
  const camList = document.getElementById('cam-list');
  const scanner = new QrScanner($("#qr-video")[0], function(result) {
      console.log(ldat.cur_el_qrr);
      if(ldat.qrr_found) return;

      if(result && result.data){
        ldat.qrr_found = true;
        let _sn = result.data.replace('https://cs.mavellair.com/?sn=','');
        ldat.cur_el_qrr.val(_sn);
        $('#qrReaderMod').modal('hide');
        scanner.stop();

        // take an action
        if(ldat.cur_el_qrr[0] && ldat.cur_el_qrr[0].id){
          if(ldat.cur_el_qrr[0].id == 'snQuery') findProductSN();
        }
        ldat.qrr_found = false;
      }
  }, {
      onDecodeError: error => {
      },
      highlightScanRegion: true,
      highlightCodeOutline: true,
  });

  $('#qrReaderMod').on('shown.bs.modal', function (e) {
    ldat.cur_el_qrr = $(e.relatedTarget).siblings('input');
    scanner.start().then(() => {
        QrScanner.listCameras(true).then(cameras => cameras.forEach(camera => {
            const option = document.createElement('option');
            option.value = camera.id;
            option.text = camera.label;
            camList.add(option);
        }));
    });
  });

  $('#qrReaderMod').on('hidden.bs.modal', function (e) {
    scanner.stop();
    ldat.cur_el_qrr = null;
  });

  window.scanner = scanner; // for debugging

  camList.addEventListener('change', event => {
      scanner.setCamera(event.target.value);
  });

  $('#qrr-stop-button').on('click', () => scanner.stop());
}
$("#errorCode").on("change", function () {
  const selected = $(this).val();

  if (selected === "error_code_group") {
    $("#errorCodeDynamicSec").show();

    // โหลดรายการ Error Code ย่อย
    const pid = $("input[name='product_id2']").val();
    if (!pid) return;

    eb.post("public", "get_supplier_id_by_product", { product_id: pid }, function (res) {
      const supplier_id = res.supplier_id;

      eb.post("public", "get_error_codes_by_supplier", { supplier_id: supplier_id }, function (errors) {
        let options = '<option disabled selected value="">- โปรดเลือก Error Code -</option>';
        errors.forEach(e => {
          if (e.supplier_id) { // เฉพาะ Error Code จริง
            options += `<option value="${e.error_code}">${e.error_code_prompt}</option>`;
          }
        });
        $("#errorCodeDynamic").html(options).trigger('change');
      });
    });

  } else {
    $("#errorCodeDynamicSec").hide();
    $("#errorCodeDynamic").val("");
  }
});



const showOTP = () => {
  if(ldat.otp.ref) $("#otpRef").html(ldat.otp.ref);
  ldat.timer = 60*5; // 5 mins
  ldat.timerID =  setInterval(function() {
    $("#otpTimer").html(ldat.timer);
    ldat.timer -= 1;
    if(ldat.timer < 0) {
      clearInterval(ldat.timerID);
      ldat.otp = false;
      Swal.fire("หมดเวลา",'','error').then(()=>{
        $('#mod_otp').modal('hide');
      });
    }
  }, 1000); // 1s
}