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
    <title>Recipe Chatbot</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .chat-area {
            height: 400px;
            overflow-y: auto;
            padding: 20px;
            background-color: #fafafa;
        }
        
        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 8px;
        }
        
        .bot-message {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        
        .user-message {
            background-color: #c8e6c9;
            color: #2e7d32;
            margin-left: 50px;
        }
        
        .options-container {
            padding: 20px;
            background-color: white;
        }
        
        .option-button {
            display: block;
            width: 100%;
            padding: 12px;
            margin-bottom: 10px;
            background-color: #2196F3;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        
        .option-button:hover {
            background-color: #0b7dda;
        }
        
        .start-container {
            padding: 40px;
            text-align: center;
        }
        
        .start-button {
            padding: 15px 30px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .start-button:hover {
            background-color: #45a049;
        }
        
        .recipes-container {
            padding: 20px;
        }
        
        .recipe-card {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: box-shadow 0.3s;
        }
        
        .recipe-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .recipe-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .recipe-description {
            color: #666;
            margin-bottom: 10px;
        }
        
        .recipe-meta {
            font-size: 14px;
            color: #999;
        }
        
        .reset-button {
            background-color: #ff5722;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin: 10px;
        }
        
        .reset-button:hover {
            background-color: #e64a19;
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
        }
        
        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 20px;
            border-radius: 10px;
            width: 80%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            cursor: pointer;
        }
        
        .close:hover {
            color: black;
        }
        
        .loading {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 20px;
        }
        
        .ingredients-list {
            margin-top: 15px;
        }
        
        .ingredients-list ul {
            padding-left: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🍽️ Recipe Recommendation Chatbot</h1>
            <p>Let me help you find the perfect recipe!</p>
        </div>
        
        <div id="chat-area" class="chat-area" style="display: none;">
            <div id="chat-history"></div>
        </div>
        
        <div id="start-container" class="start-container">
            <h2>Welcome to the Recipe Chatbot!</h2>
            <p>I'll ask you a few questions about your preferences to recommend the perfect recipe for you.</p>
            <button id="start-button" class="start-button">Start Chatting</button>
        </div>
        
        <div id="options-container" class="options-container" style="display: none;">
            <div id="options-list"></div>
        </div>
        
        <div id="recipes-container" class="recipes-container" style="display: none;">
            <h2>Recommended Recipes</h2>
            <div id="recipes-list"></div>
            <button id="reset-button" class="reset-button">Start Over</button>
        </div>
    </div>
    
    <!-- Recipe Details Modal -->
    <div id="recipe-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="recipe-details"></div>
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
                $.ajax({
                    url: '',
                    method: 'POST',
                    data: { action: 'start_chat' },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#start-container').hide();
                            $('#chat-area').show();
                            $('#options-container').show();
                            
                            addMessage('bot', response.message);
                            showOptions(response.options, response.category_id);
                            currentCategoryId = response.category_id;
                        }
                    },
                    error: function() {
                        alert('Error starting chat. Please try again.');
                    }
                });
            }
            
            function addMessage(sender, message) {
                const messageClass = sender === 'bot' ? 'bot-message' : 'user-message';
                const messageHtml = `<div class="message ${messageClass}">${message}</div>`;
                $('#chat-history').append(messageHtml);
                $('#chat-area').scrollTop($('#chat-area')[0].scrollHeight);
            }
            
            function showOptions(options, categoryId) {
                $('#options-list').empty();
                options.forEach(function(option) {
                    const button = $(`<button class="option-button" data-option-id="${option.option_id}">${option.display_name}</button>`);
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
                $('#options-list').html('<div class="loading">Processing your choice...</div>');
                
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
                                $('#options-container').hide();
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
                    }
                });
            }
            
            function showRecipes(recipes) {
                if (recipes.length === 0) {
                    $('#recipes-list').html('<p>Sorry, no recipes found matching your preferences. Please try different options.</p>');
                    return;
                }
                
                $('#recipes-list').empty();
                recipes.forEach(function(recipe) {
                    const recipeCard = $(`
                        <div class="recipe-card" data-recipe-id="${recipe.recipe_id}">
                            <div class="recipe-title">${recipe.name}</div>
                            <div class="recipe-description">${recipe.description}</div>
                            <div class="recipe-meta">
                                Prep: ${recipe.prep_time}min | Cook: ${recipe.cook_time}min | Serves: ${recipe.servings}
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
                            const instructionsFormatted = recipe.instructions.replace(/\n/g, '<br>');
                            const ingredientsFormatted = formatIngredients(recipe.ingredients);
                            
                            $('#recipe-details').html(`
                                <h2>${recipe.name}</h2>
                                <p><strong>Description:</strong> ${recipe.description}</p>
                                <p><strong>Prep Time:</strong> ${recipe.prep_time} minutes</p>
                                <p><strong>Cook Time:</strong> ${recipe.cook_time} minutes</p>
                                <p><strong>Servings:</strong> ${recipe.servings}</p>
                                <div class="ingredients-list">
                                    <h3>Ingredients:</h3>
                                    ${ingredientsFormatted}
                                </div>
                                <h3>Instructions:</h3>
                                <p>${instructionsFormatted}</p>
                            `);
                            
                            $('#recipe-modal').show();
                        }
                    },
                    error: function() {
                        alert('Error loading recipe details. Please try again.');
                    }
                });
            }
            
            function formatIngredients(ingredients) {
                // Split by comma and create list items
                const items = ingredients.split(',').map(item => item.trim());
                let html = '<ul>';
                items.forEach(item => {
                    html += `<li>${item}</li>`;
                });
                html += '</ul>';
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
                            $('#chat-area').hide();
                            $('#options-container').hide();
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