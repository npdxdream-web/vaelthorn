<?php

/**
 * Leveling configuration.
 * exp_to_next[N] = total EXP required to advance from level N to N+1.
 * Level 0→1 is gated by onboarding stages (stage_a + stage_b), NOT raw exp.
 */
return [
    'exp_to_next' => [
        1 => 10,
        2 => 15,
        3 => 20,
        4 => 30,
        5 => 45,
        6 => 65,
        7 => 90,
        8 => 120,
        9 => 160,
    ],

    // Stage B: total exp earned in the onboarding event required before level-up to 1
    'stage_b_required_exp' => 6,

    // Stat points awarded to the character on each level-up (adjust without touching logic)
    'stat_points_per_level' => 3,

    // Max EXP from posts a character can earn per day, keyed by level (ICT/UTC+7 midnight reset)
    // Level 0 is exempt — onboarding must not be blocked by this cap
    'daily_exp_cap' => [
        1 => 10,
        2 => 15,
        3 => 20,
        4 => 30,
        5 => 45,
        6 => 60,
        7 => 80,
        8 => 110,
        9 => 150,
    ],
    'daily_exp_cap_default' => 200, // Level 10+
];
