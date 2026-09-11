<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    /**
     * Seul le propriétaire d'un document peut le consulter, le modifier
     * ou le supprimer. Isolation stricte des données entre utilisateurs
     * (cahier des charges §3 et §22).
     */
    public function view(User $user, Document $document): bool
    {
        return $user->id === $document->user_id;
    }

    public function update(User $user, Document $document): bool
    {
        return $user->id === $document->user_id;
    }

    public function delete(User $user, Document $document): bool
    {
        return $user->id === $document->user_id;
    }

    public function restore(User $user, Document $document): bool
    {
        return $user->id === $document->user_id;
    }

    public function forceDelete(User $user, Document $document): bool
    {
        return $user->id === $document->user_id;
    }
}