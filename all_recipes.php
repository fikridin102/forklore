<?php
session_start();
include('dbconnect.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: module3_User Authentication/signin/signin.php");
    exit();
}

// Filter/search logic
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

$sql = "SELECT * FROM recipe WHERE 1";
$params = [];
$types = '';

if ($search !== '') {
    $sql .= " AND (recipe_name LIKE ? OR recipe_ingredient LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'ss';
}
if ($category !== '') {
    $sql .= " AND category = ?";
    $params[] = $category;
    $types .= 's';
}

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$show_success = false;
if (isset($_SESSION['success'])) {
    $show_success = true;
    unset($_SESSION['success']);
}
$error = isset($_GET['error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/images/RecipeLogo.jpeg" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <title>All Recipes</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;400&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', Arial, sans-serif;
            background: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .header {
            background: #fff;
            padding: 32px 0 16px 0;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            position: relative;
        }
        .header h1 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
            color: #222;
            letter-spacing: 1px;
        }
        .user-info-bar {
            position: absolute;
            right: 32px;
            top: 32px;
            display: flex;
            gap: 16px;
            align-items: center;
        }
        .welcome-msg {
            color: #666;
            font-size: 0.9rem;
        }
        .logout-btn {
            background: #f1f5fb;
            color: #4f8cff;
            text-decoration: none;
            padding: 6px 14px;
            border-radius: 4px;
            transition: background 0.2s;
        }
        .logout-btn:hover {
            background: #e0e7ef;
        }
        .add-recipe-bar {
            margin: 24px 0 16px 0;
        }
        .add-btn {
            background: #4f8cff;
            color: #fff;
            border: none;
            padding: 0 26px 0 18px;
            border-radius: 10px;
            font-size: 1.08rem;
            font-weight: 500;
            height: 48px;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: background 0.2s;
            box-shadow: none;
            margin-left: 8px;
        }
        .add-btn i {
            font-size: 1.15rem;
        }
        .add-btn:hover {
            background: #2563eb;
        }
        .container {
            max-width: 1100px;
            margin: 40px auto 0 auto;
            padding: 0 16px;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 32px;
        }
        .card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.06);
            padding: 0 0 18px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: box-shadow 0.2s;
            position: relative;
            overflow: hidden;
        }
        .card:hover {
            box-shadow: 0 6px 24px rgba(79,140,255,0.13);
            transform: translateY(-5px);
        }
        .card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 18px 18px 0 0;
        }
        .card h3 {
            margin: 18px 0 8px 0;
            font-size: 1.2rem;
            font-weight: 600;
            color: #222;
        }
        .card .meta {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #666;
            font-size: 0.9rem;
        }
        .meta i {
            color: #4f8cff;
        }
        .card .btns {
            margin-top: auto;
            padding: 10px;
            width: 100%;
            display: flex;
            justify-content: center;
        }
        .card .btn {
            background: #f1f5fb;
            color: #4f8cff;
            border: none;
            border-radius: 6px;
            padding: 6px 14px;
            font-size: 0.95rem;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
        }
        .card .btn:hover {
            background: #4f8cff;
            color: #fff;
        }
        .btn i {
            margin-right: 5px;
        }
        .category-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(79,140,255,0.9);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: capitalize;
        }
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0; top: 0; width: 100vw; height: 100vh;
            background: rgba(0,0,0,0.18);
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background: #fff;
            border-radius: 16px;
            padding: 38px 36px 24px 36px;
            max-width: 480px;
            width: 98%;
            position: relative;
            box-shadow: 0 6px 32px rgba(0,0,0,0.13);
            display: flex;
            flex-direction: column;
            align-items: stretch;
        }
        .modal-content h2 {
            margin: 0 0 22px 0;
            font-size: 1.35rem;
            font-weight: 600;
            color: #222;
            letter-spacing: 0.5px;
            text-align: left;
        }
        .close {
            position: absolute;
            right: 18px;
            top: 16px;
            font-size: 1.5rem;
            color: #bbb;
            cursor: pointer;
            transition: color 0.18s;
            z-index: 10;
        }
        .close:hover {
            color: #4f8cff;
        }
        .modal-content form {
            display: flex;
            flex-direction: column;
            gap: 0;
        }
        .modal-content input,
        .modal-content select,
        .custom-file-label,
        .modal-content textarea {
            width: 100%;
            height: 54px;
            margin-bottom: 14px;
            padding: 0 16px;
            border: none;
            border-radius: 10px;
            font-size: 1.08rem;
            font-family: inherit;
            background: #f4f6fa;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
            transition: box-shadow 0.18s, background 0.18s;
            display: block;
            box-sizing: border-box;
        }
        .modal-content textarea {
            min-height: 54px;
            max-height: 120px;
            padding-top: 16px;
            padding-bottom: 16px;
            resize: vertical;
        }
        .custom-file-label {
            color: #888;
            cursor: pointer;
            border: none;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
            transition: background 0.18s;
            text-align: left;
            user-select: none;
            display: flex;
            align-items: center;
            padding: 0 16px;
            height: 54px;
        }
        .custom-file-label.selected {
            color: #222;
        }
        .modal-content button[type=submit] {
            width: 100%;
            background: #4f8cff;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 15px 0;
            font-size: 1.08rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
            box-shadow: 0 2px 8px rgba(79,140,255,0.07);
            transition: background 0.18s;
        }
        .modal-content button[type=submit]:hover {
            background: #2563eb;
        }
        /* Toast/Alert */
        .toast {
            position: fixed;
            top: 24px;
            right: 24px;
            background: #4f8cff;
            color: #fff;
            padding: 14px 28px;
            border-radius: 8px;
            font-size: 1rem;
            z-index: 2000;
            box-shadow: 0 2px 8px rgba(79,140,255,0.13);
            display: none;
        }
        .toast.error {
            background: #e74c3c;
        }
        @media (max-width: 600px) {
            .header h1 { font-size: 1.5rem; }
            .add-btn, .category-filter {
                width: 100%;
                margin-left: 0;
            }
            .container { padding: 0 4px; }
            .modal-content {
                max-width: 98vw;
                padding: 18px 4vw 18px 4vw;
            }
            .modal-content h2 {
                font-size: 1.1rem;
            }
        }
        .filter-bar {
            background: none;
            box-shadow: none;
            border-radius: 0;
            padding: 0 0 24px 0;
            margin-bottom: 0;
            display: flex;
            justify-content: center;
        }
        .filter-bar form {
            display: flex;
            gap: 16px;
            align-items: center;
            width: 100%;
            max-width: 900px;
        }
        .search-wrapper {
            position: relative;
            flex: 1;
        }
        .search-input {
            width: 100%;
            padding: 12px 44px 12px 18px;
            border: 2px solid #f3d37a;
            border-radius: 32px;
            font-size: 1.15rem;
            background: #fff;
            transition: border 0.2s;
            box-sizing: border-box;
        }
        .search-input:focus {
            border: 2px solid #f3d37a;
            outline: none;
        }
        .search-icon {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #f3d37a;
            font-size: 1.3rem;
            pointer-events: none;
        }
        .category-filter {
            flex: 0 0 auto;
            padding: 12px 18px;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1.08rem;
            background: #f8f9fa;
            transition: border 0.2s;
            height: 48px;
            margin-left: 8px;
        }
        .category-filter:focus {
            border: 1.5px solid #4f8cff;
            outline: none;
        }
        /* Minimalist ChatBot Help Button */
        .chatbot-help-btn {
            position: fixed;
            bottom: 32px;
            right: 32px;
            z-index: 1500;
            background: none;
            color: #4f8cff;
            border: none;
            border-radius: 0;
            padding: 6px 10px;
            font-size: 1.05rem;
            font-family: 'Montserrat', Arial, sans-serif;
            cursor: pointer;
            text-decoration: none;
            transition: color 0.18s;
            box-shadow: none;
            outline: none;
            display: inline-block;
        }
        .chatbot-help-btn:hover {
            color: #2563eb;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>All Recipes</h1>
        <div class="user-info-bar">
            <span class="welcome-msg">Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
            <a href="module3_User Authentication/signin/signin.php?logout=1" class="btn logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <?php if ($show_success): ?>
        <div class="toast" id="toast-success" style="display:block;">Recipe added successfully!</div>
    <?php elseif (isset($_GET['error'])): ?>
        <div class="toast error" id="toast-error" style="display:block;">
            <?php
            $error = $_GET['error'];
            switch($error) {
                case 'missing_fields':
                    echo 'Please fill in all required fields.';
                    break;
                case 'upload_dir_failed':
                    echo 'Failed to create upload directory.';
                    break;
                case 'no_file':
                    echo 'No image file was uploaded.';
                    break;
                case 'invalid_image':
                    echo 'The uploaded file is not a valid image.';
                    break;
                case 'file_too_large':
                    echo 'The image file is too large (max 5MB).';
                    break;
                case 'invalid_format':
                    echo 'Only JPG, JPEG, PNG & GIF files are allowed.';
                    break;
                case 'upload_failed':
                    echo 'Failed to upload the image file.';
                    break;
                case 'db_prepare_failed':
                    echo 'Database preparation failed.';
                    break;
                case 'db_insert_failed':
                    echo 'Failed to save recipe to database.';
                    break;
                default:
                    echo 'An error occurred. Please try again.';
            }
            ?>
        </div>
    <?php endif; ?>
    <div class="container">
        <div class="filter-bar">
            <form method="get" autocomplete="off">
                <div class="search-wrapper">
                    <input type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" class="search-input">
                    <span class="search-icon"><i class="fas fa-search"></i></span>
                </div>
                <select name="category" class="category-filter">
                    <option value="">All Categories</option>
                    <option value="vegan" <?php if(isset($_GET['category']) && $_GET['category']==='vegan') echo 'selected'; ?>>Vegan</option>
                    <option value="vegetarian" <?php if(isset($_GET['category']) && $_GET['category']==='vegetarian') echo 'selected'; ?>>Vegetarian</option>
                    <option value="meat" <?php if(isset($_GET['category']) && $_GET['category']==='meat') echo 'selected'; ?>>Meat</option>
                    <option value="seafood" <?php if(isset($_GET['category']) && $_GET['category']==='seafood') echo 'selected'; ?>>Seafood</option>
                    <option value="dessert" <?php if(isset($_GET['category']) && $_GET['category']==='dessert') echo 'selected'; ?>>Dessert</option>
                    <option value="breakfast" <?php if(isset($_GET['category']) && $_GET['category']==='breakfast') echo 'selected'; ?>>Breakfast</option>
                    <option value="lunch" <?php if(isset($_GET['category']) && $_GET['category']==='lunch') echo 'selected'; ?>>Lunch</option>
                    <option value="dinner" <?php if(isset($_GET['category']) && $_GET['category']==='dinner') echo 'selected'; ?>>Dinner</option>
                </select>
                <button type="button" class="add-btn" onclick="openAddRecipeModal()"><i class="fas fa-plus"></i> Add Recipe</button>
            </form>
        </div>
        <div class="grid">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="card">
                        <img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="Recipe Image">
                        <div class="category-badge"><?php echo htmlspecialchars($row['category'] ?: 'Uncategorized'); ?></div>
                        <h3><?php echo htmlspecialchars($row['recipe_name']); ?></h3>
                        <div class="meta">
                            <i class="fas fa-clock"></i> Prep: <?php echo htmlspecialchars($row['recipe_preptime']); ?> min &middot;
                            Cook: <?php echo htmlspecialchars($row['recipe_cookingtime']); ?> min
                        </div>
                        <div class="btns">
                            <button class="btn" onclick="window.location.href='recipe/recipe.html?id=<?php echo $row['recipe_id']; ?>'">
                                <i class="fas fa-eye"></i> View
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="grid-column:1/-1;text-align:center;color:#888;">No recipes found.</p>
            <?php endif; ?>
        </div>
    </div>
    <!-- Add Recipe Modal -->
    <div id="addRecipeModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAddRecipeModal()" tabindex="0" aria-label="Close">&times;</span>
            <h2>Add Recipe</h2>
            <form action="add_recipe.php" method="post" enctype="multipart/form-data" autocomplete="off">
                <input type="text" name="recipe_name" placeholder="Recipe Name" required>
                <select name="category" required>
                    <option value="">Category</option>
                    <option value="vegan">Vegan</option>
                    <option value="vegetarian">Vegetarian</option>
                    <option value="meat">Meat</option>
                    <option value="seafood">Seafood</option>
                    <option value="dessert">Dessert</option>
                    <option value="breakfast">Breakfast</option>
                    <option value="lunch">Lunch</option>
                    <option value="dinner">Dinner</option>
                </select>
                <input type="number" name="recipe_preptime" placeholder="Prep Time (minutes)" required>
                <input type="number" name="recipe_cookingtime" placeholder="Cooking Time (minutes)" required>
                <textarea name="recipe_ingredient" placeholder="Ingredients" required></textarea>
                <textarea name="recipe_cookstep" placeholder="Steps" required></textarea>
                <div class="file-input-wrapper">
                    <!-- <label for="recipe_image" class="custom-file-label" id="fileLabel" tabindex="0">Choose Image</label> -->
                    <input type="file" name="recipe_image" id="recipe_image" accept="image/*" required>
                </div>
                <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                <button type="submit">Submit</button>
            </form>
        </div>
    </div>
    <a href="#" class="chatbot-help-btn">Need Help? Ask The ChatBot!</a>
    <script>
        function openAddRecipeModal() {
            document.getElementById('addRecipeModal').style.display = 'flex';
        }
        function closeAddRecipeModal() {
            document.getElementById('addRecipeModal').style.display = 'none';
        }
        window.onclick = function(event) {
            var modal = document.getElementById('addRecipeModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
        // Hide toast after 2.5s
        window.onload = function() {
            setTimeout(function() {
                var toast = document.querySelector('.toast');
                if (toast) toast.style.display = 'none';
            }, 2500);
        }
        // Auto-submit the search/filter form when the user changes the search input or category dropdown
        document.querySelector('.search-input').addEventListener('input', function() {
            this.form.submit();
        });
        document.querySelector('.category-filter').addEventListener('change', function() {
            this.form.submit();
        });
        // Custom file input label
        const fileInput = document.getElementById('recipe_image');
        const fileLabel = document.getElementById('fileLabel');
        if (fileInput && fileLabel) {
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    fileLabel.textContent = this.files[0].name;
                    fileLabel.classList.add('selected');
                } else {
                    fileLabel.textContent = 'Choose Image';
                    fileLabel.classList.remove('selected');
                }
            });
            // Make label clickable via keyboard
            fileLabel.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    fileInput.click();
                }
            });
        }
    </script>
</body>
</html> 