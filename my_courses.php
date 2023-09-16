<?php
session_start();
include('config.php');

// Check if user is logged in, if not, redirect to login page
if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

// Get the user ID from the session
$user_id = $_SESSION["id"];

// Fetch user details from the database
$sql = "SELECT id, username, email, age FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $fetched_id, $username, $email, $age);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// Fetch the user's enrolled courses
$enrolledCourses = array();
$query = "SELECT ec.EnrollmentID, c.CourseName, c.CourseDescription, c.CourseObjectives, c.CoursePicture
          FROM enroll_courses ec
          INNER JOIN courses c ON ec.CourseID = c.CourseID
          WHERE ec.UserID = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $enrolledCourses[] = $row;
    }
}

// Handle cancellation of enrollment
if (isset($_GET["cancel"])) {
    $enrollmentID = $_GET["cancel"];
    
    // Check if the enrollment belongs to the logged-in user
    $checkQuery = "SELECT ec.EnrollmentID
                   FROM enroll_courses ec
                   WHERE ec.EnrollmentID = ? AND ec.UserID = ?";
    $stmt = mysqli_prepare($conn, $checkQuery);
    mysqli_stmt_bind_param($stmt, "ii", $enrollmentID, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if (mysqli_stmt_num_rows($stmt) > 0) {
        // User is allowed to cancel the enrollment
        $cancelQuery = "DELETE FROM enroll_courses WHERE EnrollmentID = ?";
        $stmt = mysqli_prepare($conn, $cancelQuery);
        mysqli_stmt_bind_param($stmt, "i", $enrollmentID);
        
        if (mysqli_stmt_execute($stmt)) {
            // Enrollment canceled successfully, refresh the page
            header("location: my_courses.php");
            exit;
        } else {
            echo "Error canceling enrollment: " . mysqli_error($conn);
        }
    } else {
        // Enrollment does not belong to the user
        echo "Unauthorized access.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Courses - <?php echo $username; ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h2>My Courses - <?php echo $username; ?></h2>
    <div class="row">
        <?php foreach ($enrolledCourses as $course) : ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="./admin/uploads/<?php echo $course['CoursePicture']; ?>" class="card-img-top" alt="Course Image">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $course['CourseName']; ?></h5>
                        <p class="card-text"><?php echo $course['CourseDescription']; ?></p>
                        <p class="card-text"><strong>Course Objectives:</strong></p>
                        <p class="card-text"><?php echo $course['CourseObjectives']; ?></p>
                        <a href="my_courses.php?cancel=<?php echo $course['EnrollmentID']; ?>" class="btn btn-danger">Cancel Enrollment</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
