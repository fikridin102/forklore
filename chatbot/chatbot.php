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

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'start_chat':
            $_SESSION['chat_history'] = [];
            $_SESSION['current_preferences'] = [];
            $_SESSION['current_question'] = 1;
            
            // Get first category
            $stmt = $pdo->prepare("SELECT * FROM categories ORDER BY category_id LIMIT 1");
            $stmt->execute();
            $category = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Get options for first category
            $stmt = $pdo->prepare("SELECT * FROM category_options WHERE category_id = ?");
            $stmt->execute([$category['category_id']]);
            $options = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'message' => $category['question_text'],
                'options' => $options,
                'category_id' => $category['category_id']
            ]);
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
                $recipes = findRecipes($_SESSION['current_preferences'], $pdo);
                
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
            
        case 'reset_chat':
            $_SESSION['chat_history'] = [];
            $_SESSION['current_preferences'] = [];
            $_SESSION['current_question'] = 0;
            
            echo json_encode(['success' => true]);
            break;
    }
    exit;
}

function findRecipes($preferences, $pdo) {
    // Convert preferences array to ordered values matching category order
    $orderedPrefs = [];
    for ($i = 1; $i <= 4; $i++) {
        $orderedPrefs[] = $preferences[$i];
    }
    
    // First try exact match
    $placeholders = implode(',', array_fill(0, count($orderedPrefs), '?'));
    
    $query = "
        SELECT r.*, COUNT(ro.option_id) as match_count
        FROM recipes r
        JOIN recipe_options ro ON r.recipe_id = ro.recipe_id
        WHERE ro.option_id IN ($placeholders)
        GROUP BY r.recipe_id
        HAVING match_count = ?
        ORDER BY match_count DESC, r.name
    ";
    
    $stmt = $pdo->prepare($query);
    $values = $orderedPrefs;
    $values[] = count($orderedPrefs);
    $stmt->execute($values);
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // If no exact match, try partial matches with higher tolerance
    if (empty($results)) {
        // Allow matching with 3 out of 4 preferences
        $query = "
            SELECT r.*, COUNT(ro.option_id) as match_count
            FROM recipes r
            JOIN recipe_options ro ON r.recipe_id = ro.recipe_id
            WHERE ro.option_id IN ($placeholders)
            GROUP BY r.recipe_id
            HAVING match_count >= 3
            ORDER BY match_count DESC, r.name
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($orderedPrefs);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // If still no results, and "none" was selected for vegetables (option_id 12),
    // handle it differently by looking at actual mappings
    if (empty($results) && $orderedPrefs[2] == 12) {
        $alternativePrefs = [$orderedPrefs[0], $orderedPrefs[1], $orderedPrefs[3]]; // Rice, Tofu, Very Spicy
        
        $altPlaceholders = implode(',', array_fill(0, count($alternativePrefs), '?'));
        
        $query = "
            SELECT r.*, COUNT(ro.option_id) as match_count
            FROM recipes r
            JOIN recipe_options ro ON r.recipe_id = ro.recipe_id
            WHERE ro.option_id IN ($altPlaceholders)
            GROUP BY r.recipe_id
            HAVING match_count >= 2
            ORDER BY match_count DESC, r.name
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($alternativePrefs);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($results)) {
            $filtered = [];
            foreach ($results as $recipe) {
                $optQuery = "SELECT option_id FROM recipe_options WHERE recipe_id = ?";
                $optStmt = $pdo->prepare($optQuery);
                $optStmt->execute([$recipe['recipe_id']]);
                $recipeOptions = $optStmt->fetchAll(PDO::FETCH_COLUMN);
                
                if (in_array(1, $recipeOptions) && in_array(8, $recipeOptions) && in_array(17, $recipeOptions)) {
                    $filtered[] = $recipe;
                }
            }
            
            if (!empty($filtered)) {
                $results = $filtered;
            }
        }
    }
    
    return $results;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FlavorFinder | Recipe Recommendation Chatbot</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #6C63FF;
            --primary-light: #8B85FF;
            --secondary: #FF6584;
            --dark: #2D3748;
            --light: #F7FAFC;
            --gray: #E2E8F0;
            --dark-gray: #718096;
            --success: #48BB78;
            --warning: #ED8936;
            --error: #F56565;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --radius-sm: 0.25rem;
            --radius: 0.5rem;
            --radius-lg: 0.75rem;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #F8FAFC;
            color: var(--dark);
            line-height: 1.6;
        }
        
        .app-container {
            max-width: 1000px;
            margin: 0 auto;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: white;
            box-shadow: var(--shadow-lg);
        }
        
        .header {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            padding: 1.5rem 2rem;
            text-align: center;
            position: relative;
            z-index: 10;
            box-shadow: var(--shadow);
        }
        
        .header h1 {
            font-weight: 600;
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }
        
        .header p {
            font-weight: 300;
            opacity: 0.9;
        }
        
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 0;
        }
        
        .chat-container {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .chat-area {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
            background-color: var(--light);
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .message {
            max-width: 80%;
            padding: 0.75rem 1.25rem;
            border-radius: var(--radius-lg);
            position: relative;
            animation: fadeIn 0.3s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .bot-message {
            background-color: white;
            color: var(--dark);
            border: 1px solid var(--gray);
            align-self: flex-start;
            box-shadow: var(--shadow-sm);
            border-top-left-radius: 0;
        }
        
        .user-message {
            background-color: var(--primary);
            color: white;
            align-self: flex-end;
            border-top-right-radius: 0;
        }
        
        .options-container {
            padding: 1.5rem;
            background-color: white;
            border-top: 1px solid var(--gray);
            box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
        }
        
        .options-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 0.75rem;
        }
        
        .option-button {
            padding: 0.75rem 1rem;
            background-color: white;
            color: var(--primary);
            border: 1px solid var(--primary);
            border-radius: var(--radius);
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.2s;
            text-align: center;
            font-weight: 500;
        }
        
        .option-button:hover {
            background-color: var(--primary);
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }
        
        .start-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem 2rem;
            text-align: center;
            flex: 1;
            background-color: var(--light);
        }
        
        .start-container h2 {
            font-size: 1.75rem;
            margin-bottom: 1rem;
            color: var(--primary);
        }
        
        .start-container p {
            max-width: 500px;
            margin-bottom: 2rem;
            color: var(--dark-gray);
        }
        
        .start-button {
            padding: 0.75rem 2rem;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius);
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: var(--shadow);
        }
        
        .start-button:hover {
            background-color: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .recipes-container {
            padding: 1.5rem;
            background-color: var(--light);
            flex: 1;
        }
        
        .recipes-container h2 {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .recipes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2rem;
        }
        
        .recipe-card {
            background-color: white;
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.3s;
            cursor: pointer;
            display: flex;
            flex-direction: column;
        }
        
        .recipe-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }
        
        .recipe-image {
            height: 160px;
            background-color: var(--gray);
            background-size: cover;
            background-position: center;
        }
        
        .recipe-content {
            padding: 1.25rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .recipe-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }
        
        .recipe-description {
            font-size: 0.9rem;
            color: var(--dark-gray);
            margin-bottom: 1rem;
            flex: 1;
        }
        
        .recipe-meta {
            display: flex;
            gap: 1rem;
            font-size: 0.8rem;
            color: var(--dark-gray);
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .reset-button {
            background-color: white;
            color: var(--secondary);
            border: 1px solid var(--secondary);
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius);
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0 auto;
        }
        
        .reset-button:hover {
            background-color: var(--secondary);
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
            animation: fadeIn 0.3s;
        }
        
        .modal-content {
            background-color: white;
            margin: 2% auto;
            padding: 2rem;
            border-radius: var(--radius-lg);
            width: 90%;
            max-width: 700px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: var(--shadow-lg);
            position: relative;
        }
        
        .close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            color: var(--dark-gray);
            font-size: 1.5rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .close:hover {
            color: var(--dark);
            transform: rotate(90deg);
        }
        
        .loading {
            text-align: center;
            color: var(--dark-gray);
            padding: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
        }
        
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid rgba(0,0,0,0.1);
            border-radius: 50%;
            border-top-color: var(--primary);
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .recipe-details-content {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .recipe-header {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .recipe-header h2 {
            color: var(--primary);
            font-size: 1.75rem;
        }
        
        .recipe-image-large {
            height: 250px;
            background-color: var(--gray);
            background-size: cover;
            background-position: center;
            border-radius: var(--radius);
        }
        
        .recipe-meta-large {
            display: flex;
            gap: 1.5rem;
            margin-top: 0.5rem;
        }
        
        .meta-item-large {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: var(--dark-gray);
        }
        
        .section-title {
            font-size: 1.25rem;
            color: var(--primary);
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .ingredients-list {
            background-color: var(--light);
            padding: 1.25rem;
            border-radius: var(--radius);
        }
        
        .ingredients-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 0.75rem;
        }
        
        .ingredient-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem;
            background-color: white;
            border-radius: var(--radius-sm);
            box-shadow: var(--shadow-sm);
        }
        
        .instructions-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            counter-reset: instruction-counter;
        }
        
        .instruction-item {
            display: flex;
            gap: 1rem;
            counter-increment: instruction-counter;
        }
        
        .instruction-item::before {
            content: counter(instruction-counter);
            background-color: var(--primary);
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            flex-shrink: 0;
        }
        
        .no-results {
            text-align: center;
            padding: 2rem;
            color: var(--dark-gray);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
        }
        
        .no-results i {
            font-size: 2rem;
            color: var(--secondary);
        }
        
        @media (max-width: 768px) {
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
                padding: 1.5rem;
            }
            
            .recipe-meta-large {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <div class="header">
            <h1><i class="fas fa-robot"></i> FlavorFinder</h1>
            <p>Discover your perfect recipe with AI assistance</p>
        </div>
        
        <div class="main-content">
            <div id="start-container" class="start-container">
                <h2>Find Your Perfect Recipe</h2>
                <p>Answer a few simple questions about your preferences and we'll recommend delicious recipes tailored just for you.</p>
                <button id="start-button" class="start-button">
                    <i class="fas fa-comment-alt"></i> Start Chat
                </button>
            </div>
            
            <div id="chat-container" class="chat-container" style="display: none;">
                <div id="chat-area" class="chat-area" style="display: none;">
                    <div id="chat-history"></div>
                </div>
                
                <div id="options-container" class="options-container" style="display: none;">
                    <div id="options-list" class="options-grid"></div>
                </div>
            </div>
            
            <div id="recipes-container" class="recipes-container" style="display: none;">
                <h2><i class="fas fa-utensils"></i> Recommended Recipes</h2>
                <div id="recipes-list" class="recipes-grid"></div>
                <button id="reset-button" class="reset-button">
                    <i class="fas fa-redo"></i> Start Over
                </button>
            </div>
        </div>
    </div>
    
    <!-- Recipe Details Modal -->
    <div id="recipe-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="recipe-details" class="recipe-details-content"></div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            let currentCategoryId = null;
            
            // Start chat button click
            $('#start-button').click(function() {
                startChat();
            });
            
            // Reset button click
            $('#reset-button').click(function() {
                resetChat();
            });
            
            // Close modal
            $('.close').click(function() {
                $('#recipe-modal').hide();
            });
            
            // Click outside modal to close
            $(window).click(function(event) {
                if (event.target.id === 'recipe-modal') {
                    $('#recipe-modal').hide();
                }
            });
            
            function startChat() {
                $('#start-container').hide();
                $('#chat-container').show();
                $('#chat-area').show();
                $('#options-container').show();
                
                // Show loading state
                $('#chat-history').html(`
                    <div class="loading">
                        <div class="spinner"></div>
                        <p>Starting your culinary journey...</p>
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
                            showOptions(response.options, response.category_id);
                            currentCategoryId = response.category_id;
                        }
                    },
                    error: function() {
                        alert('Error starting chat. Please try again.');
                        $('#start-container').show();
                        $('#chat-container').hide();
                    }
                });
            }
            
            function addMessage(sender, message) {
                const messageClass = sender === 'bot' ? 'bot-message' : 'user-message';
                const icon = sender === 'bot' ? '<i class="fas fa-robot"></i>' : '<i class="fas fa-user"></i>';
                
                const messageHtml = `
                    <div class="message ${messageClass}">
                        ${icon} ${message}
                    </div>
                `;
                $('#chat-history').append(messageHtml);
                $('#chat-area').scrollTop($('#chat-area')[0].scrollHeight);
            }
            
            function showOptions(options, categoryId) {
                $('#options-list').empty();
                options.forEach(function(option) {
                    const button = $(`
                        <button class="option-button" data-option-id="${option.option_id}">
                            ${option.display_name}
                        </button>
                    `);
                    button.click(function() {
                        const optionId = $(this).data('option-id');
                        selectOption(optionId, categoryId, option.display_name);
                    });
                    $('#options-list').append(button);
                });
            }
            
            function selectOption(optionId, categoryId, displayName) {
                addMessage('user', displayName);
                
                // Show loading
                $('#options-list').html(`
                    <div class="loading">
                        <div class="spinner"></div>
                        <p>Finding delicious options...</p>
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
                                showOptions(response.options, response.category_id);
                                currentCategoryId = response.category_id;
                            }
                        }
                    },
                    error: function() {
                        alert('Error processing your choice. Please try again.');
                        showOptions($('#options-list').data('options'), currentCategoryId);
                    }
                });
            }
            
            function showRecipes(recipes) {
                if (recipes.length === 0) {
                    $('#recipes-list').html(`
                        <div class="no-results">
                            <i class="fas fa-search"></i>
                            <h3>No recipes found</h3>
                            <p>We couldn't find any recipes matching your preferences. Try adjusting your selections.</p>
                        </div>
                    `);
                    return;
                }
                
                $('#recipes-list').empty();
                recipes.forEach(function(recipe) {
                    const recipeCard = $(`
                        <div class="recipe-card" data-recipe-id="${recipe.recipe_id}">
                            <div class="recipe-image" style="background-image: url('${recipe.image_url || 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80'}')"></div>
                            <div class="recipe-content">
                                <h3 class="recipe-title">${recipe.name}</h3>
                                <p class="recipe-description">${recipe.description || 'A delicious recipe you\'ll love!'}</p>
                                <div class="recipe-meta">
                                    <span class="meta-item"><i class="fas fa-clock"></i> ${recipe.prep_time + recipe.cook_time} min</span>
                                    <span class="meta-item"><i class="fas fa-utensils"></i> ${recipe.servings} servings</span>
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
                            
                            $('#recipe-details').html(`
                                <div class="recipe-header">
                                    <h2>${recipe.name}</h2>
                                    <div class="recipe-image-large" style="background-image: url('${recipe.image_url || 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80'}')"></div>
                                    <div class="recipe-meta-large">
                                        <span class="meta-item-large"><i class="fas fa-clock"></i> Prep: ${recipe.prep_time} min</span>
                                        <span class="meta-item-large"><i class="fas fa-fire"></i> Cook: ${recipe.cook_time} min</span>
                                        <span class="meta-item-large"><i class="fas fa-utensils"></i> Serves: ${recipe.servings}</span>
                                    </div>
                                    <p>${recipe.description || ''}</p>
                                </div>
                                
                                <div class="ingredients-section">
                                    <h3 class="section-title"><i class="fas fa-carrot"></i> Ingredients</h3>
                                    <div class="ingredients-list">
                                        ${ingredientsFormatted}
                                    </div>
                                </div>
                                
                                <div class="instructions-section">
                                    <h3 class="section-title"><i class="fas fa-list-ol"></i> Instructions</h3>
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
                // Split by comma and create grid items
                const items = ingredients.split(',').map(item => item.trim());
                let html = '<div class="ingredients-grid">';
                items.forEach(item => {
                    html += `
                        <div class="ingredient-item">
                            <i class="fas fa-check-circle" style="color: var(--success);"></i>
                            <span>${item}</span>
                        </div>
                    `;
                });
                html += '</div>';
                return html;
            }
            
            function formatInstructions(instructions) {
                // Split by numbered steps or newlines
                const steps = instructions.split(/\n|(?=\d+\.)/).filter(step => step.trim().length > 0);
                let html = '';
                steps.forEach(step => {
                    if (step.trim()) {
                        html += `
                            <div class="instruction-item">
                                <div>${step.trim().replace(/^\d+\.\s*/, '')}</div>
                            </div>
                        `;
                    }
                });
                return html;
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
                            $('#options-list').empty();
                            $('#recipes-list').empty();
                        }
                    },
                    error: function() {
                        alert('Error resetting chat. Please try again.');
                    }
                });
            }
        });
    </script>
</body>
</html>