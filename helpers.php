<?php
function e(string $text): string {
    // htmlspecialchars + ENT_QUOTES защищает от <script>alert(1)</script>
    // ENT_SUBSTITUTE заменяет некорректные байты, чтобы не ломать страницу
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}