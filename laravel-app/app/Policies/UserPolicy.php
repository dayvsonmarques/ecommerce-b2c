<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Admins passam por todas as verificações.
     */
    public function before(User $currentUser, string $ability): bool|null
    {
        if ($currentUser->tokenCan('admin') || $currentUser->is_admin ?? false) {
            return true;
        }

        return null;
    }

    /**
     * Um usuário só pode visualizar seu próprio perfil.
     */
    public function view(User $currentUser, User $targetUser): bool
    {
        return $currentUser->id === $targetUser->id;
    }

    /**
     * Um usuário só pode atualizar seu próprio perfil.
     */
    public function update(User $currentUser, User $targetUser): bool
    {
        return $currentUser->id === $targetUser->id;
    }

    /**
     * Somente admins podem deletar usuários (tratado em before()).
     */
    public function delete(User $currentUser, User $targetUser): bool
    {
        return false;
    }
}
