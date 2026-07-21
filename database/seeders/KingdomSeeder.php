<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kingdom;

class KingdomSeeder extends Seeder
{
    public function run(): void
    {
        $kingdoms = [
            [
                'name'        => 'Silvaria',
                'description' => 'The Emerald Dominion — ดินแดนป่าไม้และเวทมนตร์ ราชอาณาจักรของนักเวทและผู้พิทักษ์ธรรมชาติ',
                'color'       => '#4ade80',
                'icon'        => '🌲',
                'is_active'   => true,
            ],
            [
                'name'        => 'Aurantia',
                'description' => 'The Golden Dominion — ดินแดนแห่งอัศวิน กฎหมาย และความยุติธรรม เมืองหลวงแห่งความรุ่งเรือง',
                'color'       => '#D4AF37',
                'icon'        => '⚔️',
                'is_active'   => true,
            ],
            [
                'name'        => 'Kalif',
                'description' => 'The Sandstone Sovereignty — ดินแดนทะเลทรายแห่งนักฆ่า พ่อค้า และความลึกลับ ตลาดกลางแห่งโลก',
                'color'       => '#fb923c',
                'icon'        => '🗡️',
                'is_active'   => true,
            ],
            [
                'name'        => 'Frostwell',
                'description' => 'The Frozen Throne — ดินแดนหิมะแห่งนักรบและนักล่า ผู้แกร่งกล้าในความหนาวเหน็บ',
                'color'       => '#60a5fa',
                'icon'        => '❄️',
                'is_active'   => true,
            ],
            [
                'name'        => 'Kyoren',
                'description' => 'The Eastern Sanctum — ดินแดนตะวันออกแห่งจิตวิญญาณและปรัชญา นักรบพระเอกและนักพรต',
                'color'       => '#a78bfa',
                'icon'        => '⛩️',
                'is_active'   => true,
            ],
            [
                'name'        => 'Celestia',
                'description' => 'The Neutral Ground — จุดบรรจบของทุกอาณาจักร สถานที่ศักดิ์สิทธิ์ที่ทุกคนสามารถเดินทางมาได้',
                'color'       => '#c8a84b',
                'icon'        => '✦',
                'is_active'   => true,
            ],
        ];

        foreach ($kingdoms as $data) {
            Kingdom::firstOrCreate(['name' => $data['name']], $data);
        }
    }
}
