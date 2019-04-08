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
            }

            body hr {
                border: 1px dashed #fff;
            }

            body p {
                color: black;
            }

            body div p {
                color: white;
            }

            @media (max-width: 992px) {
                body div {
                    padding: 20px;
                    font-size: 10px;
                    margin: 100px 0;
                }
            }
		</style>
	</head>
	<body>
		<div>
			<h1><?=$notice?></h1>
			<p>2 秒后返回上一页面...</p>
		</div>
		<hr />
		<p>+ [ Powered By AdminPHP<sup>V1</sup> ] +</p>
		<p>&copy; Lu'an Downtime Tech.</p>
		
		
		<script>
		setTimeout(function(){
			<?php if(isset($go)){ ?>
				<?php if($go == '#'){ ?>
					var plusReady = function(){plus.webview.currentWebview().close();}
					if(window.plus){
						plusReady();
					}else{ 
						document.addEventListener( "plusready", plusReady, false );
					}
				<?php }else{ ?>
				location.href = "<?=$go; ?>";
				<?php } ?>
			<?php }else{ ?>
			history.go(-1);
			<?php } ?>
		},2000);
		</script>
	</body>
</html>