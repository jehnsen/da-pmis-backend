<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NewsUpdate;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NewsUpdateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates realistic news updates about CARAGA agricultural initiatives and programs
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Get a user to be the creator (preferably admin or director)
            $admin = User::where('username', 'admin')->first();
            $director = User::where('username', 'mrodriguez')->first();
            $creator = $admin ?? $director ?? User::first();

            $newsUpdates = [
                [
                    'title' => 'DA-CARAGA Distributes 50,000 Bags of Certified Rice Seeds to Agusan Farmers',
                    'content' => 'The Department of Agriculture in CARAGA Region has successfully distributed 50,000 bags of certified hybrid rice seeds to farmers in Agusan del Norte and Agusan del Sur provinces. This initiative is part of the Rice Production Enhancement Program aimed at increasing rice productivity and ensuring food security in the region. The distribution ceremony was attended by local government officials, farmer cooperatives, and DA-CARAGA personnel. "This seed distribution will significantly boost our rice production and help our farmers achieve better yields," said Regional Director Maria Santos-Rodriguez.',
                    'image_url' => '/storage/news/rice-seed-distribution.jpg',
                    'published_at' => Carbon::now()->subDays(5),
                    'is_featured' => true,
                    'created_by' => $creator->id ?? 1,
                    'created_at' => Carbon::now()->subDays(5),
                    'updated_at' => Carbon::now()->subDays(5),
                ],
                [
                    'title' => 'CARAGA Cacao Industry Shows Promising Growth with 800 New Farmer Beneficiaries',
                    'content' => 'The CARAGA cacao industry continues to flourish with 800 new farmer beneficiaries joining the Cacao Development Program in Surigao del Sur. The program has distributed quality cacao seedlings and established fermentation and drying facilities to ensure premium chocolate production. DA-CARAGA has also facilitated partnerships with major chocolate manufacturers to provide assured markets for cacao farmers. With 1,500 hectares now planted with cacao, CARAGA is positioning itself as a major player in the Philippine cacao industry.',
                    'image_url' => '/storage/news/cacao-development.jpg',
                    'published_at' => Carbon::now()->subDays(12),
                    'is_featured' => true,
                    'created_by' => $creator->id ?? 1,
                    'created_at' => Carbon::now()->subDays(12),
                    'updated_at' => Carbon::now()->subDays(12),
                ],
                [
                    'title' => 'Farm-to-Market Road Project Completed in Agusan del Sur',
                    'content' => 'A 15-kilometer farm-to-market road project connecting agricultural production areas in Agusan del Sur to the provincial highway has been successfully completed. The newly constructed concrete road will benefit over 2,000 farming families and reduce transportation costs for agricultural products by 30%. The project, funded through DA-CARAGA\'s infrastructure development program, includes proper drainage systems and road safety features. Local farmers expressed their gratitude, stating that the road will significantly improve their access to markets and reduce post-harvest losses.',
                    'image_url' => '/storage/news/farm-road-completion.jpg',
                    'published_at' => Carbon::now()->subDays(18),
                    'is_featured' => false,
                    'created_by' => $creator->id ?? 1,
                    'created_at' => Carbon::now()->subDays(18),
                    'updated_at' => Carbon::now()->subDays(18),
                ],
                [
                    'title' => 'Dinagat Islands Revitalizes Abaca Industry with Disease-Resistant Varieties',
                    'content' => 'The abaca industry in Dinagat Islands is experiencing revitalization through the introduction of tissue-cultured, bunchy top disease-resistant planting materials. DA-CARAGA has distributed thousands of abaca seedlings to 600 farmers across the province, along with training on modern abaca production techniques. The program also includes the establishment of decorticating facilities to improve fiber quality and value. This initiative is expected to restore Dinagat Islands\' position as a major abaca fiber producer in the Philippines.',
                    'image_url' => '/storage/news/abaca-revival.jpg',
                    'published_at' => Carbon::now()->subDays(25),
                    'is_featured' => false,
                    'created_by' => $creator->id ?? 1,
                    'created_at' => Carbon::now()->subDays(25),
                    'updated_at' => Carbon::now()->subDays(25),
                ],
                [
                    'title' => 'Coffee Production Thrives in CARAGA Mountain Communities',
                    'content' => 'Coffee production in CARAGA\'s upland communities is showing remarkable progress with the establishment of 800 hectares of arabica and robusta coffee plantations. The Coffee Production and Processing Project has provided improved coffee varieties, processing equipment, and barista training to mountain farmers. The program not only promotes sustainable agriculture but also supports eco-tourism development in the region. Several coffee shops featuring locally-produced CARAGA coffee have opened, creating additional income opportunities for farming communities.',
                    'image_url' => '/storage/news/coffee-project.jpg',
                    'published_at' => Carbon::now()->subDays(32),
                    'is_featured' => true,
                    'created_by' => $creator->id ?? 1,
                    'created_at' => Carbon::now()->subDays(32),
                    'updated_at' => Carbon::now()->subDays(32),
                ],
                [
                    'title' => 'DA-CARAGA Launches Biosecurity Program to Protect Swine Industry',
                    'content' => 'In response to African Swine Fever (ASF) concerns, DA-CARAGA has launched a comprehensive biosecurity enhancement program for swine producers. The program includes construction of 50 biosecure piggery units, provision of quality breeding stock, and training on proper waste management and disease prevention. "Protecting our swine industry is crucial for food security and the livelihoods of our hog raisers," emphasized the Regional Director. The program targets 250 small-scale swine raisers across CARAGA provinces with strict biosecurity protocols.',
                    'image_url' => '/storage/news/swine-biosecurity.jpg',
                    'published_at' => Carbon::now()->subDays(40),
                    'is_featured' => false,
                    'created_by' => $creator->id ?? 1,
                    'created_at' => Carbon::now()->subDays(40),
                    'updated_at' => Carbon::now()->subDays(40),
                ],
                [
                    'title' => 'Mariculture Development Boosts Income of Surigao Coastal Communities',
                    'content' => 'Coastal fisherfolk families in Surigao del Norte are benefiting from the mariculture development program that has installed 100 marine cages for milkfish, grouper, and seaweed farming. The program provides complete starter packages including fingerlings, feeds, and technical assistance. Post-harvest handling facilities have also been established to maintain product quality and access premium markets. "This mariculture project has transformed our lives, providing stable income even during closed fishing seasons," shared a local fisherfolk leader.',
                    'image_url' => '/storage/news/mariculture-project.jpg',
                    'published_at' => Carbon::now()->subDays(47),
                    'is_featured' => false,
                    'created_by' => $creator->id ?? 1,
                    'created_at' => Carbon::now()->subDays(47),
                    'updated_at' => Carbon::now()->subDays(47),
                ],
                [
                    'title' => 'Organic Agriculture Certification Program Gains Momentum in CARAGA',
                    'content' => 'The Organic Agriculture Development and Certification Program in CARAGA has certified 500 hectares of organic farms with 300 farmers receiving official organic certification. The program provides comprehensive training on organic production methods, provision of organic inputs, and assistance in accessing organic markets. DA-CARAGA has partnered with organic product retailers and exporters to ensure profitable market linkages. The program aims to certify 2,000 hectares of organic farms by 2026, positioning CARAGA as a major organic agriculture producer.',
                    'image_url' => '/storage/news/organic-farming.jpg',
                    'published_at' => Carbon::now()->subDays(54),
                    'is_featured' => false,
                    'created_by' => $creator->id ?? 1,
                    'created_at' => Carbon::now()->subDays(54),
                    'updated_at' => Carbon::now()->subDays(54),
                ],
                [
                    'title' => 'Agricultural Mechanization Program Delivers 70 Units of Farm Equipment',
                    'content' => 'DA-CARAGA has distributed 70 units of modern agricultural machinery including hand tractors, 4-wheel tractors, rice transplanters, and mechanical dryers to farmer cooperatives across the region. The Farm Machinery and Equipment Distribution Program aims to increase farming efficiency, reduce labor costs, and improve productivity. Operator training and maintenance services are included in the program. "These machines will modernize our farming operations and help us compete in agricultural markets," expressed a cooperative chairman from Agusan del Norte.',
                    'image_url' => '/storage/news/farm-mechanization.jpg',
                    'published_at' => Carbon::now()->subDays(61),
                    'is_featured' => true,
                    'created_by' => $creator->id ?? 1,
                    'created_at' => Carbon::now()->subDays(61),
                    'updated_at' => Carbon::now()->subDays(61),
                ],
                [
                    'title' => 'CARAGA Farmers Adopt Climate-Resilient Rice Varieties',
                    'content' => 'Farmers across CARAGA are increasingly adopting climate-resilient rice varieties developed through DA-CARAGA\'s Research and Development program. These drought-tolerant and flood-resistant varieties have shown 25% higher yields compared to traditional varieties in field trials. The R&D program, in partnership with PhilRice and local universities, continues to test and develop crop varieties specifically adapted to CARAGA\'s climate conditions. Demonstration farms have been established in all five provinces to showcase the benefits of these improved varieties.',
                    'image_url' => '/storage/news/climate-resilient-crops.jpg',
                    'published_at' => Carbon::now()->subDays(68),
                    'is_featured' => false,
                    'created_by' => $creator->id ?? 1,
                    'created_at' => Carbon::now()->subDays(68),
                    'updated_at' => Carbon::now()->subDays(68),
                ],
                [
                    'title' => 'Coconut Rehabilitation Program Benefits 3,500 CARAGA Farmers',
                    'content' => 'The region-wide Coconut Rehabilitation and Replanting Program has reached 3,500 coconut farmers with 5,000 hectares of senile and typhoon-damaged plantations being rehabilitated. The program provides hybrid coconut seedlings, promotes intercropping with high-value crops, and offers farm insurance. Coconut product diversification training includes production of virgin coconut oil, coco sugar, and coconut-based handicrafts. "This program is revitalizing our coconut industry and creating new income opportunities for our farmers," stated the High-Value Crops Development Program coordinator.',
                    'image_url' => '/storage/news/coconut-rehabilitation.jpg',
                    'published_at' => Carbon::now()->subDays(75),
                    'is_featured' => false,
                    'created_by' => $creator->id ?? 1,
                    'created_at' => Carbon::now()->subDays(75),
                    'updated_at' => Carbon::now()->subDays(75),
                ],
                [
                    'title' => 'Irrigation System Rehabilitation Increases Rice Yield by 30%',
                    'content' => 'The recently completed irrigation system rehabilitation in Agusan del Sur has resulted in a 30% increase in rice yields for 3,200 hectares of farmlands. The project included canal lining, installation of water control structures, and strengthening of farmer irrigation associations. "With reliable irrigation, we can now plant two to three crops per year instead of just one rain-fed crop," shared a local rice farmer. The improved water efficiency has also reduced water wastage and enabled farmers to expand their rice production areas.',
                    'image_url' => '/storage/news/irrigation-rehabilitation.jpg',
                    'published_at' => Carbon::now()->subDays(82),
                    'is_featured' => false,
                    'created_by' => $creator->id ?? 1,
                    'created_at' => Carbon::now()->subDays(82),
                    'updated_at' => Carbon::now()->subDays(82),
                ],
                [
                    'title' => 'Farmers Field Schools Graduate 2,000 CARAGA Farmers',
                    'content' => 'Over 2,000 farmers have successfully completed training through the Farmers Field School and Agricultural Extension Program across CARAGA. The comprehensive training covered crop production, integrated pest management, organic farming, climate-smart agriculture, and agribusiness management. Graduates received certificates and starter packages to implement their learning. "The knowledge and skills we gained from the Farmers Field School have transformed how we approach farming," expressed a graduate from Surigao del Sur. The program continues with 100 active field schools region-wide.',
                    'image_url' => '/storage/news/farmers-field-school.jpg',
                    'published_at' => Carbon::now()->subDays(89),
                    'is_featured' => false,
                    'created_by' => $creator->id ?? 1,
                    'created_at' => Carbon::now()->subDays(89),
                    'updated_at' => Carbon::now()->subDays(89),
                ],
                [
                    'title' => 'DA-CARAGA Launches Digital Agriculture Platform for Farmers',
                    'content' => 'DA-CARAGA has launched a digital agriculture platform to provide farmers with real-time information on weather forecasts, market prices, pest alerts, and agricultural advisories. The platform, accessible via mobile phones and computers, also facilitates online consultations with agricultural technicians and enables farmers to track their production data. "This digital platform empowers our farmers with timely information to make better farming decisions," explained the Planning and Monitoring Division chief. Over 5,000 farmers have already registered on the platform.',
                    'image_url' => '/storage/news/digital-agriculture.jpg',
                    'published_at' => Carbon::now()->subDays(96),
                    'is_featured' => true,
                    'created_by' => $creator->id ?? 1,
                    'created_at' => Carbon::now()->subDays(96),
                    'updated_at' => Carbon::now()->subDays(96),
                ],
                [
                    'title' => 'CARAGA Livestock Producers Receive Veterinary Support and Breeding Stock',
                    'content' => 'Livestock producers across CARAGA have received comprehensive veterinary support services and quality breeding stock through the Livestock Development Program. The program distributed 500 heads of cattle, 1,000 heads of goats, and quality breeding materials for swine and poultry. Mobile veterinary clinics provide animal health services including vaccination, deworming, and disease diagnosis. "The improved breeding stock and veterinary support have significantly enhanced our livestock production," shared a cattle raiser from Agusan Valley.',
                    'image_url' => '/storage/news/livestock-support.jpg',
                    'published_at' => Carbon::now()->subDays(103),
                    'is_featured' => false,
                    'created_by' => $creator->id ?? 1,
                    'created_at' => Carbon::now()->subDays(103),
                    'updated_at' => Carbon::now()->subDays(103),
                ],
            ];

            foreach ($newsUpdates as $newsData) {
                NewsUpdate::create($newsData);
            }
        });

        $this->command->info('15 realistic CARAGA agricultural news updates seeded successfully!');
    }
}

