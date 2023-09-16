<?php
session_start();
include('config.php');

// Check if the user is logged in as an admin
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Function to delete a course
function deleteCourse($conn, $courseID) {
    $deleteQuery = "DELETE FROM courses WHERE CourseID = ?";
    $stmt = mysqli_prepare($conn, $deleteQuery);
    mysqli_stmt_bind_param($stmt, "i", $courseID);
    
    if (mysqli_stmt_execute($stmt)) {
        return true;
    } else {
        return false;
    }
}

// Handle delete action
if (isset($_GET["delete"]) && !empty($_GET["delete"])) {
    $courseID = $_GET["delete"];
    
    if (deleteCourse($conn, $courseID)) {
        echo "Course deleted successfully!";
    } else {
        echo "Error deleting course: " . mysqli_error($conn);
    }
}

// Query the database to fetch a list of courses
$query = "SELECT * FROM courses";
$result = mysqli_query($conn, $query);

if ($result) {
    ?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Courses</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="../css/style.css">

</head>
<body>
    <?php include('admin_navbar.php'); ?>

    <div class="container mt-5">
        <h2>Admin Courses</h2>
        <a href="add_courses.php" class="float-right mb-3 btn btn-primary">Add Courses</a>
        <table class="table mt-3">
            <thead>
                <tr>
                    <th>Course ID</th>
                    <th>Course Picture</th>
                    <th>Course Name</th>
                    <th>Course Description</th>
                    <th>Course Objectives</th>
                    <th>Publish Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?php echo $row["CourseID"]; ?></td>
                        <td><img src="uploads/<?php echo $row["CoursePicture"]; ?>" width="100" height="100" alt="Course Picture"></td>
                        <td><?php echo $row["CourseName"]; ?></td>
                        <td><?php echo $row["CourseDescription"]; ?></td>
                        <td><?php echo $row["CourseObjectives"]; ?></td>
                        <td><?php echo $row["PublishStatus"]; ?></td>
                        <td>
                            <a href="edit_courses.php?courseID=<?php echo $row["CourseID"]; ?>" class="btn btn-warning">Edit</a>
                            <a href="?delete=<?php echo $row["CourseID"]; ?>" onclick="return confirm('Are you sure you want to delete this course?')" class="mt-2 btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>


    <?php
} else {
    echo "Error fetching courses: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
