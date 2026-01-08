# Panduan Upload Gambar Produk di Admin Panel

Panduan lengkap menggunakan fitur upload gambar produk yang sudah terintegrasi di `kelola-produk.php`.

## ✨ Fitur Baru

### 1. **Drag & Drop Upload**
- Seret gambar langsung ke area upload
- Atau klik untuk membuka file picker
- Live preview sebelum submit

### 2. **Multiple Upload Options**
- **Upload File**: Upload langsung dari komputer
- **URL Gambar**: Input URL gambar dari internet
- Pilih yang cocok, tidak harus kedua-duanya

### 3. **Image Preview**
- Preview otomatis setelah pilih file
- Lihat gambar saat ini saat edit (tab "Gambar Saat Ini")
- Confirm sebelum ganti gambar

---

## 🚀 Cara Menggunakan

### **Tambah Produk Baru**

#### Step 1: Buka Modal
Klik tombol **"+ Tambah Produk"** di halaman kelola-produk.php

#### Step 2: Upload Gambar
Pilih tab **"Upload Gambar"** (default sudah aktif)

**Option A: Drag & Drop**
```
1. Drag file gambar dari file explorer
2. Drop ke area upload (zona biru)
3. Preview otomatis muncul
```

**Option B: Click to Browse**
```
1. Klik area upload
2. Pilih file dari komputer
3. Preview otomatis muncul
```

**Option C: URL Gambar**
```
1. Klik tab "URL Gambar"
2. Paste link gambar: https://example.com/image.jpg
3. Tidak perlu upload file
```

#### Step 3: Isi Data Produk
Isi form dengan data produk:
- ✓ **Nama Produk** (wajib)
- Merek
- Deskripsi
- Spesifikasi
- ✓ **Harga** (wajib)
- Stok
- Kategori
- Status (Tersedia / Tidak Tersedia)

#### Step 4: Simpan
Klik **"Simpan"** - sistem otomatis akan:
1. Upload gambar ke `uploads/produk/`
2. Generate unique filename (format: `produk_[ID]_[TIMESTAMP]_[RANDOM].jpg`)
3. Simpan ke database

**Contoh filename**: `produk_1_1673000000_abcd1234.jpg`

---

### **Edit Produk Existing**

#### Step 1: Buka Form Edit
Klik tombol **"Edit"** pada produk yang ingin diubah

#### Step 2: 3 Pilihan Gambar

**Tab 1: "Ganti Gambar" (Upload file baru)**
```
- Drag & drop gambar baru
- Atau klik untuk browse
- Gambar lama akan diganti otomatis
```

**Tab 2: "URL Gambar" (Input URL baru)**
```
- Input URL gambar baru dari internet
- Gambar lama akan diganti
```

**Tab 3: "Gambar Saat Ini" (Lihat gambar lama)**
```
- Preview gambar produk yang sedang digunakan
- Jika ingin tetap gunakan, jangan klik tab ganti
```

#### Step 3: Pilih Salah Satu

**Skenario 1: Ganti gambar**
- Upload file baru → Klik Simpan
- Gambar lama otomatis ter-replace

**Skenario 2: Tetap gunakan gambar lama**
- Jangan upload file baru
- Jangan input URL baru
- Edit data lain kalau perlu → Klik Simpan

**Skenario 3: Ganti dengan URL**
- Tab "URL Gambar" → Input URL → Klik Simpan

#### Step 4: Simpan Perubahan
Klik **"Simpan Perubahan"**

---

## 📋 Spesifikasi File

### Format Gambar yang Diizinkan
```
✓ JPG / JPEG
✓ PNG  
✓ WebP
✗ GIF, BMP, SVG (tidak didukung)
```

### Ukuran File
```
Maximum: 5MB
Rekomendasi: < 2MB untuk performa lebih baik
```

### Resolusi Gambar
```
Minimal: 300x300px
Rekomendasi: 800x800px atau lebih
Aspect ratio: Bebas (akan auto-fit di thumbnail)
```

---

## 💾 Penyimpanan Gambar

### Lokasi File
```
MobileNest/
└── uploads/
    └── produk/
        └── [gambar_files_tersimpan_disini]
```

### Format Path di Database
File disimpan dengan **nama file saja** (bukan full path):
```sql
EXAMPLE:
- Column: `gambar`
- Value: `produk_123_1673000000_abcd1234.jpg`
```

### Display di Frontend
Untuk menampilkan gambar di halaman produk:
```php
<?php
include 'includes/upload-handler.php';
$image_url = UploadHandler::getFileUrl($produk['gambar'], 'produk');
?>
<img src="<?php echo htmlspecialchars($image_url); ?>" alt="Product">
```

Output akan menjadi:
```
http://localhost/MobileNest/uploads/produk/produk_123_1673000000_abcd1234.jpg
```

---

## 🐛 Troubleshooting

### Problem: "Gagal upload file"
**Solusi:**
- Cek ukuran file < 5MB
- Cek format file (JPG, PNG, WebP)
- Cek folder `uploads/produk/` writable (chmod 755)
- Cek PHP `upload_max_filesize` di php.ini >= 10MB

### Problem: File uploaded tapi tidak muncul di preview
**Solusi:**
- Refresh halaman
- Cek database (`SELECT * FROM produk` di phpMyAdmin)
- Cek file exist di `uploads/produk/`
- Cek path URL di kolom `gambar` di database

### Problem: Preview tidak muncul saat edit
**Solusi:**
- Ensure file ada di `uploads/produk/`
- Check file permissions (chmod 644)
- Check URL accessible di browser

### Problem: "MIME type file tidak valid"
**Solusi:**
- Gunakan gambar asli dari camera/design tool
- Jangan gunakan file yang di-rename dari file lain
- Coba convert ke format standar (gunakan online converter)

---

## 📊 Workflow Diagram

```
┌─────────────────────────────────────────┐
│   Admin Panel: Kelola Produk           │
└──────────────────┬──────────────────────┘
                   │
      ┌────────────┼────────────┐
      │            │            │
   TAMBAH        EDIT         HAPUS
     │             │            │
     │        ┌────┴──────┐     │
     │        │           │     │
   Upload  Gambar_Baru  Gambar_Lama
   Gambar       │            │
     │        Upload/URL    Keep
     │        Ganti File     │
     │          │            │
     └──────────┼────────────┘
              │
        ┌─────┴─────┐
        │           │
   Validasi      Upload ke
   - Size       uploads/produk/
   - Type       Generate Filename
   - Ext        Save to Database
        │           │
        └─────┬─────┘
              │
        Success/Error
        Message
```

---

## 🔒 Security Features

✅ **File Type Validation**
- Check MIME type (bukan hanya extension)
- Whitelist file types (JPG, PNG, WebP)

✅ **File Size Validation**
- Max 5MB per file
- Prevent resource exhaustion

✅ **Filename Security**
- Auto-generate unique filename
- Prevent directory traversal
- Include timestamp untuk prevent overwrite

✅ **Upload Directory Protection**
- .htaccess prevents PHP execution
- Proper file permissions (644)
- Folder permissions (755)

---

## 📚 Database Schema

### Tabel: `produk`
```sql
-- Kolom untuk gambar sudah ada
CREATE TABLE produk (
    id_produk INT AUTO_INCREMENT PRIMARY KEY,
    nama_produk VARCHAR(255) NOT NULL,
    merek VARCHAR(100),
    deskripsi TEXT,
    spesifikasi TEXT,
    harga DECIMAL(10, 2),
    stok INT DEFAULT 0,
    gambar VARCHAR(255),          -- <-- Menyimpan nama file
    kategori VARCHAR(100),
    status_produk VARCHAR(50),
    tanggal_ditambahkan TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Update Database (jika belum ada kolom)
```sql
-- Add gambar column jika belum ada
ALTER TABLE produk ADD COLUMN gambar VARCHAR(255) DEFAULT NULL AFTER deskripsi;

-- Verify
DESC produk;  -- Lihat struktur tabel
```

---

## 💡 Tips & Best Practices

### 1. **Persiapan Gambar**
- Gunakan gambar berkualitas tinggi (minimum 800x800px)
- Crop sesuai produk (jangan ada background yang tidak perlu)
- Kompress sebelum upload (gunakan TinyPNG atau similar)
- Format: PNG untuk logo/transparent, JPG untuk foto

### 2. **Naming Convention**
- Sistem auto-generate: `produk_[id]_[timestamp]_[random].jpg`
- Contoh: `produk_5_1673012345_af3d2b1c.jpg`
- Jangan edit manual - gunakan admin panel

### 3. **Testing**
- Test upload dengan berbagai ukuran
- Test di berbagai browser (Chrome, Firefox, Edge)
- Test responsiveness di mobile
- Test preview before submit

### 4. **Maintenance**
- Backup folder `uploads/produk/` secara regular
- Monitor folder size (jangan sampai terlalu besar)
- Delete old/unused images manually jika perlu
- Review file permissions secara berkala

---

## 📞 Support & Contact

Jika ada masalah:
1. Cek TROUBLESHOOTING section di atas
2. Lihat logs di `MobileNest/logs/`
3. Check browser console (F12 → Console tab)
4. Review database column types

---

## 🎓 Related Documentation

- [UPLOAD_GUIDE.md](./UPLOAD_GUIDE.md) - API endpoint documentation
- [SETUP_LOCAL.md](./SETUP_LOCAL.md) - Local development setup
- [upload-handler.php](./MobileNest/includes/upload-handler.php) - Source code
- [kelola-produk.php](./MobileNest/admin/kelola-produk.php) - Admin panel

---

**Last Updated:** January 8, 2026
**Version:** V5.1.0
**Status:** ✅ Production Ready
