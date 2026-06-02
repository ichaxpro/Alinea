<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\User;
use App\Models\Transaction;
use App\Models\BookReview;
use App\Models\PersonalBook;
use App\Models\TimelinePost;
use Illuminate\Support\Facades\DB;

class AchievementService
{
    public function checkAndGrant(User $user): void {
        $achievements = Achievement::all();
        $earnedKeys = $user->achievements()->pluck('key')->all();

        foreach ($achievements as $achievement) {
            if (in_array($achievement->key, $earnedKeys)) {
                continue;
            }

            if ($this->qualifies($user, $achievement)) {
                DB::table('user_achievement')->insert([
                    'user_id' => $user->id,
                    'achievement_id' => $achievement->id,
                    'earned_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function qualifies(User $user, Achievement $achievement): bool {
        return match ($achievement->criteria_type) {
            'borrow_count' => Transaction::where('borrower_id', $user->id)
                ->where('status', 'disetujui')
                ->count() >= $achievement->criteria_value,

            'review_count' => BookReview::where('user_id', $user->id)
                ->count() >= $achievement->criteria_value,

            'personal_book_count' => PersonalBook::where('user_id', $user->id)
                ->count() >= $achievement->criteria_value,
            
            'post_count' => TimelinePost::where('id_user', $user->id)
                ->count() >= $achievement->criteria_value,

            default => false,
        };
    }
}
