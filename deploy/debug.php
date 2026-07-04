<?php
echo '<pre>';
$lines = file(__DIR__ . '/index.php');
echo htmlspecialchars(implode('', $lines));
echo '</pre>';
