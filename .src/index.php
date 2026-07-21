<?php
$host = 'db';
$db   = 'db_images';
$user = 'admin';
$pass = 'Password';

try {
    $pdo = new PDO("pgsql:host=$host;port=5432;dbname=$db", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec("CREATE TABLE IF NOT EXISTS images (
        id SERIAL PRIMARY KEY,
        nom VARCHAR(255) NOT NULL,
        donnees BYTEA NOT NULL
    )");
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

if (isset($_FILES['imageToUpload']) && $_FILES['imageToUpload']['error'] === UPLOAD_ERR_OK) {
    $nomFichier = basename($_FILES['imageToUpload']['name']);
    $donneesFichier = file_get_contents($_FILES['imageToUpload']['tmp_name']);
    
    $stmt = $pdo->prepare("INSERT INTO images (nom, donnees) VALUES (?, ?)");
    $stmt->bindParam(1, $nomFichier);
    $stmt->bindParam(2, $donneesFichier, PDO::PARAM_LOB);
    $stmt->execute();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Stockeur DB</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f9; padding: 20px; }
        .container { max-width: 800px; margin: auto; background: white; padding: 20px; border-radius: 8px; }
        .upload-form { border: 2px dashed #28a745; padding: 20px; text-align: center; background: #e9f7ef; margin-bottom: 20px; }
        .btn { padding: 10px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .gallery { display: flex; flex-wrap: wrap; gap: 15px; }
        .gallery div { text-align: center; }
        .gallery img { width: 150px; height: 150px; object-fit: cover; border-radius: 5px; border: 1px solid #ddd; }
    </style>
</head>
<body>
<div class="container">
    <h1>Stockeur D'images</h1>
    <div class="upload-form">
        <form action="index.php" method="POST" enctype="multipart/form-data">
            <input type="file" name="imageToUpload" accept="image/*" required>
            <button type="submit" class="btn">Sauvegarder dans la DB</button>
        </form>
    </div>
    <h2>Galerie (Base de données)</h2>
    <div class="gallery">
        <?php
        $stmt = $pdo->query("SELECT nom, donnees FROM images ORDER BY id DESC");
        $imagesTrouvees = false;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $imagesTrouvees = true;
            $donneesBrutes = is_resource($row['donnees']) ? stream_get_contents($row['donnees']) : $row['donnees'];
            $imageCodee = base64_encode($donneesBrutes);
            echo "<div><img src='data:image/jpeg;base64,{$imageCodee}' alt='img'><br><small>" . htmlspecialchars($row['nom']) . "</small></div>";
        }
        if (!$imagesTrouvees) echo "<p>Aucune image en base de données.</p>";
        ?>
    </div>
</div>
</body>
</html>