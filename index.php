<?php
$output = "Silakan isi form dan klik Generate";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dn = [
        "countryName"            => strtoupper(substr(trim((string)$_POST['country']), 0, 2)),
        "stateOrProvinceName"    => trim((string)$_POST['state']),
        "localityName"           => trim((string)$_POST['locality']),
        "organizationName"       => trim((string)$_POST['organization']),
        "commonName"             => trim((string)$_POST['commonName'])
    ];

    $opensslConfig = 'C:/laragon/etc/ssl/openssl.cnf';
    
    $configArgs = [
        "config" => $opensslConfig,
        "private_key_bits" => 2048,
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
    ];

    $resKey = openssl_pkey_new($configArgs);
    
    if (!$resKey) {
        $output = "ERROR: Gagal membuat Private Key. Pastikan file config ada di: " . $opensslConfig . "\nDetail: " . openssl_error_string();
    } else {
        $resCsr = openssl_csr_new($dn, $resKey, array_merge($configArgs, ["digest_alg" => "sha256"]));
        
        $resCert = openssl_csr_sign($resCsr, null, $resKey, 365, array_merge($configArgs, ["digest_alg" => "sha256"]));

        openssl_pkey_export($resKey, $privKeyStr, null, $configArgs);
        openssl_x509_export($resCert, $certStr);

        $output = "IDENTITAS CSR BERHASIL DISUSUN:\n";
        $output .= "C=" . $dn['countryName'] . ", ST=" . $dn['stateOrProvinceName'] . ", L=" . $dn['localityName'] . ", O=" . $dn['organizationName'] . ", CN=" . $dn['commonName'] . "\n\n";
        $output .= "--- PRIVATE KEY (RSA 2048-bit) ---\n" . $privKeyStr . "\n\n";
        $output .= "--- CERTIFICATE (X.509 CRT) ---\n" . $certStr;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SSL Generator by Muhammad Irzan</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f0f2f5; padding: 40px 0; }
        .container { max-width: 800px; margin: 0 auto; background: #fff; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: #2c3e50; color: #fff; padding: 25px; text-align: center; }
        .content { padding: 30px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        button { width: 100%; background: #3498db; color: white; padding: 15px; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; font-weight: bold; margin-top: 20px; }
        textarea { width: 100%; height: 350px; font-family: 'Courier New', Courier, monospace; background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 8px; resize: none; font-size: 11px; margin-top: 15px; }
        .footer { text-align: center; padding: 15px; font-size: 11px; color: #95a5a6; background: #fafafa; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>SSL Generator</h1>
        <p>by Muhammad Irzan Rahmadhani</p>
    </div>

    <div class="content">
        <form method="POST">
            <div class="form-grid">
                <div><label>Negara (ID)</label><input type="text" name="country" value="ID" required></div>
                <div><label>Provinsi</label><input type="text" name="state" placeholder="Kalimantan Barat" required></div>
                <div><label>Kota</label><input type="text" name="locality" placeholder="Pontianak" required></div>
                <div><label>Organisasi</label><input type="text" name="organization" placeholder="UM Pontianak" required></div>
                <div style="grid-column: span 2;"><label>Common Name (Domain)</label><input type="text" name="commonName" placeholder="www.domainmu.com" required></div>
            </div>
            <button type="submit">Generate SSL</button>
        </form>

        <textarea readonly><?php echo htmlspecialchars($output); ?></textarea>
    </div>

    <div class="footer">
        © 2026 Teknik Informatika, Universitas Muhammadiyah Pontianak. Built by Muhammad Irzan Rahmadhani (231220087).
    </div>
</div>

</body>
</html>