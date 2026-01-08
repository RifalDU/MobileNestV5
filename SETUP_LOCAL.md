# Setup Upload System Lokal

Panduan step-by-step untuk setup sistem upload di komputer lokal Anda.

## 🚀 Quick Start (3 Langkah)

### Step 1: Pull dari GitHub

```bash
cd /xampp/htdocs/MobileNestV5  # atau path XAMPP Anda
git pull origin main
```

### Step 2: Verifikasi Struktur Folder

Pastikan folder ini sudah ada:
```
MobileNest/
├── uploads/
│   ├── produk/
│   │   └── .gitkeep
│   └── pembayaran/
│       └── .gitkeep
├── includes/
│   └── upload-handler.php
├── api/
│   └── upload.php
└── .gitignore
```

### Step 3: Set Permissions (Linux/Mac)

```bash
# Di folder MobileNest
chmod 755 uploads/produk
chmod 755 uploads/pembayaran
chmod 644 uploads/.gitkeep
chmod 644 uploads/produk/.gitkeep
chmod 644 uploads/pembayaran/.gitkeep
```

**Windows:** Properties → Security → Edit → Full Control (untuk administrator)

---

## 📛 Database Setup

Tambahkan kolom untuk menyimpan nama file gambar:

### 1. Buka phpMyAdmin

```
http://localhost/phpmyadmin
```

### 2. Jalankan Query untuk Produk

```sql
-- Tambah kolom gambar ke tabel produk
ALTER TABLE produk ADD COLUMN gambar VARCHAR(255) DEFAULT NULL AFTER deskripsi;

-- Opsional: Add description untuk gambar
ALTER TABLE produk ADD COLUMN gambar_alt VARCHAR(255) DEFAULT NULL;
```

### 3. Jalankan Query untuk Transaksi

```sql
-- Tambah kolom bukti_pembayaran ke tabel transaksi
ALTER TABLE transaksi ADD COLUMN bukti_pembayaran VARCHAR(255) DEFAULT NULL AFTER status;
```

### Verifikasi Kolom

```sql
DESC produk;       -- Lihat struktur tabel produk
DESC transaksi;    -- Lihat struktur tabel transaksi
```

---

## 🧪 Test Upload

### Method 1: Menggunakan cURL (Terminal)

```bash
# Test upload produk
curl -X POST http://localhost/MobileNest/api/upload.php?action=upload_product \
  -F "image=@test-image.jpg" \
  -F "product_id=1"

# Expected response:
# {"success":true,"message":"Gambar produk berhasil diupload","filename":"produk_1_..._abcd1234.jpg","url":"uploads/produk/produk_1_..._abcd1234.jpg"}
```

### Method 2: HTML Form Test

Buat file `test-upload.html` di root MobileNest:

```html
<!DOCTYPE html>
<html>
<head>
    <title>Test Upload</title>
    <style>
        body { font-family: Arial; max-width: 600px; margin: 50px auto; }
        .form-group { margin: 15px 0; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input { width: 100%; padding: 8px; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; }
        .response { margin-top: 20px; padding: 10px; background: #f0f0f0; border-radius: 4px; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Test Upload Produk</h1>
    
    <form id="uploadForm">
        <div class="form-group">
            <label>Pilih Gambar Produk:</label>
            <input type="file" name="image" accept="image/*" required>
        </div>
        <div class="form-group">
            <label>Product ID:</label>
            <input type="number" name="product_id" value="1" required>
        </div>
        <button type="submit">Upload Gambar</button>
    </form>
    
    <div class="response" id="response" style="display:none;"></div>
    
    <script>
        document.getElementById('uploadForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('api/upload.php?action=upload_product', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                const responseDiv = document.getElementById('response');
                responseDiv.style.display = 'block';
                
                if (data.success) {
                    responseDiv.innerHTML = `
                        <h3 class="success">✓ ${data.message}</h3>
                        <p><strong>Filename:</strong> ${data.filename}</p>
                        <p><strong>URL:</strong> ${data.url}</p>
                        <img src="${data.url}" style="max-width: 300px; margin-top: 10px;" alt="Uploaded">
                    `;
                } else {
                    responseDiv.innerHTML = `<h3 class="error">✗ Error: ${data.message}</h3>`;
                }
            } catch (error) {
                document.getElementById('response').innerHTML = `
                    <h3 class="error">✗ Request Error: ${error.message}</h3>
                `;
                document.getElementById('response').style.display = 'block';
            }
        });
    </script>
</body>
</html>
```

Akses: `http://localhost/MobileNest/test-upload.html`

---

## 🚀 Implementasi di Admin Panel

### Edit Form Produk

Tambahkan input file ke form produk:

```html
<!-- Di form produk Anda -->
<div class="form-group">
    <label for="gambar">Gambar Produk:</label>
    <input type="file" id="gambar" name="gambar" accept="image/*" required>
    <small>Format: JPG, PNG, WEBP (Max 5MB)</small>
</div>
```

### Handle Upload di PHP

```php
<?php
include 'includes/upload-handler.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id']);
    $nama_produk = $_POST['nama_produk'];
    $deskripsi = $_POST['deskripsi'];
    $harga = floatval($_POST['harga']);
    
    // Handle image upload
    $gambar = null;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $result = UploadHandler::uploadProductImage($_FILES['gambar'], $product_id);
        if ($result['success']) {
            $gambar = $result['filename'];
        } else {
            $error = $result['message'];
            // Handle error
        }
    }
    
    // Simpan ke database
    if ($product_id) {
        // Update
        $sql = "UPDATE produk SET nama_produk = ?, deskripsi = ?, harga = ?";
        $params = [$nama_produk, $deskripsi, $harga];
        
        if ($gambar) {
            $sql .= ", gambar = ?";
            $params[] = $gambar;
        }
        
        $sql .= " WHERE id_produk = ?";
        $params[] = $product_id;
        
        $stmt = $conn->prepare($sql);
        // Bind parameters dynamically
        call_user_func_array([$stmt, 'bind_param'], 
            array_merge([str_repeat('s', count($params) - 1) . 'i'], $params)
        );
        $stmt->execute();
        $stmt->close();
    } else {
        // Insert baru
        $sql = "INSERT INTO produk (nama_produk, deskripsi, harga, gambar) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssds', $nama_produk, $deskripsi, $harga, $gambar);
        $stmt->execute();
        $stmt->close();
    }
}
?>
```

---

## 👀 Display Gambar

### Di Product List

```php
<?php
include 'includes/upload-handler.php';

$result = $conn->query("SELECT * FROM produk");
while ($produk = $result->fetch_assoc()) {
    $image_url = $produk['gambar'] 
        ? UploadHandler::getFileUrl($produk['gambar'], 'produk')
        : 'assets/images/placeholder.jpg';
?>
    <div class="product-card">
        <img src="<?php echo htmlspecialchars($image_url); ?>" alt="<?php echo htmlspecialchars($produk['nama_produk']); ?>">
        <h3><?php echo htmlspecialchars($produk['nama_produk']); ?></h3>
        <p><?php echo htmlspecialchars($produk['deskripsi']); ?></p>
        <p class="price">Rp <?php echo number_format($produk['harga']); ?></p>
    </div>
<?php
}
?>
```

---

## 🔐 Keamanan File

### Update `.htaccess` untuk uploads/

Buat file `.htaccess` di `uploads/`:

```apache
# Prevent PHP execution in uploads folder
<FilesMatch "\\.php$">
    Deny from all
</FilesMatch>

# Allow only images and PDF
<FilesMatch "\\.(?!jpg|jpeg|png|webp|pdf)$">
    Deny from all
</FilesMatch>

# Set proper headers
Header set Content-Type "image/jpeg"
Header set X-Content-Type-Options "nosniff"
```

---

## 📄 Checklist Setup

- [ ] Pull dari GitHub: `git pull origin main`
- [ ] Verifikasi folder struktur
- [ ] Set file permissions (chmod 755)
- [ ] Jalankan database query untuk kolom gambar
- [ ] Test upload dengan test-upload.html
- [ ] Implementasi upload di admin panel
- [ ] Implementasi display gambar di product list
- [ ] Setup .htaccess untuk security
- [ ] Testing end-to-end

---

## ⚠️ Common Issues

### Problem: "Failed to create directory"
**Solution:** Set folder permissions
```bash
chmod -R 755 MobileNest/uploads/
```

### Problem: Upload berhasil tapi gambar tidak tampil
**Solution:** 
- Cek database: `SELECT * FROM produk WHERE id_produk = X;`
- Cek file exists: `ls -la uploads/produk/`
- Cek path URL benar

### Problem: "File type not allowed"
**Solution:** Pastikan file format benar (jpg, jpeg, png, webp)

### Problem: XAMPP permission denied
**Windows:** Run XAMPP as administrator
**Linux:** `sudo chown -R $USER:$USER MobileNest/uploads/`

---

## 📚 Referensi File

File yang telah di-push ke GitHub:
- ✅ `MobileNest/uploads/produk/.gitkeep`
- ✅ `MobileNest/uploads/pembayaran/.gitkeep`
- ✅ `MobileNest/uploads/README.md`
- ✅ `MobileNest/includes/upload-handler.php`
- ✅ `MobileNest/api/upload.php`
- ✅ `.gitignore`
- ✅ `UPLOAD_GUIDE.md`
- ✅ `SETUP_LOCAL.md`

---

**Last Updated:** January 8, 2026  
**Status:** Ready for implementation
