<html><body class="<?= 'foo' ?>"><?
echo 'hi!';
?>
<?= ' how are you' ?>
<?php
echo '<? echo $foo ?><?= echo $bar ?>';
echo "<?= \$baz ?>";
echo <<<EOF
<?
echo \$qux
EOF;
?></body></html>