<html>
	<head>
		<meta charset="utf-8" />
		<title>提示</title>
		<meta name="viewport" content="width=device-width, initial-scale=0.9, maximum-scale=0.9" />
		<style>
            body {
                text-align: center;
                background: #ffffff;
            }

            body div {
				padding: 50px;
				margin: 100px;
				border: 6px solid #ff00b1;
				background: #ff7cbf;
				color: #fff;
				border-radius: 10px;
				box-shadow: 0 1px 1px rgba(0, 0, 0, 0.3);
            }

            body hr {
                border: 1px dashed #fff;
            }

            body p {
                color: #2196F3;
            }

            body div p {
                color: white;
            }

			@media (max-width: 768px){
				body div {
					padding: 20px;
					font-size: 10px;
					margin: 20px 10px;
				}
			}
			
			button {
				background: #03A9F4;
				color: #fff;
				box-shadow: 0 2px 2px rgba(0,0,0,.16), 0 2px 10px rgba(0,0,0,.12);
				border: none;
				text-align: center;
				cursor: pointer;
				padding: 6px 12px;
				border-radius: 3px;
				font-size: 15px;
			}
		</style>
	</head>
	<body>
		<div>
			<h1><?=$notice?></h1>
			
			<?php if($time){ ?>
			<p><?=$time; ?> 秒后返回上一页面...</p>
			<?php } ?>
			
			<?php if(isset($go)){ ?>
			<button id="go">跳转</button>
			<?php }else{ ?>
			<button id="go">返回上一页</button>
			<?php } ?>
		</div>
		<hr />
		<p>+ [ Powered By AdminPHP<sup>V2</sup> ] +</p>
		
		
		
		<script>
		var go = function(){
			<?php if(isset($go)){ ?>
			location.href = "<?=$go; ?>";
			<?php }else{ ?>
			history.go(-1);
			<?php } ?>
		};
		
		document.getElementById('go').onclick = go;
		<?php if($time){ ?>
		setTimeout(go, <?=$time; ?>000);
		<?php } ?>
		</script>
	</body>
</html>