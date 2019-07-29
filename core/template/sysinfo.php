<?php
$yonakaPicList = [
	'success'=>'//0d077ef9e74d8.cdn.sohucs.com/qXFevxS_png',
	'error'=>'//0d077ef9e74d8.cdn.sohucs.com/qXFevxZ_png',
	'info'=>'//0d077ef9e74d8.cdn.sohucs.com/qXFevy2_png'
];
$colorList = [
	'success'=>'#59a734',
	'error'=>'#ffb1b1',
	'info'=>'#2488ff'
];
$yonakaPic = $yonakaPicList[$type];
$color = $colorList[$type];
?>
<html lang=cn>
	<div id="huaQ">
		<!--
		不要问我上面这个“huaQ”怎么这么怪。
		事实上，我也不想让她这么怪。
		但是 set_error_handler 扑捉到的错误信息，并不能屏蔽之前输出的东西。
		为了让页面不那么猎奇，我只好在这里“huaQ”一下了 _(:з」∠)_
		-->
	<head>
		<title><?=$title; ?></title>
		<META http-equiv="content-type" content="text/html; charset=UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0">
		<style>
		body {
			background:url("//0d077ef9e74d8.cdn.sohucs.com/qXFfIC5_png");
			color: #666;
			font-family: "Microsoft YaHei","微软雅黑";
			font-size: 20px;
			animation: backgroundGun 30s;
			-moz-animation: backgroundGun 30s;
			-webkit-animation: backgroundGun 30s;
			-o-animation: backgroundGun 30s;
			animation-iteration-count: infinite;
			animation-timing-function: linear;
			animation-direction: alternate;
			padding-bottom: 100px;
		}
		@keyframes backgroundGun{
			0% {
				background-position-x: 0%;
			}
			100% {
				background-position-x: 300%;
			}
		}
		p{
			margin: 0px;
		}
		.Text{
			padding-top: 25px;
		}
		.Info {
			position: relative;
			margin: 0 auto;
			min-height:480px;
			width: 800px;
			box-shadow:0px 3px 20px -3px rgba(0, 64, 128, 0.2);
			background:#fff;
			top: 50px;
			border-radius: 20px;
		}
		.Powered,.ErrorInfoBox {
			min-height: 30px;
			margin-top: 15px;
			text-align: center;
		}
		.Img {
			float: left;
			width: 300px;
			height: 480;
			border-radius: 20px;
			overflow: hidden;
		}
		.Code {
			top:0;
			text-align: center;
		}
		.Code p:first-child {
			position: relative;
			top: 20px;
			font-family: cursive;
			font-size: 150px;
			font-weight: bold;
			line-height: 100px;
			letter-spacing: 5px;
			color: #fff;
			margin-bottom: 30px;
			padding-top: 20px;
		}
		.Code p:first-child span {
			font-family: "Arial Rounded MT Bold", "Helvetica Rounded", Arial, sans-serif;
			cursor: pointer;
			text-shadow: 0px 0px 20px #929292, 0px -2px 1px #fff;
			-webkit-transition: all .1s linear;
			transition: all .1s linear;
		}
		.Code p:not(:first-child) {
			font-size: 30px;
			line-height: 2em;
			margin-bottom: 0px;
			color: <?=$color?>;
		}
		.list{

		}
		a{
			text-decoration:none;
			color:#75b1fb;
		}
		ul{
			margin-top: 0px;
			margin-left: 300px;
			padding: 0px;
			width: 450px;
		}
		li,button{
			box-shadow: 0px 0px 20px -1px rgba(0, 0, 0, 0.2);
			margin-top: 7px;
			padding: 5px;
			font-size: 15px;
			border-radius: 10px;
			list-style: none;
		}
		button{
			box-shadow: 0px 0px 20px -1px rgba(0, 0, 0, 0.2);
			border: none;
			color: #fff;
			width:49%;
			cursor: pointer;
		}
		button.danger{
			float:right;
			background-color: #d9534f;
		}
		button.success{
			float:left;
			background-color: #5cb85c;
		}
		button.primary{
			float:center;
			width:100%;
			background-color: #5bc0de;
		}
		
		button.center{
			float:center;
			width:100%;
		}
		button.left{
			float:left;
			width:49%;
		}
		button.right{
			float:right;
			width:49%;
		}
		
		button.danger:hover{
			background-color: #c9302c;
		}
		button.success:hover{
			background-color: #449d44;
		}
		button.primary:hover{
			background-color: #31b0d5;
		}
		
		li:before {
			content: "● ";
			color: #000000;
			position: relative;
			top: -1px;
		}
		#nowJump{
			color: #ffa2ef;
		}
		#noJump{
			color: #9da0ff;
		}
		#nowJump,#noJump{
			cursor: pointer;
		}
		.ErrorText tr td:first-child{
			width:90px;
		}
		table {
			width: 100%;
			left: 0px;
			border-spacing: 0;
			border-collapse: collapse;
		}
		.table-responsive {
			min-height: .01%;
			overflow-x: auto;
		}
		.ErrorInfo{
			padding: 20px;
		}
		tr,td{
			word-break:keep-all;
			white-space:nowrap;
		}
		@media screen and (max-width:800px) {
			.ErrorInfo{
				padding: 0;
			}
			body {
				background:#fff;
				animation:none;
			}
			.Info {
				width: 100%;
				position: relative;
				margin: initial;
				height: initial;
				box-shadow: initial;
				background: initial;
				top: initial;
				border-radius: initial;
				z-index: 1000;
			}
			.Powered {
				top: 20px;
				font-size: 13px;
				position: relative;
			}
			.Img {
				position: fixed;
				bottom: 0px;
				left: 0px;
				z-index: 0;
				opacity: 0.3;
				
				float: initial;
				width: initial;
				height: initial;
				border-radius: initial;
				overflow: initial;
			}
			.list{
				z-index: 100;
				position: relative;
			}
			ul{
				margin-top: 0px;
				padding: 0px;
				margin-left: initial;
				width: 100%;
			}
			li,button{
				margin-top: 7px;
				box-shadow: 0px 0px 20px -1px rgba(0, 0, 0, 0.2);
				padding: 5px;
				font-size: 15px;
				border-radius: 10px;
				list-style: none;
				background: rgba(255, 255, 255, 0.8);
			}
			.table-responsive {
				width: 100%;
				margin-bottom: 15px;
				overflow-y: hidden;
				-ms-overflow-style: -ms-autohiding-scrollbar;
			}
			body {
				padding-bottom: 0px;
			}
			.Text{
				padding-top: 0px;
			}
		}
		</style>
	</head>
	<body>
		<div class="Info">
			<div class="Img">
				<img src="<?=$yonakaPic; ?>">
			</div>
			<div class="Code">
				<p><span><?=$code; ?></span></p>
				<p><?=$info; ?></p>
			</div>
			<div class="Text">
				<p><?=$moreTitle; ?></p>
				<div class="list">
					<ul>
						<?php foreach($more as $row){ ?>
						<li><?=$row; ?></li>
						<?php } ?>
						<?php if($buttons) foreach($buttons as $index => $button){ ?>
						<a href="<?=$button['href'];?>" target="<?=$button['target'];?>"><button class="<?=$button['type'];?>"><?=$button['title'];?></button></a>
						<?php } ?>
					</ul>
				</div>
			</div>
		</div>
		<?php if($autoJump){ ?>
		<div class="Info Powered Jump">
			<span id="sec">2333</span>秒后将为您自动跳转... <span id="nowJump">[现在跳转]</span> <span id="noJump">[雅蠛蝶！等等]</span>
		</div>
		<script src="//lib.baomitu.com/jquery/3.3.1/jquery.min.js"></script>
		<script>
		$(function(){
			url = '<?=$autoJump['url']?>';
			sec = '<?=$autoJump['sec']?>';
			Jump = function(){
				if(sec <= 0) window.location.href = url;
				$('#sec').html(sec);
				sec--;
			}
			JumpInterval = setInterval(Jump,1000);
			
			$('#noJump').click(function(){
				sec = 'TheWorld!!!';
				clearInterval(JumpInterval);
				Jump();
				$('#noJump').hide();
			});
			
			$('#nowJump').click(function(){
				$('.Jump').html('走起( • ̀ω•́ )✧ ------ >>>>>>>>>>>>>>>>>>>>>>');
				sec = 0;
				Jump();
			});
			
			Jump();
		});
		</script>
		<?php } ?>
		<div class="Info Powered">
			Powered By <a target="_blank" href="http://php.xlch8.cn/">AdminPHP</a>. &copy; <a target="_blank" href="http://flandre-studio.cn">Flandre-Studio.cn</a>
		</div>
		<script>
		document.body.innerHTML = document.getElementById('huaQ').innerHTML;
		document.head.innerHTML = '';
		</script>
	</body>
	</div>
</html>