<div class="adminphp_info adminphp_exception">
	<div class="adminphp_info_img adminphp_info_img_exception"></div>
	<div class="adminphp_exception_errorInfo">
		<h2><?=l('异常捕获 _(:з」∠)_'); ?></h2>
		<div class="table-responsive">
			<table class="adminphp_exception_info_table">
				<tr>
					<td><?=l('页面地址：'); ?></td>
					<td><?=safe_html($url); ?></td>
				</tr>
				<tr>
					<td><?=l('异常类型：'); ?></td>
					<td><font class="c-darkred"><?=$class; ?></font></td>
				</tr>
				<tr>
					<td><?=l('异常代码：'); ?></td>
					<td><font class="c-blue"><?=$code;?></font></td>
				</tr>
				<tr>
					<td><?=l('异常定位：'); ?></td>
					<td><font class="c-red"><?=$file; ?></font> <?=l('的'); ?> <font class="c-red"><?=$line; ?></font> <?=l('行'); ?></td>
				</tr>
				<tr>
					<td><?=l('异常信息：'); ?></td>
					<td><font class="c-blue"><?=$message; ?></font></td>
				</tr>
			</table>
		</div>
		<?php if($debug){ ?>
		<div class="table-responsive now-code" style="display:none;">
			<pre class="brush: php; highlight: [<?=$line; ?>]; first-line: <?=($line - 9 >= 1 ? $line - 9 : 1);?>;"><?=str_replace(['[redline]', '[/redline]'], ['<span class="redLine">', '</span>'], safe_html($fileText)); ?></pre>
		</div>
		<?php } ?>
	</div>
	<?php if($debug && $exceptionVars){ ?>
	<div class="adminphp_exception_vars">
		<h2><?=l('数据输出 (〜￣△￣)〜'); ?></h2>

		<?php foreach($exceptionVars as $key => $value){ ?>
			<?php if($value['type'] == 1){ ?>
			<table border="1">
				<tr>
					<td colspan="2"><?=$key; ?> <span>array(<?=count($value['value'])?>)</span></td>
				</tr>
				<?php foreach($value['value'] as $key2 => $value2){ ?>
				<tr class="sub">
					<td><?=$key2; ?></td>
					<td title="<?=safe_attr($value2[1]); ?>"><?=$value2[0]; ?></td>
				</tr>
				<?php } ?>
			</table>
			<?php }else{ ?>
			<table border="1">
				<tr>
					<td><?=$key; ?></td>
					<td title="<?=safe_attr($value['value'][1]); ?>"><?=$value['value'][0]; ?></td>
				</tr>
			</table>
			<?php } ?>
		<?php } ?>
	</div>
	<div class="adminphp_exception_trace">
		<h2><?=l('来源追踪 ╮(╯﹏╰）╭'); ?> <span class="allOpen"><?=l('[全部打开]'); ?></span></h2>
		<?php foreach($trace as $row){ ?>
		<div class="item">
			<div class="file-path b-green">
				<p><font class="c-red"><?=$row['file']; ?></font> (line <font class="c-red"><?=$row['line']; ?></font>):</p>
				<p><span class="func"><?=$row['function_']; ?></span>(<?php
					$echo = [];
					foreach($row['args'] as $arg){
						$tmp = '<span class="func-args" title="' . safe_attr($arg[1]) . '">';
						$tmp.= $arg[0];
						$tmp.= '</span>';
						$echo[] = $tmp;
					}
					echo implode(', ', $echo);
					?>)
				</p>
			</div>
			<div class="table-responsive" style="display:none;">
				<pre class="brush: php; first-line: <?=(int)(($row['line'] - 4) >= 1 ? $row['line'] - 4 : 1);?>; highlight: [<?=$row['line']; ?>];"><?=str_replace(['[redline]', '[/redline]'], ['    ', '<span class="redLine">', '</span>'], safe_html($row['fileText'])); ?></pre>
			</div>
		</div>
		<?php } ?>
	</div>
	<?php } ?>
	<div class="adminphp_exception_adminInfo">
		<h2><?=l('管理员信息'); ?> <span><?=l('请将错误信息发送给管理员'); ?></span></h2>
		<div class="ta ble-responsive">
			<table class="adminphp_exception_info_table">
				<thead>
				<tr>
					<td>错误发生时间：</td>
					<td><?=date('Y-m-d H:i:s'); ?></td>
				</tr>
				<?php if(!$errorInfo['debug']){ ?>
				<tr>
					<td>错误日志ＩＤ：</td>
					<td><?=$errorInfo['logId']; ?></td>
				</tr>
				<?php } ?>
				<?php foreach($adminInfo as $key => $value){ ?>
				<tr>
					<td><?=$key; ?>：</td>
					<td><?=$value; ?></td>
				</tr>
				<?php } ?>
				</thead>
			</table>
		</div>
	</div>
</div>

<link href="https://cdn.bootcdn.net/ajax/libs/SyntaxHighlighter/3.0.83/styles/shCore.min.css" rel="stylesheet" crossorigin="anonymous" integrity="sha384-jU8gV/156nb2i2HpAqxQEn1XPSJ/gKbXU2uBnZ9UvaEeJkFxgujxewaPlpTZU1yP">
<link href="https://cdn.bootcdn.net/ajax/libs/SyntaxHighlighter/3.0.83/styles/shThemeDefault.min.css" rel="stylesheet" crossorigin="anonymous" integrity="sha384-ORwhBUiLBTQVN92YaWYqzOal69wsFexG8mlI7I44PSvRQ1k76SGBbDXgRkC7+wRj">
<script src="https://cdn.bootcdn.net/ajax/libs/jquery/1.12.4/jquery.min.js" crossorigin="anonymous" integrity="sha384-nvAa0+6Qg9clwYCGGPpDQLVpLNn0fRaROjHqs13t4Ggj3Ez50XnGQqc/r8MhnRDZ"></script>
<script src="https://cdn.bootcdn.net/ajax/libs/SyntaxHighlighter/3.0.83/scripts/shCore.min.js" crossorigin="anonymous" integrity="sha384-GquH7MN7EAukzbL03Gac5E6rOq0xtps3CyAi+h+zsb10kROSZOSzjxiVqGxt8684"></script>
<script src="https://cdn.bootcdn.net/ajax/libs/SyntaxHighlighter/3.0.83/scripts/shBrushPhp.min.js" crossorigin="anonymous" integrity="sha384-wQ+20r96tS2dNHZ9ZNXvmgs+IXZ6m6Ww5UbNvKyvxQC4YoEY0AxfJWVbJd2jpWxV"></script>

<script>
$(function(){
	SyntaxHighlighter.all();
	
	$('.adminphp_exception_trace>.item>.file-path>p:nth-child(1)').click(function(){
		$('.table-responsive', $(this).parent().parent()).toggle();
	});
	
	$('.allOpen').click(function(){
		if($(this).html() === '<?=l("[全部打开]"); ?>'){
			$('.adminphp_exception_trace>.item .table-responsive').show();
			$(this).html('<?=l("[全部关闭]"); ?>');
		}else{
			$('.adminphp_exception_trace>.item .table-responsive').hide();
			$(this).html('<?=l("[全部打开]"); ?>');
		}
	});
	
	$('.adminphp_exception_vars table tr.sub').hide();
	$('.adminphp_exception_vars td[colspan=2]').click(function(){
		$('.sub', $(this).parent().parent()).toggle();
	});
	
	$('[title]').click(function(){
		alert($(this).attr('title'));
	});
	
	//不在调试控制台里显示
	$('.now-code').show();
	
	//小米电视给我爬
	if(navigator.userAgent.indexOf('MiTV') !== -1){
		setTimeout(function(){
			location.reload();
		}, 3000);
	}
});
</script>
