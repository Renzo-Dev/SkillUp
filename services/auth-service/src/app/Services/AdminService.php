<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminService
{
  public function getUsers(array $filters = []): LengthAwarePaginator
  {
    $query = User::query();

    if (isset($filters['role'])) {
      $query->where('role', $filters['role']);
    }

    if (isset($filters['is_active'])) {
      $query->where('is_active', $filters['is_active']);
    }

    if (isset($filters['search'])) {
      $query->where(function ($q) use ($filters) {
        $q->where('email', 'like', '%' . $filters['search'] . '%')
          ->orWhere('first_name', 'like', '%' . $filters['search'] . '%')
          ->orWhere('last_name', 'like', '%' . $filters['search'] . '%');
      });
    }

    return $query->paginate($filters['per_page'] ?? 15);
  }

  public function updateUserRole(int $userId, string $role): User
  {
    $user = User::findOrFail($userId);

    if (!in_array($role, ['user', 'admin'])) {
      throw new \Exception('Invalid role');
    }

    $user->update(['role' => $role]);

    return $user;
  }

  public function banUser(int $userId, string $reason): User
  {
    $user = User::findOrFail($userId);
    $user->update(['is_active' => false]);

    // TODO: Log ban reason

    return $user;
  }
}
