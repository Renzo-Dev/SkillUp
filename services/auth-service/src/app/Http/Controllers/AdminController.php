<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\UpdateUserRoleRequest;
use App\Http\Requests\Admin\BanUserRequest;
use App\Models\User;
use App\Services\AdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
  public function __construct(
    private AdminService $adminService
  ) {
  }

  /**
   * Получение списка пользователей с фильтрацией
   */
  public function getUsers(Request $request): JsonResponse
  {
    $users = $this->adminService->getUsers($request->query());

    return response()->json(['users' => $users]);
  }

  /**
   * Изменение роли пользователя
   */
  public function updateUserRole(UpdateUserRoleRequest $request, int $userId): JsonResponse
  {
    $user = $this->adminService->updateUserRole($userId, $request->validated()['role']);

    return response()->json([
      'message' => 'User role updated successfully',
      'user' => $user
    ]);
  }

  /**
   * Блокировка пользователя
   */
  public function banUser(BanUserRequest $request, int $userId): JsonResponse
  {
    $user = $this->adminService->banUser($userId, $request->validated()['reason']);

    return response()->json([
      'message' => 'User banned successfully',
      'user' => $user
    ]);
  }
}
