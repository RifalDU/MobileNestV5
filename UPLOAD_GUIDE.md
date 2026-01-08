# MobileNest Upload Guide

Panduan lengkap untuk menggunakan sistem upload di MobileNest V5.

## 📁 Struktur Folder Upload

```
MobileNest/
├── uploads/
│   ├── produk/              # Folder gambar produk
│   │   └── .gitkeep        # Memastikan folder ter-track di Git
│   └── pembayaran/          # Folder bukti pembayaran
│       └── .gitkeep
├── includes/
│   └── upload-handler.php   # Class untuk handle upload
└── api/
    └── upload.php           # API endpoint untuk upload
```

## 🔧 Konfigurasi Upload

### File: `MobileNest/includes/upload-handler.php`

Kelas `UploadHandler` menyediakan method untuk upload file dengan aman:

**Konfigurasi default:**
- **Max file size**: 5MB
- **Produk**: jpg, jpeg, png, webp
- **Pembayaran**: jpg, jpeg, png, webp, pdf

### Mengubah Konfigurasi

Untuk mengubah limit atau tipe file, edit constant di `upload-handler.php`:

```php
const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
const ALLOWED_PRODUK_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
const ALLOWED_PRODUK_EXT = ['jpg', 'jpeg', 'png', 'webp'];
```

## 📤 Implementasi Upload

### 1. Upload via API Endpoint

#### A. Upload Gambar Produk

**Endpoint:** `POST /api/upload.php?action=upload_product`

**Request (form-data):**
```
image: [file]
product_id: 123
```

**Response:**
```json
{
  "success": true,
  "message": "Gambar produk berhasil diupload",
  "filename": "produk_123_1673000000_abcd1234.jpg",
  "url": "uploads/produk/produk_123_1673000000_abcd1234.jpg"
}
```

**Contoh cURL:**
```bash
curl -X POST http://localhost/MobileNest/api/upload.php?action=upload_product \
  -F "image=@/path/to/image.jpg" \
  -F "product_id=123"
```

**Contoh JavaScript (fetch):**
```javascript
const formData = new FormData();
formData.append('image', fileInput.files[0]);
formData.append('product_id', 123);

fetch('api/upload.php?action=upload_product', {
  method: 'POST',
  body: formData
})
.then(r => r.json())
.then(data => {
  if (data.success) {
    console.log('Upload berhasil:', data.filename);
    document.getElementById('product-image').src = data.url;
  } else {
    console.error('Error:', data.message);
  }
});
```

#### B. Upload Bukti Pembayaran

**Endpoint:** `POST /api/upload.php?action=upload_payment`

**Request (form-data):**
```
proof: [file]
transaction_id: 456
```

**Response:**
```json
{
  "success": true,
  "message": "Bukti pembayaran berhasil diupload",
  "filename": "pembayaran_456_1673000000_efgh5678.pdf",
  "url": "uploads/pembayaran/pembayaran_456_1673000000_efgh5678.pdf"
}
```

**Contoh HTML Form:**
```html
<form action="api/upload.php?action=upload_payment" method="POST" enctype="multipart/form-data">
  <input type="file" name="proof" accept=".pdf,.jpg,.jpeg,.png,.webp" required>
  <input type="hidden" name="transaction_id" value="456">
  <button type="submit">Upload Bukti Pembayaran</button>
</form>
```

### 2. Upload via PHP Class (Server-side)

**Import class:**
```php
include 'includes/upload-handler.php';
```

**Upload produk:**
```php
$result = UploadHandler::uploadProductImage($_FILES['image'], $product_id);

if ($result['success']) {
  // Simpan $result['filename'] ke database
  $filename = $result['filename'];
  // UPDATE produk SET gambar = '$filename' WHERE id_produk = $product_id
} else {
  echo "Error: " . $result['message'];
}
```

**Upload pembayaran:**
```php
$result = UploadHandler::uploadPaymentProof($_FILES['proof'], $transaction_id);

if ($result['success']) {
  $filename = $result['filename'];
  // UPDATE transaksi SET bukti_pembayaran = '$filename' WHERE id_transaksi = $transaction_id
} else {
  echo "Error: " . $result['message'];
}
```

## 🖼️ Menampilkan Gambar

### Dari Database

```php
<?php
include 'includes/upload-handler.php';

// Query produk dari database
$result = $conn->query("SELECT * FROM produk WHERE id_produk = 123");
$produk = $result->fetch_assoc();

// Dapatkan URL gambar
$image_url = UploadHandler::getFileUrl($produk['gambar'], 'produk');
?>

<img src="<?php echo htmlspecialchars($image_url); ?>" alt="<?php echo htmlspecialchars($produk['nama_produk']); ?>">
```

### Direct Display

```html
<!-- Jika nama file sudah diketahui -->
<img src="uploads/produk/produk_123_1673000000_abcd1234.jpg" alt="Product">
```

## 🗑️ Menghapus File

**Via PHP:**
```php
$result = UploadHandler::deleteFile('produk_123_1673000000_abcd1234.jpg', 'produk');

if ($result['success']) {
  // Juga hapus dari database
  $conn->query("UPDATE produk SET gambar = NULL WHERE id_produk = 123");
} else {
  echo "Error: " . $result['message'];
}
```

## 🔐 Security Features

Upload handler sudah dilengkapi dengan:

✅ **File size validation** - Max 5MB
✅ **MIME type checking** - Verifikasi tipe file actual
✅ **Extension validation** - Whitelist tipe file
✅ **Directory traversal prevention** - Secure path handling
✅ **Unique filename generation** - Prevent overwrite dengan timestamp + random
✅ **Proper file permissions** - 644 untuk file
✅ **Error handling** - Detailed error messages

## 📝 Database Integration

### Table: produk

```sql
ALTER TABLE produk ADD COLUMN IF NOT EXISTS gambar VARCHAR(255) DEFAULT NULL;
```

**Contoh:**
```sql
UPDATE produk SET gambar = 'produk_123_1673000000_abcd1234.jpg' WHERE id_produk = 123;
```

### Table: transaksi

```sql
ALTER TABLE transaksi ADD COLUMN IF NOT EXISTS bukti_pembayaran VARCHAR(255) DEFAULT NULL;
```

**Contoh:**
```sql
UPDATE transaksi SET bukti_pembayaran = 'pembayaran_456_1673000000_efgh5678.pdf' WHERE id_transaksi = 456;
```

## 🧪 Testing Upload

### Test Product Image Upload

```bash
curl -X POST http://localhost/MobileNest/api/upload.php?action=upload_product \
  -F "image=@/path/to/test-image.jpg" \
  -F "product_id=1"
```

### Test Payment Proof Upload

```bash
curl -X POST http://localhost/MobileNest/api/upload.php?action=upload_payment \
  -F "proof=@/path/to/payment.pdf" \
  -F "transaction_id=1"
```

## ⚠️ Troubleshooting

### Error: "Ukuran file terlalu besar"
- Kurangi ukuran file atau increase `MAX_FILE_SIZE` constant

### Error: "Tipe file tidak diperbolehkan"
- Gunakan format: jpg, jpeg, png, webp (produk) atau tambahkan pdf (pembayaran)
- Pastikan file benar-benar adalah gambar/pdf (bukan file tersamarkan)

### Error: "Gagal membuat direktori upload"
- Pastikan folder `uploads/` writable: `chmod 755 uploads/`
- Cek permission di XAMPP/server

### Error: "Gagal mengupload file"
- Cek PHP `upload_max_filesize` dan `post_max_size` di php.ini
- Pastikan folder `uploads/` memiliki write permission

### File uploaded tapi tidak muncul
- Cek apakah database sudah ter-update dengan nama file
- Verifikasi path URL benar
- Cek file permissions: `chmod 644 uploads/produk/*`

## 📚 File References

- **Class:** `MobileNest/includes/upload-handler.php`
- **API:** `MobileNest/api/upload.php`
- **Config:** `MobileNest/config.php`
- **Uploads:** `MobileNest/uploads/`

## 🎯 Next Steps

1. ✅ Setup folder struktur (sudah di-commit)
2. ✅ Implement upload handler class (sudah di-commit)
3. ✅ Create API endpoint (sudah di-commit)
4. 📝 Add gambar column ke table produk
5. 📝 Add bukti_pembayaran column ke table transaksi
6. 📝 Update admin panel untuk upload gambar produk
7. 📝 Update user form untuk upload bukti pembayaran
8. 🧪 Test upload functionality

---

**Last Updated:** January 8, 2026
**Author:** MobileNest Development Team
