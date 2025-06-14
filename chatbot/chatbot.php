<?php
session_start();

// Database connection
class Database {
    private $host = 'localhost';
    private $dbname = 'folklore_sem';
    private $username = 'root';
    private $password = '';
    private $pdo;
    
    public function __construct() {
        try {
            $this->pdo = new PDO("mysql:host={$this->host};dbname={$this->dbname}", $this->username, $this->password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->pdo;
    }
}

// Initialize database
$db = new Database();
$pdo = $db->getConnection();

// Initialize session variables
if (!isset($_SESSION['chat_history'])) {
    $_SESSION['chat_history'] = [];
}
if (!isset($_SESSION['current_preferences'])) {
    $_SESSION['current_preferences'] = [];
}
if (!isset($_SESSION['current_question'])) {
    $_SESSION['current_question'] = 0;
}
if (!isset($_SESSION['chat_mode'])) {
    $_SESSION['chat_mode'] = 'main_menu';
}

// Main chatbot categories
$chatCategories = [
    'recipe_finder' => [
        'title' => 'Recipe Finder',
        'description' => 'Find personalized recipes based on your preferences',
        'icon' => 'fas fa-search'
    ],
    'platform_guide' => [
        'title' => 'Platform Guide',
        'description' => 'Learn how to use Folklore features',
        'icon' => 'fas fa-question-circle'
    ],
    'cooking_tips' => [
        'title' => 'Cooking Tips & Techniques',
        'description' => 'Expert cooking advice and techniques',
        'icon' => 'fas fa-utensils'
    ],
    'restaurant_finder' => [
        'title' => 'Restaurant Recommendations',
        'description' => 'Find restaurants and dining suggestions',
        'icon' => 'fas fa-map-marker-alt'
    ],
    'nutrition_info' => [
        'title' => 'Nutrition & Health',
        'description' => 'Nutritional information and healthy eating',
        'icon' => 'fas fa-heart'
    ],
    'recipe_modifications' => [
        'title' => 'Recipe Modifications',
        'description' => 'Adapt recipes for dietary needs and preferences',
        'icon' => 'fas fa-edit'
    ],
    'meal_planning' => [
        'title' => 'Meal Planning',
        'description' => 'Plan your weekly meals and shopping lists',
        'icon' => 'fas fa-calendar-alt'
    ],
    'cooking_basics' => [
        'title' => 'Cooking Basics',
        'description' => 'Essential cooking knowledge for beginners',
        'icon' => 'fas fa-graduation-cap'
    ]
];

// Platform guide questions and responses
$platformGuide = [
    'main' => [
        'question' => 'What would you like to learn about using Folklore?',
        'options' => [
            ['id' => 'like_recipe', 'text' => 'How to like a recipe'],
            ['id' => 'comment_recipe', 'text' => 'How to comment on recipes'],
            ['id' => 'save_recipe', 'text' => 'How to save recipes'],
            ['id' => 'share_recipe', 'text' => 'How to share recipes'],
            ['id' => 'create_account', 'text' => 'How to create an account'],
            ['id' => 'profile_settings', 'text' => 'Managing your profile']
        ]
    ],
    'responses' => [
        'like_recipe' => [
            'title' => 'How to Like a Recipe',
            'content' => 'To like a recipe on Folklore:<br>• Open any recipe by clicking on it<br>• Look for the heart ❤️ icon near the recipe title<br>• Click the heart to add it to your favorites<br>• The heart will turn red when liked<br>• View all your liked recipes in "My Favorites" section<br>• You can unlike by clicking the heart again'
        ],
        'comment_recipe' => [
            'title' => 'How to Comment on a Recipe',
            'content' => 'To leave a comment on a recipe:<br>• Open a recipe by clicking on it<br>• Scroll down to the bottom until you see the comment section<br>• Click on the comment text box<br>• Type your thoughts, tips, or feedback<br>• You can rate the recipe while commenting<br>• Click the "Submit Comment" button to post<br>• Your comment will appear after moderation'
        ],
        'save_recipe' => [
            'title' => 'How to Save Recipes',
            'content' => 'To save recipes for later cooking:<br>• Click the bookmark 🔖 icon on any recipe card<br>• Access saved recipes from "My Saved Recipes" in your profile<br>• Create custom collections to organize recipes<br>• Add notes to your saved recipes<br>• Share your collections with friends and family<br>• Export recipes to PDF for offline use'
        ],
        'share_recipe' => [
            'title' => 'How to Share Recipes',
            'content' => 'To share recipes with others:<br>• Open the recipe you want to share<br>• Click the share 📤 icon<br>• Choose from social media, email, or copy link<br>• Send the link to friends and family<br>• Share via WhatsApp, Facebook, or Twitter<br>• Create a shared meal plan with others'
        ],
        'create_account' => [
            'title' => 'How to Create an Account',
            'content' => 'To create your Folklore account:<br>• Click "Sign Up" in the top right corner<br>• Enter your email address and create a password<br>• Verify your email address<br>• Complete your profile with dietary preferences<br>• Upload a profile picture (optional)<br>• Start exploring and saving recipes!'
        ],
        'profile_settings' => [
            'title' => 'Managing Your Profile',
            'content' => 'To manage your profile settings:<br>• Click on your profile picture/name<br>• Select "Profile Settings"<br>• Update your dietary preferences and restrictions<br>• Change your cooking skill level<br>• Set notification preferences<br>• Manage privacy settings<br>• Update contact information'
        ]
    ]
];

// Cooking tips responses
$cookingTips = [
    'main' => [
        'question' => 'What cooking topic would you like help with?',
        'options' => [
            ['id' => 'knife_skills', 'text' => 'Knife skills and cutting techniques'],
            ['id' => 'cooking_methods', 'text' => 'Different cooking methods'],
            ['id' => 'spice_guide', 'text' => 'Using spices and herbs'],
            ['id' => 'food_safety', 'text' => 'Food safety and storage'],
            ['id' => 'baking_tips', 'text' => 'Baking techniques'],
            ['id' => 'kitchen_tools', 'text' => 'Essential kitchen tools']
        ]
    ],
    'responses' => [
        'knife_skills' => [
            'title' => 'Essential Knife Skills',
            'content' => 'Master these fundamental knife techniques:<br>• <strong>Grip:</strong> Use a pinch grip for better control<br>• <strong>Julienne:</strong> Cut into thin, matchstick-like strips<br>• <strong>Chiffonade:</strong> Roll herbs and slice thinly<br>• <strong>Brunoise:</strong> Fine dice (1/8 inch cubes)<br>• <strong>Rock chop:</strong> Keep knife tip on board, rock blade<br>• <strong>Safety:</strong> Keep fingers curved, knuckles forward<br>• <strong>Maintenance:</strong> Keep knives sharp and clean'
        ],
        'cooking_methods' => [
            'title' => 'Cooking Methods Guide',
            'content' => 'Choose the right cooking method:<br>• <strong>Sautéing:</strong> Quick cooking in a small amount of fat<br>• <strong>Braising:</strong> Brown then cook slowly in liquid<br>• <strong>Grilling:</strong> High heat cooking over open flame<br>• <strong>Roasting:</strong> Dry heat cooking in an oven<br>• <strong>Steaming:</strong> Gentle cooking with moist heat<br>• <strong>Poaching:</strong> Gentle simmering in liquid<br>• <strong>Stir-frying:</strong> High heat, constant movement'
        ],
        'spice_guide' => [
            'title' => 'Spices and Herbs Guide',
            'content' => 'Essential spice and herb tips:<br>• <strong>Storage:</strong> Keep in cool, dark, dry places<br>• <strong>Whole vs Ground:</strong> Whole spices last longer<br>• <strong>Toasting:</strong> Toast whole spices to enhance flavor<br>• <strong>Fresh herbs:</strong> Add at the end of cooking<br>• <strong>Dried herbs:</strong> Add early in cooking process<br>• <strong>Salt:</strong> Season throughout cooking, not just at end<br>• <strong>Combinations:</strong> Learn classic spice blends'
        ],
        'food_safety' => [
            'title' => 'Food Safety Essentials',
            'content' => 'Keep your food safe:<br>• <strong>Temperature:</strong> Keep hot foods hot (140°F+), cold foods cold (40°F-)<br>• <strong>Cross-contamination:</strong> Use separate cutting boards for meat and vegetables<br>• <strong>Hand washing:</strong> Wash hands frequently and thoroughly<br>• <strong>Storage:</strong> Store raw meat on bottom shelf of fridge<br>• <strong>Leftovers:</strong> Refrigerate within 2 hours, use within 3-4 days<br>• <strong>Thawing:</strong> Thaw in refrigerator, not at room temperature<br>• <strong>Internal temps:</strong> Use a thermometer to check doneness'
        ],
        'baking_tips' => [
            'title' => 'Baking Success Tips',
            'content' => 'Essential baking techniques:<br>• <strong>Measure accurately:</strong> Use a kitchen scale for best results<br>• <strong>Room temperature:</strong> Bring eggs and dairy to room temp<br>• <strong>Oven temperature:</strong> Preheat fully and use an oven thermometer<br>• <strong>Mixing:</strong> Don\'t overmix batters and doughs<br>• <strong>Testing doneness:</strong> Use toothpick test for cakes<br>• <strong>Cooling:</strong> Let baked goods cool completely before frosting<br>• <strong>Ingredient substitutions:</strong> Understand how substitutions affect results'
        ],
        'kitchen_tools' => [
            'title' => 'Essential Kitchen Tools',
            'content' => 'Must-have kitchen equipment:<br>• <strong>Knives:</strong> Chef\'s knife, paring knife, serrated knife<br>• <strong>Cutting boards:</strong> Separate boards for meat and vegetables<br>• <strong>Pots and pans:</strong> Heavy-bottomed for even cooking<br>• <strong>Measuring tools:</strong> Dry and liquid measuring cups, kitchen scale<br>• <strong>Mixing bowls:</strong> Various sizes, preferably stainless steel<br>• <strong>Thermometers:</strong> Instant-read and oven thermometers<br>• <strong>Storage:</strong> Airtight containers for pantry items'
        ]
    ]
];

// Restaurant finder responses
$restaurantGuide = [
    'main' => [
        'question' => 'What kind of restaurant help do you need?',
        'options' => [
            ['id' => 'find_nearby', 'text' => 'Find restaurants near me'],
            ['id' => 'cuisine_types', 'text' => 'Different cuisine types'],
            ['id' => 'dining_etiquette', 'text' => 'Restaurant etiquette'],
            ['id' => 'reservation_tips', 'text' => 'Making reservations'],
            ['id' => 'dietary_restaurants', 'text' => 'Restaurants for dietary restrictions'],
            ['id' => 'restaurant_apps', 'text' => 'Best restaurant finding apps']
        ]
    ],
    'responses' => [
        'find_nearby' => [
            'title' => 'Find Restaurants Near You',
            'content' => 'To find great restaurants nearby:<br>• <strong>Use location services:</strong> Enable GPS for accurate results<br>• <strong>Popular apps:</strong> Google Maps, Yelp, Zomato, TripAdvisor<br>• <strong>Filter options:</strong> By cuisine, price range, ratings<br>• <strong>Read reviews:</strong> Check recent reviews and photos<br>• <strong>Check hours:</strong> Verify opening hours and busy times<br>• <strong>Call ahead:</strong> Confirm availability and special requirements<br>• <strong>Local recommendations:</strong> Ask locals for hidden gems'
        ],
        'cuisine_types' => [
            'title' => 'Exploring Different Cuisines',
            'content' => 'Popular cuisine types to try:<br>• <strong>Asian:</strong> Chinese, Japanese, Thai, Korean, Indian<br>• <strong>European:</strong> Italian, French, Spanish, Greek<br>• <strong>American:</strong> BBQ, Southern, Tex-Mex, Cajun<br>• <strong>Middle Eastern:</strong> Lebanese, Turkish, Persian<br>• <strong>Latin American:</strong> Mexican, Peruvian, Brazilian<br>• <strong>African:</strong> Ethiopian, Moroccan, Nigerian<br>• <strong>Fusion:</strong> Creative combinations of different cuisines'
        ],
        'dining_etiquette' => [
            'title' => 'Restaurant Etiquette',
            'content' => 'Proper dining etiquette:<br>• <strong>Reservations:</strong> Arrive on time or call if running late<br>• <strong>Seating:</strong> Wait to be seated unless it\'s self-seating<br>• <strong>Ordering:</strong> Be polite to staff, ask questions about menu<br>• <strong>Phone use:</strong> Keep phone conversations brief and quiet<br>• <strong>Tipping:</strong> 15-20% for good service in most countries<br>• <strong>Dietary needs:</strong> Inform staff of allergies or restrictions<br>• <strong>Payment:</strong> Check if they accept your preferred payment method'
        ],
        'reservation_tips' => [
            'title' => 'Making Restaurant Reservations',
            'content' => 'Tips for successful reservations:<br>• <strong>Book early:</strong> Popular restaurants fill up quickly<br>• <strong>Be flexible:</strong> Consider different times or days<br>• <strong>Provide details:</strong> Mention special occasions or dietary needs<br>• <strong>Confirm details:</strong> Date, time, party size, contact info<br>• <strong>Cancellation policy:</strong> Understand their cancellation rules<br>• <strong>Special requests:</strong> Ask for preferred seating (window, quiet area)<br>• <strong>Follow up:</strong> Confirm reservation day before'
        ],
        'dietary_restaurants' => [
            'title' => 'Restaurants for Dietary Restrictions',
            'content' => 'Finding restaurants for special diets:<br>• <strong>Vegetarian/Vegan:</strong> Look for dedicated plant-based restaurants<br>• <strong>Gluten-free:</strong> Many restaurants now offer GF menus<br>• <strong>Kosher/Halal:</strong> Check certification and preparation methods<br>• <strong>Keto/Low-carb:</strong> Steakhouses and grills often accommodate<br>• <strong>Allergies:</strong> Call ahead to discuss preparation methods<br>• <strong>Apps to help:</strong> HappyCow (vegan), Find Me Gluten Free<br>• <strong>Always verify:</strong> Double-check with restaurant staff'
        ],
        'restaurant_apps' => [
            'title' => 'Best Restaurant Finding Apps',
            'content' => 'Top apps for finding restaurants:<br>• <strong>Google Maps:</strong> Comprehensive listings with reviews and photos<br>• <strong>Yelp:</strong> Detailed reviews and filtering options<br>• <strong>Zomato:</strong> Global coverage with menus and photos<br>• <strong>OpenTable:</strong> Easy reservation booking<br>• <strong>TripAdvisor:</strong> Tourist-focused recommendations<br>• <strong>Foursquare:</strong> Local recommendations and tips<br>• <strong>Local apps:</strong> Region-specific apps often have better coverage'
        ]
    ]
];

// Nutrition guide responses
$nutritionGuide = [
    'main' => [
        'question' => 'What nutrition information do you need?',
        'options' => [
            ['id' => 'reading_labels', 'text' => 'Reading nutrition labels'],
            ['id' => 'healthy_substitutions', 'text' => 'Healthy ingredient substitutions'],
            ['id' => 'portion_control', 'text' => 'Portion control tips'],
            ['id' => 'meal_balance', 'text' => 'Creating balanced meals'],
            ['id' => 'dietary_needs', 'text' => 'Special dietary needs'],
            ['id' => 'cooking_healthy', 'text' => 'Healthy cooking methods']
        ]
    ],
    'responses' => [
        'reading_labels' => [
            'title' => 'Reading Nutrition Labels',
            'content' => 'Understanding nutrition labels:<br>• <strong>Serving size:</strong> Check this first - all values are per serving<br>• <strong>Calories:</strong> Energy provided per serving<br>• <strong>% Daily Value:</strong> Based on 2000-calorie diet<br>• <strong>Nutrients to limit:</strong> Saturated fat, sodium, added sugars<br>• <strong>Nutrients to get enough:</strong> Fiber, protein, vitamins, minerals<br>• <strong>Ingredients list:</strong> Listed by weight, heaviest first<br>• <strong>Look for:</strong> "Whole grain" as first ingredient for grains'
        ],
        'healthy_substitutions' => [
            'title' => 'Healthy Ingredient Substitutions',
            'content' => 'Smart ingredient swaps:<br>• <strong>Instead of butter:</strong> Use avocado, applesauce, or Greek yogurt<br>• <strong>Instead of white rice:</strong> Try quinoa, cauliflower rice, or brown rice<br>• <strong>Instead of sugar:</strong> Use honey, maple syrup, or stevia (use less)<br>• <strong>Instead of heavy cream:</strong> Use coconut milk or cashew cream<br>• <strong>Instead of white flour:</strong> Use almond flour, whole wheat, or oat flour<br>• <strong>Instead of salt:</strong> Use herbs, spices, lemon juice, or vinegar<br>• <strong>Instead of sour cream:</strong> Use Greek yogurt'
        ],
        'portion_control' => [
            'title' => 'Portion Control Tips',
            'content' => 'Smart portion control strategies:<br>• <strong>Use smaller plates:</strong> 9-inch plates instead of 12-inch<br>• <strong>Visual guides:</strong> Palm = protein, fist = vegetables, cupped hand = carbs<br>• <strong>Eat slowly:</strong> It takes 20 minutes to feel full<br>• <strong>Pre-portion snacks:</strong> Divide into single servings<br>• <strong>Fill half your plate:</strong> With non-starchy vegetables<br>• <strong>Drink water first:</strong> Often thirst is mistaken for hunger<br>• <strong>Practice mindful eating:</strong> Eliminate distractions while eating'
        ],
        'meal_balance' => [
            'title' => 'Creating Balanced Meals',
            'content' => 'Building nutritionally complete meals:<br>• <strong>Protein (25%):</strong> Lean meats, fish, eggs, legumes, tofu<br>• <strong>Vegetables (50%):</strong> Variety of colors for different nutrients<br>• <strong>Whole grains (25%):</strong> Brown rice, quinoa, whole wheat bread<br>• <strong>Healthy fats:</strong> Olive oil, avocado, nuts, seeds<br>• <strong>Limit processed foods:</strong> Choose whole, unprocessed ingredients<br>• <strong>Stay hydrated:</strong> Water should be your main beverage<br>• <strong>Plan ahead:</strong> Meal prep to avoid unhealthy choices'
        ],
        'dietary_needs' => [
            'title' => 'Special Dietary Needs',
            'content' => 'Managing specific dietary requirements:<br>• <strong>Diabetes:</strong> Focus on low glycemic foods, consistent meal timing<br>• <strong>Heart health:</strong> Limit sodium, increase omega-3 fatty acids<br>• <strong>High blood pressure:</strong> DASH diet - fruits, vegetables, whole grains<br>• <strong>Weight management:</strong> Calorie awareness, portion control<br>• <strong>Food allergies:</strong> Read labels carefully, know hidden sources<br>• <strong>Vegetarian/Vegan:</strong> Ensure adequate B12, iron, protein sources<br>• <strong>Gluten-free:</strong> Focus on naturally gluten-free whole foods'
        ],
        'cooking_healthy' => [
            'title' => 'Healthy Cooking Methods',
            'content' => 'Nutritious cooking techniques:<br>• <strong>Steaming:</strong> Preserves nutrients and natural flavors<br>• <strong>Grilling:</strong> Allows fat to drip away, adds great flavor<br>• <strong>Roasting:</strong> Brings out natural sweetness in vegetables<br>• <strong>Sautéing:</strong> Use minimal oil or cooking spray<br>• <strong>Poaching:</strong> Gentle cooking in liquid for delicate proteins<br>• <strong>Stir-frying:</strong> Quick cooking retains nutrients and texture<br>• <strong>Avoid:</strong> Deep frying, excessive oil, overcooking vegetables'
        ]
    ]
];

// Recipe modifications guide
$recipeModifications = [
    'main' => [
        'question' => 'How would you like to modify recipes?',
        'options' => [
            ['id' => 'dietary_adaptations', 'text' => 'Dietary adaptations (vegan, gluten-free, etc.)'],
            ['id' => 'reduce_calories', 'text' => 'Reduce calories and fat'],
            ['id' => 'increase_protein', 'text' => 'Increase protein content'],
            ['id' => 'scaling_recipes', 'text' => 'Scaling recipes up or down'],
            ['id' => 'ingredient_substitutions', 'text' => 'Common ingredient substitutions'],
            ['id' => 'allergy_modifications', 'text' => 'Allergy-friendly modifications']
        ]
    ],
    'responses' => [
        'dietary_adaptations' => [
            'title' => 'Dietary Adaptations',
            'content' => 'Adapting recipes for special diets:<br>• <strong>Vegan:</strong> Replace eggs with flax eggs, dairy with plant milks<br>• <strong>Gluten-free:</strong> Use almond flour, rice flour, or gluten-free blends<br>• <strong>Keto:</strong> Replace grains with cauliflower, increase healthy fats<br>• <strong>Paleo:</strong> Use coconut flour, eliminate grains and legumes<br>• <strong>Low-carb:</strong> Substitute pasta with zucchini noodles or shirataki<br>• <strong>Dairy-free:</strong> Use coconut milk, cashew cream, nutritional yeast<br>• <strong>Test modifications:</strong> Start with small batches to perfect adaptations'
        ],
        'reduce_calories' => [
            'title' => 'Reduce Calories and Fat',
            'content' => 'Lightening up your recipes:<br>• <strong>Cooking methods:</strong> Bake, grill, or steam instead of frying<br>• <strong>Oil reduction:</strong> Use cooking spray or reduce oil by half<br>• <strong>Dairy swaps:</strong> Use low-fat yogurt instead of sour cream<br>• <strong>Bulk with vegetables:</strong> Add extra veggies to increase volume<br>• <strong>Lean proteins:</strong> Choose chicken breast, fish, or plant proteins<br>• <strong>Natural sweeteners:</strong> Use fruit purees to reduce added sugars<br>• <strong>Portion awareness:</strong> Serve appropriate portion sizes'
        ],
        'increase_protein' => [
            'title' => 'Increase Protein Content',
            'content' => 'Boosting protein in your meals:<br>• <strong>Add protein powder:</strong> In smoothies, pancakes, or muffins<br>• <strong>Include legumes:</strong> Beans, lentils, chickpeas in salads and soups<br>• <strong>Use Greek yogurt:</strong> Instead of regular yogurt or sour cream<br>• <strong>Quinoa substitution:</strong> Replace rice or pasta with quinoa<br>• <strong>Nuts and seeds:</strong> Add to salads, oatmeal, or as snacks<br>• <strong>Eggs:</strong> Add extra egg whites or whole eggs to dishes<br>• <strong>Protein-rich grains:</strong> Choose amaranth, buckwheat, or farro'
        ],
        'scaling_recipes' => [
            'title' => 'Scaling Recipes Up or Down',
            'content' => 'Successfully scaling your recipes:<br>• <strong>Simple multiplication:</strong> Most ingredients scale linearly<br>• <strong>Spices and seasonings:</strong> Scale by 75% then taste and adjust<br>• <strong>Leavening agents:</strong> Baking powder/soda - scale exactly<br>• <strong>Cooking times:</strong> Larger portions take longer, smaller cook faster<br>• <strong>Pan sizes:</strong> Adjust baking dish size accordingly<br>• <strong>Liquids in baking:</strong> May need slight adjustments for texture<br>• <strong>Keep notes:</strong> Record successful modifications for next time'
        ],
        'ingredient_substitutions' => [
            'title' => 'Common Ingredient Substitutions',
            'content' => 'Emergency ingredient swaps:<br>• <strong>1 egg:</strong> 1 tbsp ground flaxseed + 3 tbsp water (let sit 5 min)<br>• <strong>1 cup milk:</strong> 1 cup plant milk or ¾ cup water + ¼ cup powder<br>• <strong>1 cup butter:</strong> ¾ cup oil or ½ cup applesauce<br>• <strong>1 tsp baking powder:</strong> ¼ tsp baking soda + ½ tsp cream of tartar<br>• <strong>1 cup sugar:</strong> ¾ cup honey (reduce liquid by ¼ cup)<br>• <strong>1 cup flour:</strong> 1 cup almond flour or ¾ cup coconut flour<br>• <strong>Fresh herbs:</strong> Use 1/3 the amount of dried herbs'
        ],
        'allergy_modifications' => [
            'title' => 'Allergy-Friendly Modifications',
            'content' => 'Making recipes safe for allergies:<br>• <strong>Nut allergies:</strong> Use sunflower seed butter or soy butter<br>• <strong>Egg allergies:</strong> Aquafaba (chickpea liquid) or commercial egg replacer<br>• <strong>Dairy allergies:</strong> Coconut milk, oat milk, or cashew cream<br>• <strong>Wheat allergies:</strong> Rice flour, oat flour, or certified GF alternatives<br>• <strong>Soy allergies:</strong> Check labels carefully, use coconut aminos<br>• <strong>Cross-contamination:</strong> Use separate cutting boards and utensils<br>• <strong>Always double-check:</strong> Read all ingredient labels carefully'
        ]
    ]
];

// Meal planning guide
$mealPlanning = [
    'main' => [
        'question' => 'What aspect of meal planning interests you?',
        'options' => [
            ['id' => 'weekly_planning', 'text' => 'Weekly meal planning strategies'],
            ['id' => 'meal_prep_basics', 'text' => 'Meal prep basics and tips'],
            ['id' => 'budget_planning', 'text' => 'Budget-friendly meal planning'],
            ['id' => 'shopping_lists', 'text' => 'Creating efficient shopping lists'],
            ['id' => 'batch_cooking', 'text' => 'Batch cooking techniques'],
            ['id' => 'storage_organization', 'text' => 'Food storage and organization']
        ]
    ],
    'responses' => [
        'weekly_planning' => [
            'title' => 'Weekly Meal Planning Strategies',
            'content' => 'Effective weekly meal planning:<br>• <strong>Choose a planning day:</strong> Same day each week (usually Sunday)<br>• <strong>Check your schedule:</strong> Plan easier meals for busy days<br>• <strong>Use themes:</strong> Meatless Monday, Taco Tuesday, etc.<br>• <strong>Plan leftovers:</strong> Cook once, eat twice strategy<br>• <strong>Balance nutrition:</strong> Ensure variety of proteins and vegetables<br>• <strong>Consider prep time:</strong> Mix quick and longer cooking meals<br>• <strong>Keep backup meals:</strong> Simple options for unexpected changes'
        ],
        'meal_prep_basics' => [
            'title' => 'Meal Prep Basics and Tips',
            'content' => 'Successful meal prepping:<br>• <strong>Start small:</strong> Prep 2-3 meals initially, build up gradually<br>• <strong>Prep components:</strong> Cook grains, proteins, and vegetables separately<br>• <strong>Use proper containers:</strong> Glass containers for reheating, prevent staining<br>• <strong>Label everything:</strong> Include contents and date prepared<br>• <strong>Prep day workflow:</strong> Start longest-cooking items first<br>• <strong>Keep it simple:</strong> Choose recipes with minimal ingredients<br>• <strong>Wash and chop vegetables:</strong> Prepped veggies encourage healthy choices'
        ],
        'budget_planning' => [
            'title' => 'Budget-Friendly Meal Planning',
            'content' => 'Eating well on a budget:<br>• <strong>Plan around sales:</strong> Check store flyers before planning<br>• <strong>Buy seasonal produce:</strong> Cheaper and more flavorful<br>• <strong>Use affordable proteins:</strong> Eggs, beans, lentils, chicken thighs<br>• <strong>Buy in bulk:</strong> Rice, pasta, dried beans, frozen vegetables<br>• <strong>Cook at home:</strong> Much cheaper than dining out<br>• <strong>Use leftovers creatively:</strong> Transform into new meals<br>• <strong>Generic brands:</strong> Often same quality as name brands'
        ],
        'shopping_lists' => [
            'title' => 'Creating Efficient Shopping Lists',
            'content' => 'Smart shopping list strategies:<br>• <strong>Organize by store layout:</strong> Group items by department<br>• <strong>Check inventory first:</strong> Avoid buying duplicates<br>• <strong>Include quantities:</strong> Specify amounts needed for recipes<br>• <strong>Separate needs vs wants:</strong> Prioritize essential items<br>• <strong>Use apps:</strong> Digital lists can be shared with family<br>• <strong>Include substitutes:</strong> Note alternatives if items unavailable<br>• <strong>Set a budget:</strong> Know your spending limit before shopping'
        ],
        'batch_cooking' => [
            'title' => 'Batch Cooking Techniques',
            'content' => 'Efficient batch cooking methods:<br>• <strong>Cook grains in bulk:</strong> Rice, quinoa last 4-5 days refrigerated<br>• <strong>Roast sheet pans:</strong> Multiple vegetables at once<br>• <strong>Slow cooker meals:</strong> Set and forget cooking method<br>• <strong>Freezer-friendly portions:</strong> Soups, stews, casseroles freeze well<br>• <strong>Marinate proteins:</strong> Prep several proteins for week<br>• <strong>Make versatile bases:</strong> Plain proteins can be seasoned differently<br>• <strong>Use your freezer:</strong> Batch cook and freeze for busy weeks'
        ],
        'storage_organization' => [
            'title' => 'Food Storage and Organization',
            'content' => 'Proper food storage techniques:<br>• <strong>First in, first out:</strong> Use older items before newer ones<br>• <strong>Proper temperatures:</strong> Refrigerator at 40°F, freezer at 0°F<br>• <strong>Airtight containers:</strong> Keep food fresh longer, prevent pests<br>• <strong>Label with dates:</strong> Know when items were prepared or opened<br>• <strong>Store produce properly:</strong> Some fruits/vegetables need refrigeration<br>• <strong>Organize by category:</strong> Group similar items together<br>• <strong>Regular cleanouts:</strong> Check expiration dates weekly'
        ]
    ]
];

// Cooking basics guide
$cookingBasics = [
    'main' => [
        'question' => 'What cooking fundamentals would you like to learn?',
        'options' => [
            ['id' => 'kitchen_setup', 'text' => 'Setting up your kitchen'],
            ['id' => 'basic_techniques', 'text' => 'Basic cooking techniques'],
            ['id' => 'recipe_reading', 'text' => 'How to read and follow recipes'],
            ['id' => 'seasoning_basics', 'text' => 'Basic seasoning and flavoring'],
            ['id' => 'cooking_safety', 'text' => 'Kitchen safety fundamentals'],
            ['id' => 'beginner_recipes', 'text' => 'Easy recipes for beginners']
        ]
    ],
    'responses' => [
        'kitchen_setup' => [
            'title' => 'Setting Up Your Kitchen',
            'content' => 'Essential kitchen setup for beginners:<br>• <strong>Basic tools:</strong> Chef\'s knife, cutting board, measuring cups/spoons<br>• <strong>Cookware essentials:</strong> One good pan, medium pot with lid<br>• <strong>Prep tools:</strong> Can opener, vegetable peeler, wooden spoons<br>• <strong>Storage basics:</strong> Food containers, aluminum foil, plastic wrap<br>• <strong>Organize by use:</strong> Keep frequently used items easily accessible<br>• <strong>Maintain cleanliness:</strong> Clean as you go, sanitize surfaces<br>• <strong>Start small:</strong> Build your kitchen tools gradually as you cook more'
        ],
        'basic_techniques' => [
            'title' => 'Basic Cooking Techniques',
            'content' => 'Fundamental cooking methods to master:<br>• <strong>Sautéing:</strong> Cook quickly in a little fat over medium-high heat<br>• <strong>Boiling:</strong> Cooking in bubbling water (212°F/100°C)<br>• <strong>Simmering:</strong> Gentle cooking in barely bubbling liquid<br>• <strong>Baking:</strong> Dry heat cooking in an oven<br>• <strong>Pan-frying:</strong> Cooking in a pan with some oil<br>• <strong>Scrambling:</strong> Continuously stirring while cooking (like eggs)<br>• <strong>Practice:</strong> Start with simple recipes to build confidence'
        ],
        'recipe_reading' => [
            'title' => 'How to Read and Follow Recipes',
            'content' => 'Successfully following recipes:<br>• <strong>Read completely first:</strong> Understand all steps before starting<br>• <strong>Gather ingredients:</strong> Measure everything out (mise en place)<br>• <strong>Understand terminology:</strong> "Dice" vs "chop" vs "mince"<br>• <strong>Check equipment needed:</strong> Ensure you have required tools<br>• <strong>Note cooking times:</strong> These are estimates, use your senses<br>• <strong>Follow order:</strong> Steps are arranged for efficiency<br>• <strong>Taste and adjust:</strong> Season according to your preference'
        ],
        'seasoning_basics' => [
            'title' => 'Basic Seasoning and Flavoring',
            'content' => 'Foundation of good flavoring:<br>• <strong>Salt:</strong> Enhances all flavors, add gradually throughout cooking<br>• <strong>Pepper:</strong> Add freshly ground for best flavor<br>• <strong>Garlic and onion:</strong> Base flavors for most cuisines<br>• <strong>Fresh herbs:</strong> Add at end, dried herbs early in cooking<br>• <strong>Acid:</strong> Lemon juice or vinegar brightens dishes<br>• <strong>Taste as you go:</strong> Adjust seasoning throughout cooking<br>• <strong>Start conservatively:</strong> You can always add more'
        ],
        'cooking_safety' => [
            'title' => 'Kitchen Safety Fundamentals',
            'content' => 'Essential safety practices:<br>• <strong>Knife safety:</strong> Cut away from body, keep knives sharp<br>• <strong>Hot surface awareness:</strong> Use pot holders, be aware of hot handles<br>• <strong>Cross-contamination:</strong> Separate raw meat from other foods<br>• <strong>Hand washing:</strong> Wash hands frequently and thoroughly<br>• <strong>Temperature safety:</strong> Cook foods to proper internal temperatures<br>• <strong>Clean as you go:</strong> Keep workspace organized and clean<br>• <strong>Fire safety:</strong> Know how to properly extinguish grease fires'
        ],
        'beginner_recipes' => [
            'title' => 'Easy Recipes for Beginners',
            'content' => 'Start with these simple recipes:<br>• <strong>Scrambled eggs:</strong> Practice heat control and timing<br>• <strong>Pasta with simple sauce:</strong> Learn boiling and basic sauce making<br>• <strong>Roasted vegetables:</strong> Practice oven cooking and seasoning<br>• <strong>Rice pilaf:</strong> Learn absorption cooking method<br>• <strong>Pan-seared chicken:</strong> Practice protein cooking and doneness<br>• <strong>Simple soup:</strong> Combine techniques like sautéing and simmering<br>• <strong>Basic salad:</strong> Practice knife skills and flavor balancing'
        ]
    ]
];

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'start_chat':
            $_SESSION['chat_history'] = [];
            $_SESSION['current_preferences'] = [];
            $_SESSION['current_question'] = 0;
            $_SESSION['chat_mode'] = 'main_menu';
            
            echo json_encode([
                'success' => true,
                'message' => 'Hello! I\'m your Folklore cooking assistant. How can I help you today?',
                'categories' => $chatCategories
            ]);
            break;
            
        case 'select_category':
            $category = $_POST['category'];
            $_SESSION['chat_mode'] = $category;
            
            switch ($category) {
                case 'recipe_finder':
                    // Get first category from database
                    $stmt = $pdo->prepare("SELECT * FROM categories ORDER BY category_id LIMIT 1");
                    $stmt->execute();
                    $dbCategory = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Get options for first category
                    $stmt = $pdo->prepare("SELECT * FROM category_options WHERE category_id = ?");
                    $stmt->execute([$dbCategory['category_id']]);
                    $options = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo json_encode([
                        'success' => true,
                        'message' => $dbCategory['question_text'],
                        'options' => $options,
                        'category_id' => $dbCategory['category_id'],
                        'mode' => 'recipe_finder'
                    ]);
                    break;
                    
                case 'platform_guide':
                    global $platformGuide;
                    echo json_encode([
                        'success' => true,
                        'message' => $platformGuide['main']['question'],
                        'options' => $platformGuide['main']['options'],
                        'mode' => 'platform_guide'
                    ]);
                    break;
                    
                case 'cooking_tips':
                    global $cookingTips;
                    echo json_encode([
                        'success' => true,
                        'message' => $cookingTips['main']['question'],
                        'options' => $cookingTips['main']['options'],
                        'mode' => 'cooking_tips'
                    ]);
                    break;
                    
                case 'restaurant_finder':
                    global $restaurantGuide;
                    echo json_encode([
                        'success' => true,
                        'message' => $restaurantGuide['main']['question'],
                        'options' => $restaurantGuide['main']['options'],
                        'mode' => 'restaurant_finder'
                    ]);
                    break;
                    
                case 'nutrition_info':
                    global $nutritionGuide;
                    echo json_encode([
                        'success' => true,
                        'message' => $nutritionGuide['main']['question'],
                        'options' => $nutritionGuide['main']['options'],
                        'mode' => 'nutrition_info'
                    ]);
                    break;
                    
                case 'recipe_modifications':
                    global $recipeModifications;
                    echo json_encode([
                        'success' => true,
                        'message' => $recipeModifications['main']['question'],
                        'options' => $recipeModifications['main']['options'],
                        'mode' => 'recipe_modifications'
                    ]);
                    break;
                    
                case 'meal_planning':
                    global $mealPlanning;
                    echo json_encode([
                        'success' => true,
                        'message' => $mealPlanning['main']['question'],
                        'options' => $mealPlanning['main']['options'],
                        'mode' => 'meal_planning'
                    ]);
                    break;
                    
                case 'cooking_basics':
                    global $cookingBasics;
                    echo json_encode([
                        'success' => true,
                        'message' => $cookingBasics['main']['question'],
                        'options' => $cookingBasics['main']['options'],
                        'mode' => 'cooking_basics'
                    ]);
                    break;
                    
                default:
                    echo json_encode([
                        'success' => true,
                        'message' => 'This feature is coming soon! Please choose another option.',
                        'back_to_menu' => true
                    ]);
                    break;
            }
            break;
            
        case 'handle_response':
            $mode = $_POST['mode'];
            $responseId = $_POST['response_id'];
            
            switch ($mode) {
                case 'platform_guide':
                    global $platformGuide;
                    if (isset($platformGuide['responses'][$responseId])) {
                        echo json_encode([
                            'success' => true,
                            'response' => $platformGuide['responses'][$responseId],
                            'keep_options' => true,
                            'options' => $platformGuide['main']['options'],
                            'mode' => 'platform_guide'
                        ]);
                    }
                    break;
                    
                case 'cooking_tips':
                    global $cookingTips;
                    if (isset($cookingTips['responses'][$responseId])) {
                        echo json_encode([
                            'success' => true,
                            'response' => $cookingTips['responses'][$responseId],
                            'keep_options' => true,
                            'options' => $cookingTips['main']['options'],
                            'mode' => 'cooking_tips'
                        ]);
                    }
                    break;
                    
                case 'restaurant_finder':
                    global $restaurantGuide;
                    if (isset($restaurantGuide['responses'][$responseId])) {
                        echo json_encode([
                            'success' => true,
                            'response' => $restaurantGuide['responses'][$responseId],
                            'keep_options' => true,
                            'options' => $restaurantGuide['main']['options'],
                            'mode' => 'restaurant_finder'
                        ]);
                    }
                    break;
                    
                case 'nutrition_info':
                    global $nutritionGuide;
                    if (isset($nutritionGuide['responses'][$responseId])) {
                        echo json_encode([
                            'success' => true,
                            'response' => $nutritionGuide['responses'][$responseId],
                            'keep_options' => true,
                            'options' => $nutritionGuide['main']['options'],
                            'mode' => 'nutrition_info'
                        ]);
                    }
                    break;
                    
                case 'recipe_modifications':
                    global $recipeModifications;
                    if (isset($recipeModifications['responses'][$responseId])) {
                        echo json_encode([
                            'success' => true,
                            'response' => $recipeModifications['responses'][$responseId],
                            'keep_options' => true,
                            'options' => $recipeModifications['main']['options'],
                            'mode' => 'recipe_modifications'
                        ]);
                    }
                    break;
                    
                case 'meal_planning':
                    global $mealPlanning;
                    if (isset($mealPlanning['responses'][$responseId])) {
                        echo json_encode([
                            'success' => true,
                            'response' => $mealPlanning['responses'][$responseId],
                            'keep_options' => true,
                            'options' => $mealPlanning['main']['options'],
                            'mode' => 'meal_planning'
                        ]);
                    }
                    break;
                    
                case 'cooking_basics':
                    global $cookingBasics;
                    if (isset($cookingBasics['responses'][$responseId])) {
                        echo json_encode([
                            'success' => true,
                            'response' => $cookingBasics['responses'][$responseId],
                            'keep_options' => true,
                            'options' => $cookingBasics['main']['options'],
                            'mode' => 'cooking_basics'
                        ]);
                    }
                    break;
            }
            break;
            
        case 'submit_answer':
            $category_id = $_POST['category_id'];
            $option_id = $_POST['option_id'];
            
            // Store preference
            $_SESSION['current_preferences'][$category_id] = $option_id;
            
            // Get next category
            $next_category_id = $category_id + 1;
            $stmt = $pdo->prepare("SELECT * FROM categories WHERE category_id = ?");
            $stmt->execute([$next_category_id]);
            $next_category = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($next_category) {
                // Get options for next category
                $stmt = $pdo->prepare("SELECT * FROM category_options WHERE category_id = ?");
                $stmt->execute([$next_category['category_id']]);
                $options = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'success' => true,
                    'message' => $next_category['question_text'],
                    'options' => $options,
                    'category_id' => $next_category['category_id']
                ]);
            } else {
                // All questions answered, find recipes
                $recipes = findRecipesEnhanced($_SESSION['current_preferences'], $pdo);
                
                echo json_encode([
                    'success' => true,
                    'completed' => true,
                    'recipes' => $recipes
                ]);
            }
            break;
            
        case 'get_recipe_details':
            $recipe_id = $_POST['recipe_id'];
            $stmt = $pdo->prepare("SELECT * FROM recipes WHERE recipe_id = ?");
            $stmt->execute([$recipe_id]);
            $recipe = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'recipe' => $recipe
            ]);
            break;
            
        case 'back_to_menu':
            $_SESSION['chat_mode'] = 'main_menu';
            echo json_encode([
                'success' => true,
                'message' => 'How else can I help you today?',
                'categories' => $chatCategories
            ]);
            break;
            
        case 'reset_chat':
            $_SESSION['chat_history'] = [];
            $_SESSION['current_preferences'] = [];
            $_SESSION['current_question'] = 0;
            $_SESSION['chat_mode'] = 'main_menu';
            
            echo json_encode(['success' => true]);
            break;
    }
    exit;
}

function findRecipesEnhanced($preferences, $pdo) {
    // Convert preferences array to ordered values matching category order
    $orderedPrefs = [];
    for ($i = 1; $i <= 4; $i++) {
        if (isset($preferences[$i])) {
            $orderedPrefs[] = $preferences[$i];
        }
    }
    
    if (empty($orderedPrefs)) {
        return [];
    }
    
    $results = [];
    
    // Strategy 1: Exact match (all preferences)
    $results = findWithMatchCount($orderedPrefs, count($orderedPrefs), $pdo);
    
    // Strategy 2: High similarity (3 out of 4 matches)
    if (empty($results) && count($orderedPrefs) >= 3) {
        $results = findWithMatchCount($orderedPrefs, count($orderedPrefs) - 1, $pdo);
    }
    
    // Strategy 3: Medium similarity (2 out of 4 matches)
    if (empty($results) && count($orderedPrefs) >= 2) {
        $results = findWithMatchCount($orderedPrefs, count($orderedPrefs) - 2, $pdo);
    }
    
    // Strategy 4: Any similarity (at least 1 match)
    if (empty($results)) {
        $results = findWithMatchCount($orderedPrefs, 1, $pdo);
    }
    
    // Strategy 5: Popular recipes fallback
    if (empty($results)) {
        $results = getPopularRecipes($pdo);
    }
    
    // Add similarity score to results
    foreach ($results as &$recipe) {
        $recipe['similarity_score'] = calculateSimilarityScore($recipe['recipe_id'], $orderedPrefs, $pdo);
    }
    
    // Sort by similarity score
    usort($results, function($a, $b) {
        return $b['similarity_score'] <=> $a['similarity_score'];
    });
    
    return array_slice($results, 0, 12); // Return top 12 results
}

function findWithMatchCount($preferences, $minMatches, $pdo) {
    $placeholders = implode(',', array_fill(0, count($preferences), '?'));
    
    $query = "
        SELECT r.*, COUNT(DISTINCT ro.option_id) as match_count
        FROM recipes r
        JOIN recipe_options ro ON r.recipe_id = ro.recipe_id
        WHERE ro.option_id IN ($placeholders)
        GROUP BY r.recipe_id
        HAVING match_count >= ?
        ORDER BY match_count DESC, r.name
        LIMIT 15
    ";
    
    $stmt = $pdo->prepare($query);
    $values = array_merge($preferences, [$minMatches]);
    $stmt->execute($values);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPopularRecipes($pdo) {
    // Get some popular/featured recipes as fallback
    $query = "
        SELECT r.*, 0 as match_count
        FROM recipes r
        ORDER BY r.recipe_id DESC
        LIMIT 8
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function calculateSimilarityScore($recipeId, $userPreferences, $pdo) {
    // Get recipe options
    $stmt = $pdo->prepare("SELECT option_id FROM recipe_options WHERE recipe_id = ?");
    $stmt->execute([$recipeId]);
    $recipeOptions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($recipeOptions) || empty($userPreferences)) {
        return 0;
    }
    
    // Calculate intersection
    $matches = array_intersect($userPreferences, $recipeOptions);
    
    // Calculate similarity score (percentage of user preferences matched)
    $score = (count($matches) / count($userPreferences)) * 100;
    
    return round($score, 2);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Folklore | AI Cooking Assistant</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563EB;
            --primary-dark: #1D4ED8;
            --primary-light: #3B82F6;
            --secondary: #F59E0B;
            --secondary-dark: #D97706;
            --accent: #EF4444;
            --accent-light: #F87171;
            --success: #10B981;
            --warning: #F59E0B;
            --error: #EF4444;
            
            --gray-50: #F9FAFB;
            --gray-100: #F3F4F6;
            --gray-200: #E5E7EB;
            --gray-300: #D1D5DB;
            --gray-400: #9CA3AF;
            --gray-500: #6B7280;
            --gray-600: #4B5563;
            --gray-700: #374151;
            --gray-800: #1F2937;
            --gray-900: #111827;
            
            --white: #FFFFFF;
            --black: #000000;
            
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            
            --radius-sm: 0.375rem;
            --radius: 0.5rem;
            --radius-md: 0.75rem;
            --radius-lg: 1rem;
            --radius-xl: 1.5rem;
            
            --gradient-primary: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            --gradient-secondary: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-dark) 100%);
            --gradient-hero: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 50%, var(--secondary) 100%);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--gray-50);
            color: var(--gray-900);
            line-height: 1.6;
            font-size: 16px;
        }
        
        .app-container {
            max-width: 1200px;
            margin: 0 auto;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: var(--white);
            box-shadow: var(--shadow-xl);
            position: relative;
        }
        
        .header {
            background: var(--gradient-hero);
            color: var(--white);
            padding: 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="white" opacity="0.1"><path d="M0,0 Q250,100 500,50 T1000,25 L1000,0 Z"/></svg>') no-repeat center bottom;
            background-size: cover;
        }
        
        .header-content {
            position: relative;
            z-index: 2;
        }
        
        .header h1 {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .header .logo-icon {
            font-size: 2rem;
            color: var(--secondary);
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }
        
        .header p {
            font-weight: 400;
            font-size: 1.1rem;
            opacity: 0.95;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            position: relative;
        }
        
        .start-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 4rem 2rem;
            text-align: center;
            flex: 1;
            background: linear-gradient(135deg, var(--gray-50) 0%, var(--white) 100%);
            position: relative;
        }
        
        .start-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" fill="none"><circle cx="20" cy="20" r="2" fill="%23E5E7EB" opacity="0.3"/><circle cx="80" cy="40" r="2" fill="%23E5E7EB" opacity="0.3"/><circle cx="40" cy="80" r="2" fill="%23E5E7EB" opacity="0.3"/></svg>') repeat;
            opacity: 0.4;
        }
        
        .start-content {
            position: relative;
            z-index: 2;
            max-width: 600px;
        }
        
        .start-container h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--gray-900);
            font-weight: 600;
        }
        
        .start-container p {
            font-size: 1.1rem;
            margin-bottom: 2.5rem;
            color: var(--gray-600);
            line-height: 1.7;
        }
        
        .start-button {
            background: var(--gradient-primary);
            color: var(--white);
            border: none;
            border-radius: var(--radius-lg);
            padding: 1rem 2.5rem;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            box-shadow: var(--shadow-md);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin: 0 auto;
        }
        
        .start-button:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        
        .start-button:active {
            transform: translateY(0);
        }
        
        .chat-container {
            display: flex;
            flex-direction: column;
            height: 100%;
            background: var(--white);
        }
        
        .chat-area {
            flex: 1;
            overflow-y: auto;
            padding: 2rem;
            background: linear-gradient(135deg, var(--gray-50) 0%, var(--white) 100%);
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            min-height: 400px;
        }
        
        .message {
            max-width: 75%;
            padding: 1rem 1.5rem;
            border-radius: var(--radius-lg);
            position: relative;
            animation: slideIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-sm);
        }
        
        @keyframes slideIn {
            from { 
                opacity: 0; 
                transform: translateY(20px);
            }
            to { 
                opacity: 1; 
                transform: translateY(0);
            }
        }
        
        .bot-message {
            background: var(--white);
            color: var(--gray-800);
            border: 1px solid var(--gray-200);
            align-self: flex-start;
            border-bottom-left-radius: var(--radius-sm);
            position: relative;
        }
        
        .bot-message::before {
            content: '';
            position: absolute;
            bottom: -1px;
            left: -8px;
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 0 8px 8px 0;
            border-color: transparent var(--white) transparent transparent;
        }
        
        .user-message {
            background: var(--gradient-primary);
            color: var(--white);
            align-self: flex-end;
            border-bottom-right-radius: var(--radius-sm);
            position: relative;
        }
        
        .user-message::before {
            content: '';
            position: absolute;
            bottom: -1px;
            right: -8px;
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 0 0 8px 8px;
            border-color: transparent transparent var(--primary) transparent;
        }
        
        .message-icon {
            margin-right: 0.5rem;
            opacity: 0.8;
        }
        
        .categories-container {
            padding: 2rem;
            background: var(--white);
            border-top: 1px solid var(--gray-200);
        }
        
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }
        
        .category-card {
            background: var(--white);
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .category-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: var(--gradient-primary);
            transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1;
            opacity: 0.05;
        }
        
        .category-card:hover::before {
            left: 0;
        }
        
        .category-card:hover {
            border-color: var(--primary);
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }
        
        .category-content {
            position: relative;
            z-index: 2;
        }
        
        .category-icon {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 1rem;
            transition: color 0.3s;
        }
        
        .category-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
        }
        
        .category-description {
            font-size: 0.9rem;
            color: var(--gray-600);
            line-height: 1.5;
        }
        
        .options-container {
            padding: 2rem;
            background: var(--white);
            border-top: 1px solid var(--gray-200);
            box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .options-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1rem;
        }
        
        .option-button {
            padding: 1rem 1.25rem;
            background: var(--white);
            color: var(--primary);
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-lg);
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .option-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: var(--gradient-primary);
            transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1;
        }
        
        .option-button span {
            position: relative;
            z-index: 2;
            transition: color 0.3s;
        }
        
        .option-button:hover::before {
            left: 0;
        }
        
        .option-button:hover span {
            color: var(--white);
        }
        
        .option-button:hover {
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .response-container {
            padding: 2rem;
            background: var(--gray-50);
            flex: 1;
            display: flex;
            gap: 2rem;
        }
        
        .response-content {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow);
            flex: 2;
        }
        
        .response-options {
            flex: 1;
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            max-height: fit-content;
        }
        
        .response-options h4 {
            font-family: 'Playfair Display', serif;
            color: var(--primary);
            font-size: 1.2rem;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .response-options-grid {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .response-option-button {
            padding: 0.75rem 1rem;
            background: var(--white);
            color: var(--primary);
            border: 1px solid var(--gray-300);
            border-radius: var(--radius);
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-align: left;
        }
        
        .response-option-button:hover {
            background: var(--primary);
            color: var(--white);
            border-color: var(--primary);
            transform: translateX(4px);
        }
        
        .response-option-button.active {
            background: var(--primary);
            color: var(--white);
            border-color: var(--primary);
        }
        
        .response-content h3 {
            font-family: 'Playfair Display', serif;
            color: var(--primary);
            font-size: 1.75rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .response-text {
            color: var(--gray-700);
            line-height: 1.8;
            font-size: 1rem;
        }
        
        .response-text strong {
            color: var(--gray-900);
            font-weight: 600;
        }
        
        .back-to-menu-button {
            background: var(--gradient-secondary);
            color: var(--white);
            border: none;
            border-radius: var(--radius-lg);
            padding: 0.75rem 2rem;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 2rem auto 0;
            box-shadow: var(--shadow);
        }
        
        .back-to-menu-button:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .recipes-container {
            padding: 2rem;
            background: var(--gray-50);
            flex: 1;
        }
        
        .recipes-header {
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .recipes-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }
        
        .recipes-header p {
            color: var(--gray-600);
            font-size: 1.1rem;
        }
        
        .recipes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }
        
        .recipe-card {
            background: var(--white);
            border-radius: var(--radius-xl);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            display: flex;
            flex-direction: column;
            position: relative;
        }
        
        .recipe-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--gradient-primary);
            opacity: 0;
            transition: opacity 0.3s;
            z-index: 1;
            border-radius: var(--radius-xl);
        }
        
        .recipe-card:hover::before {
            opacity: 0.05;
        }
        
        .recipe-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
        }
        
        .recipe-image {
            height: 200px;
            background: var(--gray-200);
            background-size: cover;
            background-position: center;
            position: relative;
            overflow: hidden;
        }
        
        .recipe-image::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 50%;
            background: linear-gradient(transparent, rgba(0,0,0,0.1));
        }
        
        .similarity-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--white);
            color: var(--primary);
            padding: 0.25rem 0.75rem;
            border-radius: var(--radius);
            font-size: 0.8rem;
            font-weight: 600;
            box-shadow: var(--shadow-sm);
            z-index: 2;
        }
        
        .recipe-content {
            padding: 1.5rem;
            flex: 1;
            display: flex;
            flex-direction: column;
            position: relative;
            z-index: 2;
        }
        
        .recipe-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: var(--gray-900);
            line-height: 1.4;
        }
        
        .recipe-description {
            font-size: 0.9rem;
            color: var(--gray-600);
            margin-bottom: 1.25rem;
            flex: 1;
            line-height: 1.6;
        }
        
        .recipe-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            color: var(--gray-500);
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }
        
        .meta-item i {
            color: var(--primary);
        }
        
        .reset-section {
            text-align: center;
            margin-top: 2rem;
        }
        
        .reset-button {
            background: var(--white);
            color: var(--secondary);
            border: 2px solid var(--secondary);
            padding: 0.75rem 2rem;
            border-radius: var(--radius-lg);
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.95rem;
        }
        
        .reset-button:hover {
            background: var(--secondary);
            color: var(--white);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(8px);
            animation: fadeIn 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .modal-content {
            background: var(--white);
            margin: 2% auto;
            padding: 0;
            border-radius: var(--radius-xl);
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow: hidden;
            box-shadow: var(--shadow-xl);
            position: relative;
            animation: slideInModal 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideInModal {
            from { 
                opacity: 0;
                transform: translateY(-20px) scale(0.95);
            }
            to { 
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        .modal-header {
            position: sticky;
            top: 0;
            background: var(--white);
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 10;
        }
        
        .modal-body {
            max-height: calc(90vh - 80px);
            overflow-y: auto;
            padding: 2rem;
        }
        
        .close {
            background: var(--gray-100);
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray-600);
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .close:hover {
            background: var(--gray-200);
            color: var(--gray-800);
            transform: rotate(90deg);
        }
        
        .loading {
            text-align: center;
            color: var(--gray-600);
            padding: 3rem 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.5rem;
        }
        
        .spinner {
            width: 48px;
            height: 48px;
            border: 4px solid var(--gray-200);
            border-radius: 50%;
            border-top-color: var(--primary);
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .recipe-details-content {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }
        
        .recipe-header {
            text-align: center;
        }
        
        .recipe-header h2 {
            font-family: 'Playfair Display', serif;
            color: var(--gray-900);
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        
        .recipe-image-large {
            height: 300px;
            background: var(--gray-200);
            background-size: cover;
            background-position: center;
            border-radius: var(--radius-lg);
            margin: 1rem 0;
            box-shadow: var(--shadow-md);
        }
        
        .recipe-meta-large {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin: 1.5rem 0;
            flex-wrap: wrap;
        }
        
        .meta-item-large {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1rem;
            color: var(--gray-600);
            background: var(--gray-50);
            padding: 0.5rem 1rem;
            border-radius: var(--radius);
        }
        
        .meta-item-large i {
            color: var(--primary);
        }
        
        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            color: var(--gray-900);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .section-title i {
            color: var(--primary);
        }
        
        .ingredients-list {
            background: var(--gray-50);
            padding: 1.5rem;
            border-radius: var(--radius-lg);
            border: 1px solid var(--gray-200);
        }
        
        .ingredients-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 0.75rem;
        }
        
        .ingredient-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            /* Removed hover animations as requested */
        }
        
        .ingredient-item i {
            color: var(--success);
            font-size: 0.9rem;
        }
        
        .instructions-list {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            counter-reset: instruction-counter;
        }
        
        .instruction-item {
            display: flex;
            gap: 1.25rem;
            counter-increment: instruction-counter;
            padding: 1.25rem;
            background: var(--gray-50);
            border-radius: var(--radius-lg);
            border-left: 4px solid var(--primary);
        }
        
        .instruction-item::before {
            content: counter(instruction-counter);
            background: var(--gradient-primary);
            color: var(--white);
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            font-weight: 600;
            flex-shrink: 0;
            box-shadow: var(--shadow-sm);
        }
        
        .instruction-text {
            flex: 1;
            line-height: 1.7;
            color: var(--gray-700);
        }
        
        .no-results {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--gray-600);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.5rem;
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
        }
        
        .no-results i {
            font-size: 3rem;
            color: var(--gray-400);
        }
        
        .no-results h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            color: var(--gray-700);
        }
        
        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .header {
                padding: 1.5rem 1rem;
            }
            
            .header h1 {
                font-size: 2rem;
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .start-container {
                padding: 3rem 1.5rem;
            }
            
            .start-container h2 {
                font-size: 2rem;
            }
            
            .categories-grid {
                grid-template-columns: 1fr;
            }
            
            .options-grid {
                grid-template-columns: 1fr;
            }
            
            .recipes-grid {
                grid-template-columns: 1fr;
            }
            
            .ingredients-grid {
                grid-template-columns: 1fr;
            }
            
            .modal-content {
                width: 95%;
                margin: 5% auto;
            }
            
            .modal-body {
                padding: 1.5rem;
            }
            
            .recipe-meta-large {
                gap: 1rem;
            }
            
            .instruction-item {
                flex-direction: column;
                gap: 1rem;
            }
            
            .instruction-item::before {
                align-self: flex-start;
            }
            
            .response-container {
                flex-direction: column;
                gap: 1.5rem;
            }
            
            .response-content {
                flex: 1;
            }
            
            .response-options {
                flex: 1;
                order: -1;
            }
        }
        
        @media (max-width: 480px) {
            .header h1 {
                font-size: 1.5rem;
            }
            
            .start-container h2 {
                font-size: 1.75rem;
            }
            
            .recipes-header h2 {
                font-size: 1.5rem;
            }
            
            .recipe-header h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <div class="header">
            <div class="header-content">
                <h1>
                    <i class="fas fa-utensils logo-icon"></i>
                    Folklore
                </h1>
                <p>Your intelligent cooking companion for recipes, tips, and culinary guidance</p>
            </div>
        </div>
        
        <div class="main-content">
            <div id="start-container" class="start-container">
                <div class="start-content">
                    <h2>Welcome to Folklore Assistant</h2>
                    <p>I'm here to help you with all your cooking needs. Whether you're looking for recipes, cooking tips, platform guidance, or restaurant recommendations, I've got you covered!</p>
                    <button id="start-button" class="start-button">
                        <i class="fas fa-comments"></i> Start Chatting
                    </button>
                </div>
            </div>
            
            <div id="chat-container" class="chat-container" style="display: none;">
                <div id="chat-area" class="chat-area" style="display: none;">
                    <div id="chat-history"></div>
                </div>
                
                <div id="categories-container" class="categories-container" style="display: none;">
                    <div id="categories-list" class="categories-grid"></div>
                </div>
                
                <div id="options-container" class="options-container" style="display: none;">
                    <div id="options-list" class="options-grid"></div>
                </div>
                
                <div id="response-container" class="response-container" style="display: none;">
                    <div id="response-content" class="response-content"></div>
                </div>
            </div>
            
            <div id="recipes-container" class="recipes-container" style="display: none;">
                <div class="recipes-header">
                    <h2><i class="fas fa-chef-hat"></i> Your Recipe Recommendations</h2>
                    <p>Based on your preferences, here are some delicious recipes we think you'll love</p>
                </div>
                <div id="recipes-list" class="recipes-grid"></div>
                <div class="reset-section">
                    <button id="reset-button" class="reset-button">
                        <i class="fas fa-redo-alt"></i> Start New Conversation
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recipe Details Modal -->
    <div id="recipe-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Recipe Details</h3>
                <button class="close">&times;</button>
            </div>
            <div class="modal-body">
                <div id="recipe-details" class="recipe-details-content"></div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            let currentCategoryId = null;
            let currentMode = 'main_menu';
            
            // Start chat button click
            $('#start-button').click(function() {
                startChat();
            });
            
            // Reset button click
            $('#reset-button').click(function() {
                resetChat();
            });
            
            // Close modals
            $('.close').click(function() {
                $(this).closest('.modal').hide();
            });
            
            // Click outside modal to close
            $(window).click(function(event) {
                if ($(event.target).hasClass('modal')) {
                    $('.modal').hide();
                }
            });
            
            function startChat() {
                $('#start-container').hide();
                $('#chat-container').show();
                $('#chat-area').show();
                $('#categories-container').show();
                
                // Show loading state
                $('#chat-history').html(`
                    <div class="loading">
                        <div class="spinner"></div>
                        <p>Initializing your cooking assistant...</p>
                    </div>
                `);
                
                $.ajax({
                    url: '',
                    method: 'POST',
                    data: { action: 'start_chat' },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#chat-history').empty();
                            addMessage('bot', response.message);
                            showCategories(response.categories);
                        }
                    },
                    error: function() {
                        showError('Error starting chat. Please try again.');
                        $('#start-container').show();
                        $('#chat-container').hide();
                    }
                });
            }
            
            function addMessage(sender, message) {
                const messageClass = sender === 'bot' ? 'bot-message' : 'user-message';
                const icon = sender === 'bot' ? '<i class="fas fa-robot message-icon"></i>' : '<i class="fas fa-user message-icon"></i>';
                
                const messageHtml = `
                    <div class="message ${messageClass}">
                        ${icon}${message}
                    </div>
                `;
                $('#chat-history').append(messageHtml);
                $('#chat-area').scrollTop($('#chat-area')[0].scrollHeight);
            }
            
            function showCategories(categories) {
                $('#categories-list').empty();
                $('#options-container').hide();
                $('#response-container').hide();
                $('#categories-container').show();
                
                Object.keys(categories).forEach(function(key) {
                    const category = categories[key];
                    const categoryCard = $(`
                        <div class="category-card" data-category="${key}">
                            <div class="category-content">
                                <div class="category-icon">
                                    <i class="${category.icon}"></i>
                                </div>
                                <h3 class="category-title">${category.title}</h3>
                                <p class="category-description">${category.description}</p>
                            </div>
                        </div>
                    `);
                    
                    categoryCard.click(function() {
                        const categoryKey = $(this).data('category');
                        selectCategory(categoryKey, category.title);
                    });
                    
                    $('#categories-list').append(categoryCard);
                });
            }
            
            function selectCategory(categoryKey, categoryTitle) {
                addMessage('user', categoryTitle);
                currentMode = categoryKey;
                
                // Show loading
                $('#categories-container').hide();
                $('#options-container').show();
                $('#options-list').html(`
                    <div class="loading">
                        <div class="spinner"></div>
                        <p>Loading options...</p>
                    </div>
                `);
                
                $.ajax({
                    url: '',
                    method: 'POST',
                    data: { 
                        action: 'select_category',
                        category: categoryKey
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            if (response.back_to_menu) {
                                addMessage('bot', response.message);
                                showBackToMenuButton();
                            } else {
                                addMessage('bot', response.message);
                                
                                if (response.mode === 'recipe_finder') {
                                    showRecipeOptions(response.options, response.category_id);
                                } else {
                                    showChatOptions(response.options, response.mode);
                                }
                            }
                        }
                    },
                    error: function() {
                        showError('Error loading category. Please try again.');
                    }
                });
            }
            
            function showRecipeOptions(options, categoryId) {
                currentCategoryId = categoryId;
                $('#options-list').empty();
                
                options.forEach(function(option) {
                    const button = $(`
                        <button class="option-button" data-option-id="${option.option_id}">
                            <span>${option.display_name}</span>
                        </button>
                    `);
                    button.click(function() {
                        const optionId = $(this).data('option-id');
                        selectRecipeOption(optionId, categoryId, option.display_name);
                    });
                    $('#options-list').append(button);
                });
            }
            
            function showChatOptions(options, mode) {
                $('#options-list').empty();
                
                options.forEach(function(option) {
                    const button = $(`
                        <button class="option-button" data-response-id="${option.id}">
                            <span>${option.text}</span>
                        </button>
                    `);
                    button.click(function() {
                        const responseId = $(this).data('response-id');
                        handleChatResponse(responseId, mode, option.text);
                    });
                    $('#options-list').append(button);
                });
            }
            
            function selectRecipeOption(optionId, categoryId, displayName) {
                addMessage('user', displayName);
                
                // Show loading
                $('#options-list').html(`
                    <div class="loading">
                        <div class="spinner"></div>
                        <p>Processing your preferences...</p>
                    </div>
                `);
                
                $.ajax({
                    url: '',
                    method: 'POST',
                    data: { 
                        action: 'submit_answer',
                        option_id: optionId,
                        category_id: categoryId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            if (response.completed) {
                                // Show recipes
                                $('#chat-container').hide();
                                $('#recipes-container').show();
                                showRecipes(response.recipes);
                            } else {
                                // Show next question
                                addMessage('bot', response.message);
                                showRecipeOptions(response.options, response.category_id);
                                currentCategoryId = response.category_id;
                            }
                        }
                    },
                    error: function() {
                        showError('Error processing your choice. Please try again.');
                    }
                });
            }
            
            function handleChatResponse(responseId, mode, userText) {
                addMessage('user', userText);
                
                $.ajax({
                    url: '',
                    method: 'POST',
                    data: { 
                        action: 'handle_response',
                        response_id: responseId,
                        mode: mode
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            if (response.keep_options) {
                                // Add engaging bot response to chat history
                                let engagingResponse = getEngagingResponse(response.response.title);
                                addMessage('bot', engagingResponse);
                                
                                // Show split view with response on left, options on right
                                $('#options-container').hide();
                                $('#response-container').show();
                                
                                $('#response-content').html(`
                                    <h3>${response.response.title}</h3>
                                    <div class="response-text">${response.response.content}</div>
                                    <button class="back-to-menu-button" onclick="backToMenu()" style="margin-top: 2rem;">
                                        <i class="fas fa-arrow-left"></i> Back to Main Menu
                                    </button>
                                `);
                                
                                // Show options on the right
                                if (!$('#response-options').length) {
                                    $('#response-container').append(`
                                        <div id="response-options" class="response-options">
                                            <h4>Other Topics</h4>
                                            <div id="response-options-list" class="response-options-grid"></div>
                                        </div>
                                    `);
                                }
                                
                                $('#response-options-list').empty();
                                response.options.forEach(function(option) {
                                    const activeClass = option.id === responseId ? 'active' : '';
                                    const button = $(`
                                        <button class="response-option-button ${activeClass}" data-response-id="${option.id}">
                                            ${option.text}
                                        </button>
                                    `);
                                    button.click(function() {
                                        if (!$(this).hasClass('active')) {
                                            const newResponseId = $(this).data('response-id');
                                            const newOptionText = $(this).text();
                                            handleChatResponseSplit(newResponseId, mode, newOptionText);
                                        }
                                    });
                                    $('#response-options-list').append(button);
                                });
                            } else {
                                // Original single response view
                                $('#options-container').hide();
                                $('#response-container').show();
                                
                                let backButton = '';
                                if (response.show_back_to_menu) {
                                    backButton = `
                                        <button class="back-to-menu-button" onclick="backToMenu()">
                                            <i class="fas fa-arrow-left"></i> Back to Main Menu
                                        </button>
                                    `;
                                }
                                
                                $('#response-content').html(`
                                    <h3>${response.response.title}</h3>
                                    <div class="response-text">${response.response.content}</div>
                                    ${backButton}
                                `);
                            }
                        }
                    },
                    error: function() {
                        showError('Error loading response. Please try again.');
                    }
                });
            }
            
            function handleChatResponseSplit(responseId, mode, userText) {
                // Add the user message to chat history
                addMessage('user', userText);
                
                // Get the response and update content
                $.ajax({
                    url: '',
                    method: 'POST',
                    data: { 
                        action: 'handle_response',
                        response_id: responseId,
                        mode: mode
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Add engaging bot response to chat history
                            let engagingResponse = getEngagingResponse(response.response.title);
                            addMessage('bot', engagingResponse);
                            
                            // Update the detailed content on the left
                            $('#response-content').html(`
                                <h3>${response.response.title}</h3>
                                <div class="response-text">${response.response.content}</div>
                                <button class="back-to-menu-button" onclick="backToMenu()" style="margin-top: 2rem;">
                                    <i class="fas fa-arrow-left"></i> Back to Main Menu
                                </button>
                            `);
                            
                            // Update active state
                            $('.response-option-button').removeClass('active');
                            $(`.response-option-button[data-response-id="${responseId}"]`).addClass('active');
                        }
                    },
                    error: function() {
                        showError('Error loading response. Please try again.');
                    }
                });
            }
            
            function getEngagingResponse(title) {
                const engagingResponses = {
                    // Platform Guide responses
                    'How to Like a Recipe': 'Great choice! Let me show you how to save your favorite recipes with just one click! ❤️',
                    'How to Comment on a Recipe': 'Perfect! Sharing your cooking experiences helps our community grow. Here\'s how to leave feedback! 💬',
                    'How to Save Recipes': 'Smart thinking! Building your personal recipe collection is so useful. Here\'s the easy way! 🔖',
                    'How to Share Recipes': 'Wonderful! Spreading delicious recipes with friends makes cooking even more fun! 📤',
                    'How to Create an Account': 'Excellent! Getting started with Folklore will unlock so many features for you! 🌟',
                    'Managing Your Profile': 'Good question! Personalizing your profile helps us give you better recommendations! ⚙️',
                    
                    // Cooking Tips responses
                    'Essential Knife Skills': 'Fantastic choice! Mastering knife skills will transform your cooking efficiency! 🔪',
                    'Cooking Methods Guide': 'Perfect! Understanding different cooking methods opens up endless possibilities! 🍳',
                    'Spices and Herbs Guide': 'Excellent! Spices are the secret to making any dish extraordinary! 🌿',
                    'Food Safety Essentials': 'Very important! Keeping your kitchen safe ensures delicious and healthy meals! 🛡️',
                    'Baking Success Tips': 'Great pick! Baking is both science and art - let me share the secrets! 🧁',
                    'Essential Kitchen Tools': 'Smart question! Having the right tools makes cooking so much easier! 🔧',
                    
                    // Restaurant Guide responses
                    'Find Restaurants Near You': 'Perfect! Let me help you discover amazing dining spots in your area! 📍',
                    'Exploring Different Cuisines': 'Exciting! Trying new cuisines is such a delicious adventure! 🌍',
                    'Restaurant Etiquette': 'Great thinking! Good etiquette makes dining experiences better for everyone! 🍽️',
                    'Making Restaurant Reservations': 'Smart move! Proper reservation skills ensure you get the best tables! 📞',
                    'Restaurants for Dietary Restrictions': 'Important question! Everyone deserves to enjoy dining out safely! 🌱',
                    'Best Restaurant Finding Apps': 'Excellent! Technology makes finding great food so much easier! 📱',
                    
                    // Nutrition responses
                    'Reading Nutrition Labels': 'Great choice! Understanding labels empowers you to make healthier decisions! 🏷️',
                    'Healthy Ingredient Substitutions': 'Smart thinking! Small swaps can make big differences in nutrition! 🔄',
                    'Portion Control Tips': 'Wise question! Portion awareness is key to balanced eating! ⚖️',
                    'Creating Balanced Meals': 'Perfect! Balanced meals give you energy and keep you satisfied! 🥗',
                    'Special Dietary Needs': 'Important topic! Everyone deserves to enjoy delicious, suitable food! 💚',
                    'Healthy Cooking Methods': 'Excellent! Cooking methods can boost nutrition while keeping flavors amazing! 🥘',
                    
                    // Recipe Modifications responses
                    'Dietary Adaptations': 'Great question! Adapting recipes means everyone can enjoy the same delicious meal! 🔄',
                    'Reduce Calories and Fat': 'Smart choice! You can make dishes lighter without sacrificing flavor! ✨',
                    'Increase Protein Content': 'Perfect! Boosting protein helps with energy and satisfaction! 💪',
                    'Scaling Recipes Up or Down': 'Practical thinking! Whether cooking for one or twenty, I\'ve got you covered! 📏',
                    'Common Ingredient Substitutions': 'Lifesaver topic! These swaps will rescue so many cooking situations! 🆘',
                    'Allergy-Friendly Modifications': 'Very important! Safe cooking means everyone can enjoy the meal together! 🛡️',
                    
                    // Meal Planning responses
                    'Weekly Meal Planning Strategies': 'Excellent choice! Good planning saves time, money, and stress! 📅',
                    'Meal Prep Basics and Tips': 'Smart thinking! Meal prep is your secret weapon for busy weeks! 📦',
                    'Budget-Friendly Meal Planning': 'Great question! Eating well doesn\'t have to break the bank! 💰',
                    'Creating Efficient Shopping Lists': 'Perfect! Organized shopping saves time and prevents forgotten ingredients! 📝',
                    'Batch Cooking Techniques': 'Brilliant strategy! Cook once, eat multiple times - so efficient! 🍲',
                    'Food Storage and Organization': 'Essential knowledge! Proper storage keeps food fresh and your kitchen organized! 📦',
                    
                    // Cooking Basics responses
                    'Setting Up Your Kitchen': 'Perfect starting point! A well-organized kitchen makes cooking so much easier! 🏠',
                    'Basic Cooking Techniques': 'Excellent foundation! These techniques will serve you for life! 👨‍🍳',
                    'How to Read and Follow Recipes': 'Great question! Recipe reading is like learning a new language - so useful! 📖',
                    'Basic Seasoning and Flavoring': 'Fantastic choice! Good seasoning is what separates good cooks from great ones! 🧂',
                    'Kitchen Safety Fundamentals': 'Very important! A safe kitchen is a happy kitchen! 🛡️',
                    'Easy Recipes for Beginners': 'Perfect! Everyone starts somewhere - these will build your confidence! 🌟'
                };
                
                return engagingResponses[title] || 'Here\'s what you need to know about this topic!';
            }
            
            function showRecipes(recipes) {
                if (recipes.length === 0) {
                    $('#recipes-list').html(`
                        <div class="no-results">
                            <i class="fas fa-search"></i>
                            <h3>No recipes found</h3>
                            <p>We couldn't find any recipes matching your exact preferences, but don't worry! Try adjusting your selections or browse our popular recipes.</p>
                        </div>
                    `);
                    return;
                }
                
                $('#recipes-list').empty();
                recipes.forEach(function(recipe) {
                    const totalTime = (parseInt(recipe.prep_time) || 0) + (parseInt(recipe.cook_time) || 0);
                    const similarityScore = recipe.similarity_score || 0;
                    
                    const recipeCard = $(`
                        <div class="recipe-card" data-recipe-id="${recipe.recipe_id}">
                            ${similarityScore > 0 ? `<div class="similarity-badge">${similarityScore}% match</div>` : ''}
                            <div class="recipe-image" style="background-image: url('${recipe.image_url || 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80'}')"></div>
                            <div class="recipe-content">
                                <h3 class="recipe-title">${recipe.name}</h3>
                                <p class="recipe-description">${recipe.description || 'A delicious recipe crafted with traditional techniques and authentic flavors.'}</p>
                                <div class="recipe-meta">
                                    <span class="meta-item">
                                        <i class="fas fa-clock"></i> ${totalTime || 30} min
                                    </span>
                                    <span class="meta-item">
                                        <i class="fas fa-users"></i> ${recipe.servings || 4} servings
                                    </span>
                                </div>
                            </div>
                        </div>
                    `);
                    
                    recipeCard.click(function() {
                        const recipeId = $(this).data('recipe-id');
                        showRecipeDetails(recipeId);
                    });
                    
                    $('#recipes-list').append(recipeCard);
                });
            }
            
            function showRecipeDetails(recipeId) {
                // Show loading in modal
                $('#recipe-details').html(`
                    <div class="loading">
                        <div class="spinner"></div>
                        <p>Loading recipe details...</p>
                    </div>
                `);
                $('#recipe-modal').show();
                
                $.ajax({
                    url: '',
                    method: 'POST',
                    data: { 
                        action: 'get_recipe_details',
                        recipe_id: recipeId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            const recipe = response.recipe;
                            const instructionsFormatted = formatInstructions(recipe.instructions);
                            const ingredientsFormatted = formatIngredients(recipe.ingredients);
                            const totalTime = (parseInt(recipe.prep_time) || 0) + (parseInt(recipe.cook_time) || 0);
                            
                            $('#recipe-details').html(`
                                <div class="recipe-header">
                                    <h2>${recipe.name}</h2>
                                    <div class="recipe-image-large" style="background-image: url('${recipe.image_url || 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80'}')"></div>
                                    <div class="recipe-meta-large">
                                        <span class="meta-item-large">
                                            <i class="fas fa-clock"></i> Prep: ${recipe.prep_time || 15} min
                                        </span>
                                        <span class="meta-item-large">
                                            <i class="fas fa-fire"></i> Cook: ${recipe.cook_time || 15} min
                                        </span>
                                        <span class="meta-item-large">
                                            <i class="fas fa-users"></i> Serves: ${recipe.servings || 4}
                                        </span>
                                    </div>
                                    ${recipe.description ? `<p style="color: var(--gray-600); font-size: 1.1rem; line-height: 1.6; margin-top: 1rem;">${recipe.description}</p>` : ''}
                                </div>
                                
                                <div class="ingredients-section">
                                    <h3 class="section-title">
                                        <i class="fas fa-carrot"></i> Ingredients
                                    </h3>
                                    <div class="ingredients-list">
                                        ${ingredientsFormatted}
                                    </div>
                                </div>
                                
                                <div class="instructions-section">
                                    <h3 class="section-title">
                                        <i class="fas fa-list-ol"></i> Instructions
                                    </h3>
                                    <div class="instructions-list">
                                        ${instructionsFormatted}
                                    </div>
                                </div>
                            `);
                        }
                    },
                    error: function() {
                        $('#recipe-details').html(`
                            <div class="no-results">
                                <i class="fas fa-exclamation-triangle"></i>
                                <h3>Error Loading Recipe</h3>
                                <p>We couldn't load the recipe details. Please try again.</p>
                            </div>
                        `);
                    }
                });
            }
            
            function formatIngredients(ingredients) {
                if (!ingredients) return '<p>No ingredients listed.</p>';
                
                // Split by comma, semicolon, or newline and create grid items
                const items = ingredients.split(/[,;\n]/).map(item => item.trim()).filter(item => item.length > 0);
                let html = '<div class="ingredients-grid">';
                items.forEach(item => {
                    html += `
                        <div class="ingredient-item">
                            <i class="fas fa-check-circle"></i>
                            <span>${item}</span>
                        </div>
                    `;
                });
                html += '</div>';
                return html;
            }
            
            function formatInstructions(instructions) {
                if (!instructions) return '<p>No instructions available.</p>';
                
                // Split by numbered steps, newlines, or periods followed by space
                const steps = instructions.split(/\n|(?=\d+\.)|(?<=\.)\s+(?=[A-Z])/)
                    .map(step => step.trim())
                    .filter(step => step.length > 0);
                
                let html = '';
                steps.forEach(step => {
                    if (step.trim()) {
                        // Remove existing numbering
                        const cleanStep = step.replace(/^\d+\.\s*/, '');
                        if (cleanStep.length > 0) {
                            html += `
                                <div class="instruction-item">
                                    <div class="instruction-text">${cleanStep}</div>
                                </div>
                            `;
                        }
                    }
                });
                return html || '<p>No instructions available.</p>';
            }
            
            function resetChat() {
                $.ajax({
                    url: '',
                    method: 'POST',
                    data: { action: 'reset_chat' },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#chat-container').hide();
                            $('#recipes-container').hide();
                            $('#start-container').show();
                            $('#chat-history').empty();
                            $('#categories-list').empty();
                            $('#options-list').empty();
                            $('#recipes-list').empty();
                            $('#response-content').empty();
                            $('#response-options').remove();
                            currentMode = 'main_menu';
                        }
                    },
                    error: function() {
                        showError('Error resetting chat. Please try again.');
                    }
                });
            }
            
            function showError(message) {
                // You can implement a toast notification system here
                alert(message);
            }
            
            // Global function for back to menu button
            window.backToMenu = function() {
                $.ajax({
                    url: '',
                    method: 'POST',
                    data: { action: 'back_to_menu' },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            addMessage('bot', response.message);
                            showCategories(response.categories);
                            $('#response-container').hide();
                            $('#response-options').remove();
                        }
                    },
                    error: function() {
                        showError('Error returning to menu. Please try again.');
                    }
                });
            };
        });
    </script>
</body>
</html>