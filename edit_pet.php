<?php
// edit_pet.php
include 'config.php';

$editMode = false;
$pet = [];

if (isset($_GET['id'])) {
    $pet_id = $_GET['id'];
    $query = "SELECT * FROM pets WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $pet_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $pet = $result->fetch_assoc();
    $editMode = true;
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pet_id = $_POST['pet_id'];
    $name = $_POST['name'];
    $age = $_POST['age'];
    $breed = $_POST['breed'];
    $description = $_POST['description'];

    $query = "UPDATE pets SET name = ?, age = ?, type = ?, description = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sissi", $name, $age, $type, $description, $pet_id);

    if ($stmt->execute()) {
        header("Location: add_pet.php?msg=Pet updated successfully");
    } else {
        echo "Error updating pet: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
    exit;
}
?>

<!-- Edit Pet Form -->
<?php if ($editMode): ?>
    <h2>Edit Pet</h2>
    <form action="edit_pet.php" method="post">
        <input type="hidden" name="pet_id" value="<?php echo htmlspecialchars($pet['id']); ?>">
        <label>Name: <input type="text" name="name" value="<?php echo htmlspecialchars($pet['name']); ?>"></label><br>
        <label>Age: <input type="number" name="age" value="<?php echo htmlspecialchars($pet['age']); ?>"></label><br>
        <label>type: <input type="text" name="breed" value="<?php echo htmlspecialchars($pet['type']); ?>"></label><br>
        <label>Description: <textarea name="description"><?php echo htmlspecialchars($pet['description']); ?></textarea></label><br>
        <button type="submit">Update Pet</button>
    </form>
<?php else: ?>
    <p>No pet selected for editing.</p>
<?php endif; ?>
