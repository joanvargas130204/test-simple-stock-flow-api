<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Mapper;

use App\Domain\Model\User;
use App\Domain\ValueObject\Role;
use App\Domain\ValueObject\Username;
use App\Infrastructure\Persistence\Model\UserModel;

final class UserMapper
{
    public static function toDomain(UserModel $model): User
    {
        return new User(
            id: $model->id,
            username: new Username($model->username),
            passwordHash: $model->password_hash,
            role: Role::from($model->role)
        );
    }

    public static function toModel(User $domain): UserModel
    {
        $model = new UserModel();
        $model->id = $domain->id;
        $model->username = $domain->username->value;
        $model->password_hash = $domain->passwordHash;
        $model->role = $domain->role->value;

        return $model;
    }
}
