<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repository;

use App\Application\Ports\Outbound\UserRepository;
use App\Domain\Model\User;
use App\Infrastructure\Persistence\Mapper\UserMapper;
use App\Infrastructure\Persistence\Model\UserModel;

final class EloquentUserRepository implements UserRepository
{
    public function findById(string $id): ?User
    {
        $model = UserModel::find($id);

        return $model !== null ? UserMapper::toDomain($model) : null;
    }

    public function findByUsername(string $username): ?User
    {
        $model = UserModel::where('username', $username)->first();

        return $model !== null ? UserMapper::toDomain($model) : null;
    }

    public function existsByUsername(string $username): bool
    {
        return UserModel::where('username', $username)->exists();
    }

    public function save(User $user): void
    {
        $model = UserModel::find($user->id);
        if ($model === null) {
            $model = UserMapper::toModel($user);
            $model->save();
            return;
        }

        $model->username = $user->username->value;
        $model->password_hash = $user->passwordHash;
        $model->role = $user->role->value;
        $model->save();
    }
}
