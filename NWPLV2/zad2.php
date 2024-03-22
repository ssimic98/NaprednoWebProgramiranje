<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload i kriptiranje dokumenata</title>
</head>
<body>
    <h1>Upload i kriptiranje dokumenata</h1>
    <h2>Upload datoteke</h2>
    <form action="" method="post" enctype="multipart/form-data">
        <input type="file" name="file">
        <button type="submit">Upload</button>
    </form>
    
    <hr>

    <h2>Dekriptirane datoteke</h2>
    <?php
   #provjeri je li datoteka poslana putem POST zahtjeva
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["file"])) {
        $file = $_FILES["file"];
        $file_name = $file["name"];
        $file_tmp = $file["tmp_name"];

       #definiranje dozvoljenih ekstenzija
        $allowedTypes = array('pdf', 'jpeg', 'jpg', 'png');
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        #provjeri je li ekstenzija dozvoljena
        if (!in_array($file_extension, $allowedTypes)) {
            echo "Nedozvoljena ekstenzija datoteke.";
            exit();
        }

        #kreiraj direktorije ako ne postoje
        $upload_folder = 'uploads';
        $encrypted_folder = 'encrypted';
        $decrypted_folder = 'decrypted';
        if (!file_exists($upload_folder)) {
            if(!@mkdir($upload_folder))
            {die("<p>Ne možemo stvoriti direktorij: $upload_folder</p>");}
        }
        if (!file_exists($encrypted_folder)) {
            if(!@mkdir($encrypted_folder))
            {die("<p>Ne možemo stvoriti direktorij: $encrypted_folder</p>");}
        }
        if (!file_exists($decrypted_folder)) {
            if(!@mkdir($decrypted_folder))
            {die("<p>Ne možemo stvoriti direktorij: $decrypted_folder</p>");}
        }

        #spremi datoteku u upload direktorij
        $upload_path = $upload_folder . '/' . $file_name;
        move_uploaded_file($file_tmp, $upload_path);

       #generiranje nasumicnog kljuca
        $encryption_key = openssl_random_pseudo_bytes(32);

        #kriptiranje datoteke
        $encrypted_data = openssl_encrypt(file_get_contents($upload_path), 'aes-256-cbc', $encryption_key, 0, openssl_random_pseudo_bytes(16));

        #spremanje kriptirane datoteke
        $encrypted_path = $encrypted_folder . '/encrypted_' . $file_name;
        file_put_contents($encrypted_path, $encrypted_data);
    }

    #funkcija za dekriptiranje datoteke
    function decryptFile($encrypted_path, $decrypted_folder, $encryption_key) {
        #dohvacamo kriptirane podatke sa putanje /encrypted
        $encrypted_data = file_get_contents($encrypted_path);
        
        #radimo dekriptiranje
        $decrypted_data = openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, openssl_random_pseudo_bytes(16));
        
        #spremi dekriptiranu datoteku
        $decrypted_path = $decrypted_folder . '/decrypted_' . basename($encrypted_path);
        file_put_contents($decrypted_path, $decrypted_data);

        return $decrypted_path;
    }

    #funkcija za dohvaćanje kriptiranih datoteka i dekriptiranje
    function displayDecryptedFiles() {
        $encrypted_folder = 'encrypted';
        $decrypted_folder = 'decrypted';

        #provjeravamo postoji li u encrypted folderu kriptirana datoteka
        if (!file_exists($encrypted_folder)) {
            echo "Nema kriptiranih datoteka za dekriptiranje.";
            return;
        }

        $decrypted_files = array();

       #prođi kroz sve datoteke u kriptiranom direktoriju
        foreach (glob($encrypted_folder . '/*') as $encrypted_file) {
            #dekriptiranje datoteke
            $decrypted_path = decryptFile($encrypted_file, $decrypted_folder, $GLOBALS['encryption_key']);

            #dodaj putanju dekriptirane datoteke u polje
            $decrypted_files[] = $decrypted_path;
        }

        #prikaz linkova za preuzimanje dekriptirane datoteke
        foreach ($decrypted_files as $decrypted_file) {
            echo "<p><a href='$decrypted_file'>Preuzmi " . basename($decrypted_file) . "</a></p>";
        }
    }

    #poziv funkcije za prikaz dekriptiranih datoteka
    displayDecryptedFiles();
    ?>
</body>
</html>
