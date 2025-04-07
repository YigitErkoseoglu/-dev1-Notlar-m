<?php
// Veritabanı bağlantısı
$servername = "localhost";
$username = "root"; // MySQL kullanıcı adınız
$password = ""; // MySQL şifreniz
$dbname = "anime_db"; // Kullandığınız veritabanı adı

// Veritabanı bağlantısını oluştur
$conn = new mysqli($servername, $username, $password, $dbname);

// Bağlantı kontrolü
if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

// Dil kodunu al
if (isset($_GET['language'])) {
    $language =  $_GET['language'];// Örneğin "en" veya "tr" gibi bir dil kodu
}else {
    $language = 'en';
}

// 1. Tüm türleri döndürme
if (isset($_GET['request']) && $_GET['request'] == 'all_types') {
    $sql = "SELECT id, label FROM types";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $typesList = array();
        while ($row = $result->fetch_assoc()) {
            // Tür adını seçilen dile çevirme
            $type_id = $row['id'];
            $type_label = translateTypeLabel($conn, $type_id, $language);
            $row['label'] = $type_label;
            $typesList[] = $row;
        }
        echo json_encode($typesList);
    } else {
        echo "[]";
    }
}

// 2. Anime adı ve türlerine göre animeleri döndürme
elseif (isset($_GET['request']) && $_GET['request'] == 'anime_by_type') {
    $name = $_GET['name'];
    $sql = "SELECT * FROM anime WHERE name LIKE '%$name%' AND ";
    $selectedTypes = $_GET['selectedTypes'];

    $selectedTypesArray = explode(',', $selectedTypes);
    foreach ($selectedTypesArray as $type) {
        $sql .= "typess LIKE '%" . $type . "%' AND ";
    }
    $sql = rtrim($sql, " AND "); // Son OR ifadesini temizle

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $animeList = array();
        while ($row = $result->fetch_assoc()) {
            $animeList[] = $row;
        }
        echo json_encode($animeList);
    } else {
        echo "[]";
    }
}

// 4. return 4 random anime
elseif (isset($_GET['request']) && $_GET['request'] == 'randomAnime') {
    $sql = "SELECT * FROM anime ORDER BY RAND() LIMIT 4";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $animeList = array();
        while ($row = $result->fetch_assoc()) {
            $animeList[] = $row;
        }
        echo json_encode($animeList);
    } else {
        echo "[]";
    }
}

// 3. İstenilen animenin seçilen dile göre açıklamasını döndürme
elseif (isset($_GET['request']) && $_GET['request'] == 'anime_description') {
    $anime_id = $_GET['anime_id']; // Anime kimliği
    $sql = "SELECT description FROM anime_description WHERE anime_id = $anime_id AND language = '$language'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode($row);
    } else {
        echo "Bu anime için açıklama bulunamadı.";
    }
}



// Bilinmeyen bir istek
else {
    echo "Bilinmeyen istek.";
}

// Veritabanı bağlantısını kapat
$conn->close();

// Tür adını seçilen dile çevirme işlevi
function translateTypeLabel($conn, $type_id, $language) {
    $sql = "SELECT name FROM type_names WHERE type_id = '$type_id' AND language = '$language'";

    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['name'];
    } else {
        return "Çeviri yok";
    }
}
?>