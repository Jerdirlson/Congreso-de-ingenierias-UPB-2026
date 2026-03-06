<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function view(User $user, Review $review): bool
    {
        return $review->reviewer_id === $user->id;
    }

    public function update(User $user, Review $review): bool
    {
        return $review->reviewer_id === $user->id;
    }
}
