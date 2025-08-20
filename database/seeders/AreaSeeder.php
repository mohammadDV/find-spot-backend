<?php

namespace Database\Seeders;

use Domain\Address\Models\Area;
use Illuminate\Database\Seeder;

class AreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $istanbulAreas = [
            ['id' => 1, 'title' => 'Adalar', 'city_id' => 1, 'status' => 1],
            ['id' => 2, 'title' => 'Aksaray', 'city_id' => 1, 'status' => 1],
            ['id' => 3, 'title' => 'Arnavutköy', 'city_id' => 1, 'status' => 1],
            ['id' => 4, 'title' => 'Ataşehir', 'city_id' => 1, 'status' => 1],
            ['id' => 5, 'title' => 'Avcılar', 'city_id' => 1, 'status' => 1],
            ['id' => 6, 'title' => 'Bahçelievler', 'city_id' => 1, 'status' => 1],
            ['id' => 7, 'title' => 'Bağcılar', 'city_id' => 1, 'status' => 1],
            ['id' => 8, 'title' => 'Bakırköy', 'city_id' => 1, 'status' => 1],
            ['id' => 9, 'title' => 'Başakşehir', 'city_id' => 1, 'status' => 1],
            ['id' => 10, 'title' => 'Bayrampaşa', 'city_id' => 1, 'status' => 1],
            ['id' => 11, 'title' => 'Beşiktaş', 'city_id' => 1, 'status' => 1],
            ['id' => 12, 'title' => 'Beyoğlu', 'city_id' => 1, 'status' => 1],
            ['id' => 13, 'title' => 'Beykoz', 'city_id' => 1, 'status' => 1],
            ['id' => 14, 'title' => 'Beylikdüzü', 'city_id' => 1, 'status' => 1],
            ['id' => 15, 'title' => 'Bursa', 'city_id' => 1, 'status' => 1],
            ['id' => 16, 'title' => 'Büyükçekmece', 'city_id' => 1, 'status' => 1],
            ['id' => 17, 'title' => 'Çatalca', 'city_id' => 1, 'status' => 1],
            ['id' => 18, 'title' => 'Çekmeköy', 'city_id' => 1, 'status' => 1],
            ['id' => 19, 'title' => 'Çilingir', 'city_id' => 1, 'status' => 1],
            ['id' => 20, 'title' => 'Eyüpsultan', 'city_id' => 1, 'status' => 1],
            ['id' => 21, 'title' => 'Esenler', 'city_id' => 1, 'status' => 1],
            ['id' => 22, 'title' => 'Esenyurt', 'city_id' => 1, 'status' => 1],
            ['id' => 23, 'title' => 'Fatih', 'city_id' => 1, 'status' => 1],
            ['id' => 24, 'title' => 'Gaziosmanpaşa', 'city_id' => 1, 'status' => 1],
            ['id' => 25, 'title' => 'Güngören', 'city_id' => 1, 'status' => 1],
            ['id' => 26, 'title' => 'Kadıköy', 'city_id' => 1, 'status' => 1],
            ['id' => 27, 'title' => 'Kağıthane', 'city_id' => 1, 'status' => 1],
            ['id' => 28, 'title' => 'Kartal', 'city_id' => 1, 'status' => 1],
            ['id' => 29, 'title' => 'Kocaeli', 'city_id' => 1, 'status' => 1],
            ['id' => 30, 'title' => 'Küçükçekmece', 'city_id' => 1, 'status' => 1],
            ['id' => 31, 'title' => 'Maltepe', 'city_id' => 1, 'status' => 1],
            ['id' => 32, 'title' => 'Ortaköy', 'city_id' => 1, 'status' => 1],
            ['id' => 33, 'title' => 'Pendik', 'city_id' => 1, 'status' => 1],
            ['id' => 34, 'title' => 'Sancaktepe', 'city_id' => 1, 'status' => 1],
            ['id' => 35, 'title' => 'Sarıyer', 'city_id' => 1, 'status' => 1],
            ['id' => 36, 'title' => 'Selçuk', 'city_id' => 1, 'status' => 1],
            ['id' => 37, 'title' => 'Silivri', 'city_id' => 1, 'status' => 1],
            ['id' => 38, 'title' => 'Şile', 'city_id' => 1, 'status' => 1],
            ['id' => 39, 'title' => 'Şişli', 'city_id' => 1, 'status' => 1],
            ['id' => 40, 'title' => 'Şamlar', 'city_id' => 1, 'status' => 1],
            ['id' => 41, 'title' => 'Sultanbeyli', 'city_id' => 1, 'status' => 1],
            ['id' => 42, 'title' => 'Sultanahmet', 'city_id' => 1, 'status' => 1],
            ['id' => 43, 'title' => 'Sultangazi', 'city_id' => 1, 'status' => 1],
            ['id' => 44, 'title' => 'Taksim', 'city_id' => 1, 'status' => 1],
            ['id' => 45, 'title' => 'Tuzla', 'city_id' => 1, 'status' => 1],
            ['id' => 46, 'title' => 'Ümraniye', 'city_id' => 1, 'status' => 1],
            ['id' => 47, 'title' => 'Üsküdar', 'city_id' => 1, 'status' => 1],
            ['id' => 48, 'title' => 'Zeytinburnu', 'city_id' => 1, 'status' => 1],
        ];

        foreach ($istanbulAreas as $area) {
            Area::create([
                'id' => $area['id'],
                'title' => $area['title'],
                'image' => null,
                'status' => $area['status'],
                'city_id' => $area['city_id'],
                'created_at' => '2023-11-07 02:06:39',
                'updated_at' => '2023-11-07 02:06:39',
            ]);
        }
    }
}