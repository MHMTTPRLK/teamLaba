<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LSMTree İşlem Süreleri</title>
    <style>
        table {
            border-collapse: collapse;
            width: 50%;
            margin: auto;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
<h2 style="text-align: center;">LSMTree İşlem Süreleri</h2>
<table>
    <tr>
        <th>İşlem</th>
        <th>Süre (saniye)</th>
    </tr>
    <?php
    include 'treeLsmClass.php'; // LSMTree sınıfının bulunduğu dosya

    // LSMTree örneği oluşturun
    $lsmTree = new LSMTree();

    // Veri eklemeyi ölç
    $start = microtime(true);
    for ($i = 0; $i < 1000; $i++) {
        $lsmTree->put("anahtar_$i", "değer_$i");
    }
    $end = microtime(true);
    $elapsed = $end - $start;
    echo "<tr><td>Veri Ekleme</td><td>$elapsed</td></tr>";

    // Bir anahtara göre veri alma işlemini ölç
    $start = microtime(true);
    $value = $lsmTree->get("anahtar_500");
    $end = microtime(true);
    $elapsed = $end - $start;
    echo "<tr><td>Veri Alma</td><td>$elapsed</td></tr>";

    // Bir anahtarı silme işlemini ölç
    $start = microtime(true);
    $lsmTree->delete("anahtar_500");
    $end = microtime(true);
    $elapsed = $end - $start;
    echo "<tr><td>Veri Silme</td><td>$elapsed</td></tr>";

    // SSTable dosyasına veriyi yazma işlemini ölç
    $start = microtime(true);
    $mergedData = $lsmTree->readSSTable(0); // Buradaki 0, compaction işlemi sonucunda birleştirilen ilk SSTable'ın indeksi
    $lsmTree->rewriteSSTable($mergedData);
    $end = microtime(true);
    $elapsed = $end - $start;
    echo "<tr><td>SSTable Yazma</td><td>$elapsed</td></tr>";

    // Compaction işlemini ölç
    $start = microtime(true);
    $lsmTree->compaction();
    $end = microtime(true);
    $elapsed = $end - $start;
    echo "<tr><td>Compaction</td><td>$elapsed</td></tr>";


    ?>
</table>
</body>
</html>
