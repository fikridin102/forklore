<?php
session_start();
include('../dbconnect.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$recipe_id = isset($_GET['id']) && is_numeric($_GET['id']) ? intval($_GET['id']) : die('Invalid recipe ID.');

// Fetch recipe
$stmt = $conn->prepare("SELECT * FROM recipe WHERE recipe_id = ?");
$stmt->bind_param("i", $recipe_id);
$stmt->execute();
$recipe = $stmt->get_result()->fetch_assoc();
if (!$recipe) die('Recipe not found.');

// Handle rating submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating'])) {
    $rating = intval($_POST['rating']);
    $user_id = $_SESSION['user_id'];

    // Check if rating exists
    $check_stmt = $conn->prepare("SELECT rating_id FROM rating WHERE recipe_id = ? AND user_id = ?");
    $check_stmt->bind_param("ii", $recipe_id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // Update existing rating
        $update_stmt = $conn->prepare("UPDATE rating SET rating_value = ?, rated_at = NOW() WHERE recipe_id = ? AND user_id = ?");
        $update_stmt->bind_param("iii", $rating, $recipe_id, $user_id);
        if ($update_stmt->execute()) {
            header("Location: recipedetail.php?id=$recipe_id&rating_success=1");
        } else {
            header("Location: recipedetail.php?id=$recipe_id&rating_error=1");
        }
    } else {
        // Insert new rating
        $insert_stmt = $conn->prepare("INSERT INTO rating (recipe_id, user_id, rating_value, rated_at) VALUES (?, ?, ?, NOW())");
        $insert_stmt->bind_param("iii", $recipe_id, $user_id, $rating);
        if ($insert_stmt->execute()) {
            header("Location: recipedetail.php?id=$recipe_id&rating_success=1");
        } else {
            header("Location: recipedetail.php?id=$recipe_id&rating_error=1");
        }
    }
    exit();
}


// Fetch average rating
$rating_stmt = $conn->prepare("SELECT AVG(rating_value) as avg_rating FROM rating WHERE recipe_id = ?");
$rating_stmt->bind_param("i", $recipe_id);
$rating_stmt->execute(); // Add this line!
$rating_result = $rating_stmt->get_result();
$rating_row = $rating_result->fetch_assoc();
$avg_rating = $rating_row['avg_rating'] !== null ? round($rating_row['avg_rating'], 1) : null;

// Handle note submission
// Fetch user's note for this recipe
$note_stmt = $conn->prepare("SELECT * FROM note WHERE user_id = ? AND recipe_id = ?");
$note_stmt->bind_param("ii", $_SESSION['user_id'], $recipe_id);
$note_stmt->execute();
$note_result = $note_stmt->get_result();
$user_note = $note_result->fetch_assoc();

// Save or update note
if (isset($_POST['note'])) {
    $note_text = trim($_POST['note']);
    $user_id = $_SESSION['user_id'];

    if ($user_note) {
        // Update
        $update_note = $conn->prepare("UPDATE note SET note_text = ?, updated_at = NOW() WHERE note_id = ?");
        $update_note->bind_param("si", $note_text, $user_note['note_id']);
        $update_note->execute();
    } else {
        // Insert
        $insert_note = $conn->prepare("INSERT INTO note (user_id, recipe_id, note_text, updated_at) VALUES (?, ?, ?, NOW())");
        $insert_note->bind_param("iis", $user_id, $recipe_id, $note_text);
        $insert_note->execute();
    }
    header("Location: recipedetail.php?id=$recipe_id&note_saved=1");
    exit();

}


// Handle comment submission
if (isset($_POST['comment_text']) && !empty($_POST['comment_text'])) {
    $comment = trim($_POST['comment_text']);
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("INSERT INTO comment (recipe_id, user_id, comment_text, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $recipe_id, $user_id, $comment);
    $stmt->execute();
    header("Location: recipedetail.php?id=$recipe_id");
    exit();
}

// Update comment
if (isset($_POST['edit_comment_id'], $_POST['edited_comment'])) {
    $comment_id = intval($_POST['edit_comment_id']);
    $new_text = trim($_POST['edited_comment']);
    $user_id = $_SESSION['user_id'];

    $update_cmt = $conn->prepare("UPDATE comment SET comment_text = ? WHERE comment_id = ? AND user_id = ?");
    $update_cmt->bind_param("sii", $new_text, $comment_id, $user_id);
    $update_cmt->execute();
    header("Location: recipedetail.php?id=$recipe_id&comment_updated=1");
    exit();
}

// Delete comment
if (isset($_POST['delete_comment_id'])) {
    $comment_id = intval($_POST['delete_comment_id']);
    $user_id = $_SESSION['user_id'];

    $delete_cmt = $conn->prepare("DELETE FROM comment WHERE comment_id = ? AND user_id = ?");
    $delete_cmt->bind_param("ii", $comment_id, $user_id);
    $delete_cmt->execute();
    header("Location: recipedetail.php?id=$recipe_id&comment_deleted=1");
    exit();
}


// Fetch comments
$comment_stmt = $conn->prepare("SELECT c.comment_id, c.comment_text, c.created_at, c.user_id, u.user_fullname FROM comment c JOIN user u ON c.user_id = u.user_id WHERE c.recipe_id = ? ORDER BY c.created_at DESC");
$comment_stmt->bind_param("i", $recipe_id);
$comment_stmt->execute();
$comments = $comment_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($recipe['recipe_name']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;400&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', Arial, sans-serif;
            background: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 900px;
            margin: 40px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.06);
            padding: 24px;
        }
        h1 {
            margin-top: 0;
            font-size: 2rem;
            color: #333;
        }
        .category-badge {
            display: inline-block;
            background: rgba(79,140,255,0.9);
            color: #fff;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-bottom: 12px;
        }
        .meta {
            color: #666;
            margin-bottom: 12px;
        }
        img.recipe-img {
            width: 100%;
            border-radius: 8px;
            margin-bottom: 16px;
        }
        h2 {
            color: #4f8cff;
            margin-top: 24px;
        }
        ul, ol {
            margin-top: 8px;
        }
        .comments {
            background: #f1f5fb;
            padding: 16px;
            border-radius: 8px;
            margin-top: 24px;
        }
        .comment {
            border-bottom: 1px solid #ccc;
            padding: 8px 0;
        }
        .comment:last-child {
            border-bottom: none;
        }
        .ingredient-item {
            cursor: pointer;
            padding: 6px;
            transition: 0.2s;
        }
        .ingredient-item:hover {
            background: #f0f8ff;
        }
        #ingredient-have .ingredient-item {
            text-decoration: line-through;
            color: #4CAF50;
        }
        ol li {
            margin-bottom: 12px;
            line-height: 1.6;
        }
        #ingredient-have {
            background: #e8f5e9;
            padding: 10px;
            border-radius: 8px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1><?php echo htmlspecialchars($recipe['recipe_name']); ?></h1>
    <span class="category-badge"><?php echo htmlspecialchars($recipe['category'] ?? 'Uncategorized'); ?></span>
    <div class="meta"><i class="fas fa-clock"></i> Prep: <?php echo $recipe['recipe_preptime']; ?> min | Cook: <?php echo $recipe['recipe_cookingtime']; ?> min</div>
    <img src="../<?php echo htmlspecialchars($recipe['image_url']); ?>" alt="Recipe Image" class="recipe-img">

    <h2>Ingredients Needed</h2>
    <p style="font-size: 0.95rem; color: #666; margin-top: -8px;">
        Click on an ingredient to mark it as “Have”. You can click again to move it back.
    </p>
    <ul id="ingredient-need">
        <?php 
        $ingredients = preg_split("/\r\n|\n|\r/", $recipe['recipe_ingredient']);
        foreach ($ingredients as $index => $ing): 
            if (trim($ing) !== ''):
        ?>
            <li class="ingredient-item" data-index="<?= $index ?>">
                <?= htmlspecialchars(trim($ing)); ?>
            </li>
        <?php 
            endif;
        endforeach; 
        ?>
    </ul>

    <h2>You Have These Ingredients</h2>
    <ul id="ingredient-have"></ul>

    <h2>Directions</h2>
    <ol>
        <?php 
        $steps = preg_split("/\r\n|\n|\r/", $recipe['recipe_cookstep']);
        foreach ($steps as $step) {
            $cleanStep = trim($step);
            if ($cleanStep !== '') {
                echo "<li>" . htmlspecialchars($cleanStep) . "</li>";
            }
        }
        ?>
    </ol>

    <h2>Average Rating: <?php echo $avg_rating !== null ? $avg_rating . "/5" : "Not yet rated"; ?></h2>
    <form method="post">
        <label>Rate this recipe:</label>
        <select name="rating" required>
            <option value="">Select</option><?php for($i=1;$i<=5;$i++) echo "<option value='$i'>$i</option>"; ?>
        </select>
        <button type="submit">Submit Rating</button>
    </form>

    <h2>Your Notes</h2>
    <form method="post">
        <textarea name="note" rows="3" cols="40"><?= htmlspecialchars($user_note['note_text'] ?? '') ?></textarea><br>
        <button type="submit">Save Note</button>
    </form>


    <div class="comments">
        <h2>Comments</h2>
        <form method="post">
            <textarea name="comment_text" required placeholder="Add a comment..."></textarea><br>
            <button type="submit">Submit Comment</button>
        </form>
        <?php if($comments->num_rows > 0): foreach($comments as $c): ?>
            <div class="comment">
                <strong><?php echo htmlspecialchars($c['user_fullname'] ?? ''); ?></strong> (<?php echo $c['created_at']; ?>)
                <p><?php echo nl2br(htmlspecialchars($c['comment_text'])); ?></p>

                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $c['user_id']): ?>
                    <button type="button" onclick="toggleEditForm(<?= $c['comment_id'] ?>)">Edit</button>
                    <form method="post" onsubmit="return confirm('Are you sure you want to delete this comment?');" style="display:inline;">
                        <input type="hidden" name="delete_comment_id" value="<?= $c['comment_id'] ?>">
                        <button type="submit" style="background:#e74c3c;color:white;">Delete</button>
                    </form>

                    <form method="post" id="edit-form-<?= $c['comment_id'] ?>" style="display:none; margin-top:10px;">
                        <input type="hidden" name="edit_comment_id" value="<?= $c['comment_id'] ?>">
                        <textarea name="edited_comment" rows="2" style="width:100%;"><?= htmlspecialchars($c['comment_text']) ?></textarea>
                        <button type="submit">Update Comment</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; else: ?>
            <p>No comments yet.</p>
        <?php endif; ?>
    </div>
</div>

<div id="popup-message" style="display:none;position:fixed;top:20px;right:20px;background:#4CAF50;color:white;padding:10px 20px;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,0.1);z-index:1000;"></div>
<script>
    window.onload = function () {
        const urlParams = new URLSearchParams(window.location.search);
        const popup = document.getElementById('popup-message');

        let message = "";
        if (urlParams.has('rating_success')) {
            message = "Your rating has been submitted successfully!";
            popup.style.background = "#4CAF50";
            urlParams.delete('rating_success');
        } else if (urlParams.has('rating_error')) {
            message = "Failed to submit your rating. Please try again.";
            popup.style.background = "#e74c3c";
            urlParams.delete('rating_error');
        } else if (urlParams.has('note_saved')) {
            message = "Note saved successfully!";
            popup.style.background = "#4CAF50";
            urlParams.delete('note_saved');
        }
        else if (urlParams.has('comment_updated')) {
            message = "Comment updated successfully!";
            popup.style.background = "#4CAF50";
            urlParams.delete('comment_updated');
        } else if (urlParams.has('comment_deleted')) {
            message = "Comment deleted.";
            popup.style.background = "#4CAF50";
            urlParams.delete('comment_deleted');
        }


        if (message) {
            popup.textContent = message;
            popup.style.display = "block";

            setTimeout(() => {
                popup.style.display = "none";

                // Construct clean URL
                const base = window.location.origin + window.location.pathname;
                const remainingParams = urlParams.toString();
                const newUrl = remainingParams ? `${base}?${remainingParams}` : base;

                window.history.replaceState({}, document.title, newUrl);
            }, 3000);
        }
    };


    // Ingredient check functionality
    function saveCheckedIngredients() {
        const checked = Array.from(document.querySelectorAll('#ingredient-have .ingredient-item'))
            .map(item => item.dataset.index);
        localStorage.setItem('recipe_<?php echo $recipe_id; ?>_checked', JSON.stringify(checked));
    }

    function loadCheckedIngredients() {
        const saved = JSON.parse(localStorage.getItem('recipe_<?php echo $recipe_id; ?>_checked') || '[]');
        const allItems = document.querySelectorAll('#ingredient-need .ingredient-item');
        saved.forEach(i => {
            const el = document.querySelector(`#ingredient-need .ingredient-item[data-index='${i}']`);
            if (el) document.getElementById('ingredient-have').appendChild(el);
        });
    }

    // Toggle ingredient between "need" and "have"
    document.addEventListener("DOMContentLoaded", function () {
        loadCheckedIngredients();

        document.querySelectorAll('.ingredient-item').forEach(item => {
            item.addEventListener('click', () => {
                const targetList = item.parentElement.id === 'ingredient-need'
                    ? document.getElementById('ingredient-have')
                    : document.getElementById('ingredient-need');
                targetList.appendChild(item);
                saveCheckedIngredients();
            });
        });
    });

    function toggleEditForm(id) {
        const form = document.getElementById('edit-form-' + id);
        if (form.style.display === 'none' || form.style.display === '') {
            form.style.display = 'block';
        } else {
            form.style.display = 'none';
        }
    }

</script>

</body>
</html>
<?php
$stmt->close();
