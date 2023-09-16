<?php
session_start();
include('config.php');

// Check if the user is logged in as an admin
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}
$uploadsFolder = "uploads/";

// Handle form submission to create a new course
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $courseName = $_POST["courseName"];
    $courseDescription = $_POST["courseDescription"];
    $courseObjectives = $_POST["courseObjectives"];
    $publishStatus = $_POST["publishStatus"];

    // Handle course picture upload
    $coursePicture = ""; // Initialize the variable to store the picture name

    if (!empty($_FILES['coursePicture']['name'])) {
        $file_name = $_FILES['coursePicture']['name'];
        $file_tmp = $_FILES['coursePicture']['tmp_name'];
        $file_size = $_FILES['coursePicture']['size'];
        $file_error = $_FILES['coursePicture']['error'];

        // Check if the file is an image
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_extensions = array("jpg", "jpeg", "png", "gif");

        if (in_array($file_ext, $allowed_extensions)) {
            if ($file_error === 0) {
                if ($file_size <= 5242880) { // 5MB maximum file size
                    $new_file_name = uniqid() . '.' . $file_ext; // Generate a unique filename
                    $file_destination = $uploadsFolder . $new_file_name; // Set the file destination
                    move_uploaded_file($file_tmp, $file_destination); // Move the uploaded file to the destination

                    // Set the course picture name to be stored in the database
                    $coursePicture = $new_file_name;
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

    // Insert course data into the database
    $insertQuery = "INSERT INTO courses (CoursePicture, CourseName, CourseDescription, CourseObjectives, PublishStatus)
                    VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insertQuery);
    mysqli_stmt_bind_param($stmt, "sssss", $coursePicture, $courseName, $courseDescription, $courseObjectives, $publishStatus);

    if (mysqli_stmt_execute($stmt)) {
        echo "Course created successfully!";
    } else {
        echo "Error creating course: " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Course</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php
include('admin_navbar.php');
?>
<div class="container mt-5 mb-4">
    <h2>Create Course</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
    <div class="form-group">
            <label for="coursePicture">Course Picture:</label>
            <input type="file" class="form-control-file" name="coursePicture" accept="image/*">
        </div>
        <div class="form-group">
            <label for="courseName">Course Name:</label>
            <input type="text" class="form-control" name="courseName" required>
        </div>
        <div class="form-group">
            <label for="courseDescription">Course Description:</label>
            <textarea class="form-control" name="courseDescription"></textarea>
        </div>
        <div class="form-group">
            <label for="courseObjectives">Course Objectives:</label>
            <textarea class="form-control" name="courseObjectives"></textarea>
        </div>
        <div class="form-group">
            <label for="publishStatus">Publish Status:</label>
            <select class="form-control" name="publishStatus">
                <option value="Draft">Draft</option>
                <option value="Published">Published</option>
            </select>
        </div>

        <div class="form-group">
            <input type="submit" class="btn btn-primary" value="Create Course">
            <a href="view_courses.php" class="btn btn-secondary">View Courses</a>

        </div>
    </form>
</div>
<!-- Bootstrap JS -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

