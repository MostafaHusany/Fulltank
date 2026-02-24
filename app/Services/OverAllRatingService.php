<?php

namespace App\Services;

class OverAllRatingService {

    public function UpdateRating($target)
    {
        $reviews = [
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
        ];

        foreach ($reviews as $key => $value) {
            $rating_count = $target->reviews()->where('star_rating', $key)->count();
            $reviews[$key] = $rating_count;
        }

        $star_calculation = $reviews[1]*1+$reviews[2]*2+$reviews[3]*3+$reviews[4]*4+$reviews[5]*5;

        $overall_rating = $star_calculation / $target->reviews()->count();

        $target->update([
           'star_rating_avg' => number_format($overall_rating, 1)
        ]);
    }
}
