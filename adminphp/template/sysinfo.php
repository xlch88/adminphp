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
			
			<style><?php include(adminphp . 'template/style.css'); ?></style>
		</head>
		<body class="<?=isset($errorInfo) ? 'exception' : ''; ?>">
			<?php if(!isset($showTips) || $showTips){ ?>
			<div class="info">
				<div class="img">
					<img src="<?=$yonakaPic; ?>">
				</div>
				<div class="code">
					<p><span><?=$code; ?></span></p>
					<p style="color:<?=$color?>;"><?=$info; ?></p>
				</div>
				<div class="text">
					<p><?=$moreTitle; ?></p>
					<div class="list">
						<ul>
							<?php foreach($more as $row){ ?>
							<li><?=$row; ?></li>
							<?php } ?>
							<?php foreach($buttons as $index => $button){ ?>
							<a href="<?=$button['href'];?>" target="<?=isset($button['target']) ? $button['target'] : '_self';?>">
								<button class="<?=$button['type'];?>"><?=$button['title'];?></button>
							</a>
							<?php } ?>
						</ul>
					</div>
				</div>
			</div>
			<?php } ?>
			<?php if(isset($errorInfo)){ view(adminphp . 'template/exception', $errorInfo, 1); } ?>
			<?php if($autoJump){ ?>
			<div class="info powered">
				<span id="sec">2333</span>秒后将为您自动跳转... <span id="nowJump">[现在跳转]</span> <span id="noJump">[雅蠛蝶！等等]</span>
			</div>
			<script>
			url = "<?=$autoJump['url']?>";
			sec = "<?=$autoJump['sec']?>";
			window.onload = function() {
				Jump = function() {
					if (sec <= 0) {
						window.location.href = url
					}
					document.getElementById("sec").innerHTML = sec;
					sec--
				};
				JumpInterval = setInterval(Jump, 1000);
				document.getElementById("noJump").onclick = function() {
					sec = "TheWorld!!! ";
					clearInterval(JumpInterval);
					Jump();
					document.getElementById("noJump").style.display = "none"
				};
				document.getElementById("nowJump").onclick = function() {
					document.getElementById("noJump").style.display = "none";
					sec = 0;
					Jump()
				};
				Jump()
			};
			</script>
			<?php } ?>
			<div class="info powered">
				Powered By <a target="_blank" href="//www.adminphp.net/">AdminPHP<sup><?=adminphp_version_name; ?></sup></a>
			</div>
		</body>
	</div>
	<script>
	document.body.innerHTML = document.getElementById('huaQ').innerHTML;
	document.head.innerHTML = '';
	</script>
</html>