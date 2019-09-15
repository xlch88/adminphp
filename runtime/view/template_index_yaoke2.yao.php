<?php \AdminPHP\Engine\View\KeYao\Layout::push('qwq', function($__args){ extract($__args); foreach(\AdminPHP\View::$globalVar as $__globalVar){ global $$__globalVar; } unset($__args, $__globalVar); ?>
1
<?php }); ?>
<?php \AdminPHP\Engine\View\KeYao\Layout::push('qwq', function($__args){ extract($__args); foreach(\AdminPHP\View::$globalVar as $__globalVar){ global $$__globalVar; } unset($__args, $__globalVar); ?>
2
<?php }); ?>
<?php \AdminPHP\Engine\View\KeYao\Layout::push('qwq', function($__args){ extract($__args); foreach(\AdminPHP\View::$globalVar as $__globalVar){ global $$__globalVar; } unset($__args, $__globalVar); ?>
3243124
<?php }, true); ?>

<?php \AdminPHP\Engine\View\KeYao\Layout::stack('qwq', get_defined_vars()); ?>

<?=json_encode(['qwq'=>'0w0']); ?>
<?php $i = 2 ?>
<?php switch($i):
    case 0: ?>
		<?=safe_html("i equals 0"); ?>

        <?php break;
    case 1: ?>
        <?=safe_html("i equals 1"); ?>

        <?php break;
    case 2: ?>
        <?=safe_html("i equals 2"); ?>

        <?php break;
    default: ?>
        <?=safe_html("i is not equal to 0, 1 or 2"); ?>

        <?php break;
endswitch; ?>
<pre><?php echo htmlspecialchars_decode('&lt;hr/&gt;') . file_get_contents(templatePath . 'index/yaoke.yao.php'); ?></pre>
<?php echo htmlspecialchars_decode('&lt;hr/&gt;'); highlight_string(file_get_contents(__FILE__)); ?>