<?
include 'auth_admin.php';
include 'db.php';

$firstname = $_POST['firstname'];
$name = $_POST['name'];
$id_ortho = $_POST['id_orthophoniste'];

$sql = "INSERT INTO patients (firstname, `name`, id_orthophoniste) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->execute([$firstname, $name, $id_ortho]);

header("Location: admin_dashboard.php");
exit();