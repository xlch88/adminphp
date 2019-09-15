<link href="https://cdn.bootcss.com/highlight.js/9.15.9/styles/solarized-light.min.css" rel="stylesheet">
<div class="info exception">
	<div class="img img_exception"></div>
	<div class="errorInfo">
		<h2><?=l('异常捕获 _(:з」∠)_'); ?></h2>
		<div class="table-responsive">
			<table class="errorText">
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
			<pre><code><?=str_replace(["\t", '[redline]', '[/redline]'], ['    ', '<span class="redLine">', '</span>'], safe_html($fileText)); ?></code></pre>
		</div>
		<?php } ?>
	</div>
	<?php if($debug){ ?>
	<div class="vars">
		<h2><?=l('数据输出 (〜￣△￣)〜'); ?></h2>

		<?php foreach($exceptionVars as $key => $value){ ?>
			<?php if($value['type'] == 1){ ?>
			<table class="varTable" border="1">
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
			<table class="varTable" border="1">
				<tr>
					<td><?=$key; ?></td>
					<td title="<?=safe_attr($value['value'][1]); ?>"><?=$value['value'][0]; ?></td>
				</tr>
			</table>
			<?php } ?>
		<?php } ?>
	</div>
	<div class="trace">
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
				<pre><code><?=str_replace(["\t", '[redline]', '[/redline]'], ['    ', '<span class="redLine">', '</span>'], safe_html($row['fileText'])); ?></code></pre>
			</div>
		</div>
		<?php } ?>
	</div>
	<?php }else{ ?>
	<div class="adminInfo">
		<h2><?=l('管理员信息'); ?> <span class="allOpen"><?=l('请将错误信息发送给管理员'); ?></span></h2>
		<div class="table-responsive">
			<table class="errorText">
				<thead>
				<?php foreach($adminInfo as $key => $value){ ?>
				<tr>
					<td><?=$key; ?>：</td>
					<td><?=$value; ?></td>
				</tr>
				<?php } ?>
				</thead>
			</table>
		</div>
		<?php if($debug){ ?>
		<div class="table-responsive">
			<pre><code><?=str_replace(["\t", '[redline]', '[/redline]'], ['    ', '<span class="redLine">', '</span>'], safe_html($fileText)); ?></code></pre>
		</div>
		<?php } ?>
	</div>
	<?php } ?>
</div>
<!-- 没有jq真是太难受了 -->
<script src="https://cdn.bootcss.com/jquery/3.4.1/jquery.min.js"></script>
<script src="https://cdn.bootcss.com/highlight.js/9.15.9/highlight.min.js"></script>
<script>
$(function(){
	hljs.initHighlightingOnLoad();
	
	$('.trace>.item>.filepath>p:nth-child(1)').click(function(){
		$('.table-responsive', $(this).parent().parent()).toggle();
	});
	
	$('.allOpen').click(function(){
		if($(this).html() == '<?=l("[全部打开]"); ?>'){
			$('.trace>.item .table-responsive').show();
			$(this).html('<?=l("[全部关闭]"); ?>');
		}else{
			$('.trace>.item .table-responsive').hide();
			$(this).html('<?=l("[全部打开]"); ?>');
		}
	});
	
	$('.vars table tr.sub').hide();
	$('.vars td[colspan=2]').click(function(){
		$('.sub', $(this).parent().parent()).toggle();
	});
	
	$('[title]').click(function(){
		alert($(this).attr('title'));
	});
});
</script>