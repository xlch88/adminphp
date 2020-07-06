<link href="https://cdn.bootcss.com/SyntaxHighlighter/3.0.83/styles/shCore.min.css" rel="stylesheet">
<link href="https://cdn.bootcss.com/SyntaxHighlighter/3.0.83/styles/shThemeDefault.min.css" rel="stylesheet">
<div class="adminphp_info adminphp_exception">
	<div class="adminphp_info_img adminphp_info_img_exception"></div>
	<div class="adminphp_exception_errorInfo">
		<h2><?=l('异常捕获 _(:з」∠)_'); ?></h2>
		<div class="table-responsive">
			<table class="adminphp_exception_errorInfo_errorText">
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
		<div class="table-responsive">
			<pre class="brush: php; highlight: [<?=$line; ?>]; first-line: <?=($line - 9);?>;"><?=str_replace(['[redline]', '[/redline]'], ['<span class="redLine">', '</span>'], safe_html($fileText)); ?></pre>
		</div>
		<?php } ?>
	</div>
	<?php if($debug){ ?>
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
			<div class="filepath b-green">
				<p><font class="c-red"><?=$row['file']; ?></font> (line <font class="c-red"><?=$row['line']; ?></font>):</p>
				<p><span class="func"><?=$row['function_']; ?></span>(<?php
					$echo = [];
					foreach($row['args'] as $arg){
						$tmp = '<span class="funcargs" title="' . safe_attr($arg[1]) . '">';
						$tmp.= $arg[0];
						$tmp.= '</span>';
						$echo[] = $tmp;
					}
					echo implode(', ', $echo);
					?>)
				</p>
			</div>
			<div class="table-responsive" style="display:none;">
				<pre class="brush: php; first-line: <?=((int)$row['line'] - 4);?>; highlight: [<?=$row['line']; ?>];"><?=str_replace(['[redline]', '[/redline]'], ['    ', '<span class="redLine">', '</span>'], safe_html($row['fileText'])); ?></pre>
			</div>
		</div>
		<?php } ?>
	</div>
	<?php } ?>
	<div class="adminphp_exception_adminInfo">
		<h2><?=l('管理员信息'); ?> <span><?=l('请将错误信息发送给管理员'); ?></span></h2>
		<div class="ta ble-responsive">
			<table class="adminphp_exception_errorInfo_errorText">
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
<!-- 没有jq真是太难受了 -->
<script src="https://cdn.bootcss.com/jquery/3.4.1/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.bootcss.com/SyntaxHighlighter/3.0.83/scripts/shCore.js"></script>
<script type="text/javascript" src="https://cdn.bootcss.com/SyntaxHighlighter/3.0.83/scripts/shBrushPhp.js"></script>
<script type="text/javascript">
	SyntaxHighlighter.all()
</script>
<script>
$(function(){
	$('.adminphp_exception_trace>.item>.filepath>p:nth-child(1)').click(function(){
		$('.table-responsive', $(this).parent().parent()).toggle();
	});
	
	$('.allOpen').click(function(){
		if($(this).html() == '<?=l("[全部打开]"); ?>'){
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
	
	//小米电视给我爬
	if(navigator.userAgent.indexOf('MiTV') != -1){
		setTimeout(function(){
			location.reload();
		}, 3000);
	}
});
</script>
<style>
.syntaxhighlighter a, .syntaxhighlighter div, .syntaxhighlighter code{
	font-size:15px!important;
}
.syntaxhighlighter .toolbar{
	display:none;
}
.syntaxhighlighter {
    margin: 0!important;
	overflow-y: hidden!important;
}
.syntaxhighlighter .gutter .line.highlighted {
    background-color: #93ddff!important;
}
.syntaxhighlighter .gutter .line {
    border-right: 3px solid #93ddff!important;
}
</style>