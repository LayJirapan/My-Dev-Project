<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Hi Mavell | CSMS</title>
  <meta name="theme-color" content="#103379">
  <meta name="google-site-verification" content="_H4cU_vgMl2RViQNT_Xor45KWhOqUrzL1r3LxIuieos" />
  <link rel="icon" type="image/png" href="https://mavellair.com/images/MavellAir/Logo/Mavell_Logo_2020_1_32x32.png">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="./plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="./view/css/adminlte.min.css">
  <link rel="stylesheet" href="./view/css/frontend.css">
  <link rel="stylesheet" href="./plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <link rel="stylesheet" type="text/css" href="./plugins/jQuery-File-Upload/css/jquery.fileupload.css" />
  <link rel="stylesheet" type="text/css" href="./plugins/jQuery-File-Upload/css/jquery.fileupload-ui.css" />
  <link rel="stylesheet" href="./plugins/select2/css/select2.min.css">
  <link rel="stylesheet" href="./plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
  <link rel="stylesheet" href="./plugins/jquery-Thailand/jquery.Thailand.min.css">
  <link rel="stylesheet" href="./plugins/select2/css/select2.min.css">
  <script>
   window.cb_sconf = function(){};
  </script>
  <!-- Global site tag (gtag.js) - Google Analytics -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-5E4BDH64TF"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-5E4BDH64TF');
  </script>
  <style>
    #menu-tab .card{
      height: 194px;
    }
    #menu-dealer .card{
      height: 600px;
    }
    #menu-dealer .card-header{
      z-index: 10;
    }
    #mainMenuSec .card-link.active .card-body{
      background: rgb(255,255,255);
      background: radial-gradient(circle, rgba(255,255,255,1) 0%, rgb(15 51 121 / 69%) 100%);
    }

   .sidebar {
     background-color: white;
     position: absolute;
     width: 33.3333%;
     min-width: 200px;
     max-width: 400px;
     height: 89%;
     left: 0;
     overflow: hidden;
     border-right: 1px solid rgba(0, 0, 0, 0.25);
   }
   .quiet {
     color: #888;
   }
   .map {
     position: absolute;
     left: 33.3333%;
     top: 46px;
     bottom: 0;
   }
   #listingSCDiv {
     overflow: auto;
     float: left;
   }

   .listings {
     height: 100%;
     overflow: auto;
     padding-bottom: 60px;
   }

   .listings .item {
     display: block;
     border-bottom: 1px solid #eee;
     padding: 10px;
     text-decoration: none;
   }

   .listings .item:last-child {
     border-bottom: none;
   }

   .listings .item .title {
     display: block;
     /*      color:#00853e;*/
     color: #000;
     font-weight: 700;
   }

   .listings .item .title small {
     font-weight: 400;
   }
   .listings .item .details{
     font-weight: normal;
     color:#4c4c4c;
     line-height: 1.2;
   }

   .listings .item.active .title,
   .listings .item .title:hover {
     color: #b74042;
   }
   /*#8cc63f*/

   .listings .item.active {
     background-color: #f8f8f8;
   }

   #storeinfo {
     padding: 10px 10px 20px 10px;
     display: none;
     background-color: gainsboro;
   }

    ::-webkit-scrollbar {
     width: 3px;
     height: 3px;
     border-left: 0;
     background: rgba(0, 0, 0, 0.1);
   }

    ::-webkit-scrollbar-track {
     background: none;
   }

    ::-webkit-scrollbar-thumb {
     background: #00853e;
     border-radius: 0;
   }

   .clearfix {
     display: block;
   }

   .clearfix:after {
     content: '.';
     display: block;
     height: 0;
     clear: both;
     visibility: hidden;
   }


   #closeButton {
     float: right;
   }

   #closeButton:hover,
    :focus {
     color: #b74042;
     cursor: pointer;
     /*padding: 5px;*/
   }

   #searchIcon:hover,
    :focus {
     color: #b74042;
     cursor: pointer;
   }

   #infoClose {
     float: right;
   }

   #searchIcon {
     float: right;
     font-size: 26px;
     margin-top: 1px;
   }

   @media screen and (min-width:601px) and (max-width: 1199px) {
     .map {
       width: 66.6666%;
     }
   }

   @media screen and (max-width:600px) {
     .map {
       margin-left: 200px;
       right: 0;
       left: 0;
     }
   }

   @media screen and (min-width: 1200px) {
     .map {
       left: 400px;
       right: 0;
     }
   }

   @media screen and (max-width:968px) {
     h1 {
       font-size: 1.2em;
     }
     #searchIcon {
       font-size: 16px;
     }
   }

  #mapCanvas1,#mapCanvas2 {
    width: 100%;
    height: 360px;
    float: left;
  }
  #mapViewContent{ height: 100%;width: 100%; }

  .select2-container--default .select2-selection--single{
    height: 36px;
  }

  #qr-video{width: 100%;}

 </style>
</head>
<body class="hold-transition layout-top-nav">
<div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand-md navbar-light navbar-white">
    <div class="container">
      <a href="./" class="navbar-brand">
        <img src="./view/img/mavell_flogo.png" alt="Mavell Air" class="brand-image" style="opacity: .8">
        <span class="brand-text font-weight-light">Hi Mavell - CSMS</span>
      </a>

      <!-- <button class="navbar-toggler order-1" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button> -->

      <div class="collapse navbar-collapse order-3" id="navbarCollapse">
        <!-- Left navbar links -->
        <!-- <ul class="navbar-nav">
          <li class="col-lg-3 col-md-6 col-6 col-sm-6">
            <a href="./" class="nav-link">Home</a>
          </li>
        </ul> -->
      </div>


    </div>
  </nav>
  <!-- /.navbar -->

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header" id="main">
      <div class="container">
        <div class="row mb-2">
          <div class="col-sm-6">
            <!-- <h3 class="m-0"> ยินดีต้อนรับเข้าสู่ Mavell Air</h3> -->
          </div><!-- /.col -->
          <div class="col-sm-6">


          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
      <div class="container">
        <div class="row">
          <div class="col-lg-3 col-md-6 col-12 col-sm-12" style="display:none;" id="qrCodeInfoSec">
            <div class="card card-primary">
              <div class="card-header">
                <h3 class="card-title">ข้อมูล QR Code</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <strong><i class="far fa-file-alt mr-1"></i> ชื่อผลิตภัณฑ์ / รุ่น</strong> <br/><span class="text-muted" id="qProductCode">N/A</span><br/>
                <strong><i class="fas fa-book mr-1"></i> S/N</strong> <br/><span class="text-muted" id="qSN">N/A</span><br/>
                <strong><i class="fas fa-calendar mr-1"></i> วันที่ผลิต</strong> <br/><span class="text-muted" id="qMFD">N/A</span><br/>
                <strong><i class="fas fa-pencil-alt mr-1"></i> สถานะลงทะเบียน</strong><br/><span class="text-muted" id="qRegiestered">N/A</span><br/>
                <strong><i class="fas fa-calendar mr-1"></i> วันที่ติดตั้ง</strong> <br/><span class="text-muted" id="qInstDate">N/A</span><br/>
                <strong><i class="fas fa-user mr-1"></i> ช่างผู้ติดตั้ง</strong> <br/><span class="text-muted" id="qTech">N/A</span><br/>
                <strong><i class="fas fa-pencil-alt mr-1"></i> การรับประกัน</strong>
                <p class="text-muted" id="qWarranty">
                  <img src="./view/img/warranty_1year.png" width="60px"/>
                  <img src="./view/img/warranty_5year.png" width="60px"/>
                  <img src="./view/img/warranty_12year.png" width="60px"/>
                </p>
                <hr/>
                <small>โปรดเลือกเมนูเพื่อทำรายการที่ต้องการ</small>
              </div>
              <!-- /.card-body -->
            </div>
          </div>

          <div class="col-12" id="mainMenuSec">
            <ul class="nav nav-pills nav-fill mb-3" id="menu-tab" role="tablist">
              <li class="col-lg-3 col-md-6 col-6 col-sm-6">
                <a class="card-link" id="menu-reg-tab" data-toggle="pill" href="#menu-reg" role="tab" aria-controls="menu-reg" aria-selected="false">
                  <div class="card card-primary card-outline text-center">
                    <div class="card-body">
                      <p class="card-text">
                        <i class="fas fa-shield-alt fa-6x"></i>
                      </p>
                      <h1 class="card-title float-none"> ลงทะเบียนผลิตภัณฑ์</h1>
                    </div>
                  </div>
                </a>
              </li>
              <li class="col-lg-3 col-md-6 col-6 col-sm-6">
                <a class="card-link" id="menu-req-tab" data-toggle="pill" href="#menu-req" role="tab" aria-controls="menu-req" aria-selected="false">
                  <div class="card card-primary card-outline text-center">
                    <div class="card-body">
                      <p class="card-text">
                        <i class="fas fa-tools fa-6x"></i>
                      </p>
                      <h1 class="card-title float-none"> แจ้งขอรับบริการ / ซ่อม</h1>
                    </div>
                  </div>
                </a>
              </li>
              <li class="col-lg-3 col-md-6 col-6 col-sm-6">
                <a class="card-link" id="menu-faq-tab" data-toggle="pill" href="#menu-faq" role="tab" aria-controls="menu-faq" aria-selected="false">
                  <div class="card card-primary card-outline text-center">
                    <div class="card-body">
                      <p class="card-text">
                        <i class="fas fa-lightbulb fa-6x"></i>
                      </p>
                      <h1 class="card-title float-none"> ความรู้/การแก้ปัญหา</h1>
                    </div>
                  </div>
                </a>
              </li>

              <li class="col-lg-3 col-md-6 col-6 col-sm-6">
                <a class="card-link" href="asc.html">
                  <div class="card card-primary card-outline text-center">
                    <div class="card-body">
                      <p class="card-text">
                        <i class="fas fa-building fa-6x"></i>
                      </p>
                      <h1 class="card-title float-none"> ศูนย์บริการ</h1>
                    </div>
                  </div>
                </a>
              </li>
            </ul>
            <!-- /.row -->

          </div>
        </div>
        <!-- /.row -->

        <div class="tab-content" id="menu-tabContent">
          <div class="tab-pane fade show active" id="menu-reg" role="tabpanel" aria-labelledby="menu-reg-tab">
            <!-- Register -->
          <form id="form_register">
            <div class="card card-primary">
                <div class="card-header">
                <h3 class="card-title"><i class="fas fa-shield-alt"></i> ลงทะเบียนผลิตภัณฑ์</h3>
              </div>
                <div class="card-body">
                  <div class="mb-3">
                    <!-- <h5 class="text-danger">Warranty & Registration inquiry form</h5> -->
                    <button type="button" data-toggle="modal" data-target="#wcdModal" class="btn btn-outline-primary btn-sm">
                      <i class="fas fa-hand-point-right mr-1"></i>เงื่อนไขการรับประกันเครื่องปรับอากาศมาเวล
                    </button>
                    <button type="button" data-toggle="modal" data-target="#rewcdModal" class="btn btn-outline-primary btn-sm">
                      <i class="fas fa-hand-point-right mr-1"></i>เงื่อนไขการสะสมคะแนน MAVELL POINT เพื่อแลกรางวัล
                    </button>
                  </div>
                  <div class="row">
                    <div class="col-md-6 col-12">
                      <div class="card card-danger">
                        <div class="card-header">
                          <h3 class="card-title"><i class="fas fa-user"></i> ข้อมูลผลิตภัณฑ์ และลูกค้า</h3>
                        </div>
                        <div class="card-body">
                        <!-- /.card-header -->
                            <!-- <p>
                              การลงทะเบียนโปรดกรอกแบบฟอร์มด้านล่างเพื่อดำเนินการตามคำขอของคุณอย่างรวดเร็วโปรดส่งข้อมูลต่อไปนี้ในไฟล์แนบและทีมลงทะเบียนของเรายินดีให้ความช่วยเหลือภายใน 24 ชั่วโมง<br/>
                            </p> -->
                            <!-- <div class="form-group">
                              <label for="productType">ประเภทสินค้า <span class="text-danger ml-1">*</span></label>
                              <select class="form-control select2" name="product_type" id="productType">
                                <option disabled selected>- โปรดระบุประเภทแอร์มาเวล -</option>
                                <option value="1">เครื่องปรับอากาศ แบบ ติดผนัง-ฟิกซ์สปีด / Wall Type / Fixed Speed (Standard)</option>
                                <option value="2">เครื่องปรับอากาศ แบบ ติดผนัง-ฟิกซ์สปีด-เต็มบีทียู / Wall Type / Fixed Speed (Full BTU)</option>
                                <option value="3">เครื่องปรับอากาศ แบบ ติดผนัง-อินเวอร์เตอร์-สมาร์ทพลัส-1ดาว / Wall Type / Inverter / Smart Plus (1 Star)</option>
                                <option value="4">เครื่องปรับอากาศ แบบ ติดผนัง-อินเวอร์เตอร์-สมาร์ทคูล-0ดาว / Wall Type / Inverter / Smart Cool (0 Star)</option>
                                <option value="5">เครื่องปรับอากาศ แบบ ติดผนัง-อินเวอร์เตอร์-สมาร์ททรี-3ดาว / Wall Type / Inverter / Smart III (3 Star)</option>
                                <option value="6">เครื่องปรับอากาศ แบบ ติดผนัง-อินเวอร์เตอร์ ไวไฟ / Wall Type / Inverter WIFI</option>
                                <option value="7">เครื่องปรับอากาศ แบบ แขวน-ฟิกซ์สปีด-R32 / Ceiling Type / Fixed Speed / R32 </option>
                                <option value="8">เครื่องปรับอากาศ แบบ แขวน-ฟิกซ์สปีด-R410A / Ceiling Type / Fixed Speed / R410A </option>
                                <option value="9">เครื่องปรับอากาศ แบบ 4 ทิศทาง-ฟิกซ์สปีด-R32 / Cassette Type / Fixed Speed / R32 </option>
                                <option value="10">เครื่องปรับอากาศ แบบ 4 ทิศทาง-ฟิกซ์สปีด-R410A / Cassette Type / Fixed Speed / R410A </option>
                                <option value="-1">เครื่องปรับอากาศ ประเภทอื่น (โปรดระบุ) </option>
                              </select>
                            </div> -->
                            <div class="form-group" id="otherTypeSec" style="display:none;">
                              <label for="device_cateory">เครื่องปรับอากาศ ประเภทอื่น (โปรดระบุ) <span class="text-danger ml-1">*</span></label>
                              <input type="text" class="form-control" name="device_cateory" placeholder="Other product type">
                            </div>
                            <div class="form-group" id="otherModelSec" style="display:none;">
                              <label for="other_model">เครื่องปรับอากาศ รุ่นอื่น (โปรดระบุ) <span class="text-danger ml-1">*</span></label>
                              <input type="text" class="form-control" name="other_model" placeholder="Other prduct model">
                            </div>
                            <div class="form-group">
                              <label for="indoor_sn">หมายเลขเครื่องภายใน (Fancoil)<span class="text-danger ml-1">*</span></label>
                              <div class="input-group">
                                  <input type="search" class="form-control" name="indoor_sn" placeholder="ตัวอย่าง เช่น VF10FL0121030055">
                                  <div class="input-group-append" data-toggle="modal" data-target="#qrReaderMod">
                                      <div class="input-group-text bg-warning"><i class="fa fa-qrcode mr-1"></i>QR Code</div>
                                  </div>
                              </div>
                            </div>
                            <div class="form-group">
                              <label for="outdoor_sn">หมายเลขเครื่องภายนอก (Condensing)<span class="text-danger ml-1">*</span></label>
                              <div class="input-group">
                                  <input type="search" class="form-control" name="outdoor_sn" placeholder="">
                                  <div class="input-group-append" data-toggle="modal" data-target="#qrReaderMod">
                                      <div class="input-group-text bg-warning"><i class="fa fa-qrcode mr-1"></i> QR Code</div>
                                  </div>
                              </div>
                              <button type="button" class="btn btn-block btn-sm btn-secondary mt-3" id="btnFindSN1"><i class="fas fa-search"></i> ค้นหารหัสผลิตภัณฑ์</button>
                              <div class="mt-1"><strong>สถานะรหัสสินค้า</strong> : <span id="resultSN1">-</span></div>
                            </div>
                            <div class="form-group" id="productModelSec">
                              <label for="productModel">รุ่นสินค้า <span class="text-danger ml-1">*</span></label>
                              <select class="form-control select2" id="productModel" name="product_model">
                                <option disabled selected>- โปรดระบุรุ่น -</option>
                              </select>
                              <div class="badge">ประเภทสินค้า : <span id="productTypeTxt" class="text-wrap">(ยังไม่ได้เลือกรุ่นสินค้า)</span></div>
                              <input type="hidden" name="serial_id">
                              <input type="hidden" name="product_type">
                            </div>
                            <label for="customer_name">ชื่อและนามสกุลผู้ซื้อ <span class="text-danger ml-1">*</span></label>
                            <div class="input-group mb-3">
                              <div class="input-group-prepend">
                                <select name="customer_title" id="customerTitle" class="form-control">
                                  <option value="">(คำนำหน้า)</option>
                                  <option value="นาย">นาย</option>
                                  <option value="นาง">นาง</option>
                                  <option value="นางสาว">นางสาว</option>
                                  <option value="อื่นๆ">อื่นๆ</option>
                                </select>
                              </div>
                              <input type="text" class="form-control hide" name="customer_title_other" placeholder="อื่นๆ โปรดระบุ">
                              <input type="text" class="form-control" name="customer_name" placeholder="Full Name">
                            </div>

                            <div class="form-group">
                              <label for="customer_phone">โทรศัพท์ผู้ซื้อ<span class="text-danger ml-1">*</span></label>
                              <input type="phone" class="form-control" id="customer_phone" name="customer_phone" placeholder="Consumer Phone No.">
                              <span class="text-danger small"> ระบบจะส่ง One-Time Password (OTP) เพื่อยืนยันหลังจากกดปุ่มลงทะเบียน</span>
                            </div>
                            <div class="form-group">
                              <label for="customer_email">อีเมล์ผู้ซื้อ </label>
                              <input type="text" class="form-control" name="customer_email" placeholder="Consumer Email">
                            </div>
                            <div class="form-group">
                              <label for="customer_lineid">LINE ID </label>
                              <input type="text" class="form-control" name="customer_lineid" placeholder="LINE ID">
                            </div>

                            <hr/>
                            <div class="form-group row">
                              <label for="date_purchased" class="col-sm-5 col-form-label">วันที่ซื้อ <span class="text-danger ml-1">*</span></label>
                              <div class="col-sm-7">
                                <div class="input-group date" id="date_purchased" data-target-input="nearest">
                                    <input type="text" class="form-control datetimepicker-input" name="date_purchased" data-target="#date_purchased" placeholder="Date of Purchase"/>
                                    <div class="input-group-append" data-target="#date_purchased" data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                              </div>
                            </div>
                            <div class="form-group row">
                              <label for="seller_name" class="col-sm-5 col-form-label">ซื้อร้าน / ตัวแทนจำหน่าย <span class="text-danger ml-1">*</span></label>
                              <div class="col-sm-7">
                                <select class="form-control" name="seller_name" id="seller"></select>
                                <div class="input-group-append">
                                    <div class="btn btn-sm btn-outline-warning btn-block" id="btnAddDealer"><i class="fa fa-plus-circle mr-1"></i> กดที่นี้หากไม่พบชื่อ</div>
                                </div>
                                <input type="hidden" name="seller_phone">
                                <!-- <div class="small text-muted"> * </div> -->
                                <!-- <input type="text" class="form-control" name="seller_name" placeholder="Seller Name (Agent / Shop / Dealer)"> -->
                              </div>
                            </div>

                          </div>
                        </div>
                      </div>

                    <div class="col-md-6 col-12">
                      <div class="card card-danger">
                        <div class="card-header">
                          <h3 class="card-title"><i class="fas fa-cogs"></i> การติดตั้ง</h3>
                        </div>
                        <div class="card-body">
                          <div class="form-group row">
                            <label for="date_installed" class="col-sm-3 col-form-label">วันที่ติดตั้ง <span class="text-danger ml-1">*</span></label>
                            <div class="col-sm-9">
                              <div class="input-group date" id="date_installed" data-target-input="nearest">
                                  <input type="text" class="form-control datetimepicker-input" name="date_installed" data-target="#date_installed"/>
                                  <div class="input-group-append" data-target="#date_installed" data-toggle="datetimepicker">
                                      <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                  </div>
                              </div>
                            </div>
                          </div>

                          <h5 class="title"><i class="fas fa-map"></i> สถานที่ติดตั้ง</h5>
                          <small>กรุณาเลือกวิธีการให้ข้อมูลสถานที่</small>
                          <ul class="nav nav-tabs" id="placeTab" role="tablist">
                            <li class="nav-item">
                              <a class="nav-link" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">1. ลากวางบนแผนที่</a>
                            </li>
                            <li class="nav-item">
                              <a class="nav-link active" id="placeManual-tab" data-toggle="tab" href="#placeManual" role="tab" aria-controls="placeManual" aria-selected="false">2.กรอกที่อยู่เอง</a>
                            </li>
                          </ul>
                          <div class="tab-content mt-2" id="placeTabContent">
                            <div class="tab-pane fade" id="home" role="tabpanel" aria-labelledby="home-tab">
                              <!-- <div id="myPlace"></div> -->
                              <div>กรุณาลากจุดสีแดงให้ไปสถานที่ติดตั้งตามต้องการ :</div>
                              <div id="mapCanvas1"></div>
                              <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                  <span class="input-group-text">GPS Loc</span>
                                </div>
                                <input type="text" class="form-control" name="install_latlng" placeholder="" readonly>
                              </div>
                                <!-- <b>Marker status:</b> -->
                                <!-- <div id="markerStatus"><i>Click and drag the marker.</i></div> -->
                                <!-- <b>Current position:</b>
                                <div id="info"></div> -->
                                <!-- <b>Closest matching address:</b>
                                <div id="address"></div> -->
                            </div>
                            <div class="tab-pane fade show active" id="placeManual" role="tabpanel" aria-labelledby="placeManual-tab">
                              <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                  <span class="input-group-text">เลขที่</span>
                                </div>
                                <input type="text" class="form-control" name="address_no" placeholder="House No.">
                              </div>

                              <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                  <span class="input-group-text">หมู่ที่</span>
                                </div>
                                <input type="text" class="form-control" name="address_moo" placeholder="Village No">
                              </div>
                              <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                  <span class="input-group-text">อาคาร/ตึก</span>
                                </div>
                                <input type="text" class="form-control" name="address_building" placeholder="Building / Tower name">
                              </div>
                              <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                  <span class="input-group-text">ซอย/ถนน</span>
                                </div>
                                <input type="text" class="form-control" name="address_road" placeholder="Road">
                              </div>
                              <div class="input-group mb-2">
                                <input type="text" class="form-control" name="address_subdistrict" placeholder="แขวง/ตำบล">
                              </div>
                              <div class="input-group mb-2">
                                <input type="text" class="form-control" name="address_district" placeholder="เขต/อำเภอ">
                              </div>
                              <div class="input-group mb-2">
                                <input type="text" class="form-control" name="address_province" placeholder="จังหวัด">
                              </div>
                              <div class="input-group mb-2">
                                <input type="text" class="form-control" name="address_postcode" placeholder="รหัสไปรษณีย์">
                              </div>
                            </div>
                          </div>

                        <hr/>

                        <div class="form-group">
                          <label for="customer_org_name">ชื่อหน่วยงาน / บริษัท ของสถานที่ติดตั้ง (ถ้ามี)</label>
                          <input type="text" class="form-control" name="customer_org_name" placeholder="Organization name">
                        </div>

                        <div class="card card-secondary " id="installerSec">
                          <!-- <div class="card-header"> collapsed-card
                            <div class="custom-control custom-checkbox">
                              <input class="custom-control-input" name="is_installer" type="checkbox" value="1" id="isInstaller" name="customRadio">
                              <label for="isInstaller" class="custom-control-label">มีข้อมูลช่างติดตั้ง เพื่อประโยชน์ในการติดตามงานบริการ</label>
                            </div>
                            <div class="card-tools">
                              <button id="toggleInstaller" type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-plus"></i>
                              </button>
                            </div>
                          </div> -->
                          <div class="card-body">
                            <div class="form-group row">
                              <label for="technician_name" class="col-sm-4 col-form-label">ชื่อช่างติดตั้ง</label>
                              <div class="col-sm-8">
                                <input type="email" class="form-control" name="technician_name" placeholder="ตัวอย่างเช่น นาย สุรัตน์ รัตนะมณีราษฎ์">
                              </div>
                            </div>
                            <div class="form-group row">
                              <label for="installer_phone" class="col-sm-4 col-form-label bg-warning">โทรศัพท์ช่างติดตั้ง</label>
                              <div class="col-sm-8">
                                <input type="phone" class="form-control" name="technician_phone" placeholder="**** สำหรับช่างสะสมคะแนน">
                              </div>
                            </div>
                            <!-- <div class="form-group row">
                              <label for="technician_code" class="col-sm-4 col-form-label">รหัสช่าง (ถ้ามี)</label>
                              <div class="col-sm-8">
                                <input type="email" class="form-control" name="technician_code" placeholder="Installer Code">
                              </div>
                            </div> -->
                          </div>
                          <!-- /.card-body -->
                        </div>
                        <!-- <div class="form-group">
                          <label for="warranty_photo">ภาพถ่ายใบรับประกันทั้งหมด / Warranty Card Photo</label>
                          <div class="input-group">
                            <div class="custom-file">
                              <input type="file" class="custom-file-input" id="warranty_photo">
                              <label class="custom-file-label" for="warranty_photo">เลือกไฟล์</label>
                            </div>
                          </div>
                        </div> -->

                      </div>
                    </div>
                  </div>
                </div>
                <!-- end of Register -->
                <div class="card-footer">
                  <label class="small" for="userAccept1">ท่านตกลงยินยอมให้บริษัทเก็บรวบรวมข้อมูลส่วนบุคคลของท่านที่ได้ให้ไว้แก่บริษัท และ/หรืออยู่ในความครอบครองของบริษัท โดยมีวัตถุประสงค์เพื่อนำเสนอบริการที่มีคุณภาพยิ่งขึ้นให้แก่ผู้ใช้ โดยให้ใช้อย่างถูกต้องและเป็นไปตามกฏหมาย ทั้งนี้เจ้าของข้อมูลมีสิทธิ์ขอแก้ไข ระงับการใช้ หรือทำลาย โดยแจ้งบริษัทเป็นลายลักษณ์อักษร</label>
                  <br/>
                  <div class="form-check mb-3 mt-2">
                    <input type="checkbox" class="form-check-input" name="user_accept_1" value="1" id="userAccept1">
                    <label class="form-check-label" for="userAccept1">ยินยอม / Accept</label>
                  </div>
                  <button type="reset" class="btn btn-lg btn-default" id="btnSubmitRegisterReset">เริ่มใหม่</button>
                  <button type="button" id="btnSubmitRegister" class="btn btn-lg btn-primary mr-3">  <i class="fas fa-paper-plane mr-1"></i> ลงทะเบียน</button>
                  <a class="btn btn-lg btn-default disabled hide"> <i class="fas fa-file-alt mr-1"></i> รหัสลงทะเบียนของคุณที่ทำรายการล่าสุด : <span id="statusRegister">ไม่พบข้อมูล</span></a>
                </div>
              </div>
             </div>
            </form>
            <!-- end of Register -->
          </div>
          <div class="tab-pane fade" id="menu-faq" role="tabpanel" aria-labelledby="menu-faq-tab">
            <div class="card card-primary">
              <div class="card-header">
                <h3 class="card-title"><i class="fas fa-lightbulb"></i> ความรู้/การแก้ปัญหา</h3>
              </div>
              <div class="card-body">
              <div class="row">
                <div class="col-lg-4">
                  <iframe class="mb-3" width="100%" style="min-height:200px;" src="https://www.youtube.com/embed/EbL4lC8wwhs" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                  <br/>
                  <p class="small">เลือกหมวดหมู่เนื้อหา : </p>
                    <div class="nav nav-pills faq-nav" id="faq-tabs" role="tablist" aria-orientation="vertical">
                        <!-- <a href="#tab1" class="nav-link active" data-toggle="pill" role="tab" aria-controls="tab1" aria-selected="true">
                            <i class="mdi mdi-help-circle"></i> คำถามความรู้เบื้องต้นเกี่ยวกับระบบปรับอากาศ
                        </a>
                        <a href="#tab2" class="nav-link" data-toggle="pill" role="tab" aria-controls="tab2" aria-selected="false">
                            <i class="mdi mdi-account"></i> คำถามเกี่ยวกับวิธีการใช้และบำรุงรักษาเครื่องปรับอากาศมาเวล
                        </a>
                        <a href="#tab3" class="nav-link" data-toggle="pill" role="tab" aria-controls="tab3" aria-selected="false">
                            <i class="mdi mdi-account-settings"></i>  คําถามเกี่ยวกับระบบอินเวอร์เตอร์
                        </a>
                        <a href="#tab4" class="nav-link" data-toggle="pill" role="tab" aria-controls="tab4" aria-selected="false">
                            <i class="mdi mdi-heart"></i> คําถามเกี่ยวกับสารทําความเย็น R32
                        </a>
                        <a href="#tab5" class="nav-link" data-toggle="pill" role="tab" aria-controls="tab5" aria-selected="false">
                            <i class="mdi mdi-coin"></i> คําถามเกี่ยวกับเครื่องปรับอากาศ VRV
                        </a>
                        <a href="#tab6" class="nav-link" data-toggle="pill" role="tab" aria-controls="tab6" aria-selected="false">
                            <i class="mdi mdi-help"></i> การขอความช่วยเหลือ
                        </a> -->
                    </div>
                </div>
                  <div class="col-lg-8">
                      <div class="tab-content" id="faq-tab-content_0">
                        <iframe src="https://docs.google.com/viewerng/viewer?url=https://cs.mavellair.com/view/mavell_error_code.pdf&embedded=true" frameborder="0" height="500px" width="100%"></iframe>
                      </div>
                      <div class="tab-content" id="faq-tab-content"></div>
                  </div>
              </div>
              </div>
            </div>

          </div>
          <div class="tab-pane fade" id="menu-req" role="tabpanel" aria-labelledby="menu-req-tab">
                            
          <div class="mb-3 d-flex justify-content-center">
        <div class="text-center">
            <div><strong>กรุณาเลือกประเภทผู้ใช้งาน</strong></div>
            <div class="mt-2">
            <label><input type="radio" name="userType" value="customer" onclick="toggleServiceForm()"> ลูกค้าทั่วไป</label>
            <label class="ml-3"><input type="radio" name="userType" value="technician" onclick="toggleServiceForm()"> ช่าง/ASC</label>
            </div>
        </div>
        </div>

        <!-- ฟอร์มลูกค้า -->
            <div id="serviceFormCustomer" style="display:none;">
            <!-- Service Request -->
            <form id="form_service_request">
            <div class="card card-primary">
              <div class="card-header">
                <h3 class="card-title"><i class="fas fa-tools"></i> แจ้งขอรับบริการ / แจ้งซ่อม / สอบถาม</h3>
              </div>
                <div class="card-body">
                  <div class="mb-3">
                    <h5 class="text-danger">Service request form</h5>
                    <button type="button" class="btn btn-outline-primary btn-sm" data-toggle="pill" href="#menu-reg" role="tab" aria-controls="menu-reg" aria-selected="true">
                      <i class="fas fa-shield-alt mr-1"></i>กดที่นี่ หากคุณต้องการลงทะเบียนผลิตภัณฑ์
                    </button>
                    <button type="button" data-toggle="modal" data-target="#wcdModal" class="btn btn-outline-primary btn-sm">
                      <i class="fas fa-h and-point-right mr-1"></i>กดที่นี่ เพื่ออ่านเงื่อนไขการรับประกันเครื่องปรับอากาศมาเวล
                    </button>
                  </div>
                  <div class="small"> การขอรับบริการโปรดกรอกแบบฟอร์มด้านล่างเพื่อดำเนินการตามคำขอของคุณอย่างรวดเร็วโปรดส่งข้อมูลต่อไปนี้ในไฟล์แนบ และทีมงานของเรายินดีให้ความช่วยเหลือภายใน 24 ชั่วโมง</div>
                  <br/>
                  <div class="row">
                    <div class="col-md-6 col-12">
                      <div class="card card-danger">
                        <div class="card-header">
                          <h3 class="card-title"><i class="fas fa-cube"></i> ผลิตภัณฑ์ที่ขอรับบริการ</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                              <label for="serviceType">แจ้งเรื่อง <span class="text-danger ml-1">*</span></label>
                              <select class="form-control select2" name="service_type2" id="serviceType">
                                <option disabled>- โปรดระบุเรื่องที่แจ้งขอบริการ -</option>
                                <option value="repair" selected>แจ้งขอรับบริการซ่อม</option>
                                <option value="inquiry_part">สอบถามและสั่งซื้ออะไหล่</option>
                                <option value="inquiry_technical">สอบถามปัญหาทางเทคนิค</option>
                              </select>
                            </div>
                            <!-- <div class="form-group">
                              <label for="productType">ประเภทสินค้า <span class="text-danger ml-1">*</span></label>
                              <select class="form-control select2" name="product_type2" id="productType2">
                                <option disabled selected>- โปรดระบุประเภทแอร์มาเวล  -</option>
                                <option value="1">เครื่องปรับอากาศ แบบ ติดผนัง-ฟิกซ์สปีด / Wall Type / Fixed Speed (Standard)</option>
                                <option value="2">เครื่องปรับอากาศ แบบ ติดผนัง-ฟิกซ์สปีด-เต็มบีทียู / Wall Type / Fixed Speed (Full BTU)</option>
                                <option value="3">เครื่องปรับอากาศ แบบ ติดผนัง-อินเวอร์เตอร์-สมาร์ทพลัส-1ดาว / Wall Type / Inverter / Smart Plus (1 Star)</option>
                                <option value="4">เครื่องปรับอากาศ แบบ ติดผนัง-อินเวอร์เตอร์-สมาร์ทคูล-0ดาว / Wall Type / Inverter / Smart Cool (0 Star)</option>
                                <option value="5">เครื่องปรับอากาศ แบบ ติดผนัง-อินเวอร์เตอร์-สมาร์ททรี-3ดาว / Wall Type / Inverter / Smart III (3 Star)</option>
                                <option value="6">เครื่องปรับอากาศ แบบ ติดผนัง-อินเวอร์เตอร์ ไวไฟ / Wall Type / Inverter WIFI</option>
                                <option value="7">เครื่องปรับอากาศ แบบ แขวน-ฟิกซ์สปีด-R32 / Ceiling Type / Fixed Speed / R32 </option>
                                <option value="8">เครื่องปรับอากาศ แบบ แขวน-ฟิกซ์สปีด-R410A / Ceiling Type / Fixed Speed / R410A </option>
                                <option value="9">เครื่องปรับอากาศ แบบ 4 ทิศทาง-ฟิกซ์สปีด-R32 / Cassette Type / Fixed Speed / R32 </option>
                                <option value="10">เครื่องปรับอากาศ แบบ 4 ทิศทาง-ฟิกซ์สปีด-R410A / Cassette Type / Fixed Speed / R410A </option>
                                <option value="-1">เครื่องปรับอากาศ ประเภทอื่น (โปรดระบุ) </option>
                              </select>
                            </div> -->
                            <div class="form-group">
                              <label for="indoor_sn">หมายเลขเครื่องภายใน หรือ ภายนอก ที่ลงทะเบียนไว้<span class="text-danger ml-1">*</span></label>
                              <div class="input-group">
                                  <input type="search" class="form-control" id="snQuery"></select>
                                  <div class="input-group-append" data-toggle="modal" data-target="#qrReaderMod">
                                      <div class="input-group-text bg-warning"><i class="fa fa-qrcode mr-1"></i> QR Code</div>
                                  </div>
                              </div>
                              <button type="button" class="btn btn-block btn-sm btn-secondary mt-1" id="btnFindSN2"><i class="fas fa-search"></i> ค้นหารหัสผลิตภัณฑ์</button>
                              <!-- <select class="form-control" id="snQuery"></select> -->
                              <input type="hidden" name="indoor_sn2">
                              <input type="hidden" name="outdoor_sn2">
                              <input type="hidden" name="product_id2">
                              <input type="hidden" name="serial_id2">
                              <input type="hidden" name="product_type2">
                              <!-- <input type="hidden" name="product_model2"> -->

                              <div class=""><strong>ข้อมูลการลงทะเบียน</strong> : <span id="resultSN2">-</span></div>
                              <div class="mt-1"><strong>สถานะรหัสสินค้า</strong> : <span id="resultSN3">-</span></div>

                            </div>
                            <div class="form-group">
                              <label for="productModel2">รุ่นสินค้า <span class="text-danger ml-1">*</span></label>
                              <select class="form-control select2" id="productModel2" name="product_model2">
                                <option disabled selected>- โปรดระบุรุ่น -</option>
                              </select>
                              <div class=""><strong>ประเภทสินค้า</strong> : <span id="productTypeTxt2" class="text-wrap">(ยังไม่ได้ค้นหารหัสผลิตภัณฑ์)</span></div>
                            </div>

                            <!-- <div class="form-group" id="otherTypeSec2" style="display:none;">
                              <label for="other_type">เครื่องปรับอากาศ ประเภทอื่น (โปรดระบุ) <span class="text-danger ml-1">*</span></label>
                              <input type="text" class="form-control" id="other_type2" placeholder="Other product type">
                            </div>
                            <div class="form-group" id="otherModelSec2" style="display:none;">
                              <label for="other_model">เครื่องปรับอากาศ รุ่นอื่น (โปรดระบุ) <span class="text-danger ml-1">*</span></label>
                              <input type="text" class="form-control" id="other_model2" placeholder="Other prduct model">
                            </div> -->
                            <div class="form-group" id="errorCodeSec">
                              <label for="errorCode">อาการเสีย <span class="text-danger ml-1">*</span></label>
                              <select class="form-control select2" name="error_code2" id="errorCode">
                                <option disabled selected value="0">- โปรดระบุอาการ -</option>
                                <option value="E0">ขึ้น Error Code E0</option>
                                <option value="E1">ขึ้น Error Code E1</option>
                                <option value="E2">ขึ้น Error Code E2</option>
                                <option value="E3">ขึ้น Error Code E3</option>
                                <option value="E4">ขึ้น Error Code E4</option>
                                <option value="E5">ขึ้น Error Code E5 หรือ 5E</option>
								                <option value="E6">ขึ้น Error Code E6</option>
                                <option value="F0">ขึ้น Error Code F0</option>
                                <option value="F1">ขึ้น Error Code F1</option>
                                <option value="F3">ขึ้น Error Code F3</option>
                                <option value="F4">ขึ้น Error Code F4</option>
                                <option value="F6">ขึ้น Error Code F6</option>
                                <option value="P3">ขึ้น Error Code P3</option>
                                <option value="indoor_noise">Indoor มีเสียงดัง</option>
                                <option value="outdoor_noise">Outdoor มีเสียงดัง</option>
                                <option value="not_start">แอร์เปิดไม่ติด</option>
                                <option value="remote_failed">รีโมทกดไม่ติด</option>
                                <option value="water_drop">แอร์น้ำหยด</option>
                                <option value="frozen_coil">แผงคอยล์เย็นเป็นน้ำแข็ง</option>
                                <option value="not_cold">แอร์ไม่เย็น</option>
                                <option value="slow_continue">แอร์ต่อช้า</option>
                                <option value="not_back_off">แอร์ไม่ตัด</option>
                                <option value="smell">แอร์มีกลิ่นเหม็น</option>
                                <option value="no_nitrogen">ไม่มีไนโตรเจนในแผงคอยล์เย็น</option>
                                <option value="no_refrigerant">น้ำยาในคอยล์ร้อนมีน้อยหรือไม่มีเลย</option>
								<option value="coil_leak">แผงคอยล์เย็นรั่ว</option>
								<option value="com_leak">คอมเพรสเซอร์รั่ว</option>
                                <option value="com_lock">คอมเพรสเซอร์ล็อค</option>
                                <option value="com_notsuck">คอมเพรสเซอร์ไม่ดูด/ไม่อัด</option>
                              </select>
                            </div>

                            <div class="form-group">
                              <label for="description2">รายละเอียด</label>
                              <textarea class="form-control" name="description2" rows="3" placeholder="Enter your description..." id="description"></textarea>
                            </div>
                            <div class="form-group">
                              <label for="attach_photo">ไฟล์หรือภาพประกอบ (อย่างน้อย1ไฟล์)<span class="text-danger ml-1">*</span></label>
                              <div class="input-group">
                                <div class="custom-file">
                                  <input type="file" class="custom-file-input" accept="image/*,video/*" multiple required name="attach_photo" id="attachPhoto">
                                  <label class="custom-file-label" for="attach_photo">เลือกไฟล์</label>
                                  <div class="progress hide" id="progress">
                                     <div class="progress-bar" role="progressbar" style="width:100%">0%</div>
                                  </div>
                                </div>
                              </div>
                              <ul>
                                <li>ขนาดไฟล์ที่สามารนำเข้า ได้ไม่ควรเกิน 5 MB และเป็น .jpg, png, bmp, gif, mp4, avi, mov เท่านั้น</li>
                                <li>จำกัดจำนวนไฟล์ 5 ไฟล์</li>
                              </ul>
                              <div class="m-2">
                                  <div id="previewFile" class="row"></div>
                              </div>
                            </div>
                          </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                      <div class="card card-danger">
                        <div class="card-header">
                          <h3 class="card-title"><i class="fas fa-file-alt"></i> ข้อมูลผู้ขอ และสถานที่ขอรับบริการ</h3>
                        </div>
                        <div class="card-body">
                          <h5 class="title"><i class="fas fa-user"></i> ผู้รับบริการ <span class="text-danger ml-1">*</span></h5>
                          <div class="input-group mb-2">
                            <div class="input-group-prepend">
                              <span class="input-group-text">ชื่อ/นามสกุล</span>
                              <select name="requester_title2" id="requesterTitle" class="form-control">
                                <option value="">(คำนำหน้า)</option>
                                <option value="นาย">นาย</option>
                                <option value="นาง">นาง</option>
                                <option value="นางสาว">นางสาว</option>
                                <option value="อื่นๆ">อื่นๆ</option>
                              </select>
                            </div>
                            <input type="text" class="form-control hide" name="requester_title_other2" placeholder="อื่นๆ โปรดระบุ">
                            <input type="text" class="form-control" name="requester_name2" placeholder="First and last name">
                          </div>
                          <div class="input-group mb-2">
                            <div class="input-group-prepend">
                              <span class="input-group-text">ชื่อหน่วยงาน (ถ้ามี)</span>
                            </div>
                            <input type="text" class="form-control" name="requester_org_name2" placeholder="Organization Name">
                          </div>

                          <div class="input-group mb-2">
                            <div class="input-group-prepend">
                              <span class="input-group-text">โทรศัพท์ติดต่อ</span>
                            </div>
                            <input type="text" class="form-control" name="requester_phone2" placeholder="Phone No.">
                          </div>
                          <div class="input-group mb-2">
                            <div class="input-group-prepend">
                              <span class="input-group-text">อีเมล์ (ถ้ามี)</span>
                            </div>
                            <input type="text" class="form-control" name="requester_email2" placeholder="Contact email">
                          </div>
                          <div class="input-group mb-2">
                            <div class="input-group-prepend">
                              <span class="input-group-text">LINE ID (ถ้ามี)</span>
                            </div>
                            <input type="text" class="form-control" name="requester_lineid2" placeholder="LINE ID">
                          </div>

                          <h5 class="title mt-3"><i class="fas fa-map"></i> สถานที่ขอรับบริการ</h5>
                          <small>กรุณาเลือกวิธีการให้ข้อมูลสถานที่</small>
                          <ul class="nav nav-tabs" id="placeTab" role="tablist">
                            <li class="nav-item">
                              <a class="nav-link" id="home2-tab" data-toggle="tab" href="#home2" role="tab" aria-controls="home" aria-selected="true">1. ลากวางบนแผนที่</a>
                            </li>
                            <li class="nav-item">
                              <a class="nav-link active" id="placeManual2-tab" data-toggle="tab" href="#placeManual2" role="tab" aria-controls="placeManual" aria-selected="false">2.กรอกที่อยู่เอง</a>
                            </li>
                          </ul>
                          <div class="tab-content mt-2" id="placeTabContent2">
                            <div class="tab-pane fade" id="home2" role="tabpanel" aria-labelledby="home2-tab">
                              <div>กรุณาลากจุดสีแดงให้ไปสถานที่ติดตั้งตามต้องการ :</div>
                              <div id="mapCanvas2"></div>
                              <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                  <span class="input-group-text">GPS Loc</span>
                                </div>
                                <input type="text" class="form-control" name="request_latlng2" placeholder="" readonly>
                              </div>
                            </div>
                            <div class="tab-pane fade show active" id="placeManual2" role="tabpanel" aria-labelledby="placeManual2-tab">
                              <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                  <span class="input-group-text">เลขที่</span>
                                </div>
                                <input type="text" class="form-control" name="address_no2" placeholder="House No.">
                              </div>

                              <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                  <span class="input-group-text">หมู่ที่</span>
                                </div>
                                <input type="text" class="form-control" name="address_moo2" placeholder="Village No">
                              </div>
                              <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                  <span class="input-group-text">อาคาร/ตึก</span>
                                </div>
                                <input type="text" class="form-control" name="address_building2" placeholder="Building / Tower name">
                              </div>
                              <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                  <span class="input-group-text">ซอย/ถนน</span>
                                </div>
                                <input type="text" class="form-control" name="address_road2" placeholder="Road">
                              </div>
                              <div class="input-group mb-2">
                                <input type="text" class="form-control" name="address_subdistrict2" placeholder="แขวง/ตำบล">
                              </div>
                              <div class="input-group mb-2">
                                <input type="text" class="form-control" name="address_district2" placeholder="เขต/อำเภอ">
                              </div>
                              <div class="input-group mb-2">
                                <input type="text" class="form-control" name="address_province2" placeholder="จังหวัด">
                              </div>
                              <div class="input-group mb-2">
                                <input type="text" class="form-control" name="address_postcode2" placeholder="รหัสไปรษณีย์">
                              </div>
                            </div>
                          </div>

                      </div>
                    </div>
                  </div>

                  </div>
                </div>
              <div class="card-footer">
                <label class="small" for="userAccept2">ท่านตกลงยินยอมให้บริษัทเก็บรวบรวมข้อมูลส่วนบุคคลของท่านที่ได้ให้ไว้แก่บริษัท และ/หรืออยู่ในความครอบครองของบริษัท โดยมีวัตถุประสงค์เพื่อนำเสนอบริการที่มีคุณภาพยิ่งขึ้นให้แก่ผู้ใช้ โดยให้ใช้อย่างถูกต้องและเป็นไปตามกฏหมาย ทั้งนี้เจ้าของข้อมูลมีสิทธิ์ขอแก้ไข ระงับการใช้ หรือทำลาย โดยแจ้งบริษัทเป็นลายลักษณ์อักษร</label>
                <br/>
                <div class="form-check mb-3 mt-2">
                  <input type="checkbox" class="form-check-input" name="user_accept_2" value="1" id="userAccept2">
                  <label class="form-check-label" for="userAccept2">ยินยอม / Accept</label>
                </div>
                <button type="button" class="btn btn-lg btn-default" id="btnSubmitServReqReset">เริ่มใหม่</button>
                <button type="button" id="btnSubmitServReq" class="btn btn-lg btn-primary">  <i class="fas fa-paper-plane mr-1"></i> แจ้งขอรับบริการ</button>
                <a class="btn btn-lg btn-default hide"> <i class="fas fa-file-alt mr-1"></i> สถานะการขอรับบริการ : <span id="statusServRerv">ไม่พบข้อมูล</span></a>
              </div>
            </div>
            <!-- End of Service Request -->
            </form>
            </div>
          </div>
          <div class="tab-pane fade" id="menu-dealer" role="tabpanel" aria-labelledby="menu-dealer-tab">
            <div class="card card-primary mb-5">
              <div class="card-header">
                <h3 class="card-title"><i class="fas fa-building"></i> ศูนย์บริการมาเวล - Service Center</h3>
              </div>
              <div class="card-body">
                <div class="sidebar">
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                    <input type="text" class="form-control" id="search_dealer" autofocus placeholder="พิมพ์เพื่อค้นหา...">
                  </div>

                  <div id="storeinfo"><i id="infoClose" class="fa fa-times"></i>
                    <div id="infoDivInner"></div>
                  </div>
                  <div id="listingSCDiv" class="listings"></div>
                </div>
                <div id="mapView" class="map"></div>
              </div>
            </div>
          </div>

          <div class="mb-5 mt-3">
            <div class="text-center mb-3">
              <img src="./view/img/mavell_bg1.jpg" class="img-fluid rounded" alt="Mavell Air Conditioner">
            </div>
            <a href="#main" type="button" class="btn btn-outline-secondary btn-sm" id="backMenu">
              <i class="fas fa-arrow-left mr-1"></i> แสดงเมนูหลัก
            </a>
            <div class="float-right">
              <a href="https://apps.apple.com/us/app/hi-mavell/id1558978802" target="_blank">
                <img src="./view/img/applestore.png" alt="Apple Store Hi Mavell" style="height:35px;">
              </a>
              <a href=" https://play.google.com/store/apps/details?id=biz.harmoniz.mavell" target="_blank">
                <img src="./view/img/googleplay.png" alt="Google Play Hi Mavell" style="height:35px;">
              </a>
            </div>
          </div>
        </div>



      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <!-- Warranty Conditions Modal -->
  <div class="modal fade" id="wcdModal" tabindex="-1" role="dialog" aria-labelledby="wcdModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="wcdModal"><i class="fas fa-file"></i> เงื่อนไขการรับประกันเครื่องปรับอากาศมาเวล</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body small">
บริษัทฯ ยินดีมอบการรับประกันผลิตภัณฑ์ของ MAVELL เพื่อความมั่นใจในคุณภาพตามเงื่อนไขดังต่อไปนี้
1.หากเกิดปัญหาในช่วงระยะเวลารับประกัน ให้ท่านติดต่อศูนย์บริการเครื่องปรับอากาศมาเวล หรือตัวแทนจำหน่ายที่ท่านซื้อ พร้อมระบุรุ่น (Model) และหมายเลขเครื่อง (Serial Number) ทุกครั้งที่ขอรับการบริการ<br/>
2. การรับประกันจะไม่ครอบคลุมถึงกรณีต่อไปนี้<br/>
2.1 บัตรรับประกันไม่ได้ระบุรุ่น หมายเลขเครื่อง วันที่ซื้อ หรือวันที่ติดตั้ง และไม่มีรายชื่อหรือตราประทับจากบริษัทฯ<br/>
2.2 ไม่ส่งบัตรรับประกัน (ส่วนสำหรับส่งคืนบริษัทฯ ) หรือลงทะเบียนการรับประกันออนไลน์ภายใน 15 วันหลังจากวันติดตั้งแล้วเสร็จ บริษัทฯ ขอสงวนสิทธิ์ในการพิจารณาการรับประกันความเสียหายเกิดจากสาเหตุอื่นๆ อันไม่ได้เกิดจากคุณภาพหรือข้อผิดพลาดจากมาตรฐานการผลิต<br/>
3. คอมเพรสเซอร์, ชิ้นส่วนภายใน, อุปกรณ์และอะไหล่ทุกชิ้นของเครื่องปรับอากาศและผลิตภัณฑ์อื่นภายใต้กฎหมายการค้าของ MAVELL ที่เสียเนื่องจากคุณภาพหรือมาตรฐานในการผลิตของโรงงานบริษัทฯ ยินดีเปลี่ยนให้โดยไม่คิดมูลค่าใดๆทั้งสิ้น ทั้งนี้ไม่รวมถึงท่อลม ระบบไฟฟ้า ท่อน้ำยา ท่อน้ำทิ้ง และงานติดตั้งระบบอื่นๆที่อยู่ภายนอกตัวเครื่อง ความเสียหายที่เกิดจากการติดตั้งที่ไม่ถูกวิธี การเคลื่อนย้าย ขนส่ง อุบัติเหตุ ภัยจากธรรมชาติ ไฟตก ไฟเกิน การใช้งานไม่ถูกต้อง แมลงสัตว์กัดแทะ และการกระทำใดๆ ที่ไม่ได้กระทำโดยบุคคลของโรงงานหรือบริษัทฯ<br/>
4. รับประกันเปลี่ยนเครื่องใหม่ภายใน 1 ปี สามารถเปลี่ยนเครื่องใหม่ได้กรณีที่แผงคอยล์เย็นหรือแผงคอยล์ร้อนรั่วเท่านั้น การรั่วซึมที่เกิดจากการติดตั้งที่ไม่ได้มาตรฐาน ไม่สามารถใช้สิทธิ์การรับประกันเปลี่ยนเครื่องใหม่ได้<br/>
5. รับประกันอุปกรณ์ไฟฟ้า 5 ปี กรณีเสียหายเนื่องจากคุณภาพหรือข้อผิดพลาดตามมาตรฐานในการผลิตของโรงงานบริษัทฯ ยินดีเปลี่ยนให้โดยไม่คิดมูลค่าใดๆทั้งสิ้น<br/>
6. รับประกันคอมเพรสเซอร์ 12 ปี กรณีคอมเพรสเซอร์เสียเท่านั้น และในกรณีที่มีการเปลี่ยนคอมเพรสเซอร์ที่อยู่ในระยะเวลารับประกัน จะไม่รวมค่าใช้จ่ายอื่นๆ ที่เกิดขึ้นในการเปลี่ยนคอมเพรสเซอร์<br/>
7. กรณีไม่มีบัตรรับประกันมาแสดงและใบเสร็จรับเงินสูญหายจะเริ่มรับประกันนับจากวันที่ผลิตที่ระบุในฉลากสินค้าไม่เกิน 15 เดือน โดยจะไม่นับจากวันที่ติดตั้ง<br/>
8. บริษัทฯ ไม่รับประกันเครื่องปรับอากาศที่ซื้อเฉพาะแฟนคอยล์หรือเฉพาะคอนเดนซิ่งที่ติดตั้งไปกับเครื่องปรับอากาศยี่ห้ออื่น<br/>
โปรดกรอกข้อความในบัตรประกันให้ครบถ้วนชัดเจนและส่งด่วนสำหรับส่วนส่งคืนบริษัทฯ ภายใน 15 วันนับจากวันติดตั้งแล้วเสร็จมิฉะนั้นทางบริษัทฯ สงวนสิทธิ์ในการรับประกันเครื่องปรับอากาศและถือว่าการรับประกันเป็นอันสิ้นสุด<br/>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด / Close</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="rewcdModal" tabindex="-1" role="dialog" aria-labelledby="rewcdModal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="rewcdModal"><i class="fas fa-file"></i> เงื่อนไขการสะสมคะแนน MAVELL POINT เพื่อแลกรางวัล</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body small">
<p><span style="white-space: pre;">		</span>บริษัท มาเวล คอร์ปอร์เรชั่น จำกัด มีความยินดีมอบสิทธิประโยชน์ในการแลกรับของรางวัลมากมายแก่ช่างผู้ติดตั้งเครื่องปรับอากาศมาเวลทุกท่าน เพียงช่างติดตั้งเครื่องปรับอากาศมาเวล ตามเงื่อนไขการสะสมคะแนน MAVELL POINT ต่อไปนี้</p><p><span style="white-space:pre">		</span>1. ช่างผู้ติดตั้ง ต้องดาวน์โหลดแอปพลิเคชั่น “HI MAVELL” และลงทะเบียนรับประกันสินค้าหลังการติดตั้งแล้วเสร็จ ผ่านทาง แอปพลิเคชั่น “HI MAVELL”&nbsp; ช่างผู้ติดตั้ง จึงจะมีสิทธิ์เข้าร่วมรายการสะสมคะแนน “สะสมแต้มแอร์มาเวล”</p><p>**หมายเหตุ : ผลิตภัณฑ์ที่เข้าร่วมรายการสะสมคะแนน มีเฉพาะประเภท Wall type ตั้งแต่ขนาด 9000 BTU – 25000 BTU</p><p>ทั้งรุ่น Fixed Speed และรุ่น Inverter**</p><p><span style="white-space:pre">		</span>2. ต้องเป็นเครื่องปรับอากาศมาเวลที่ติดตั้ง และลงทะเบียนรับประกันผ่านแอปพลิเคชั่น “HI MAVELL” ตั้งแต่</p><p>วันที่ 1 ม.ค. 2565 เป็นต้นไป โดยต้องกรอกข้อมูลให้ครบถ้วน และต้องแนบไฟล์เอกสารตามที่บริษัทกำหนดอย่างครบถ้วน สมบูรณ์</p><p><span style="white-space:pre">		</span>3.ต้องเป็นเครื่องปรับอากาศมาเวลที่จำหน่ายผ่านตัวแทนจำหน่ายเท่านั้น (ไม่รวมตัวโชว์ ราคาพิเศษ การขายโปรเจค)</p><p><span style="white-space:pre">		</span>4.บริษัทฯ จะไม่รับผิดชอบต่อข้อมูลที่ล่าช้า สูญหาย ไม่สมบูรณ์ อ่านไม่ออก การฉ้อโกงหรือส่งข้อมูลที่ไม่ตรง ที่เกิดจากผู้เข้าร่วมรายการ และระบบโทรศัพท์</p><p><span style="white-space:pre">		</span>5.กรณีมีการส่งข้อมูลเครื่องปรับอากาศเพื่อการสะสมแต้มคะแนนซ้ำซ้อน จะสงวนสิทธิยกผลประโยชน์ให้กับช่างผู้ติดตั้งจริงเท่านั้น</p><p><span style="white-space:pre">		</span>6.บริษัทฯ ขอสงวนสิทธิ์ในการเปลี่ยนแปลงรายการสินค้าที่ร่วมรายการ “สะสมแต้ม แอร์มาเวล” และเกณฑ์มูลค่าคะแนนสะสม</p><p><span style="white-space:pre">		</span>7.บริษัทฯ ขอสงวนสิทธิ์ในการปรับปรุงคะแนนสะสมให้ถูกต้องในกรณีที่เกิดจากการคำนวณผิดพลาดเนื่องจากระบบ หรือกรณีใดๆ โดยบริษัทจะแจ้งยอดคะแนนสะสมที่ถูกต้องในเดือนถัดไป</p><p><span style="white-space:pre">		</span>8.จำนวนคะแนนที่ใช้ในการแลกเปลี่ยนอาจมีการเปลี่ยนแปลงได้โดยไม่ต้องแจ้งล่วงหน้า</p><p><span style="white-space:pre">		</span>9.คะแนนสะสม “สะสมแต้ม แอร์มาเวล” จะถูกยกเลิกหรือสิ้นสุดในกรณีที่</p><p><span style="white-space:pre">			</span>9.1 ลูกค้าใช้สิทธินำคะแนนสะสม “สะสมแต้ม แอร์มาเวล” แลกสินค้า</p><p><span style="white-space:pre">			</span>9.2 ลูกค้ากระทำการฉ้อฉล ปลอมแปลงเพื่อให้ได้มา ไม่ว่าทั้งหมด หรือบางส่วน</p><p><span style="white-space:pre">			</span>9.3 บริษัทฯ มีสิทธิ์ในการยกเลิกโครงการสะสมคะแนนได้โดยจะมีการแจ้งให้ลูกค้าทราบล่วงหน้า</p><p><span style="white-space:pre">		</span>10.บริษัทฯ ขอสงวนสิทธิ์ ระยะเวลาในการจัดหาของรางวัล ภายใน 45 วันทำการ หลังจากวันที่แสดงความประสงค์แลกของรางวัล</p><p><span style="white-space:pre">		</span>11.คะแนนสะสม “สะสมแต้มแอร์มาเวล” ที่แลกแล้วไม่สามารถขอคืนได้ไม่ว่ากรณีใดๆ ทั้งสิ้น</p><p><span style="white-space:pre">		</span>12.หากมีข้อมูลโต้แย้งใดๆ เกิดขึ้น ไม่ว่าจะเป็นโดยทางตรงหรือทางอ้อม ที่เกี่ยวกับรายการสะสมคะแนนของบริษัทฯ ให้ถือเอาการตัดสินของบริษัทฯ เป็นที่สิ้นสุด</p><p><span style="white-space:pre">		</span>ประกาศนี้ ให้มีผลบังคับใช้ตั้งแต่วันที่ 1 มกราคม 2565 เป็นต้นไป จนกว่าบริษัทฯ จะมีการเปลี่ยนแปลงหรือยกเลิกประกาศนโยบายฉบับนี้</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด / Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- QR Code Reader Modal -->
  <div class="modal fade" id="qrReaderMod" tabindex="-1" role="dialog" aria-labelledby="qrReaderModH" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="qrReaderModH"><i class="fas fa-qrcode"></i> อ่านฉลาก QR Code Mavell</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <label for="video-container">เปิดแสดง QR Code มาบนกล้องเพื่ออ่านค่า:</label>
          <div id="video-container" class="default-style">
              <video id="qr-video"></video>
          </div>
          <div>
              <b>เปลี่ยนแหล่งของกล้อง:</b>
              <select id="cam-list">
                  <option value="environment" selected>Environment Facing (default)</option>
                  <option value="user">User Facing</option>
              </select>
          </div>
          <!-- <button id="qrr-start-button">Start</button>
          <button id="qrr-stop-button">Stop</button> -->
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด / Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal OTP -->
  <div id="mod_otp" class="modal fade" role="dialog" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title"><i class="fa fa-phone mr-1"></i>ยืนยันหมายเลขโทรศัพท์ของคุณด้วย OTP</h4>
          <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button> -->
        </div>
          <div class="modal-body">
            <form id="form_otp" onsubmit="return false;">
              <p>ระบบได้ส่งหมายเลขยืนยันไปยังเบอร์ <span id="otpMobile"></span></p>
              <div class="form-group row">
                <label for="technician_name" class="col-sm-5 col-form-label text-right">รหัสอ้างอิง (Ref. Code)</label>
                <div class="col-sm-7 mt-2" id="otpRef">XXXXXX</div>
              </div>
              <div class="form-group row">
                <label for="installer_phone" class="col-sm-5 col-form-label text-right">เลข OTP ที่ได้รับ</label>
                <div class="col-sm-7">
                  กรุณากรอกรหัส OTP ภายใน <span id="otpTimer"></span> วินาที
                  <input type="text" maxlength="6" class="form-control bg-warning" id="otpCode" placeholder="XXXXXX">

                </div>
              </div>
            </form>
          </div>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
  </div>

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
    <div class="p-3">
      <h5>Title</h5>
      <p>Sidebar content</p>
    </div>
  </aside>
  <!-- /.control-sidebar -->

  <!-- Main Footer -->
  <footer class="main-footer">
    <!-- To the right -->
    <div class="float-right d-none d-sm-inline">
      <a href="https://web.facebook.com/mavellairth/" target="_blank"><i class="fab fa-facebook mr-2"></i></a>
      <a href="https://www.youtube.com/channel/UCed0du6LHhR4KM8rtYGuCFg" target="_blank"><i class="fab fa-youtube mr-2"></i></a>
    </div>
    <!-- Default to the left -->
    <strong>&copy; 2021 Mavell Corporation Co.,Ltd.</strong> All rights reserved. | <a href="./login.html">CSMS Login</a>
  </footer>
</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->

<script src="./plugins/jquery/jquery.min.js"></script>
<script src="./plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="./view/js/adminlte.min.js"></script>
<script src="./plugins/moment/moment.min.js"></script>
<script src="./plugins/inputmask/jquery.inputmask.min.js"></script>
<script src="./view/js/global.js?v=4" type="text/javascript"></script>
<script src="./plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<script src="./plugins/jQuery-File-Upload/js/vendor/jquery.ui.widget.js"></script>
<script src="./plugins/jQuery-File-Upload/js/jquery.fileupload.js"></script>
<script src="https://www.google.com/recaptcha/api.js?render=6LcVOJoaAAAAAOZLlQT_bF5EoKZJEXIbqm2-YOxI"></script>
<script src="./plugins/select2/js/select2.full.min.js"></script>
<script src="./plugins/sweetalert2/sweetalert2.all.js"></script>
<script src="./plugins/jquery.mask.min.js?v=1"></script>
<script src="./plugins/JQL.min.js"></script>
<script src="./plugins/typeahead.bundle.js"></script>
<script src="./plugins/jquery-Thailand/jquery.Thailand.min.js"></script>
<script src="./plugins/qr-scanner.umd.min.js"></script>
<script src="./view/js/index.js?v=22"></script>

<script>
  function toggleServiceForm() {
    const selected = document.querySelector('input[name="userType"]:checked').value;
    document.getElementById('serviceFormCustomer').style.display = selected === 'customer' ? 'block' : 'none';
    document.getElementById('serviceFormTechnician').style.display = selected === 'technician' ? 'block' : 'none';
  }
</script>


<script>
  const urlParams = new URLSearchParams(window.location.search);
  const qsn = urlParams.get('sn');

  if (qsn) {
    const snQueryField = document.getElementById('snQuery');
    if (snQueryField) {
      snQueryField.value = qsn;
    }

    const indoorField = document.querySelector('input[name="indoor_sn"]');
    if (indoorField) {
      indoorField.value = qsn;
    } 

  }
</script>


<script>
$($ => {

});

</script>
</body>

</html>
