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

// Check if the courseID is provided in the URL
if (isset($_GET["courseID"])) {
    $courseID = $_GET["courseID"];
    
    // Check if the user is enrolled in this course
    $checkQuery = "SELECT * FROM enroll_courses WHERE UserID = ? AND CourseID = ?";
    $stmt = mysqli_prepare($conn, $checkQuery);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $courseID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if (mysqli_stmt_num_rows($stmt) > 0) {
        // User is enrolled, so cancel their enrollment in the course
        $cancelQuery = "DELETE FROM enroll_courses WHERE UserID = ? AND CourseID = ?";
        $stmt = mysqli_prepare($conn, $cancelQuery);
        mysqli_stmt_bind_param($stmt, "ii", $user_id, $courseID);
        
        if (mysqli_stmt_execute($stmt)) {
            // Enrollment canceled successfully, redirect to the dashboard
            header("location: home.php");
            exit;
        } else {
            echo "Error canceling enrollment: " . mysqli_error($conn);
        }
    } else {
        // User is not enrolled in this course
        header("location: home.php");
        exit;
    }
} else {
    echo "Course ID not provided.";
}
?>
