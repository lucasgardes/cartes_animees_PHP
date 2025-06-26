<?
include 'auth_admin.php';
include 'db.php';

$user_id = $_POST['user_id'];

$sql = "DELETE FROM users WHERE id = ? AND role = 'ortho'";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);

header("Location: admin_dashboard.php");
exit();