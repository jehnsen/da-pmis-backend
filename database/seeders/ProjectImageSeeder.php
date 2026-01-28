<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProjectImage;
use App\Models\Project;
use App\Models\User;

class ProjectImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates realistic project images for CARAGA agricultural projects
     */
    public function run(): void
    {
        $projects = Project::all();
        $users = User::all();

        if ($projects->isEmpty() || $users->isEmpty()) {
            $this->command->warn('No projects or users found. Please run required seeders first.');
            return;
        }

        $imageDescriptions = [
            'cover' => [
                'Overview of the project site showing the main area of implementation',
                'Aerial view of the project location highlighting key features',
                'Project signboard at the main entrance',
                'Wide angle shot of the complete project facility',
            ],
            'progress' => [
                'Construction progress showing foundation work completed',
                'Installation of equipment in progress',
                'Planting activities being conducted by farmer beneficiaries',
                'Mid-term project implementation showing 60% completion',
                'Workers conducting excavation and site preparation',
                'Beneficiaries receiving training on new techniques',
                'Installation of irrigation systems underway',
            ],
            'before' => [
                'Site condition before project implementation',
                'Old facility before rehabilitation works',
                'Farm area before development and improvement',
                'Pre-project condition of the agricultural land',
            ],
            'after' => [
                'Completed project facility ready for operations',
                'Fully developed farm showing healthy crop growth',
                'Modernized facility after renovation and upgrades',
                'Transformed agricultural area after project completion',
            ],
            'documentation' => [
                'Project launch and groundbreaking ceremony',
                'Beneficiaries signing attendance during distribution',
                'Technical team conducting field inspection',
                'Regional Director visiting the project site',
                'Farmers participating in training session',
                'Harvest festival celebrating project success',
                'Equipment turnover ceremony with beneficiaries',
                'Project monitoring and evaluation team visit',
            ],
        ];

        $totalImages = 0;

        foreach ($projects as $project) {
            // Each project gets 3-8 images
            $imageCount = rand(3, 8);
            $imageTypes = [];

            // Always include a cover image
            $imageTypes[] = 'cover';

            // Add random other types
            $otherTypes = ['progress', 'documentation', 'before', 'after'];
            shuffle($otherTypes);
            $selectedTypes = array_slice($otherTypes, 0, $imageCount - 1);
            $imageTypes = array_merge($imageTypes, $selectedTypes);

            foreach ($imageTypes as $index => $imageType) {
                $descriptions = $imageDescriptions[$imageType];
                $caption = $descriptions[array_rand($descriptions)];

                // Generate realistic filename
                $projectSlug = strtolower(str_replace(' ', '-', substr($project->title, 0, 30)));
                $fileName = sprintf(
                    '%s-%s-%d.jpg',
                    $projectSlug,
                    $imageType,
                    rand(1000, 9999)
                );

                // File path in storage structure
                $filePath = sprintf(
                    'projects/%d/images/%s',
                    $project->id,
                    $fileName
                );

                // File size between 500KB and 5MB
                $fileSize = rand(500000, 5000000);

                ProjectImage::create([
                    'project_id' => $project->id,
                    'file_name' => $fileName,
                    'file_path' => $filePath,
                    'mime_type' => 'image/jpeg',
                    'file_size' => $fileSize,
                    'caption' => $caption,
                    'image_type' => $imageType,
                    'display_order' => $index + 1,
                    'uploaded_by' => $users->random()->id,
                    'created_at' => $project->created_at->copy()->addDays(rand(1, 30)),
                    'updated_at' => now(),
                ]);

                $totalImages++;
            }
        }

        $this->command->info("Created {$totalImages} project images across all projects!");
    }
}
