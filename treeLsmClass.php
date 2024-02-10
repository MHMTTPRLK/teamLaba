<?php
class LSMTree {
    public $sstableMaxSize = 30; // SSTable dosyasının maksimum boyutu
    public $sstableFileName = 'sstable'; // SSTable dosyasının adı
    public $currentSSTableIndex = 0; // Güncel SSTable'ın indeksi

    // Yapıcı metot
    public function __construct($sstableMaxSize = 30) {
        $this->sstableMaxSize = $sstableMaxSize;
    }

    // Yeni bir key-value çifti eklemek için
    public function put($key, $value) {
        // Yeni bir SSTable oluştur ve dosyaya yaz
        $file = fopen($this->sstableFileName . '_' . $this->currentSSTableIndex . '.txt', 'a');
        if ($file) {
            if (fwrite($file, "$key:$value\n") === false) {
                echo "Dosyaya yazma işlemi başarısız oldu.";
            }
            fclose($file);
        } else {
            echo "Dosya açma başarısız oldu.";
        }

        // Eğer SSTable dosyası maksimum boyuta ulaşırsa, yeni bir SSTable oluştur
        if (filesize($this->sstableFileName . '_' . $this->currentSSTableIndex . '.txt') >= $this->sstableMaxSize) {
            $this->currentSSTableIndex++;
        }
    }

    // Veriyi bir anahtara göre getirmek için
    public function get($key) {
        // Diskteki SSTable'ları kontrol et
        for ($i = $this->currentSSTableIndex; $i >= 0; $i--) {
            $sstable = $this->readSSTable($i);
            if (isset($sstable[$key])) {
                return $sstable[$key];
            }
        }

        // Eğer veri hiçbir yerde bulunamazsa, null döndür
        return null;
    }

    // Bir anahtara göre veriyi silmek için
    public function delete($key) {
        // Diskteki SSTable'ları kontrol et
        for ($i = $this->currentSSTableIndex; $i >= 0; $i--) {
            $sstableFileName = $this->sstableFileName . '_' . $i . '.txt';
            if (file_exists($sstableFileName)) {
                $lines = file($sstableFileName, FILE_IGNORE_NEW_LINES);
                foreach ($lines as $lineNumber => $line) {
                    list($storedKey, $value) = explode(':', $line, 2);
                    if ($key == $storedKey) {
                        unset($lines[$lineNumber]); // Anahtara karşılık gelen satırı sil
                        if (file_put_contents($sstableFileName, implode("\n", $lines)) === false) {
                            echo "Dosyaya geri yazma işlemi başarısız oldu.";
                        }
                        return;
                    }
                }
            }
        }
    }

    // SSTable dosyasından veri oku
    public function readSSTable($index) {
        $sstableFileName = $this->sstableFileName . '_' . $index . '.txt';
        if (file_exists($sstableFileName)) {
            $lines = file($sstableFileName, FILE_IGNORE_NEW_LINES);
            $sstable = [];
            foreach ($lines as $line) {
                list($key, $value) = explode(':', $line, 2);
                $sstable[$key] = $value;
            }
            return $sstable;
        }
        return [];
    }

    // Compaction işlemi
    public function compaction() {
        // Tüm SSTable dosyalarını birleştir
        $mergedData = [];
        for ($i = 0; $i <= $this->currentSSTableIndex; $i++) {
            $sstable = $this->readSSTable($i);
            $mergedData = array_merge($mergedData, $sstable);
        }

        // Birleştirilen veri varsa, yeni bir SSTable dosyasına yaz
        if (!empty($mergedData)) {
            $this->rewriteSSTable($mergedData);

            // Tüm SSTable dosyalarını sil
            for ($i = 0; $i < $this->currentSSTableIndex; $i++) { // Önceki dosyaları silecek şekilde indeks sınırını düzelttik
                unlink($this->sstableFileName . '_' . $i . '.txt');
            }
            $this->currentSSTableIndex = 0; // Güncel SSTable'ın indeksini sıfırla
        } else {
            echo "Compaction işlemi sırasında birleştirilen veriler boş.";
        }
    }

    // Birleştirilmiş veriyi yeni bir SSTable dosyasına yaz
    public function rewriteSSTable($data) {
        // Birleştirilmiş veriyi yeni bir SSTable dosyasına yaz
        $file = fopen($this->sstableFileName. '_' . $this->currentSSTableIndex . '.txt', 'w');
        if ($file) {
            foreach ($data as $key => $value) {
                if (fwrite($file, "$key:$value\n") === FALSE) {
                    $error = error_get_last();
                    echo "Dosyaya yazma işlemi başarısız oldu. Hata: " . $error['message'];
                }
            }
            fclose($file);
        } else {
            echo "Dosya oluşturma başarısız oldu.";
        }
    }
}
?>
