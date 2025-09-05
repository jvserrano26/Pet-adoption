<?php
include 'config.php';

if (isset($_GET['id'])) {
    $pet_id = $_GET['id'];

    // First, delete reservations related to this pet
    $deleteReservations = $conn->prepare("DELETE FROM reservations WHERE pet_id = ?");
    $deleteReservations->bind_param("i", $pet_id);
    $deleteReservations->execute();
    $deleteReservations->close();

    // Now, delete the pet
    $deletePet = $conn->prepare("DELETE FROM pets WHERE id = ?");
    $deletePet->bind_param("i", $pet_id);

    if ($deletePet->execute()) {
        header("Location: add_pet.php?msg=Pet deleted successfully");
    } else {
        echo "Error deleting pet: " . $conn->error;
    }

    $deletePet->close();
} else {
    echo "No pet ID provided.";
}

$conn->close();
?>
