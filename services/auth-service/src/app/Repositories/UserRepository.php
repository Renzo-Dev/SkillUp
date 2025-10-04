<?php

namespace App\Repositories;

use App\Models\User;
use Contracts\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Log;

class UserRepository implements UserRepositoryInterface
{
  public function create(array $data): ?User
  {
    try {
      return User::create($data);
    } catch (\Exception $e) {
      Log::error('Error creating user: ' . $e->getMessage());
      return null;
    }

  }

  public function findById(int $id): ?User
  {
    try {
      return User::find($id);
    } catch (\Exception $e) {
      Log::error('Error finding user by id: ' . $e->getMessage());
      return null;
    }
  }
  
  public function findByEmail(string $email): ?User
  {
    try {
      return User::where('email', $email)->first();
    } catch (\Exception $e) {
      Log::error('Error finding user by email: ' . $e->getMessage());
      return null;
    }
  }

  public function update(User $user, array $data): ?User
  {
    try {
      return $user->update($data);
    } catch (\Exception $e) {
      Log::error('Error updating user: ' . $e->getMessage());
      return null;
    }
  }

  public function delete(User $user): bool
  {
    try {
      return $user->delete();
    } catch (\Exception $e) {
      Log::error('Error updating user: ' . $e->getMessage());
      return false;
    }

  }

  public function deleteById(int $id): bool
  {
    try {
      return User::where('id', $id)->delete();
    } catch (\Exception $e) {
      Log::error('Error deleting user by id: ' . $e->getMessage());
      return false;
    }
  }

  public function activate(User $user): ?User
  {
    try {
      return $user->update(['is_active' => true]);
    } catch (\Exception $e) {
      Log::error('Error activating user: ' . $e->getMessage());
      return null;
    }
  }

  public function deactivate(User $user): ?User
  {
    try {
      return $user->update(['is_active' => false]);
    } catch (\Exception $e) {
      Log::error('Error deactivating user: ' . $e->getMessage());
      return null;
    }
  }

  public function updateLastLogin(User $user): ?User
  {
    try {
      return $user->update(['last_login_at' => now()]);
    } catch (\Exception $e) {
      Log::error('Error updating last login: ' . $e->getMessage());
      return null;
    }
  }
}