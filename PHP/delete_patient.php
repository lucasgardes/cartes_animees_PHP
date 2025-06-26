<?
include 'auth_admin.php';
include 'db.php';

$patient_id = $_POST['patient_id'];

$sql = "DELETE FROM patients WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$patient_id]);

header("Location: admin_dashboard.php");
exit();