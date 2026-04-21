<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Categorias
        $categories = [
            ['name' => 'Camisetas',    'slug' => 'camisetas'],
            ['name' => 'Calçados',     'slug' => 'calcados'],
            ['name' => 'Acessórios',   'slug' => 'acessorios'],
            ['name' => 'Eletrônicos',  'slug' => 'eletronicos'],
        ];

        $createdCategories = [];
        foreach ($categories as $cat) {
            $createdCategories[$cat['slug']] = Category::firstOrCreate(
                ['slug' => $cat['slug']],
                ['name' => $cat['name'], 'is_active' => true]
            );
        }

        // Produtos com imagens do Unsplash (via picsum.photos para confiabilidade)
        $products = [
            [
                'name'              => 'Camiseta Básica Branca',
                'slug'              => 'camiseta-basica-branca',
                'category'          => 'camisetas',
                'price'             => 59.90,
                'quantity'          => 120,
                'image_url'         => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=600&q=80',
                'short_description' => '100% algodão, corte regular, disponível em vários tamanhos.',
                'description'       => 'Camiseta básica confeccionada em algodão 100% natural. Tecido macio e respirável, ideal para o dia a dia. Disponível nos tamanhos P, M, G e GG.',
            ],
            [
                'name'              => 'Camiseta Oversized Preta',
                'slug'              => 'camiseta-oversized-preta',
                'category'          => 'camisetas',
                'price'             => 89.90,
                'quantity'          => 80,
                'image_url'         => 'https://images.unsplash.com/photo-1503341455253-b2e723bb3dbb?w=600&q=80',
                'short_description' => 'Estilo oversized, perfeita para looks casuais.',
                'description'       => 'Camiseta oversized em algodão premium. Design moderno e confortável, com caimento levemente largo para um visual atual. Ideal para combinar com jeans ou shorts.',
            ],
            [
                'name'              => 'Tênis Running Pro',
                'slug'              => 'tenis-running-pro',
                'category'          => 'calcados',
                'price'             => 349.90,
                'quantity'          => 45,
                'image_url'         => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600&q=80',
                'short_description' => 'Amortecimento superior para corridas longas.',
                'description'       => 'Tênis desenvolvido para corredores que buscam conforto e performance. Solado com amortecimento EVA, cabedal em mesh respirável e entressola de alta resiliência.',
            ],
            [
                'name'              => 'Tênis Casual Urban',
                'slug'              => 'tenis-casual-urban',
                'category'          => 'calcados',
                'price'             => 249.90,
                'quantity'          => 60,
                'image_url'         => 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=600&q=80',
                'short_description' => 'Design moderno para o dia a dia urbano.',
                'description'       => 'Tênis casual com design clean e versátil. Solado de borracha antiderrapante, palmilha removível e cabedal em couro sintético de alta durabilidade.',
            ],
            [
                'name'              => 'Relógio Minimalista',
                'slug'              => 'relogio-minimalista',
                'category'          => 'acessorios',
                'price'             => 299.90,
                'quantity'          => 35,
                'image_url'         => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&q=80',
                'short_description' => 'Design clean, resistente à água até 30m.',
                'description'       => 'Relógio de pulso com design minimalista e elegante. Caixa em aço inoxidável, pulseira em couro genuíno e resistência à água de 30 metros. Ideal para qualquer ocasião.',
            ],
            [
                'name'              => 'Mochila Notebook 15"',
                'slug'              => 'mochila-notebook-15',
                'category'          => 'acessorios',
                'price'             => 189.90,
                'quantity'          => 55,
                'image_url'         => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=600&q=80',
                'short_description' => 'Compartimento acolchoado para notebook até 15".',
                'description'       => 'Mochila resistente e funcional com compartimento principal para notebook de até 15 polegadas. Múltiplos bolsos organizadores, alças acolchoadas e material impermeável.',
            ],
            [
                'name'              => 'Fone Bluetooth Premium',
                'slug'              => 'fone-bluetooth-premium',
                'category'          => 'eletronicos',
                'price'             => 499.90,
                'quantity'          => 28,
                'image_url'         => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&q=80',
                'short_description' => 'Cancelamento de ruído ativo, 30h de bateria.',
                'description'       => 'Fone de ouvido over-ear com cancelamento ativo de ruído (ANC). Conexão Bluetooth 5.2, bateria de 30 horas, driver de 40mm para graves encorpados e agudos nítidos.',
            ],
            [
                'name'              => 'Smartwatch Fitness',
                'slug'              => 'smartwatch-fitness',
                'category'          => 'eletronicos',
                'price'             => 599.90,
                'quantity'          => 22,
                'image_url'         => 'https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?w=600&q=80',
                'short_description' => 'Monitor cardíaco, GPS e 7 dias de bateria.',
                'description'       => 'Smartwatch completo para monitorar sua saúde e atividades físicas. GPS integrado, monitor cardíaco 24h, SpO2, mais de 100 modos esportivos e resistência à água de 50 metros.',
            ],
        ];

        foreach ($products as $data) {
            $category = $createdCategories[$data['category']];

            Product::firstOrCreate(
                ['slug' => $data['slug']],
                [
                    'category_id'       => $category->id,
                    'name'              => $data['name'],
                    'description'       => $data['description'],
                    'short_description' => $data['short_description'],
                    'price'             => $data['price'],
                    'cost_price'        => round($data['price'] * 0.6, 2),
                    'sku'               => strtoupper(Str::random(8)),
                    'quantity'          => $data['quantity'],
                    'min_quantity_alert' => 5,
                    'image_url'         => $data['image_url'],
                    'is_active'         => true,
                ]
            );
        }

        $this->command->info('✓ ' . count($products) . ' produtos criados com sucesso.');
    }
}
