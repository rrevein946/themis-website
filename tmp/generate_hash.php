<?php
$hash = password_hash('123456', PASSWORD_DEFAULT);
echo '<h2>Скопируйте этот хеш:</h2>';
echo '<textarea style="width:100%; height:60px; font-family:monospace;">' . $hash . '</textarea>';
echo '<p>Затем вставьте его в базу данных (см. Шаг 2).</p>';