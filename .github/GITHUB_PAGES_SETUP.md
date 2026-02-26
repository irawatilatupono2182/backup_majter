# GitHub Pages Setup Instructions

## Automated Deployment

GitHub Pages deployment untuk folder `prototype/` sudah dikonfigurasi menggunakan GitHub Actions. Workflow akan otomatis berjalan setiap kali ada perubahan di folder `prototype/` yang dipush ke branch default repository (`main` atau `master`).

## Manual Configuration Required

Untuk mengaktifkan GitHub Pages, repository owner perlu melakukan konfigurasi di GitHub repository settings:

### Steps:

1. **Buka Repository Settings**
   - Pergi ke repository: https://github.com/irawatilatupono2182/backup_majter
   - Klik tab **Settings**

2. **Aktifkan GitHub Pages**
   - Di sidebar kiri, klik **Pages** (di bawah section "Code and automation")
   - Di section "Build and deployment":
     - **Source**: Pilih **GitHub Actions**
   
3. **Selesai!**
   - Setelah workflow pertama kali berjalan, GitHub Pages akan aktif
   - URL akan tersedia di: https://irawatilatupono2182.github.io/backup_majter/
   - URL akan ditampilkan di halaman GitHub Pages settings

## Workflow Details

File workflow ada di: `.github/workflows/deploy-prototype.yml`

### Triggers:
- **Automatic**: Setiap push ke branch default repository (`main` atau `master`) yang mengubah file di `prototype/**`
- **Manual**: Bisa di-trigger manual dari tab "Actions" di GitHub

### Apa yang Di-deploy:
- Semua file di folder `prototype/` akan dipublikasikan
- Termasuk: HTML, CSS, JavaScript, images, dan assets lainnya

## Verification

Setelah workflow selesai berjalan:

1. **Check Workflow Status**
   - Pergi ke tab "Actions" di repository
   - Lihat workflow "Deploy Prototype to GitHub Pages"
   - Pastikan status hijau (✓)

2. **Access GitHub Pages**
   - Buka: https://irawatilatupono2182.github.io/backup_majter/
   - Anda akan melihat prototype index.html sebagai homepage
   - Semua link ke sub-pages seharusnya berfungsi

## Troubleshooting

### Workflow Gagal
- Check logs di tab "Actions"
- Pastikan permissions sudah benar di repository settings
- Pastikan GitHub Pages sudah enabled dengan source "GitHub Actions"

### Page 404
- Tunggu beberapa menit setelah deployment pertama
- Clear browser cache
- Check apakah workflow sudah selesai dengan sukses

### Links Tidak Berfungsi
- Pastikan semua links di HTML menggunakan relative paths
- Contoh: `pages/filament/customers.html` bukan `/pages/filament/customers.html`

## Updating Content

Untuk update konten di GitHub Pages:

1. Edit file di folder `prototype/`
2. Commit dan push ke branch default repository (`main` atau `master`)
3. Workflow akan otomatis berjalan
4. Dalam beberapa menit, perubahan akan live di GitHub Pages

## Additional Notes

- **Free Hosting**: GitHub Pages gratis untuk public repositories
- **Custom Domain**: Bisa dikonfigurasi custom domain jika diperlukan
- **HTTPS**: Otomatis enabled dengan SSL/TLS certificate
- **Bandwidth**: Unlimited untuk static sites
