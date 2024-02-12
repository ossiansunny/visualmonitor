<?php
echo '<script>';
echo 'function confirm_test() {';
echo 'var select = confirm("問い合わせますか？n「OK」で送信n「キャンセル」で送信中止");';
echo 'return select;';
echo '}';
echo '</script>';
 
echo '<form method="POST" action="test.php" onsubmit="return confirm_test()">';
echo '名前<br />';
echo '<input type="text" name="user_name" value="" /><br /><br />';
echo '問い合わせ内容<br />';
echo '<textarea name="user_question"></textarea><br /><br />';
echo '<input type="submit" value="問い合わせる" />';
echo '</form>';
?>