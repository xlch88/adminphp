<html>
    <head>
        <meta charset="utf-8"/>
        <title>AdminPHP</title>
        <style>
            h1 {
                font-family: "微软雅黑", sans-serif;
                font-size: 100px;
                color: #E91E63;
                -webkit-text-fill-color: #ff73a2;
                -webkit-text-stroke: 1px #ff4785;
                text-shadow: 0px 0px 2px #686868, 0px 1px 1px #ddd, 0px 2px 1px #d6d6d6, 0px 3px 1px #ccc, 0px 4px 1px #c5c5c5, 0px 5px 1px #c1c1c1, 0px 6px 1px #bbb, 0px 7px 1px #777, 0px 8px 3px rgba(100, 100, 100, 0.4), 0px 9px 5px rgba(100, 100, 100, 0.1), 0px 10px 7px rgba(100, 100, 100, 0.15), 0px 11px 9px rgba(100, 100, 100, 0.2), 0px 12px 11px rgba(100, 100, 100, 0.25), 0px 13px 15px rgba(100, 100, 100, 0.3);
            }

            body {
                background: #ffffff;
            }

            a {
                border: 1px solid #2196F3;
                padding: 5px;
                color: #03A9F4;
                text-decoration: none;
                font-size: 20px;
            }

            p {
                color: #03A9F4;
            }
        </style>
    </head>
    <body>
        <center>
            <h1><?=$text; ?></h1>
			
            <br/>
            <br/>
			
            <a href="<?=url('index/sysinfo'); ?>" target="_blank">系统提示页1</a>
            | 
			<a href="<?=url('index/notice'); ?>" target="_blank">系统提示页2</a>
            | 
			<a href="<?=url('index/exception'); ?>" target="_blank">错误/异常管理</a>
			
            <br/>
            <br/>
			
			<img src="<?=url('verifyCode/get'); ?>">
			
            <br/>
            <br/>
			
            <p>
                Powered By AdminPHP<sup>V2</sup>
                !
            
            </p>
        </center>
    </body>
</html>
