
<?php $qwq = 1; ?>
<?=safe_html($qwq); ?>

<?=$qwq; ?>

<?=isset($qwq) ? $qwq : $fuck; ?>


<?php if(1 == TRUE): ?>
	qwq
<?php elseif(2 == true): ?>
	0w0
<?php else: ?>
	哇哇哇
<?php endif; ?>

<?php foreach(['a', 'b', 'c'] as $qwq): ?>
	<?=safe_html($qwq); ?>

<?php endforeach; ?>

<?php for($x = 0; $x < 10; $x++): ?>
	<?php continue; ?>
	<?php continue 1; ?>
	<?php if($a > 233) continue; ?>
	<?php break; ?>
	<?php break 1; ?>
	<?php if($a > 233) break; ?>
<?php endfor; ?>

<?php if(isset($a)): ?>
	0w0
	<?php if(empty($a)): ?>
		qwq
	<?php endif; ?>
	
	<?php if(!($a)): ?>
		wawa
	<?php endif; ?>
<?php endif; ?>

<?php 
	echo 'hello world';
?>

<?php echo 'hello world'; ?>


1
<?php if(false): ?>
2
<?php endif; ?>