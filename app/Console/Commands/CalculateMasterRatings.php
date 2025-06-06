<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Master;
use App\Models\Rating;
use Carbon\Carbon;
use DB;

class CalculateMasterRatings extends Command
{
    protected $signature = 'ratings:calculate';
    protected $description = 'Calculate master ratings based on the last 5 days of activity';

    public function handle()
    {
        // Определяем ID роли "мастер"
        $masterRoleId = DB::table('roles')->where('slug', 'master')->value('id');

        if (!$masterRoleId) {
//            $this->error('Role "master" not found in database.');
            return;
        }

        // Получаем всех мастеров (users с ролью "master")
        $masters = DB::table('users')
            ->join('users_roles', 'users.id', '=', 'users_roles.user_id')
            ->where('users_roles.role_id', $masterRoleId)
            ->select('users.*')
            ->get();
        // Весовые коэффициенты (можно менять)
        $K1 = DB::table('variables')->where('id',2)->first()->value;
        $K2 = DB::table('variables')->where('id',5)->first()->value;
        $K3 = DB::table('variables')->where('id',6)->first()->value;
        $K4 = DB::table('variables')->where('id',7)->first()->value;

        $fiveDaysAgo = Carbon::now()->subDays(5);
//        $fiveDaysAgo = Carbon::now()->subMonths(6);;

//        $masters = User::all(); // Получаем всех мастеров

        foreach ($masters as $master) {
            // Получаем P1, P2, P3, P4 за последние 5 дней
            $P1 = $this->calculateClickRate($master->id, $fiveDaysAgo);
            $P2 = $this->calculateReadRate($master->id, $fiveDaysAgo);
            $P3 = $this->calculateReplyRate($master->id, $fiveDaysAgo);
            $P4 = $this->calculateOrderRate($master->id, $fiveDaysAgo);

            // Рассчитываем рейтинг
            $score = ($K1 * $P1) + ($K2 * $P2) + ($K3 * $P3) + ($K4 * $P4);
//            $this->info($P1);
//            $this->info($P2);
//            $this->info($P3);
//            $this->info($P4);

            // Записываем в таблицу `ratings`
            Rating::updateOrCreate(
                ['master_id' => $master->id],
                [
                    'score' => $score,
                    'updated_at' => now()
                ]
            );
        }
        $this->getStarRating();
//        $this->info('Master ratings calculated successfully.');
    }

    // P1 — % кликов на ролик от числа показов в ленте
    private function calculateClickRate($userId, $fromDate)
    {
        $views = DB::table('video_views')
            ->join('videos', 'video_views.video_id', '=', 'videos.id')
            ->where('videos.user_id', $userId)
            ->where('video_views.created_at', '>=', $fromDate)
            ->count();
//        $this->info($views);

        $clicks =
            DB::table('video_clicks')
                ->join('videos', 'video_clicks.video_id', '=', 'videos.id')
                ->where('videos.user_id', $userId)
                ->where('video_clicks.created_at', '>=', $fromDate)
                ->count();
//        DB::table('profile_clicks')
//            ->where('master_id', $userId)
//            ->where('created_at', '>=', $fromDate)
//            ->count();
//        $this->info($clicks);

        return $views ? ($clicks / $views) * 100 : 0;
    }

    // P2 — % прочитанных запросов от общего количества полученных
    private function calculateReadRate($masterId, $fromDate)
    {

        $totalRequests = DB::table('orders')->where('master_id', $masterId)->where('created_at', '>=', $fromDate)->count();
        $readRequests = DB::table('orders')->where('master_id', $masterId)->where('is_read', true)->where('created_at', '>=', $fromDate)->count();
        return $totalRequests ? ($readRequests / $totalRequests) * 100 : 0;
    }

    // P3 — % ответов мастера от общего числа запросов
    private function calculateReplyRate($masterId, $fromDate)
    {
        $totalRequests = DB::table('orders')->where('master_id', $masterId)->where('created_at', '>=', $fromDate)->count();
        $replies = DB::table('orders')->where('master_id', $masterId)->whereIn('status_id', [2,3,4,5,10,14])->where('created_at', '>=', $fromDate)->count();
        return $totalRequests ? ($replies / $totalRequests) * 100 : 0;
    }

    // P4 — % заказов от общего числа отправленных ответов
    private function calculateOrderRate($masterId, $fromDate)
    {
        $totalReplies = DB::table('orders')->where('master_id', $masterId)->whereIn('status_id', [2,3,4,5,10,14])->where('created_at', '>=', $fromDate)->count();
//        $totalReplies = DB::table('replies')->where('master_id', $masterId)->where('created_at', '>=', $fromDate)->count();
        $orders = DB::table('orders')->where('master_id', $masterId)->whereIn('status_id', [4,14])->where('created_at', '>=', $fromDate)->count();
        return $totalReplies ? ($orders / $totalReplies) * 100 : 0;
    }
    
    private function getStarRating()
    {
        $ratings = Rating::all();
        $min = Rating::min('score'); // Минимальный рейтинг среди всех мастеров
        $max = Rating::max('score'); // Максимальный рейтинг среди всех мастеров
        foreach ($ratings as $rating) {
            if ($max - $min != 0){
                $rating->stars = round((($rating->score - $min) / ($max - $min)) * 5, 1);
                $rating->save();
            }
            else {
                $rating->stars = 5;
                $rating->save();
            }
        }

//        return round((($this->rating - $min) / ($max - $min)) * 5, 1);
    }
}
