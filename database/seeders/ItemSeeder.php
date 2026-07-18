<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // Weapons
            ['name' => 'Iron Sword',        'type' => 'weapon',     'rarity' => 'common',    'description' => 'ดาบเหล็กธรรมดา เหมาะสำหรับนักรบมือใหม่',                 'bonus_str' => 5,  'bonus_agi' => 0,  'bonus_int' => 0,  'bonus_hp' => 0,   'bonus_mana' => 0,  'is_tradeable' => true],
            ['name' => 'Silver Blade',      'type' => 'weapon',     'rarity' => 'uncommon',  'description' => 'ดาบเงินคมกริบ เพิ่มพลังโจมตีอย่างเห็นได้ชัด',              'bonus_str' => 12, 'bonus_agi' => 3,  'bonus_int' => 0,  'bonus_hp' => 0,   'bonus_mana' => 0,  'is_tradeable' => true],
            ['name' => 'Arcane Staff',      'type' => 'weapon',     'rarity' => 'uncommon',  'description' => 'ไม้เท้าเวทย์มนตร์ เพิ่มพลังเวทย์และ Mana สูงสุด',          'bonus_str' => 0,  'bonus_agi' => 0,  'bonus_int' => 15, 'bonus_hp' => 0,   'bonus_mana' => 30, 'is_tradeable' => true],
            ['name' => 'Shadow Dagger',     'type' => 'weapon',     'rarity' => 'rare',      'description' => 'มีดสั้นเงา ของนักฆ่าแห่ง Kalif สร้างพลังโจมตีไว',          'bonus_str' => 8,  'bonus_agi' => 18, 'bonus_int' => 0,  'bonus_hp' => 0,   'bonus_mana' => 0,  'is_tradeable' => true],
            ['name' => 'Legendary Katana',  'type' => 'weapon',     'rarity' => 'legendary', 'description' => 'ดาบญี่ปุ่นในตำนานจาก Kyoren มีพลังทั้งกายและจิต',          'bonus_str' => 25, 'bonus_agi' => 15, 'bonus_int' => 10, 'bonus_hp' => 0,   'bonus_mana' => 0,  'is_tradeable' => false],

            // Armor
            ['name' => 'Leather Vest',      'type' => 'armor',      'rarity' => 'common',    'description' => 'เกราะหนังบางๆ ป้องกันพื้นฐาน',                              'bonus_str' => 0,  'bonus_agi' => 2,  'bonus_int' => 0,  'bonus_hp' => 20,  'bonus_mana' => 0,  'is_tradeable' => true],
            ['name' => 'Chain Mail',        'type' => 'armor',      'rarity' => 'uncommon',  'description' => 'เกราะโซ่ ป้องกันได้ดีพอสมควร',                              'bonus_str' => 3,  'bonus_agi' => 0,  'bonus_int' => 0,  'bonus_hp' => 45,  'bonus_mana' => 0,  'is_tradeable' => true],
            ['name' => 'Mage Robe',         'type' => 'armor',      'rarity' => 'uncommon',  'description' => 'เสื้อคลุมของนักเวทย์ เพิ่ม Mana สูงสุดและ INT',             'bonus_str' => 0,  'bonus_agi' => 0,  'bonus_int' => 8,  'bonus_hp' => 10,  'bonus_mana' => 50, 'is_tradeable' => true],
            ['name' => 'Frost Plate',       'type' => 'armor',      'rarity' => 'rare',      'description' => 'เกราะน้ำแข็งจาก Frostwell ป้องกันความหนาวและการโจมตี',       'bonus_str' => 5,  'bonus_agi' => 0,  'bonus_int' => 0,  'bonus_hp' => 80,  'bonus_mana' => 0,  'is_tradeable' => true],

            // Consumables
            ['name' => 'Health Potion',     'type' => 'consumable', 'rarity' => 'common',    'description' => 'ยาฟื้นฟู HP พื้นฐาน ใช้ในสนามรบหรือหลังการต่อสู้',           'bonus_str' => 0,  'bonus_agi' => 0,  'bonus_int' => 0,  'bonus_hp' => 0,   'bonus_mana' => 0,  'is_tradeable' => true],
            ['name' => 'Mana Crystal',      'type' => 'consumable', 'rarity' => 'common',    'description' => 'คริสตัลเวทย์ ฟื้นฟู Mana ทันที',                             'bonus_str' => 0,  'bonus_agi' => 0,  'bonus_int' => 0,  'bonus_hp' => 0,   'bonus_mana' => 0,  'is_tradeable' => true],
            ['name' => 'Elixir of Strength','type' => 'consumable', 'rarity' => 'uncommon',  'description' => 'ยาเพิ่มพลังชั่วคราว ผลิตโดยนักเล่นแร่แปรธาตุแห่ง Aurantia', 'bonus_str' => 0,  'bonus_agi' => 0,  'bonus_int' => 0,  'bonus_hp' => 0,   'bonus_mana' => 0,  'is_tradeable' => true],

            // Materials
            ['name' => 'Iron Ore',          'type' => 'material',   'rarity' => 'common',    'description' => 'แร่เหล็กดิบ ใช้ในการตีเครื่องมือและอาวุธ',                   'bonus_str' => 0,  'bonus_agi' => 0,  'bonus_int' => 0,  'bonus_hp' => 0,   'bonus_mana' => 0,  'is_tradeable' => true],
            ['name' => 'Forest Wood',       'type' => 'material',   'rarity' => 'common',    'description' => 'ไม้จากป่า Silvaria มีพลังธรรมชาติแฝงอยู่',                   'bonus_str' => 0,  'bonus_agi' => 0,  'bonus_int' => 0,  'bonus_hp' => 0,   'bonus_mana' => 0,  'is_tradeable' => true],
            ['name' => 'Frost Crystal',     'type' => 'material',   'rarity' => 'uncommon',  'description' => 'คริสตัลน้ำแข็งจาก Frostwell ใช้ในการผสมอาวุธและเกราะ',       'bonus_str' => 0,  'bonus_agi' => 0,  'bonus_int' => 0,  'bonus_hp' => 0,   'bonus_mana' => 0,  'is_tradeable' => true],
            ['name' => 'Dragon Scale',      'type' => 'material',   'rarity' => 'epic',      'description' => 'เกล็ดมังกรหายาก วัสดุที่แข็งแกร่งที่สุดในโลก',               'bonus_str' => 0,  'bonus_agi' => 0,  'bonus_int' => 0,  'bonus_hp' => 0,   'bonus_mana' => 0,  'is_tradeable' => true],

            // Key Items
            ['name' => 'City Pass',         'type' => 'key_item',   'rarity' => 'uncommon',  'description' => 'บัตรผ่านเข้าเมือง ต้องใช้เพื่อเข้าดินแดนล็อค',               'bonus_str' => 0,  'bonus_agi' => 0,  'bonus_int' => 0,  'bonus_hp' => 0,   'bonus_mana' => 0,  'is_tradeable' => false],
            ['name' => 'Ancient Relic',     'type' => 'key_item',   'rarity' => 'legendary', 'description' => 'โบราณวัตถุเก่าแก่ มีพลังลึกลับที่ยังไม่ถูกค้นพบ',            'bonus_str' => 0,  'bonus_agi' => 0,  'bonus_int' => 0,  'bonus_hp' => 0,   'bonus_mana' => 0,  'is_tradeable' => false],
        ];

        foreach ($items as $data) {
            Item::firstOrCreate(['name' => $data['name']], $data);
        }
    }
}
