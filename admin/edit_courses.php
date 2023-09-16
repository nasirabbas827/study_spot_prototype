<?php
session_start();
include('config.php');

// Check if the user is logged in as an admin
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Function to update a course
function updateCourse($conn, $courseID, $courseName, $courseDescription, $courseObjectives, $publishStatus, $coursePicture) {
    $updateQuery = "UPDATE courses
                    SET CourseName = ?, CourseDescription = ?, CourseObjectives = ?, PublishStatus = ?, CoursePicture = ?
                    WHERE CourseID = ?";
    $stmt = mysqli_prepare($conn, $updateQuery);
    mysqli_stmt_bind_param($stmt, "sssssi", $courseName, $courseDescription, $courseObjectives, $publishStatus, $coursePicture, $courseID);
    
    if (mysqli_stmt_execute($stmt)) {
        return true;
    } else {
        return false;
    }
}

// Handle form submission to update the course
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $courseID = $_POST["courseID"];
    $courseName = $_POST["courseName"];
    $courseDescription = $_POST["courseDescription"];
    $courseObjectives = $_POST["courseObjectives"];
    $publishStatus = $_POST["publishStatus"];
    $existingCoursePicture = $_POST["existingCoursePicture"];

    $coursePicture = $existingCoursePicture; // Initialize with existing picture name

    // Handle course picture upload
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
                    $file_destination = "uploads/" . $new_file_name; // Set the file destination
                    move_uploaded_file($file_tmp, $file_destination); // Move the uploaded file to the destination

                    // Set the course picture name to the new file name
                    $coursePicture = $new_file_name;

                    // Delete the existing course picture
                    if (!empty($existingCoursePicture)) {
                        unlink("uploads/" . $existingCoursePicture);
                    }
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

    if (updateCourse($conn, $courseID, $courseName, $courseDescription, $courseObjectives, $publishStatus, $coursePicture)) {
        // Redirect to view_courses.php after successfully updating the course
        header("Location: view_courses.php");
        exit;
    } else {
        echo "Error updating course: " . mysqli_error($conn);
    }
}

// Fetch course details for editing
if (isset($_GET["courseID"]) && !empty($_GET["courseID"])) {
    $courseID = $_GET["courseID"];
    $query = "SELECT * FROM courses WHERE CourseID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $courseID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $courseName = $row["CourseName"];
        $courseDescription = $row["CourseDescription"];
        $courseObjectives = $row["CourseObjectives"];
        $publishStatus = $row["PublishStatus"];
        $existingCoursePicture = $row["CoursePicture"];
    } else {
        echo "Course not found.";
        exit;
    }
} else {
    echo "Course ID not provided.";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Course</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php
include('admin_navbar.php');
?>
<div class="container mt-5 mb-4">
    <h2>Edit Course</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
        <div class="form-group">
            <label>Existing Course Picture:</label>
            <?php if (!empty($existingCoursePicture)) : ?>
                <img src="uploads/<?php echo $existingCoursePicture; ?>" alt="Course Picture" width="150">
            <?php else : ?>
                <p>No picture uploaded.</p>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label for="coursePicture">Course Picture:</label>
            <input type="file" class="form-control-file" name="coursePicture" accept="image/*">
        </div>
        <input type="hidden" name="courseID" value="<?php echo $courseID; ?>">
        <input type="hidden" name="existingCoursePicture" value="<?php echo $existingCoursePicture; ?>">

        <div class="form-group">
            <label for="courseName">Course Name:</label>
            <input type="text" class="form-control" name="courseName" value="<?php echo $courseName; ?>" required>
        </div>
        <div class="form-group">
            <label for="courseDescription">Course Description:</label>
            <textarea class="form-control" name="courseDescription"><?php echo $courseDescription; ?></textarea>
        </div>
        <div class="form-group">
            <label for="courseObjectives">Course Objectives:</label>
            <textarea class="form-control" name="courseObjectives"><?php echo $courseObjectives; ?></textarea>
        </div>
        <div class="form-group">
            <label for="publishStatus">Publish Status:</label>
            <select class="form-control" name="publishStatus">
                <option value="Draft" <?php if ($publishStatus === "Draft") echo "selected"; ?>>Draft</option>
                <option value="Published" <?php if ($publishStatus === "Published") echo "selected"; ?>>Published</option>
            </select>
        </div>

        <div class="form-group">
            <input type="submit" class="btn btn-primary" value="Update Course">
        </div>
    </form>
</div>
<!-- Bootstrap JS -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

