<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supply;

class SuppliesSeeder extends Seeder
{
    public function run(): void
    {
        $supplies = [
            ['name' => 'Tissu coton 1m', 'category' => 'Tissus', 'unit' => 'm', 'image_url' => '/images/supplies/tissu-coton.svg'],
            ['name' => 'Fil polyester 100m', 'category' => 'Fils', 'unit' => 'pcs', 'image_url' => '/images/supplies/fil-polyester.svg'],
            ['name' => 'Bouton 15mm', 'category' => 'Accessoires', 'unit' => 'pcs', 'image_url' => '/images/supplies/bouton.svg'],
            ['name' => 'Fermeture éclair 20cm', 'category' => 'Accessoires', 'unit' => 'pcs', 'image_url' => '/images/supplies/zipper.svg'],
            ['name' => 'Aiguille de couture', 'category' => 'Accessoires', 'unit' => 'pcs', 'image_url' => '/images/supplies/aiguille.svg'],
            ['name' => 'Élastique 3cm', 'category' => 'Accessoires', 'unit' => 'm', 'image_url' => '/images/supplies/elastique.svg'],
            ['name' => 'Biais 20mm', 'category' => 'Accessoires', 'unit' => 'm', 'image_url' => '/images/supplies/biais.svg'],
        ];

        foreach ($supplies as $item) {
            // Use updateOrCreate to ensure existing records get their image_url and other attributes updated
            Supply::updateOrCreate(['name' => $item['name']], $item);
        }

        $this->command->info(count($supplies).' fournitures ajoutées.');
    }
}
