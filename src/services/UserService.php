<?php
declare(strict_types=1);

class UserService
{
    public function list(): ServiceResult
    {
        $db = Database::getInstance();
        $users = $db->query("SELECT id, username, email, created_at FROM users ORDER BY id ASC")->fetchAll();
        return ServiceResult::ok(['users' => $users]);
    }

    public function createUser(string $username, string $email, string $password, string $passwordConfirm = ''): ServiceResult
    {
        // Validate username
        if (empty($username) || mb_strlen($username) < 3) {
            return ServiceResult::fail(['username' => [__('validation.min_length', ['min' => 3])]]);
        }

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ServiceResult::fail(['email' => [__('validation.invalid_email')]]);
        }

        // Validate password
        $passwordValidator = Validator::make(
            ['password' => $password],
            ['password' => 'required|password']
        );
        if ($passwordValidator->fails()) {
            return ServiceResult::fail(['password' => [$passwordValidator->getError('password')]]);
        }

        // Confirm match (skip if empty — CLI may not use confirmation)
        if ($passwordConfirm !== '' && $password !== $passwordConfirm) {
            return ServiceResult::fail(['password' => [__('validation.passwords_dont_match')]]);
        }

        // Uniqueness
        if (User::usernameExists($username)) {
            return ServiceResult::fail(['username' => [__('validation.duplicate')]]);
        }
        if (User::emailExists($email)) {
            return ServiceResult::fail(['email' => [__('validation.duplicate')]]);
        }

        $userId = User::createUser($username, $email, $password);
        if (!$userId) {
            return ServiceResult::fail([], __('flash.error'));
        }

        return ServiceResult::ok(
            ['id' => $userId, 'username' => $username],
            __('flash.created', ['item' => $username])
        );
    }

    public function deleteUser(int $userId, ?int $currentUserId = null): ServiceResult
    {
        if ($currentUserId !== null && $userId === $currentUserId) {
            return ServiceResult::fail([], __('user.cannot_delete_self'));
        }

        if ($userId <= 0) {
            return ServiceResult::fail([], __('validation.invalid_value'));
        }

        $user = User::find($userId);
        if (!$user) {
            return ServiceResult::fail([], __('validation.invalid_value'));
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);

        return ServiceResult::ok(
            ['id' => $userId],
            __('flash.deleted', ['item' => $user['username']])
        );
    }
}
