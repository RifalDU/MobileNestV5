# Uploads Directory

Folder ini digunakan untuk menyimpan file-file upload dari aplikasi MobileNest.

## Struktur Folder

### `/produk/`
Menyimpan gambar produk yang di-upload oleh admin atau seller.

**Tipe file yang diperbolehkan:**
- `.jpg`, `.jpeg`
- `.png`
- `.webp`

**Ukuran maksimal:** 5MB per file

### `/pembayaran/`
Menyimpan bukti pembayaran atau dokumen terkait transaksi.

**Tipe file yang diperbolehkan:**
- `.jpg`, `.jpeg`
- `.png`
- `.pdf`

## Catatan Keamanan

⚠️ **Penting:**
1. Jangan commit file gambar actual ke Git (terlalu besar)
2. Gunakan `.gitkeep` untuk keep struktur folder di Git
3. Implementasi validasi file di server-side
4. Sanitize nama file untuk prevent directory traversal
5. Set proper file permissions (644 untuk file, 755 untuk folder)

## Development Setup

Untuk development lokal, pastikan folder ini memiliki write permission:

```bash
chmod 755 uploads/produk
chmod 755 uploads/pembayaran
```
