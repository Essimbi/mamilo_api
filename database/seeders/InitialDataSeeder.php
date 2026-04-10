<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Event;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InitialDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Christian Mamilo',
            'email' => 'admin@mamilo.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'bio' => 'Chercheur et expert en intelligence artificielle et éthique numérique.',
        ]);

        // Create editor user
        $editor = User::create([
            'name' => 'Éditeur Test',
            'email' => 'editor@mamilo.com',
            'password' => Hash::make('password'),
            'role' => 'editor',
            'bio' => 'Éditeur de contenu pour le blog Mamilo.',
        ]);

        // Create categories
        $categories = [
            [
                'name' => 'Intelligence Artificielle',
                'slug' => 'intelligence-artificielle',
                'description' => 'Articles sur l\'IA, le machine learning et les technologies émergentes.',
            ],
            [
                'name' => 'Éthique Numérique',
                'slug' => 'ethique-numerique',
                'description' => 'Réflexions sur l\'éthique dans le monde numérique.',
            ],
            [
                'name' => 'Recherche',
                'slug' => 'recherche',
                'description' => 'Publications et travaux de recherche académique.',
            ],
            [
                'name' => 'Technologie',
                'slug' => 'technologie',
                'description' => 'Actualités et analyses technologiques.',
            ],
        ];

        foreach ($categories as $categoryData) {
            Category::create($categoryData);
        }

        // Create tags
        $tags = [
            ['name' => 'IA', 'slug' => 'ia'],
            ['name' => 'Machine Learning', 'slug' => 'machine-learning'],
            ['name' => 'Éthique', 'slug' => 'ethique'],
            ['name' => 'Big Data', 'slug' => 'big-data'],
            ['name' => 'Blockchain', 'slug' => 'blockchain'],
            ['name' => 'IoT', 'slug' => 'iot'],
            ['name' => 'Cybersécurité', 'slug' => 'cybersecurite'],
            ['name' => 'Cloud Computing', 'slug' => 'cloud-computing'],
        ];

        foreach ($tags as $tagData) {
            Tag::create($tagData);
        }

        // Create sample articles
        $iaCategory = Category::where('slug', 'intelligence-artificielle')->first();
        $ethiqueCategory = Category::where('slug', 'ethique-numerique')->first();
        
        $iaTag = Tag::where('slug', 'ia')->first();
        $ethiqueTag = Tag::where('slug', 'ethique')->first();

        $article1 = Article::create([
            'title' => 'Les défis éthiques de l\'intelligence artificielle',
            'slug' => 'defis-ethiques-intelligence-artificielle',
            'excerpt' => 'Une exploration des questions éthiques soulevées par le développement rapide de l\'IA.',
            'status' => 'published',
            'published_at' => now()->subDays(5),
            'author_id' => $admin->id,
            'reading_time' => 8,
            'likes_count' => 42,
        ]);

        $article1->categories()->attach([$iaCategory->id, $ethiqueCategory->id]);
        $article1->tags()->attach([$iaTag->id, $ethiqueTag->id]);

        // Add content blocks to article 1
        $article1->blocks()->createMany([
            [
                'type' => 'heading',
                'content' => ['text' => 'Introduction', 'level' => 2],
                'position' => 1,
            ],
            [
                'type' => 'paragraph',
                'content' => ['text' => 'L\'intelligence artificielle transforme notre société à une vitesse sans précédent. Cependant, cette révolution technologique soulève de nombreuses questions éthiques fondamentales.'],
                'position' => 2,
            ],
            [
                'type' => 'heading',
                'content' => ['text' => 'Les principaux enjeux', 'level' => 2],
                'position' => 3,
            ],
            [
                'type' => 'paragraph',
                'content' => ['text' => 'Parmi les défis majeurs, on trouve la protection de la vie privée, la transparence des algorithmes, et l\'équité dans les décisions automatisées.'],
                'position' => 4,
            ],
        ]);

        // Add SEO metadata
        $article1->seo()->create([
            'meta_title' => 'Les défis éthiques de l\'IA - Blog Mamilo',
            'meta_description' => 'Découvrez les principales questions éthiques soulevées par l\'intelligence artificielle et comment y répondre.',
            'og_title' => 'Les défis éthiques de l\'intelligence artificielle',
            'og_description' => 'Une exploration des questions éthiques soulevées par le développement rapide de l\'IA.',
        ]);

        $article2 = Article::create([
            'title' => 'Machine Learning : Guide pour débutants',
            'slug' => 'machine-learning-guide-debutants',
            'excerpt' => 'Un guide complet pour comprendre les bases du machine learning et ses applications.',
            'status' => 'published',
            'published_at' => now()->subDays(10),
            'author_id' => $admin->id,
            'reading_time' => 12,
            'likes_count' => 67,
        ]);

        $article2->categories()->attach([$iaCategory->id]);
        $article2->tags()->attach([Tag::where('slug', 'machine-learning')->first()->id]);

        // Create sample events
        Event::create([
            'title' => 'Conférence IA & Éthique 2026',
            'slug' => 'conference-ia-ethique-2026',
            'description' => '<p>Une conférence internationale sur les enjeux éthiques de l\'intelligence artificielle.</p><p>Rejoignez-nous pour deux jours de discussions et d\'échanges avec des experts du monde entier.</p>',
            'location' => 'Paris, France',
            'event_date' => now()->addMonths(2),
            'status' => 'upcoming',
            'likes_count' => 23,
        ]);

        Event::create([
            'title' => 'Workshop Machine Learning',
            'slug' => 'workshop-machine-learning',
            'description' => '<p>Atelier pratique sur les techniques avancées de machine learning.</p>',
            'location' => 'Lyon, France',
            'event_date' => now()->subMonths(1),
            'status' => 'past',
            'likes_count' => 15,
        ]);

        // Create site settings
        Setting::create([
            'key' => 'site_name',
            'value' => 'Blog Mamilo',
        ]);

        Setting::create([
            'key' => 'site_description',
            'value' => 'Blog sur l\'intelligence artificielle, l\'éthique numérique et la recherche technologique.',
        ]);

        Setting::create([
            'key' => 'contact_email',
            'value' => 'contact@mamilo.com',
        ]);

        $this->command->info('✅ Données initiales créées avec succès !');
        $this->command->info('');
        $this->command->info('👤 Utilisateurs créés :');
        $this->command->info('   Admin: admin@mamilo.com / password');
        $this->command->info('   Éditeur: editor@mamilo.com / password');
    }
}
