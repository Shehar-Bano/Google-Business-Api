<?php

namespace App\Support;

final class ApiValidationRules
{
    /**
     * @return array<int, string>
     */
    public static function email(string $table = 'users', string $column = 'email'): array
    {
        return ['required', 'email', "unique:{$table},{$column}"];
    }

    /**
     * @return array<int, string>
     */
    public static function phone(string $table = 'users', string $column = 'phone'): array
    {
        return ['required', 'string', 'max:25', "unique:{$table},{$column}"];
    }

    /**
     * @return array<int, string>
     */
    public static function password(): array
    {
        return ['required', 'string', 'min:8'];
    }

    /**
     * @return array<int, string>
     */
    public static function otp(): array
    {
        return ['required', 'digits:6'];
    }

    /**
     * @return array<int, string>
     */
    public static function dob(): array
    {
        return ['required', 'date_format:Y-m-d'];
    }

    /**
     * @return array<int, string>
     */
    public static function gender(): array
    {
        return ['required', 'in:male,female,other'];
    }

    /**
     * @return array<int, string>
     */
    public static function playingLevel(): array
    {
        return ['required', 'in:beginner,intermediate,advanced,professional'];
    }

    /**
     * @return array<int, string>
     */
    public static function primaryHand(): array
    {
        return ['required', 'in:left,right'];
    }

    /**
     * @return array<int, string>
     */
    public static function numberOfCourts(): array
    {
        return ['required', 'integer', 'gt:0'];
    }
}
