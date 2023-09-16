<?php
include('config.php');

session_start();

// Check if user is logged in, if not, redirect to login page
if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

// Get the user ID from the session
$user_id = $_SESSION["id"];

// Fetch user details from the database
$sql = "SELECT id, username, email, age, profile_picture FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $fetched_id, $username, $email, $age, $profile_picture);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// Define the folder where profile pictures are stored
$uploads_folder = "uploads/";

// Update user profile
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newUsername = $_POST["username"];
    $newEmail = $_POST["email"];
    $newAge = $_POST["age"];

    // Handle profile picture upload
    if (!empty($_FILES['profile_picture']['name'])) {
        $file_name = $_FILES['profile_picture']['name'];
        $file_tmp = $_FILES['profile_picture']['tmp_name'];
        $file_size = $_FILES['profile_picture']['size'];
        $file_error = $_FILES['profile_picture']['error'];

        // Check if the file is an image
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_extensions = array("jpg", "jpeg", "png", "gif");

        if (in_array($file_ext, $allowed_extensions)) {
            if ($file_error === 0) {
                if ($file_size <= 5242880) { // 5MB maximum file size
                    $new_file_name = uniqid() . '.' . $file_ext; // Generate a unique filename
                    $file_destination = $uploads_folder . $new_file_name; // Set the file destination
                    move_uploaded_file($file_tmp, $file_destination); // Move the uploaded file to the destination

                    // Delete the old profile picture if it exists
                    if (!empty($profile_picture) && file_exists($uploads_folder . $profile_picture)) {
                        unlink($uploads_folder . $profile_picture);
                    }

                    // Update the profile picture filename
                    $profile_picture = $new_file_name;
                } else {
                    echo "Error: File size is too large. Max 5MB allowed.";
                }
            } else {
                echo "Error: Error uploading file.";
            }
        } else {
            echo "Error: Invalid file format. Please upload a JPG, JPEG, PNG, or GIF file.";
        }
    }

    // Update user details in the database
    $update_query = "UPDATE users 
                     SET username = ?, email = ?, age = ?, profile_picture = ? 
                     WHERE id = ?";
    
    $update_stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($update_stmt, "ssisi", $newUsername, $newEmail, $newAge, $profile_picture, $user_id);
    
    if (mysqli_stmt_execute($update_stmt)) {
        echo "Profile updated successfully!";
        // Update session data if needed
        $_SESSION["username"] = $newUsername;
    } else {
        echo "Error updating profile: " . mysqli_error($conn);
    }

    mysqli_stmt_close($update_stmt);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Profile</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">

</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5 mb-5">
    <h2>Update Profile</h2>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
    <div class="form-group">
            <label for="profile_picture">Profile Picture:</label>
            <img src="<?php echo $uploads_folder . $profile_picture; ?>" alt="Profile Picture" class="img-thumbnail rounded" style="max-width: 200px;">
            <input type="file" class="mt-4 form-control-file" name="profile_picture">
        </div>
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" class="form-control" name="username" value="<?php echo $username; ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" class="form-control" name="email" value="<?php echo $email; ?>" required>
        </div>
        <div class="form-group">
            <label for="age">Age:</label>
            <input type="number" class="form-control" name="age" value="<?php echo $age; ?>" required>
        </div>

        <button type="submit" class="btn btn-primary">Update Profile</button>
    </form>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
