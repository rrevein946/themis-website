<?php
function e(?string $text): string {
    if ($text === null) {
        return '';
    }
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function safe_substr(string $text, int $start, int $length): string {
    if (function_exists('mb_substr')) {
        return mb_substr($text, $start, $length, 'UTF-8');
    }
    return substr($text, $start, $length);
}


