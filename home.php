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
$sql = "SELECT id, username, email, age FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $fetched_id, $username, $email, $age);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// Fetch published courses from the database
$publishedCourses = array();
$query = "SELECT CourseID, CourseName, CourseDescription, CourseObjectives, CoursePicture FROM courses WHERE PublishStatus = 'Published'";
$result = mysqli_query($conn, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $publishedCourses[] = $row;
    }
}

// Check if the user is already enrolled in courses
$enrolledCourses = array();
$query = "SELECT CourseID FROM enroll_courses WHERE UserID = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $enrolledCourses[] = $row['CourseID'];
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Welcome, <?php echo $username; ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h2>Welcome, <?php echo $username; ?></h2>
    <h3>Published Courses</h3>
    <div class="row">
        <?php foreach ($publishedCourses as $course) : ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="./admin/uploads/<?php echo $course['CoursePicture']; ?>" class="card-img-top" alt="Course Image">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $course['CourseName']; ?></h5>
                        <p class="card-text"><?php echo $course['CourseDescription']; ?></p>
                        <p class="card-text"><strong>Course Objectives:</strong></p>
                        <p class="card-text"><?php echo $course['CourseObjectives']; ?></p>
                        <?php if (in_array($course['CourseID'], $enrolledCourses)) : ?>
                            <button class="btn btn-success" disabled>Enrolled</button>
                            <a href="cancel_enrollment.php?courseID=<?php echo $course['CourseID']; ?>" class="btn btn-danger">Cancel Enrollment</a>
                        <?php else : ?>
                            <a href="enroll.php?courseID=<?php echo $course['CourseID']; ?>" class="btn btn-primary">Enroll Now</a>
                        <?php endif; ?>
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
