<?php
class Validation {
    public static function validateText(string $text): string {
        return htmlspecialchars(trim($text), ENT_QUOTES, 'UTF-8');
    }

    public static function validateISBN(string $isbn): bool {
        return preg_match('/^\d{13}$/', $isbn) === 1;
    }

    public static function validatePositiveInt($value): bool {
        return filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) !== false;
    }

    public static function validateEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}
