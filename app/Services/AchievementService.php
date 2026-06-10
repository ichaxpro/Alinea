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
        return $this->getCurrentProgress($user, $achievement) >= $achievement->criteria_value;
    }

    public function getCurrentProgress(User $user, Achievement $achievement): int {
        return match ($achievement->criteria_type) {
            'borrow_count' => Transaction::where('borrower_id', $user->id)
                ->where('status', 'disetujui')
                ->count(),

            'review_count' => BookReview::where('user_id', $user->id)
                ->count(),

            'personal_book_count' => PersonalBook::where('user_id', $user->id)
                ->where('status', '!=', 'tidak_tersedia')
                ->count(),
            
            'reading_history_count' => PersonalBook::where('user_id', $user->id)
                ->whereNotNull('reading_status')
                ->count(),
            
            'post_count' => TimelinePost::where('id_user', $user->id)
                ->count(),

            default => 0,
        };
    }

    public function getInProgressAchievements(User $user) {
        $earnedKeys = $user->achievements()->pluck('key')->all();
        $achievements = Achievement::whereNotIn('key', $earnedKeys)->get();

        foreach ($achievements as $achievement) {
            $achievement->current_progress = $this->getCurrentProgress($user, $achievement);
        }

        return $achievements;
    }
}
