<?php

namespace Database\Seeders;

use App\Models\Blog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $blogs = [
            [
                'title' => 'Discovering the Hidden Gems of Failaka Island',
                'slug' => Str::slug('Discovering the Hidden Gems of Failaka Island'),
                'description' => 'Explore the rich history and stunning natural beauty of Failaka Island, Kuwait\'s archaeological treasure in the Persian Gulf.',
                'content' => '<p>Failaka Island, located about 20 kilometers off the coast of Kuwait City, is a destination that seamlessly blends ancient history with natural beauty. This hidden gem offers visitors a unique opportunity to step back in time while enjoying the serene waters of the Persian Gulf.</p>

<h2>A Journey Through Time</h2>
<p>The island has been inhabited since prehistoric times and has witnessed the rise and fall of several civilizations. From Bronze Age settlements to Greek colonies established by Alexander the Great\'s commanders, Failaka\'s archaeological sites tell fascinating stories of maritime trade and cultural exchange.</p>

<h2>What to See and Do</h2>
<p>Visitors can explore the ruins of Greek temples, ancient houses, and fortifications. The island\'s museum showcases artifacts discovered during excavations, including pottery, coins, and tools that date back thousands of years. Don\'t miss the iconic water tower and abandoned village, which serve as poignant reminders of more recent history.</p>

<h2>Natural Beauty and Recreation</h2>
<p>Beyond its historical significance, Failaka Island offers pristine beaches, crystal-clear waters perfect for swimming and snorkeling, and abundant marine life. The island is also home to various bird species, making it a paradise for birdwatchers and nature enthusiasts.</p>

<h2>Plan Your Visit</h2>
<p>The best time to visit is during the cooler months from October to April. Regular ferry services operate from Kuwait City, making it an easy day trip. Whether you\'re a history buff, nature lover, or simply seeking a peaceful escape, Failaka Island promises an unforgettable experience.</p>

<p>Stay in our comfortable island houses and rooms to fully immerse yourself in this unique destination. Book your trip today and discover why Failaka Island is Kuwait\'s best-kept secret!</p>',
                'image' => 'blogs/failaka-island-discovery.jpg',
                'date' => now()->subDays(15),
                'is_published' => true,
            ],
            [
                'title' => 'Top 10 Things to Do on a Yacht Charter in the Persian Gulf',
                'slug' => Str::slug('Top 10 Things to Do on a Yacht Charter in the Persian Gulf'),
                'description' => 'From sunset cruises to fishing adventures, discover the ultimate yacht experience in Kuwait\'s beautiful waters.',
                'content' => '<p>A yacht charter in the Persian Gulf offers an exclusive way to explore Kuwait\'s stunning coastline and islands. Whether you\'re planning a romantic getaway, family adventure, or corporate event, here are the top 10 activities to make your yacht experience unforgettable.</p>

<h2>1. Sunset Cruise</h2>
<p>There\'s nothing quite like watching the sun dip below the horizon from the deck of a luxury yacht. The golden hour casts a magical glow over the Gulf waters, creating the perfect backdrop for photos and memories.</p>

<h2>2. Island Hopping</h2>
<p>Explore multiple islands in one trip, each offering its own unique charm. From Failaka\'s historical sites to Kubbar Island\'s pristine beaches, discover the diverse beauty of Kuwait\'s archipelago.</p>

<h2>3. Fishing Expedition</h2>
<p>The Persian Gulf is rich in marine life. Try your hand at catching hamour, shari, or zubedi with professional fishing equipment provided on board.</p>

<h2>4. Water Sports Adventure</h2>
<p>Dive into excitement with jet skiing, wakeboarding, banana boat rides, and kayaking. Most yacht charters offer a variety of water sports equipment.</p>

<h2>5. Snorkeling and Diving</h2>
<p>Explore the underwater world teeming with colorful fish, coral reefs, and fascinating marine ecosystems. Equipment and guidance are typically provided.</p>

<h2>6. Private Beach Picnic</h2>
<p>Anchor at a secluded beach and enjoy a gourmet picnic prepared by your yacht\'s chef. It\'s the perfect way to combine luxury with nature.</p>

<h2>7. Dolphin Watching</h2>
<p>Keep your eyes peeled for playful dolphins that often swim alongside yachts in the Gulf. These intelligent creatures provide entertainment and joy for all ages.</p>

<h2>8. Corporate Events</h2>
<p>Host meetings, team-building activities, or celebrations on the water. The unique setting inspires creativity and strengthens team bonds.</p>

<h2>9. Stargazing at Night</h2>
<p>Away from city lights, the night sky reveals countless stars. It\'s a romantic and awe-inspiring experience you won\'t find on land.</p>

<h2>10. Onboard Dining</h2>
<p>Enjoy freshly prepared meals featuring local seafood and international cuisine. Many charters offer BBQ facilities for an authentic outdoor dining experience.</p>

<p>Ready to embark on your yacht adventure? Browse our selection of luxury boats and book your charter today for an experience you\'ll treasure forever!</p>',
                'image' => 'blogs/yacht-charter-guide.jpg',
                'date' => now()->subDays(7),
                'is_published' => true,
            ],
            [
                'title' => 'The Ultimate Guide to Planning Your Perfect Island Getaway',
                'slug' => Str::slug('The Ultimate Guide to Planning Your Perfect Island Getaway'),
                'description' => 'Expert tips and insider advice for planning an unforgettable island vacation with IKARUS Marine.',
                'content' => '<p>Planning an island getaway can be both exciting and overwhelming. With so many options and details to consider, where do you start? This comprehensive guide will help you plan the perfect escape to Kuwait\'s beautiful islands.</p>

<h2>Choosing the Right Accommodation</h2>
<p>Your choice of accommodation sets the tone for your entire trip. Consider these factors:</p>

<ul>
<li><strong>Group Size:</strong> Traditional houses are perfect for families and large groups, while rooms suit couples and small groups.</li>
<li><strong>Amenities:</strong> Look for properties with the features you need - kitchen facilities, air conditioning, Wi-Fi, and outdoor spaces.</li>
<li><strong>Location:</strong> Beachfront properties offer convenience, while inland options provide a more traditional experience.</li>
<li><strong>Budget:</strong> Early booking often comes with discounts, and weekday stays can be more affordable than weekends.</li>
</ul>

<h2>Best Time to Visit</h2>
<p>Kuwait\'s climate varies significantly throughout the year:</p>

<ul>
<li><strong>October to April:</strong> Perfect weather with temperatures ranging from 15-30°C. Ideal for all outdoor activities.</li>
<li><strong>May to September:</strong> Hot summer months with temperatures exceeding 45°C. Early morning and evening activities recommended.</li>
</ul>

<h2>What to Pack</h2>
<p>Essential items for your island getaway:</p>

<ul>
<li>Lightweight, breathable clothing</li>
<li>Sunscreen (SPF 50+) and sun protection</li>
<li>Swimwear and beach towels</li>
<li>Comfortable walking shoes and sandals</li>
<li>Insect repellent</li>
<li>Power bank and waterproof phone case</li>
<li>Basic first-aid kit</li>
<li>Snorkeling gear (if you have your own)</li>
</ul>

<h2>Activities and Experiences</h2>
<p>Make the most of your island stay with these activities:</p>

<ul>
<li>Beach relaxation and swimming</li>
<li>Snorkeling and diving</li>
<li>Fishing trips</li>
<li>Boat tours and yacht charters</li>
<li>Archaeological site visits (especially on Failaka Island)</li>
<li>Birdwatching and wildlife spotting</li>
<li>Traditional Kuwaiti BBQ (mashawi)</li>
<li>Photography and sunset watching</li>
</ul>

<h2>Booking Tips</h2>
<p>Get the best value from your trip:</p>

<ul>
<li><strong>Book Early:</strong> Secure better rates and availability, especially during peak season.</li>
<li><strong>Flexible Dates:</strong> Weekday bookings often offer better prices than weekends.</li>
<li><strong>Package Deals:</strong> Look for combined accommodation and boat charter packages.</li>
<li><strong>Group Discounts:</strong> Traveling with friends? Ask about group rates.</li>
<li><strong>Cancellation Policy:</strong> Understand the terms before booking.</li>
</ul>

<h2>Transportation</h2>
<p>Getting to the islands is easy:</p>

<ul>
<li>Regular ferry services from Kuwait City</li>
<li>Private boat charters available</li>
<li>Some accommodations offer pickup/drop-off services</li>
</ul>

<h2>Local Etiquette and Tips</h2>
<ul>
<li>Respect local customs and traditions</li>
<li>Dress modestly when not on private beaches</li>
<li>Dispose of waste properly to protect the environment</li>
<li>Be mindful of marine life and coral reefs</li>
<li>Stay hydrated, especially during summer months</li>
</ul>

<h2>Making Memories</h2>
<p>Don\'t forget to capture your experiences:</p>

<ul>
<li>Bring a good camera or smartphone</li>
<li>Take sunset and sunrise photos</li>
<li>Document local cuisine and traditions</li>
<li>Share your experience on social media</li>
</ul>

<h2>Ready to Plan Your Escape?</h2>
<p>With IKARUS Marine, planning your perfect island getaway has never been easier. Browse our selection of traditional houses, modern rooms, and luxury yacht charters. Our team is here to help you create memories that will last a lifetime.</p>

<p>Book now and take advantage of our early bird specials. Your dream island vacation awaits!</p>',
                'image' => 'blogs/island-getaway-planning.jpg',
                'date' => now()->subDays(3),
                'is_published' => true,
            ],
        ];

        foreach ($blogs as $blog) {
            Blog::create($blog);
        }
    }
}
