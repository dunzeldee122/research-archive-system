<?php
$connection = new mysqli("localhost", "root", "", "research_archive");

$username = "zppsu";
$password = password_hash("zppsu@2025", PASSWORD_DEFAULT);
$full_name = "Research Administrator";
$email = "zppsu@gmail.com";

$sql = "INSERT INTO admins (username, password, full_name, email) VALUES (?, ?, ?, ?)";
$stmt = $connection->prepare($sql);
$stmt->bind_param("ssss", $username, $password, $full_name, $email);

if ($stmt->execute()) {
    echo "Admin account created successfully!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$connection->close();
?>
