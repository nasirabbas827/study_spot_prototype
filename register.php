<?php
include('config.php');

// define variables and initialize with empty values
$username = $password = $email = $phone = $age = "";
$username_err = $password_err = $email_err = $phone_err = $age_err = $profile_picture_err = "";

// check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // validate username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter a username.";
    } else {
        // check if username already exists in the database
        $sql = "SELECT id FROM users WHERE username = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $param_username);
        $param_username = trim($_POST["username"]);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) == 1) {
            $username_err = "This username is already taken.";
        } else {
            $username = trim($_POST["username"]);
        }
    }

    // validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter an email address.";
    } else {
        $email = trim($_POST["email"]);
        // check if email already exists in the database
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $param_email);
        $param_email = $email;
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) == 1) {
            $email_err = "This email address is already taken.";
        }
    }

    // validate phone number
    if (empty(trim($_POST["phone"]))) {
        $phone_err = "Please enter a phone number.";
    } else {
        $phone = trim($_POST["phone"]);
        // check if phone number already exists in the database
        $sql = "SELECT id FROM users WHERE phone = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $param_phone);
        $param_phone = $phone;
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) == 1) {
            $phone_err = "This phone number is already taken.";
        }
    }

    // validate age
    if (empty(trim($_POST["age"]))) {
        $age_err = "Please enter your age.";
    } elseif (!is_numeric($_POST["age"])) {
        $age_err = "Age must be a number.";
    } else {
        $age = trim($_POST["age"]);
        if ($age < 18) {
            $age_err = "You must be at least 18 years old to register.";
        }
    }

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
                    $file_destination = 'uploads/' . $new_file_name; // Set the file destination
                    move_uploaded_file($file_tmp, $file_destination); // Move the uploaded file to the destination

                    // Store the filename in the database
                    $profile_picture = $new_file_name;
                } else {
                    $profile_picture_err = "File size is too large. Max 5MB allowed.";
                }
            } else {
                $profile_picture_err = "Error uploading file.";
            }
        } else {
            $profile_picture_err = "Invalid file format. Please upload a JPG, JPEG, PNG, or GIF file.";
        }
    }

    // If no errors, insert the user into the database
    if (empty($username_err) && empty($password_err) && empty($email_err) && empty($phone_err) && empty($age_err) && empty($profile_picture_err)) {
        $sql = "INSERT INTO users (username, password, email, phone, age, profile_picture) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssis", $param_username, $param_password, $param_email, $param_phone, $param_age, $param_profile_picture);
        $param_username = $username;
        $param_password = password_hash($password, PASSWORD_DEFAULT);
        $param_email = $email;
        $param_phone = $phone;
        $param_age = $age;
        $param_profile_picture = $profile_picture; // Add the profile picture filename
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        echo '<div class="alert alert-success" role="alert">User registered successfully.</div>';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Registration</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-image: url("https://images.unsplash.com/photo-1640340434855-6084b1f4901c?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=464&q=80");
            background-size: cover;
        }
        h2 , p , label {
            color:white;
        }

    </style>
</head>
<body>
<?php
include('navbar.php');
?>
<div class="container mt-5">
    <h2 class="text-center">User Registration</h2>
    <p class="text-center">Please fill in your details to register.</p>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-6">
                <!-- Add profile picture upload field -->
                <div class="form-group">
                    <label>Profile Picture</label>
                    <input type="file" name="profile_picture" class="text-white form-control-file">
                    <span class="text-danger"><?php echo $profile_picture_err; ?></span>
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                    <span class="invalid-feedback"><?php echo $username_err; ?></span>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="number" name="phone" class="form-control <?php echo (!empty($phone_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $phone; ?>">
                    <span class="invalid-feedback"><?php echo $phone_err; ?></span>
                </div>
                
            </div>
            <div class="col-md-6">
            <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                    <span class="invalid-feedback"><?php echo $email_err; ?></span>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $password; ?>">
                    <span class="invalid-feedback"><?php echo $password_err; ?></span>
                </div>
                <div class="form-group">
                    <label>Age</label>
                    <input type="number" name="age" class="form-control <?php echo (!empty($age_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $age; ?>">
                    <span class="invalid-feedback"><?php echo $age_err; ?></span>
                </div>
                
            </div>
        </div>
        <div class="form-group text-center">
            <input type="submit" class="btn btn-primary" value="Register">
        </div>
    </form>

    <p class="text-center">Already have an account? <a href="login.php">Login here</a></p>
</div>

<!-- Bootstrap JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
