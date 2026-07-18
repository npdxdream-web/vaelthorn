<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Village;
use App\Models\City;

class VillageSeeder extends Seeder
{
    public function run(): void
    {
        $villages = [
            'Aurantia' => [
                ['name' => 'Viente',      'description' => 'เมืองหลวงของ Aurantia ศูนย์กลางการปกครองของสภาตระกูล'],
                ['name' => 'Lafonte',     'description' => 'เมืองการค้าริมแม่น้ำ เต็มไปด้วยพ่อค้าและนักเดินทาง'],
                ['name' => 'Kingsbridge', 'description' => 'เมืองชายแดนที่มีสะพานโบราณเชื่อมดินแดน'],
            ],
            'Kalif' => [
                ['name' => 'Akancia', 'description' => 'เมืองหลวงของ Kalif ตลาดกลางทะเลทรายที่ไม่เคยหลับใหล'],
                ['name' => 'Sandune', 'description' => 'เมืองชายทะเลทรายที่เต็มไปด้วยนักผจญภัยและกองโจร'],
            ],
            'Silvaria' => [
                ['name' => 'Mokagi',      'description' => 'เมืองหลวงของ Silvaria ซ่อนอยู่กลางใจป่าโบราณ'],
                ['name' => 'Rootvale',    'description' => 'หมู่บ้านของผู้พิทักษ์ต้นไม้โบราณ'],
            ],
            'Frostwell' => [
                ['name' => 'Alasia',     'description' => 'เมืองหลวงของ Frostwell ป้อมปราการบนยอดเขาหิมะ'],
                ['name' => 'Glacierholt', 'description' => 'หมู่บ้านนักล่าที่อาศัยอยู่กลางพายุหิมะ'],
            ],
            'Kyoren' => [
                ['name' => 'Ainu',     'description' => 'เมืองหลวงของ Kyoren วัดเก่าแก่และตลาดจิตวิญญาณ'],
                ['name' => 'Misthaven', 'description' => 'หมู่บ้านในหมอกที่นักบวชและนักเดินทางพักพิง'],
            ],
            'Celestia' => [
                ['name' => 'The Nexus', 'description' => 'จุดบรรจบของทุกอาณาจักร ที่ทุกคนสามารถพบปะได้'],
            ],
        ];

        foreach ($villages as $cityName => $cityVillages) {
            $city = City::where('name', $cityName)->first();
            if (! $city) {
                continue;
            }
            foreach ($cityVillages as $v) {
                Village::firstOrCreate(
                    ['city_id' => $city->id, 'name' => $v['name']],
                    ['description' => $v['description']]
                );
            }
        }
    }
}
