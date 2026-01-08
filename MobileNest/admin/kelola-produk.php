<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . '/../config.php';

// autentikasi
if (!isset($_SESSION['admin']) && !isset($_SESSION['user'])) {
    header('Location: ../user/login.php');
    exit;
}

// handle actions: add, edit, delete
$message = '';
$msg_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ADD produk baru
    if ($action === 'add') {
        $nama_produk = trim($_POST['nama_produk'] ?? '');
        $merek = trim($_POST['merek'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $spesifikasi = trim($_POST['spesifikasi'] ?? '');
        $harga = !empty($_POST['harga']) ? (float)$_POST['harga'] : 0;
        $stok = !empty($_POST['stok']) ? (int)$_POST['stok'] : 0;
        $gambar = trim($_POST['gambar'] ?? '');
        $kategori = trim($_POST['kategori'] ?? '');
        $status_produk = trim($_POST['status_produk'] ?? 'Tersedia');

        if (empty($nama_produk) || $harga <= 0) {
            $message = 'Nama produk dan harga tidak boleh kosong.';
            $msg_type = 'danger';
        } else {
            $query = "INSERT INTO produk (nama_produk, merek, deskripsi, spesifikasi, harga, stok, gambar, kategori, status_produk, tanggal_ditambahkan) 
                      VALUES ('" . mysqli_real_escape_string($conn, $nama_produk) . "', '" . mysqli_real_escape_string($conn, $merek) . "', '" . mysqli_real_escape_string($conn, $deskripsi) . "', '" . mysqli_real_escape_string($conn, $spesifikasi) . "', $harga, $stok, '" . mysqli_real_escape_string($conn, $gambar) . "', '" . mysqli_real_escape_string($conn, $kategori) . "', '" . mysqli_real_escape_string($conn, $status_produk) . "', NOW())";
            if (mysqli_query($conn, $query)) {
                $message = 'Produk berhasil ditambahkan.';
                $msg_type = 'success';
            } else {
                $message = 'Error: ' . mysqli_error($conn);
                $msg_type = 'danger';
            }
        }
    }

    // EDIT produk
    if ($action === 'edit') {
        $id_produk = (int)$_POST['id_produk'];
        $nama_produk = trim($_POST['nama_produk'] ?? '');
        $merek = trim($_POST['merek'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $spesifikasi = trim($_POST['spesifikasi'] ?? '');
        $harga = !empty($_POST['harga']) ? (float)$_POST['harga'] : 0;
        $stok = !empty($_POST['stok']) ? (int)$_POST['stok'] : 0;
        $gambar = trim($_POST['gambar'] ?? '');
        $kategori = trim($_POST['kategori'] ?? '');
        $status_produk = trim($_POST['status_produk'] ?? 'Tersedia');

        if (empty($nama_produk) || $harga <= 0 || $id_produk <= 0) {
            $message = 'Data produk tidak valid.';
            $msg_type = 'danger';
        } else {
            $query = "UPDATE produk SET nama_produk='" . mysqli_real_escape_string($conn, $nama_produk) . "', merek='" . mysqli_real_escape_string($conn, $merek) . "', deskripsi='" . mysqli_real_escape_string($conn, $deskripsi) . "', spesifikasi='" . mysqli_real_escape_string($conn, $spesifikasi) . "', harga=$harga, stok=$stok, gambar='" . mysqli_real_escape_string($conn, $gambar) . "', kategori='" . mysqli_real_escape_string($conn, $kategori) . "', status_produk='" . mysqli_real_escape_string($conn, $status_produk) . "' WHERE id_produk=$id_produk";
            if (mysqli_query($conn, $query)) {
                $message = 'Produk berhasil diperbarui.';
                $msg_type = 'success';
            } else {
                $message = 'Error: ' . mysqli_error($conn);
                $msg_type = 'danger';
            }
        }
    }

    // DELETE produk
    if ($action === 'delete') {
        $id_produk = (int)$_POST['id_produk'];
        if (mysqli_query($conn, "DELETE FROM produk WHERE id_produk=$id_produk")) {
            $message = 'Produk berhasil dihapus.';
            $msg_type = 'success';
        } else {
            $message = 'Error: ' . mysqli_error($conn);
            $msg_type = 'danger';
        }
    }
}

// fetch produk untuk list
$limit = 100;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$where = "1=1";

if (!empty($_GET['kategori'])) {
    $kategori = mysqli_real_escape_string($conn, $_GET['kategori']);
    $where .= " AND kategori = '$kategori'";
}

if (!empty($_GET['status'])) {
    $status = mysqli_real_escape_string($conn, $_GET['status']);
    $where .= " AND status_produk = '$status'";
}

if (!empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where .= " AND (nama_produk LIKE '%$search%' OR merek LIKE '%$search%')";
}

$sql = "SELECT id_produk, nama_produk, merek, harga, stok, gambar, kategori, status_produk, tanggal_ditambahkan FROM produk WHERE $where ORDER BY tanggal_ditambahkan DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);

// fetch unique categories
$categories = [];
$cat_query = mysqli_query($conn, "SELECT DISTINCT kategori FROM produk WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori");
if ($cat_query) {
    while ($row = mysqli_fetch_assoc($cat_query)) {
        $categories[] = $row['kategori'];
    }
}

$statuses = ['Tersedia', 'Tidak Tersedia'];

// fetch produk untuk edit
$edit_produk = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $edit_result = mysqli_query($conn, "SELECT id_produk, nama_produk, merek, deskripsi, spesifikasi, harga, stok, gambar, kategori, status_produk FROM produk WHERE id_produk=$edit_id");
    if ($edit_result) {
        $edit_produk = mysqli_fetch_assoc($edit_result);
    }
}

?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Kelola Produk - MobileNest Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background: #f5f7fa; }
        .navbar { background: linear-gradient(135deg, #667eea, #764ba2) !important; }
        .navbar-brand { font-weight: 700; font-size: 18px; }
        .nav-link { color: rgba(255,255,255,0.8) !important; transition: all 0.3s; }
        .nav-link:hover, .nav-link.active { color: white !important; }
        .container-fluid { margin-top: 20px; }
        .table { background: white; border-radius: 10px; overflow: hidden; }
        .table th { background: #f0f7ff; font-weight: 700; color: #2c3e50; border: none; }
        .table td { border-color: #e0e0e0; }
        .btn-info { background: #667eea; border-color: #667eea; color: white; }
        .btn-info:hover { background: #764ba2; border-color: #764ba2; color: white; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php"><i class="bi bi-box-seam"></i> MobileNest Admin</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="index.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
        <li class="nav-item"><a class="nav-link active" href="kelola-produk.php"><i class="bi bi-phone"></i> Produk</a></li>
        <li class="nav-item"><a class="nav-link" href="verifikasi-pembayaran.php"><i class="bi bi-credit-card"></i> Verifikasi</a></li>
        <li class="nav-item"><a class="nav-link" href="kelola-transaksi.php"><i class="bi bi-receipt"></i> Transaksi</a></li>
        <li class="nav-item"><a class="nav-link" href="laporan.php"><i class="bi bi-bar-chart"></i> Laporan</a></li>
        <li class="nav-item"><a class="nav-link text-danger" href="../user/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<main class="container-fluid">
  <div class="row mb-4">
    <div class="col">
      <h2 style="font-weight: 700; color: #2c3e50;">
        <i class="bi bi-phone"></i> Kelola Produk
      </h2>
    </div>
  </div>

  <?php if (!empty($message)): ?>
    <div class="alert alert-<?= htmlspecialchars($msg_type) ?> alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($message) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <div class="mb-3">
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal"><i class="bi bi-plus-circle"></i> Tambah Produk</button>
  </div>

  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
      <form class="row g-3" method="get" action="kelola-produk.php">
        <div class="col-md-3">
          <label class="form-label fw-bold">Cari</label>
          <input type="search" name="search" class="form-control" placeholder="Nama/Merek..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label fw-bold">Kategori</label>
          <select name="kategori" class="form-select">
            <option value="">Semua Kategori</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= htmlspecialchars($cat) ?>" <?= (isset($_GET['kategori']) && $_GET['kategori'] === $cat) ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label fw-bold">Status</label>
          <select name="status" class="form-select">
            <option value="">Semua Status</option>
            <?php foreach ($statuses as $s): ?>
              <option value="<?= htmlspecialchars($s) ?>" <?= (isset($_GET['status']) && $_GET['status'] === $s) ? 'selected' : '' ?>><?= htmlspecialchars($s) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3 d-flex align-items-end gap-2">
          <button type="submit" class="btn btn-primary fw-bold flex-grow-1"><i class="bi bi-funnel"></i> Filter</button>
          <a href="kelola-produk.php" class="btn btn-outline-secondary fw-bold"><i class="bi bi-arrow-counterclockwise"></i> Reset</a>
        </div>
      </form>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead>
        <tr>
          <th>ID</th>
          <th>Gambar</th>
          <th>Nama Produk</th>
          <th>Merek</th>
          <th>Kategori</th>
          <th>Harga</th>
          <th>Stok</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$result || mysqli_num_rows($result) === 0): ?>
          <tr><td colspan="9" class="text-center py-4 text-muted"><i class="bi bi-inbox"></i> Tidak ada produk.</td></tr>
        <?php else: ?>
          <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
              <td><strong>#<?= (int)$row['id_produk'] ?></strong></td>
              <td>
                <?php if (!empty($row['gambar'])): ?>
                  <img src="<?= htmlspecialchars($row['gambar']) ?>" alt="<?= htmlspecialchars($row['nama_produk']) ?>" style="max-width:60px;max-height:60px;object-fit:cover;border-radius:5px;">
                <?php else: ?>
                  <span class="text-muted">-</span>
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($row['nama_produk']) ?></td>
              <td><?= htmlspecialchars($row['merek']) ?></td>
              <td><?= htmlspecialchars($row['kategori']) ?></td>
              <td><strong>Rp <?= number_format((float)$row['harga'],0,',','.') ?></strong></td>
              <td><?= (int)$row['stok'] ?></td>
              <td>
                <span class="badge bg-<?= $row['status_produk'] === 'Tersedia' ? 'success' : 'secondary' ?>">
                  <?= htmlspecialchars($row['status_produk']) ?>
                </span>
              </td>
              <td class="text-nowrap">
                <a href="?edit=<?= (int)$row['id_produk'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i> Edit</a>
                <form method="post" class="d-inline" onsubmit="return confirm('Hapus produk ini?');">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id_produk" value="<?= (int)$row['id_produk'] ?>">
                  <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i> Hapus</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <p class="text-muted text-center mt-3">Menampilkan maksimal <?= $limit ?> produk per halaman.</p>
</main>

<!-- Modal Tambah Produk -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="post">
        <input type="hidden" name="action" value="add">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Tambah Produk Baru</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-bold">Nama Produk *</label>
            <input type="text" name="nama_produk" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Merek</label>
            <input type="text" name="merek" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Deskripsi</label>
            <textarea name="deskripsi" class="form-control" rows="2"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Spesifikasi</label>
            <textarea name="spesifikasi" class="form-control" rows="2"></textarea>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold">Harga *</label>
              <input type="number" name="harga" class="form-control" step="0.01" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold">Stok</label>
              <input type="number" name="stok" class="form-control" value="0">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Gambar URL</label>
            <input type="text" name="gambar" class="form-control" placeholder="https://...">
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold">Kategori</label>
              <input type="text" name="kategori" class="form-control" placeholder="Flagship">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold">Status</label>
              <select name="status_produk" class="form-select">
                <option value="Tersedia">Tersedia</option>
                <option value="Tidak Tersedia">Tidak Tersedia</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Edit Produk -->
<?php if ($edit_produk): ?>
<div class="modal fade show" id="editModal" tabindex="-1" style="display: block;">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="post">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id_produk" value="<?= (int)$edit_produk['id_produk'] ?>">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-pencil"></i> Edit Produk</h5>
          <a href="kelola-produk.php" class="btn-close"></a>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-bold">Nama Produk *</label>
            <input type="text" name="nama_produk" class="form-control" value="<?= htmlspecialchars($edit_produk['nama_produk']) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Merek</label>
            <input type="text" name="merek" class="form-control" value="<?= htmlspecialchars($edit_produk['merek']) ?>">
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Deskripsi</label>
            <textarea name="deskripsi" class="form-control" rows="2"><?= htmlspecialchars($edit_produk['deskripsi']) ?></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Spesifikasi</label>
            <textarea name="spesifikasi" class="form-control" rows="2"><?= htmlspecialchars($edit_produk['spesifikasi']) ?></textarea>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold">Harga *</label>
              <input type="number" name="harga" class="form-control" step="0.01" value="<?= (float)$edit_produk['harga'] ?>" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold">Stok</label>
              <input type="number" name="stok" class="form-control" value="<?= (int)$edit_produk['stok'] ?>">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Gambar URL</label>
            <input type="text" name="gambar" class="form-control" value="<?= htmlspecialchars($edit_produk['gambar']) ?>">
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold">Kategori</label>
              <input type="text" name="kategori" class="form-control" value="<?= htmlspecialchars($edit_produk['kategori']) ?>">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold">Status</label>
              <select name="status_produk" class="form-select">
                <option value="Tersedia" <?= $edit_produk['status_produk'] === 'Tersedia' ? 'selected' : '' ?>>Tersedia</option>
                <option value="Tidak Tersedia" <?= $edit_produk['status_produk'] === 'Tidak Tersedia' ? 'selected' : '' ?>>Tidak Tersedia</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <a href="kelola-produk.php" class="btn btn-secondary">Batal</a>
          <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="modal-backdrop fade show"></div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>