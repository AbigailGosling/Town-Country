<?php
//This PHP Script is responsible for generating a PDF Statement and sends it to the invoice address at Town&Country!
//-----IMPORTS-----//
require_once('../functions.php');
include_once('../includes/frontHeader.php');
require_once '../vendor/autoload.php';
//SOCKETLABS IMPORTS//
use Socketlabs\SocketLabsClient;
use Socketlabs\Message\BasicMessage;
use Socketlabs\Message\EmailAddress;
//SOCKETLABS CONFIG//
$SocketID = 1;
$APIKey = "";
//---PHP CONFIG---//
ini_set('memory_limit', '1024M');
//---POST DATA---//
$htmlData = json_decode($_POST["web"]);
$customerID = $_POST["id"];

//Define the CSS Info we want to render in the PDF
$css = '
@page {
    margin-top: 5mm;
    margin-bottom: 5mm;
}
*{
    color-adjust: exact;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
} 
.flex{ display:flex; } 
.space-between{ justify-content: space-between; }
.space-evenly { justify-content: space-evenly; }
.v-center{ align-items:center; }
html {height: 100%;margin: 0;padding: 0;position: relative;}
html.int {height: 100%;}
body.menu {background:  #f44336;}
main {
	max-width: 1024px;
	margin: 0 auto;
	padding: 65px 0 60px 0;
	display: block;
	clear: both;
	box-sizing: border-box;
}
main.int {padding: 50px 30px 50px 30px;}
main.int--extra-padding {
	padding-top: 100px;
	position: relative;
}
main h1 {
	margin: 0;
	padding: 0;
	display: block;
	clear: both;
	color: #f44336;
	font-size: 40px;
	line-height: 40px;
	font-weight: normal;
	text-align: center;
	text-transform: uppercase; 
}
main h1.int {color: #fff;}
main div#login {
	width: 350px;
	margin: 0 auto;
	padding: 125px 0 0 0;
	display: block;
	clear: both;
}
div#login h2 {
	margin: 0;
	padding: 0;
	display: block;
	clear: both;
	color: #4a4a4a;
	font-size: 30px;
	line-height: 30px;
	font-weight: normal;
}
div#login form {
	margin: 0;
	padding: 25px 0 0 0;
	display: block;
	clear: both;
}
div#login input[type="text"], div#login input[type="password"] {
	width: 100%;
	margin: 0;
	padding: 0 5px 0 5px;
	display: block;
	clear: both;
	color: #a5a1a1;
	font-size: 16px;
	line-height: 32px;
	border: 0;
	border-bottom: 1px #797979 solid;
	-webkit-appearance: none;
	box-sizing: border-box;
}
div#login input[type="submit"] {
	background: #f44336 !important;
	width: 120px;
	height: 36px;
	margin: 0;
	padding: 0;
	display: block;
	clear: both;
	color: #fff;
	font-size: 14px;
	line-height: 36px;
	text-align: center;
	cursor: pointer;
	border: 0 !important;
	border-radius: 0 !important;
	-webkit-appearance: none;
}

span.small {
    font-size: 12px;
}

div#menu_wrap a:hover {background: #f44336;color: #fff;border: 1px #fff solid;}


/* Product */
div#product {
	margin: 0;
	padding: 0 15px 0 15px;
	display: block;
	clear: both;
	position: relative;
}
div#product div#product_heading {
	margin: 0;
	padding: 0;
	display: block;
	clear: both;
	color: #4a4a4a;
	font-size: 40px;
	line-height: 40px;
	font-weight: normal;
}
placeholder {color: #4a4a4a !important;}

div#product_list {
	margin: 0 15px 0 15px;
	padding: 10px 0 5px 0;
	display: block;
	clear: both;
	/* overflow-x: scroll; */
	box-sizing: border-box;
}
div#product_list table {
	table-layout: fixed;
}

 /* width */
div#product_list::-webkit-scrollbar {
    width: 10px;
}
/* Track */
div#product_list::-webkit-scrollbar-track {
    background: #4a4a4a;
}
/* Handle */
div#product_list::-webkit-scrollbar-thumb {
    background: #f44336;
}
/* Handle on hover */
div#product_list::-webkit-scrollbar-thumb:hover {
    background: #f44336;
}

.overview {
	margin: 0;
	padding: 0;
	display: flex;
	flex-wrap: wrap;
	justify-content: space-between;
	clear: both;
	box-sizing: border-box;
}
.overview_block {
	width: 25% !important;
	min-height: 70px;
	display: inline-block;
	padding: 10px 10px 20px 10px;
	margin: 0;
	text-align:center;
	border:1px solid grey;
	box-sizing: border-box;
}
.overview_block label {
	margin: 0 0 20px 0;
	padding: 0 0 4px 0;
	display: block;
	clear: both;
	font-size:10px;
	border-bottom:1px solid grey;
}

div#product_list th {
	width: 105px;
	margin: 0;
	padding: 10px 0 10px 0;
	text-align: center;
	color: #4a4a4a;
	font-size: 11px;
	line-height: 14px;
	text-transform: uppercase;
}
div#product_list td {
	width: 105px;
	margin: 0;
	padding: 10px 0 15px 0;
	text-align: center;
	color: #4a4a4a;
	font-size: 14px;
	line-height: 14px;
}
div#product_list th.even, div#product_list td.even {background: #ebf7fc;}

a.add_product {
	width: 192px;
	height: 39px;
	margin: 35px 0 0 15px;
	padding: 0 0 0 50px;
	display: block;
	float: left;
	color: #fff;
	font-size: 14px;
	line-height: 39px;
	text-decoration: none;
	box-sizing: border-box;
}
a.print_intake {
	width: 192px;
	height: 39px;
	margin: 35px 15px 0 0;
	padding: 0 0 0 50px;
	display: block;
	float: right;
	color: #fff;
	font-size: 14px;
	line-height: 39px;
	text-decoration: none;
	box-sizing: border-box;
}
.clearfix {clear: both;}

#box, #box2, #editBox {
	background: #f44336;
	width: 100%;
	min-height: 100%;
    height: auto;
	margin: 0;
	padding: 40px 0 40px 0;
	display: none;
	position: absolute;
	top: 70px;
	left: 0;
	z-index: 998;
}
#box h1, #box2 h1, #editBox h1 {
	margin: 0;
	padding: 0 0 50px 0;
	display: block;
	clear: both;
	color: #fff;
	font-size: 40px;
	line-height: 40px;
	font-weight: normal;
	text-align: center;
	text-transform: uppercase;
}
#box form, #box2 form, #editBox form {width: 100%;margin: 0 auto;display: block;box-sizing: border-box;}
#box div.float, #box2 div.float, #editBox div.float {width: 50%;padding: 0 20px 0 20px;display: block;float: left;overflow: hidden;box-sizing: border-box;}

#box form label, #box2 form label, #editBox form label{
	margin: 0;
	padding: 10px 0 5px 0;
	display: block;
	clear: both;
	color: #fff;
	font-size: 14px;
	line-height: 14px;
	font-weight: normal;
	text-align: left;
	text-transform: uppercase;
}
#box form input[type="text"], #box form input[type="number"], #box form input[type="email"], #box form input[type="password"], #editBox form input[type="text"], #editBox form input[type="number"], #editBox form input[type="email"], #editBox form input[type="password"], #box2 form input[type="text"],#box2 form input[type="number"], #box2 form input[type="email"], #box2 form input[type="password"] {
	background: #f9f9f9;
    width: 100%;
    margin: 0;
    padding: 0 5px 0 5px;
    display: block;
    clear: both;
    color: #4a4a4a;
    font-size: 16px;
    line-height: 27px;
    border: 1px #bfbfbf solid;
    box-sizing: border-box;
    height: 55px;
    font-size: 26px;
}
#box form select, #box2 form select, #editBox form select {
	background: #f9f9f9;
	width: 100%;
    margin: 0;
    padding: 0 5px 0 5px;
    display: block;
    clear: both;
    color: #4a4a4a;
    font-size: 16px;
    line-height: 27px;
    border: 1px #bfbfbf solid;
	box-sizing: border-box;
	height: 55px;
    font-size: 26px;
}
#box form textarea, #box2 form textarea, #editBox form textarea {
	background: #f9f9f9;
	width: 100%;
	height: 55px;
    margin: 0;
    padding: 5px 5px 5px 5px;
    display: block;
    clear: both;
    color: #4a4a4a;
    font-size: 16px;
    line-height: 20px;
    border: 1px #bfbfbf solid;
	box-sizing: border-box;
}
#box form input[type="submit"], #box form input[type="button"], #box2 form input[type="submit"], #box2 form input[type="button"], #editBox form input[type="submit"], #editBox form input[type="button"] {
    background: #fff;
	width: 50%;
	margin: 0 auto;
	margin-top: 50px;
	padding: 0;
	display: block;
	clear: both;
	color: #f44336;
	font-size: 20px;
	line-height: 50px;
	text-align: center;
	text-decoration: none;
	text-transform: uppercase;
	transition: all .15s ease-in-out;
	border: 1px #f44336 solid;
	cursor: pointer;
	-webkit-appearance: none;
}
#box form input[type="submit"]:hover, #box2 form input[type="submit"]:hover, #editBox form input[type="submit"]:hover {background: #f44336;color: #fff;border: 1px #fff solid;}
#intakelist {
	width: 100%;
	max-width: 100%;
	margin: 0 auto;
	padding: 0 0 40px 0;
	display: block;
    z-index: 999;
}
#intakelist form {width: 75%;margin: 0 auto;display: block;box-sizing: border-box;}
#intakelist form table {margin-bottom: 50px;}
#intakelist h1 {padding-bottom: 30px;}

#intakelist form label {
	margin: 0;
	padding: 0 0 5px 0;
	display: block;
	clear: both;
	color: #fff;
	font-size: 14px;
	line-height: 14px;
	font-weight: normal;
	text-align: left;
	text-transform: uppercase;
}
#intakelist form input[type="text"], #box form input[type="email"], #box form input[type="password"] {
    background: #f9f9f9;
	width: 100%;
    margin: 0 0 15px 0;
    padding: 0 5px 0 5px;
    display: block;
    clear: both;
    color: #4a4a4a;
    font-size: 16px;
    line-height: 27px;
    border: 1px #bfbfbf solid;
	box-sizing: border-box;
}
#intakelist form select {
	background: #f9f9f9;
	width: 100%;
    margin: 0 0 15px 0;
    padding: 0 5px 0 5px;
    display: block;
    clear: both;
    color: #4a4a4a;
    font-size: 16px;
    line-height: 27px;
    border: 1px #bfbfbf solid;
	box-sizing: border-box;
}
#intakelist form textarea {
	background: #f9f9f9;
	width: 100%;
	height: 85px;
    margin: 0;
    padding: 5px 5px 5px 5px;
    display: block;
    clear: both;
    color: #4a4a4a;
    font-size: 16px;
    line-height: 20px;
    border: 1px #bfbfbf solid;
	box-sizing: border-box;
}
#intakelist form input[type="submit"] {
	background: #fff;
	width: 50%;
	margin: 0 auto;
	margin-top: 20px;
	padding: 0;
	display: block;
	clear: both;
	color: #f44336;
	font-size: 20px;
	line-height: 50px;
	text-align: center;
	text-decoration: none;
	text-transform: uppercase;
	transition: all .15s ease-in-out;
	border: 1px #f44336 solid;
	cursor: pointer;
	-webkit-appearance: none;
}
#intakelist form input[type="submit"]:hover {background: #f44336;color: #fff;border: 1px #fff solid;}
.intake {
	background: #fff;
	margin: 0 auto;
	margin-top: 7px;
	padding: 0 10px 0 10px;
	display: block;
	clear: both;
	color: #f44336;
	font-size: 14px;
	line-height: 40px;
	text-align: left;
	text-decoration: none;
	text-transform: capitalize;
	transition: all .15s ease-in-out;
	border: 1px #f44336 solid;
	cursor: pointer;
	position: relative;
}
.intake:hover {background: #f44336;color: #fff;border: 1px #fff solid;}
td.pos {position: relative;}
a#delete_intake {
	margin: 0;
	padding: 0;
	display: block;
	position: absolute;
	top: 18px;
	right: 15px;
	color:red !important;
}

.clearfix {
	clear: both;
}

.product{
	padding-bottom:10px;
	/* border:1px dashed grey; */
}

.product .title{
	padding-top:10px;
	/* padding-bottom:10px; */
	width:100%;
	display: inline-block;
	
	position:relative;
}

.buttonsContainer{
	/* display:block; */
	/* float:right; */
	
	display: block;
    float: right;
    position: absolute;
    right: 0px;
    top: 0px;
	
}

.buttonsContainer a i.fa{
	cursor:pointer !important;
	padding:4px;
	color:grey;
}

#MultiWeightDiv{
	/* background:green; */
	margin:  0;
	padding: 0 50px 0 20px;
	height: auto;
	clear: both;
	display: flex;
	flex-wrap: wrap;
	justify-content: space-between;
	box-sizing: border-box;
}

#MultiWeightDiv div{
	width: 17%;
    padding: 0;
    padding-top: 0px;
    padding-bottom: 0px;
	box-sizing: border-box;
}

#cut_search_results, #brand_search_results{
	position: absolute;
	/* background: grey; */
    width: 533px;
    /* padding: 10px; */
	display:none;
	z-index:99999;
}

.intakeCutDropdown{
	background: #fff;
    width: 100%;
    margin: 0 auto;
    padding-left: 10px;
    /* margin-top: 7px; */
    /* padding: 0 8px 0 10px; */
	padding: 10px 8px 8px 8px;
    display: block;
    clear: both;
    color: #f44336;
    font-size: 14px;
    line-height: 40px;
    text-align: left;
    text-decoration: none;
    text-transform: uppercase;
    transition: all .15s ease-in-out;
    border: 1px #f44336 solid;
    cursor: pointer;
    position: relative;
	line-height: 22px;
}

.gross{
	display:none;
}

a i.fa-times{
	font-size:22px !important;
}

a i.fa-trash{
	font-size:22px !important;
	padding-right:10px;
}
a i.fa-pencil{
	font-size:22px !important;
	padding-right:10px;
}


.picksheetType{
	background:#f44336;
	padding:8px;
	margin-top:10px;
	color:#fff;
	cursor:pointer;
	position:relative;
}

.picksheetPallets{
	padding-left:20px;
	padding-top:20px;
	padding-bottom:20px;
}


/* #address{
    padding: 30px;
    display: flex;
    width: 100%;
    justify-content: space-around;
} */

a#delete_intake {
    margin: 0;
    padding: 0;
    display: block;
    position: absolute;
    top: 8px;
    right: -35px;
    color: red !important;
    background: #fff;
    height: 34px;
    padding-top: 12px;
    width: 30px;
}


#best_by, #best_by2, #best_by3{
	display:inline-block !important;
	width:calc(100% - 98px) !important;
	margin-right:0px !important;
}

#bestbyBtn{
    display: inline-block;
    width: 98px;
    height: 55px;
    float: right;
    text-align: center;
    line-height: 55px;
	font-weight:700;
	color:#FFF;
	cursor:pointer;
    background: #3488b0;
}

.loadPalletBtn{
	background:#f44336;
    display: inline-block;
    margin: 0 auto;
    padding: 10px;
	width:200px;
	margin-bottom:10px;
	color:#fff;
	text-align:center;
	font-weight:700;
	cursor:pointer;
}

.palletnotepopup{
	position:fixed;
	width:300px;
	top:220px;
	left:calc(50% - 150px);
	padding:35px;
	background:#f44336;
	text-align:center;
	color:#fff;
	font-weight:700;
	font-size:20px;
	z-index:9999;
	display:none;
	border: 2px solid #fff;
}
.palletnotepopup .close{
	color:#fff;
	font-weight:700;
	font-size:24px;
	position:absolute;
    top: 6px;
    right: 10px;
}

#month, #year{
	width:65px;
	height: 35px;
}

.datesearchcontainer{
	display:inline-block;
    float:right;
    position:relative;
}

.datesearchcontainer label{
	padding-left:10px;
	display:inline-block;
	color:#fff;
	font-weight:700;
}
.commentText{
	position:relative;
}

.theSpan{
	display:none;
	min-width:190px;
	min-height:120px;
	position:absolute;
	top:0px;
	left:0px;
	background:#f44336;
	z-index:1000;
	padding:10px;
}

.theSpan textarea{
	min-width:calc(100% - 10px);
	min-height:calc(120px - 10px);
	/* resize:none; */
}

.commentText:hover span, .commentText:focus span{
	display:block;
}

.resetBtn{
	background: #cacaca;
    padding-left: 10px;
    color: #fff;
    text-decoration: none;
    padding-right: 10px;
    padding-top: 8px;
    font-weight: 700;
    padding-bottom: 8px;
}

#bestbyBtn, #ubbbBtn{
    display: inline-block;
    width: 98px;
    height: 55px;
    float: right;
    text-align: center;
    line-height: 55px;
	font-weight:700;
	color:#FFF;
	cursor:pointer;
    background: #3488b0;
}

#ubbbBtn{
    top: -55px;
    position: relative;
}

.productsList div{
	display: flex;
}

.productsList div span{
	cursor:pointer;
	padding-left:10px;
}

.inputProductsText{
	width:408px;
}

.inputProductsText2{
    width: 280px;
    margin-right: 10px !important;
}

.producttext{
	margin: 0;
	margin-top:15px;
	background: #f9f9f9;
    padding: 0 5px 0 5px;
    display: inline-block;
    clear: both;
    color: #4a4a4a;
    font-size: 16px;
    line-height: 27px;
    border: 1px #bfbfbf solid;
    width:140px;
}

select.producttext{
	height:29px;
}

.bluebtn{
	background: #f44336 !important;
    border: 0px !important;
    padding: 15px !important;
    color: #fff !important;
}

.viewpurchase{
	background: #f44336 !important;
    border: 0px !important;
    position: absolute;
    padding: 10px !important;
    color: #fff !important;
    top: 20px;
    right: 30px;
}

.calendar_blank_box, .calendar_head_box, .calendar_box{
	height:100px;
	width:130px;
	text-align:center !important;
	color:#333;
}

.calendar_head_box { background:#f44336; height:40px; color:#fff; border:1px solid #f44336; }
.calendar_blank_box { background:#ddd; border:1px solid #a1a1a1; }
.calendar_box { background:#ddd; border:1px solid #a1a1a1;}


.calendar_box.time{
	width:30px;
}

.slim{
	height:50px !important;
}

.calendar_box.event_here span{
	background: #f44336;
    padding: 15px;
    border-radius: 100%;
    color: #fff;
	cursor:pointer;
	
	width: 20px;
    height: 20px;
    display: inline-block;
    line-height: 22px;
}

.ui-dialog-titlebar{
	padding-top: 15px;
    padding-bottom: 15px;
}

.calprev, .calnext{
	padding:10px;
	cursor:pointer;
	text-decoration:none;
	font-weight:700;
	color:#f44336;
	font-size:22px;
}

.caltop{
	display:flex;
	justify-content:space-between;
	width:932px;
    align-items: baseline;
	position:relative;
}

.listedevent{
	display:none;
}

.eventList{
	padding-top:40px;
}

.calMonth{
	width:120px;
	height:30px;
	outline:none;
	margin-bottom: 15px;
}

.calendar_box a{
	font-size:12px;
	font-weight:700;
	color:#333;
	text-decoration:none;
}

.deliveryBox{
	width:400px;
	background:#fff;
	padding:45px;
}

.deliveryBox a.btn{
	margin-right: 10px;
    background: #f44336;
    padding: 10px;
    font-size: 14px;
    color: #fff;
    text-decoration: none;
}

#datetimepicker, #datetimepicker2{
	position:relative;
}

span.add-on{
	position: absolute !important;
    right: 80px !important;
    top: 36px !important;
}

#datetimepicker input, #datetimepicker2 input{
	max-width: 160px;
    width: 160px;
    height: 28px;
}

#networkError{
	width:300px;
	position:fixed;
	left:50%;
	margin-left:-150px;
	top:200px;
	text-align:center;
	padding:20px;
	background:#fff;
	border:3px solid #41abdd;
	border-radius:4px;
	z-index:999;
	display:none;
}

.intakeNotes{
	min-width:425px;
	min-height:80px;
	
	width:425px;
	height:80px;
	
	max-width:425px;
	max-height:120px;
}


.formBackButton a{
	font-size:14px;
}
.formBackButton{
	position:absolute;
	top:80px;
}
.formBackButton--invoice {
	position:absolute;
	top:50px;
}


.picksheetPalletDetail{
	/* display:none; */
    padding-left:15px;
    display:flex;
	justify-content:flex-start;
	flex-wrap:wrap;
}

.picksheetPalletDetail .row{
	display:flex;
	justify-content:flex-start;
	flex-wrap:wrap;
	position:relative;
}

.rowSelector{
	position: absolute;
    left: -70px;
    top: 14px;
	cursor:pointer;
}

.weightbox{
	width: calc(10% - 10px);
	line-height: 30px;
	border:2px solid #cacaca;
	box-sizing:border-box;
	cursor:pointer;
	margin:5px; 
	text-align:center;
	height: 30px;
}
	
.activeWeight { background:#f44336 !important; color:#fff !important}
.weightbox:hover{ background:#cacaca; }

.backbtn{
    position: absolute;
    top: 87px;
    left: 46px;
    font-weight: 700;
    color: #333;
    font-size: 20px;
    background: none;
    border: 0px;
    outline: none;
		cursor:pointer;
		z-index: 1;
}

.picksheet_controls{
	/* position: fixed; */
    /* top: 268px; */
    /* right: 120px; */
}

.topFlexbox{
	display: flex;
    width: 70%;
    margin: 0 auto;
    flex-wrap: wrap;
    column-count: 3;
	display:none;
}

.topBox{
	/* border: 2px solid red; */
    width: 1200px;
    margin-left: 50px;
    display: flex;
}



.overviewcomment{
	width: 80%;
	height: 47px;
	resize: none;
	margin: 5px;
}

.searchContainer .searchGo{
	position: absolute;
	right: 4px;
	top: -9px;
	background: #f44336;
	height: 36px;
	width: 37px;
	text-align: center;
	line-height: 36px;
	color: white;
	cursor:pointer;
	font-size:9px;
}

.activeRedRow{
	background:#666 !important;
}

.activeRedRow td,
.activeRedRow a b{
	color:white !important;
}

.textareadisabled_holder{
	position: absolute;
    right: 0px;
    border: 1px solid;
    top: 0px;
}

.textareadisabled_holder div{
	padding: 8px;
	padding-bottom: 0px;
	font-weight: 700;
    font-size: 13px;
}

.textareadisabled{
    resize: none;
    background: white;
    padding: 10px;
    line-height: 22px;
    color: black;
	border:0px;
	padding-bottom: 0px;
}

#flexContainerTwo{
	display:flex;
}


#customerContainer{
	display:flex;
	justify-content:space-between;
}

#customerContainer .box{
	width:100%;
	border:1px solid black;
	margin:5px;
	padding:5px;
	box-sizing:border-box;
}

.fullbox{
	width:49%;
	border:1px solid black;
	margin:5px;
	padding:5px;
	box-sizing:border-box;
}

#customerContainer .box h3{
	margin:0;
}

.input{
	margin-bottom: 5px;
    border: 0px;
    padding: 5px;
    border-radius: 4px;
	outline:none;
}

.input.postcode{ text-transform:uppercase; }

#customerContainer select{
	width:180px;
	height:30px;
	border-radius:4px;
}

.status.active{
	text-align: right;
    padding: 5px;
    border-radius: 4px;
	background:green;
	text-align:center;
	width:43%;
}

.status.closetolimit{
	text-align: right;
    padding: 5px;
    border-radius: 4px;
	background:#f1c40f;
	
	margin-top:5px;
	margin-bottom:5px;
	text-align:center;
	width:43%;
}

.status.stop{
	text-align: right;
    padding: 5px;
    border-radius: 4px;
	background:#e74c3c;
	text-align:center;
	width:43%;
}

.fullbox.controls a.override{
	display: inline-block;
    padding: 10px;
    color: black;
    background: #e74c3c;
    text-decoration: none;
    border-radius: 4px;
    cursor: pointer;
}

.fullbox.controls a.update{
	display: inline-block;
    padding: 10px;
    color: black;
    background: #e74c3c;
    text-decoration: none;
    border-radius: 4px;
    cursor: pointer;
}

.fullbox.controls .update{
	display: inline-block;
    padding: 10px;
    color: black;
    background: #e74c3c;
    text-decoration: none;
    border-radius: 4px;
    cursor: pointer;
	border:0px;
}


.fullbox.controls a.override{
	display: inline-block;
    padding: 10px;
    color: black;
    background: #228fbf;
    text-decoration: none;
    border-radius: 4px;
    cursor: pointer;
}

#changeAddress{
	background: white;
    padding: 20px;
	width: 520px;
}

#changeAddress .row{
    border: 1px solid white;
    padding: 10px;
    width: 410px;
    margin: 12px;
    text-align: center;
    background: #f44336;
    color: #fff;
	cursor:pointer;
}

#changeAddress h2{
	font-size: 15px;
    padding-left: 16px;

}

.picksheetType .numRequired{
    background: grey;
    display: block;
    width: 57px;
    height: 70px;
    line-height: 70px;
    text-align: center;
}

.rowEndContainer{
	position: absolute;
    top: 0px;
    right: 0px;
	display:flex;
	justify-content:space-between;
}

.rowEndContainer .weightcomment{
	line-height: 70px;
    padding-left: 20px;
    padding-right: 20px;

}
.weightnote{
	border: 1px solid grey;
    height: 25px;
    padding-left: 20px;
    box-sizing: border-box;
    width: 80px;
}

.returnStockBtn{
    width: 160px;
    height: 40px;
    background: #f44336;
    line-height: 40px;
    text-align: center;
    margin: 20px;
    color: #fff;
    cursor: pointer;
	display:none;
	outline:none;
	border:0px;
}

.printedLabel{
	display: inline-block;
    font-size: 12px;
    background: #cacaca;
    color: #fff;
    padding-left: 20px;
    padding-right: 20px;
    margin-left: 30px;

}

.binContainer{
	text-align: center;
    margin-left: 390px;
    margin-top: 52px;
    position: absolute;
    background: red;
    padding: 8px;
    display: flex;
	cursor:pointer;
}

.binContainer input{
    width: 28px;
    height: 28px;
}

.binContainer label{
	display: inline-block;
    font-size: 22px !important;
}

.weightEditWhiteBox {
    background: white;
    text-align: center;
    margin-bottom: 10px;
    margin-top: 20px;
    line-height: 42px;
    height: 42px;
	cursor:pointer;
}

.weightEditWhiteBox.sel{
	background:#cacaca;
}

.btnContainer{ display:flex; }


.completepickwarning{
	background: #9e1d1d;
    padding: 10px;
    text-align: center;
    color: white;
    margin-bottom: 10px;
    font-weight: bold;
    border-radius: 7px;
}

.override-enabled{
	display:none;
}










#print .bottom{
	display:flex;
	justify-content:space-between;

}

#print .bottom p{
	text-align:center;
	padding:0;
	margin:0;
	font-size:8px;
	padding-bottom:2px;
}
#print .bottom .signbox{
	background:#ffa50017;
	width:200px;
}

#print .bottom .signbox span{
	display:block;
	padding:5px;
}


#print .bankdetails .bankbox{
	background-color:#ffa50017;
	width:100%;
 	margin-bottom:13px;
	
	display:flex;
	justify-content:space-between;
	align-items:center;
	padding:4px;
	box-sizing: border-box;
}

#print .bankdetails .bankbox p{
	padding:0;
	margin:0;
	line-height:20px;
	font-size:8px;
}

#print .bankbox .col1{
	width:40%;
}

#print .bankbox .col2{
	width:20%;
	padding-right:31px;
}

#print .bankbox .col3{
	width:40%;
}

#print .totalPayable, #print .paymentDue{
	text-align:right;
}
#print .paymentDue{
	font-size:12px;
}

#print .payvalue{
	width: 150px;
    display: inline-block;
    text-align: right;	
}

#print .bankcircle{
	text-align: center;
    border: 1px solid black;
    border-radius: 100%;
    padding-top: 7px;
    font-size: 8px;
    box-sizing: border-box;
    width: 63px;
    height: 40px;
}

.productsHeading{
	background-color:#ffa50017;
}

.productsRow{
	height:29px;
}

#print .topheading .logocontainer{
	text-align: center;
    width: 330px;
    font-size: 10px;
    line-height: 13px;
	position: absolute;
    left: calc(50% - 200px);
	top:0px;

}

#print .topheading .logocontainer span{
	color:#8c8c8c;
}

#print .topheading .logo{
	width: 330px;
	padding-bottom: 10px;
}

#print .topheading{
	display: flex;
    justify-content: space-between;
    align-items: flex-end;
	margin-bottom: 5px;
	position:relative;
}

.invoice{
 	width:200px;
}

.invoicebox{
    border: 1px solid #8c8c8c;
	padding: 5px;
	height: auto;
}

.invoicebox p{
	font-size:10px;
	color:#8c8c8c;
}

.invoicebox span{
	font-size:12px;
	text-align:center;
	color: #8c8c8c;
}


#print .delivery{
 	width:216px;
}

#print .deliverybox{
    border: 1px solid #8c8c8c;
	padding: 5px;
	background-color: #9e9e9e6e;
}

#print .deliverybox p{
	font-weight:bold;
	/* font-size:10px; */
	margin-top:0px;
	font-size:12px;
}

#print .deliverybox span{
	font-weight: bold;
    color: #8c8c8c;
    font-size: 22px;
}

#print .deliverydate{
	border: 1px solid #8c8c8c;
	padding: 2px;
	margin-bottom:3px;
	font-weight:700;
	display: flex;
    justify-content: space-between;
	font-size:12px;
	background-color: #9e9e9e6e;
}

#print .deliverydate span{
 	font-size:14px;
}
#print .deliverydate span.date{
	font-size:18px;
}

#print .po{ 
	text-align:right;
	font-size:13px;
	color:#8c8c8c;
}

#print h2{
	text-align: right;
    margin: 0;
    font-weight: 700;
	font-size: 22px;
}

#print .footerlogo{
	display: flex;
    justify-content: space-between;
    width: 145px;
    align-items: flex-end;	
}

#print .footerlogo img.one{
	width:auto;
	height:40px;
}


#print .footerlogo img.two{
	width:auto;
	height:40px;
}




td span.palletid{
    font-size:8px;
    text-align:center;
}

td b.species{
	font-size:10px;
}

td b.cut{
	font-size:10px;
}

td b.brand{
	font-size:8px;
	font-weight:400;
}

td b.quantity{
	font-size:10px;
}

td b.unit{
	font-size:8px;
	font-weight:400;
}

td b.weight{
	font-size:10px;
}

td.price{
	font-size:10px;
	font-weight:400;
}

td span.chilled{
	font-size:8px;
	font-weight:400;
}
.productsHeading th{
	font-size:12px;
}

.weightEditWhiteBox {display: flex;}

.weightEditWhiteBox input{
	/* width: 82% !important; */
    height: 35px !important;
}
.weightEditWhiteBox div.icon {
	width: 18% !important;
	line-height: 42px !important;
	color: black;
}
.weightEditWhiteBox div.icon i {font-size: 20px;}

.menuItem{
    width: 100%;
    background: white;
   
    line-height: 50px;
    height: 50px;
    margin-bottom: 10px;
    color: #f44336;
    font-size: 14px;
    text-transform: capitalize;
    transition: all .15s ease-in-out;
    border: 1px #f44336 solid;
    cursor: pointer;
    position: relative;
    text-align:center;
    font-size:18px;
}

.actions{
    position: absolute;
    right: 20px;
    top: 0px;
}

.menuItem a.icon{
    font-size:22px;
    color: #f44336;

    margin-left:50px;
}


.sendcontainer{
    margin: 0;
    padding: 0;
    display: block;
    position: absolute;
    top: 8px;
    right: -110px;
    color: red !important;
    background: #fff;
    height: 34px;
    padding-top: 12px;
    width: 40px;
    cursor: pointer;
}
.sendcontainer--invoice-list {
     right: -42px;
}

.sendcontainer .active.sel{
    
}

.fa.fa-check{
    font-size: 22px !important;
}

.completedby-tag{
    background: #f44336;
    padding: 8px 20px;
    border-radius: 20px;
    color: #fff;
    display:flex;
    align-items:center;
}

.picksheet_buttons{
    display:flex;
    margin-top: 20px;
}

.picksheet_btn{
    display: block;
    padding: 10px;
    margin-right: 12px;
    color: #f44336;
    font-weight: bold;
}


@page  
{ 
    size: auto;   /* auto is the initial value */ 

    /* this affects the margin in the printer settings */ 
    margin: 10mm 10mm 10mm 10mm;  
} 

.form-control {
  width: 100%;
  margin: 0;
  margin-bottom: 15px;
  padding: 0 5px;
  display: block;
  clear: both;
  appearance: none;
  box-sizing: border-box;
  background: #f9f9f9;
  font-size: 16px;
  line-height: 27px;
  color: #4a4a4a;
  border: 1px #bfbfbf solid;
  border-radius: 5px;
}

textarea {
  height: 110px;
}

.menuItem .text{
	font-size:15px;
	text-align: left;
    padding-left: 100px;
}

.return-highlight{
	color: black;
  font-size: 12px;

}

.pages_container{
	width:100%;
	margin:0 auto;
}

.page_number{
	font-weight:bold;
	color:white;
	text-decoration: none;
}

.page_number.selected{
	text-decoration: underline;
}

.pages_container .pages_heading{
	display: flex;
	align-items:center;
	justify-content: center;
	color:#fff;
	font-weight:bold;
	padding-bottom:10px;
}
.pages_container .pages_heading a{
	color:#fff;
	font-weight:bold;
	font-size:22px;
	text-decoration: none;
	padding:0px 10px;
}
 

.searchAccordTitle.locked{
	pointer-events: none;
	opacity:0.7;
	background: #d1e1d1!important;
}

.searchAccordTitle.locked .intakeLink{
	pointer-events: all !important;
	opacity: 1;
	display:flex;
	align-items: center;
	padding: 0px 20px;
	text-decoration: none !important;
}
.searchAccordTitle.locked .intakeLink b{ text-decoration:underline; }

.searchAccordTitle.locked i.fa-lock{
	font-size:32px;
	padding-right:10px;
}

 
.customer_info{
	display:flex;
	justify-content: space-between;
}

.tooltip-content{
	text-align: left;
	display: none;
	position: absolute;
    z-index: 100;
    top: 0;
    right: 0;
    left: 0;
    background: white;
    padding: 7px;
    width: 80%;
    border: 3px solid black;
    float: none;
    margin: 0 auto;
    color: black;
}

.tooltip-content table{
	width: 100%;
}

.resend-invoice{
	width:100%;
	height:40px;
	line-height:40px;
	text-align:center;
	background:rgb(255, 174, 25);
	margin-bottom:10px;
	cursor:pointer;
	border-radius:6px;
	font-weight:bold;
	color:#fff;
}

.loadMoreBtn{
	width:100%;
	background:#fff;
	color: #f44336;
	text-align:center;
	cursor:pointer;
	font-size:18px;
	height:32px;
	line-height:32px;
	margin-top:20px;
}

.soa_cr_label{
	background: #f44336;
    display: inline-block;
    color: #fff;
    border-radius: 100%;
    font-size: 12px;
    width: 20px;
    height: 20px;
    line-height: 20px;
    padding: 4px;
    margin-left: 4px;
}
.sticky-header{
	position: sticky; 
	background: #e2e2e2;
}
.sticky-footer{
	position: sticky; 
	background: #e2e2e2;
}';

//This function generates a Unique ID using Mersenne Twister RNG
//Going to want to switch RNG algo before pushing to LIVE!!!!
function generate_uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
        mt_rand( 0, 0xffff ),
        mt_rand( 0, 0x0C2f ) | 0x4000,
        mt_rand( 0, 0x3fff ) | 0x8000,
        mt_rand( 0, 0x2Aff ), mt_rand( 0, 0xffD3 ), mt_rand( 0, 0xff4B )
    );
}

//This function renders a PDF document from a string using mPDF
function renderPDF($inArray, $css, $customerID){
	$statementDate = date();
	$filename2 = 'Statement_'.$customerID.'_'.$statementDate.'.pdf';
	$filename = '../PDF/' . $filename2;
	//Set up the socketlabs client
	$client = new SocketLabsClient($SocketID, $APIKey);
	$message = new BasicMessage();
	$message->subject = "Statement of Account from Town and Country Meats";
	$message->htmlBody = "<html>Please find attached an example statement of accounts from Town and Country Meats Group.</html>";
	$message->from = new EmailAddress("tang-socketlabs@townandcountrymeats.co.uk");
	$message->addToAddress("CLIENT EMAIL HERE");
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => [210, 297],
		'setAutoTopMargin' => 'stretch',
        'autoMarginPadding' => 0,
        'bleedMargin' => 0,
        'crossMarkMargin' => 0,
        'cropMarkMargin' => 0,
        'nonPrintMargin' => 0,
        'margBuffer' => 0,
        'collapseBlockMargins' => true,
    ]);
    $mpdf->WriteHTML($css, 1);
	foreach($inArray as $i => $printDiv){
		$mpdf->WriteHTML($printDiv);
		if(++$i === count($inArray)){
			//Dont add another page on the last iteration
		}else{
			$mpdf->AddPage();
		}
	}

    $mpdf->Output($filename,'F');
	$attachment = \Socketlabs\Message\Attachment::createFromPath(__DIR__ . $filename, $filename2, "PDF", "Statement of Account");
	$message->attachments[] = $attachment;
	//Generate a Unique Identifier for this Email
	$message->messageId = generate_uuid();
	//Append a item to the database
	/*

	*/
	$response = $client->send($message);
    return 1;
}

//Main Decleration
renderPDF($htmlData, $css, $customerID);

?>