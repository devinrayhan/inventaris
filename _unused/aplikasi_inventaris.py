import os
import flet as ft
from datetime import datetime
import json

# BAGIAN LOGIKA INTI 
class ManajemenInventaris:
    def __init__(self):
        self.stok_barang = {}
        self.riwayat_transaksi = []
        self.muat_data()

    def catat_transaksi(self, nama_barang, jumlah, harga, tanggal, jenis):
        """Mencatat transaksi barang masuk/keluar."""
        try:
            if not nama_barang:
                return False, "Nama barang tidak boleh kosong!"
            if jumlah <= 0 or harga <= 0:
                return False, "Jumlah dan Harga harus lebih dari 0."

            if jenis == 'KELUAR':
                if self.stok_barang.get(nama_barang, 0) == 0:
                    return False, f"Barang '{nama_barang}' tidak ada atau stok habis."
                if jumlah > self.stok_barang.get(nama_barang, 0):
                    return False, f"Stok tidak mencukupi. Sisa {self.stok_barang.get(nama_barang, 0)}."
                self.stok_barang[nama_barang] -= jumlah
            else:  # MASUK
                self.stok_barang[nama_barang] = self.stok_barang.get(nama_barang, 0) + jumlah

            self.riwayat_transaksi.append({
                'jenis': jenis, 'nama': nama_barang, 'jumlah': jumlah,
                'harga': harga, 'tanggal': tanggal
            })
            self.simpan_data()
            return True, f"Berhasil! Stok '{nama_barang}' sekarang: {self.stok_barang[nama_barang]}."
        except Exception as e:
            return False, f"Terjadi kesalahan: {e}"

    def catat_barang_masuk(self, nama_barang, jumlah, harga, tanggal):
        return self.catat_transaksi(nama_barang, jumlah, harga, tanggal, 'MASUK')

    def catat_barang_keluar(self, nama_barang, jumlah, harga, tanggal):
        return self.catat_transaksi(nama_barang, jumlah, harga, tanggal, 'KELUAR')

    def hapus_transaksi(self, index_transaksi):
        """Menghapus transaksi berdasarkan index dan menyesuaikan stok."""
        try:
            if index_transaksi < 0 or index_transaksi >= len(self.riwayat_transaksi):
                return False, "Transaksi tidak ditemukan."
            
            transaksi = self.riwayat_transaksi[index_transaksi]
            nama_barang = transaksi['nama']
            jumlah = transaksi['jumlah']
            jenis = transaksi['jenis']
            
            # Sesuaikan stok berdasarkan jenis transaksi yang dihapus
            if jenis == 'MASUK':
                # Jika menghapus transaksi masuk, kurangi stok
                if self.stok_barang.get(nama_barang, 0) < jumlah:
                    return False, f"Tidak dapat menghapus. Stok '{nama_barang}' akan menjadi negatif."
                self.stok_barang[nama_barang] -= jumlah
                if self.stok_barang[nama_barang] == 0:
                    del self.stok_barang[nama_barang]
            else:  # KELUAR
                # Jika menghapus transaksi keluar, tambah stok kembali
                self.stok_barang[nama_barang] = self.stok_barang.get(nama_barang, 0) + jumlah
            
            # Hapus transaksi dari riwayat
            del self.riwayat_transaksi[index_transaksi]
            self.simpan_data()  # Auto-save setelah penghapusan
            
            return True, f"Transaksi berhasil dihapus. Stok '{nama_barang}' sekarang: {self.stok_barang.get(nama_barang, 0)}."
        except Exception as e:
            return False, f"Terjadi kesalahan: {e}"

    def simpan_data(self, nama_file="data_inventaris.json"):
        """Menyimpan data stok dan riwayat ke file JSON."""
        try:
            # Salin riwayat agar bisa diubah format tanggalnya tanpa mengganggu aplikasi
            riwayat_untuk_simpan = []
            for trx in self.riwayat_transaksi:
                # Ubah objek tanggal menjadi string format YYYY-MM-DD
                trx_copy = trx.copy()
                trx_copy['tanggal'] = trx_copy['tanggal'].strftime('%Y-%m-%d')
                riwayat_untuk_simpan.append(trx_copy)

            data_untuk_disimpan = {
                'stok': self.stok_barang,
                'riwayat': riwayat_untuk_simpan
            }
            with open(nama_file, 'w') as f:
                json.dump(data_untuk_disimpan, f, indent=4)
            print(f"Data berhasil disimpan ke {nama_file}")
        except Exception as e:
            print(f"Gagal menyimpan data: {e}")

    def muat_data(self, nama_file="data_inventaris.json"):
        """Memuat data stok dan riwayat dari file JSON saat aplikasi dimulai."""
        try:
            if os.path.exists(nama_file):
                with open(nama_file, 'r') as f:
                    data_dari_file = json.load(f)
                    self.stok_barang = data_dari_file.get('stok', {})
                    
                    # Ubah kembali string tanggal menjadi objek tanggal
                    riwayat_dari_file = data_dari_file.get('riwayat', [])
                    self.riwayat_transaksi = []
                    for trx in riwayat_dari_file:
                        trx['tanggal'] = datetime.strptime(trx['tanggal'], '%Y-%m-%d').date()
                        self.riwayat_transaksi.append(trx)

                print(f"Data berhasil dimuat dari {nama_file}")
            else:
                print(f"File {nama_file} tidak ditemukan. Memulai dengan data kosong.")
        except Exception as e:
            print(f"Gagal memuat data: {e}")

# BAGIAN TAMPILAN ANTARMUKA (UI
def main(page: ft.Page):
    page.title = "Aplikasi Manajemen Inventaris"
    page.vertical_alignment = ft.MainAxisAlignment.START
    page.window_width = 900
    page.window_height = 1000

    # Mengatur latar belakang halaman utama menjadi putih
    page.bgcolor = ft.Colors.WHITE

    inventaris = ManajemenInventaris()

    # --- UI Components ---
    txt_nama_barang = ft.TextField(
    label="Nama Barang",
    width=300,
    color=ft.Colors.BLACK,
    label_style=ft.TextStyle(color=ft.Colors.BLACK)
    )
    txt_jumlah = ft.TextField(
    label="Jumlah",
    width=150,
    input_filter=ft.NumbersOnlyInputFilter(),
    color=ft.Colors.BLACK,
    label_style=ft.TextStyle(color=ft.Colors.BLACK)
    )
    
    def format_rupiah(value):
        """Format angka menjadi format dengan titik sebagai pemisah ribuan"""
        if not value:
            return ""
        # Hapus semua karakter non-digit
        clean_value = ''.join(filter(str.isdigit, str(value)))
        if not clean_value:
            return ""
        # Format dengan titik sebagai pemisah ribuan
        formatted = f"{int(clean_value):,}".replace(',', '.')
        return formatted
    
    def extract_number_from_rupiah(value):
        """Ekstrak angka dari format dengan titik"""
        if not value:
            return ""
        # Hapus titik dan spasi
        clean_value = value.replace(".", "").replace(" ", "")
        return clean_value if clean_value.isdigit() else ""
    
    def on_harga_change(e):
        """Handler ketika field harga berubah"""
        # Ambil posisi cursor
        current_pos = e.control.selection.start if e.control.selection else 0
        
        # Extract hanya angka dari input
        raw_value = extract_number_from_rupiah(e.control.value)
        
        # Format dengan titik pemisah ribuan
        formatted_value = format_rupiah(raw_value)
        
        # Set nilai yang sudah diformat
        e.control.value = formatted_value
        
        # Atur posisi cursor yang tepat
        if formatted_value:
            # Hitung posisi cursor baru berdasarkan jumlah titik yang ditambahkan
            dots_before = e.control.value[:current_pos].count('.')
            new_pos = min(current_pos + dots_before, len(formatted_value))
            e.control.selection = ft.TextSelection(start=new_pos, end=new_pos)
        
        e.control.update()
    
    txt_harga = ft.TextField(
        label="Harga Satuan",
        width=150,
        color=ft.Colors.BLACK,
        label_style=ft.TextStyle(color=ft.Colors.BLACK),
        on_change=on_harga_change,
        hint_text="0",
        prefix_text="Rp ",
        prefix_style=ft.TextStyle(color=ft.Colors.BLACK, weight=ft.FontWeight.BOLD)
    )
    txt_tanggal = ft.TextField(
    value=datetime.now().strftime('%d-%m-%Y'),
    label="Tanggal Transaksi",
    width=180,
    color=ft.Colors.BLACK,
    label_style=ft.TextStyle(color=ft.Colors.BLACK)
            )

    def date_picked(e):
        txt_tanggal.value = e.control.value.strftime('%Y-%m-%d')
        page.update()

    def open_datepicker(e):
        datepicker.open = True
        page.update()

    # 1. Buat objek DatePicker
    datepicker = ft.DatePicker(
        on_change=date_picked,
        first_date=datetime(2000, 1, 1),
        last_date=datetime(3000, 12, 31),
        help_text="Pilih tanggal transaksi"
    )

    # 2. Tambahkan DatePicker ke 'overlay' halaman agar bisa muncul di atas elemen lain
    page.overlay.append(datepicker) # <-- INI KUNCINYA

    # 3. Buat Tombol Ikon Kalender
    # Saat diklik, tombol ini akan menjalankan fungsi 'open_datepicker'
    icon_kalender = ft.IconButton(
        icon=ft.Icons.CALENDAR_MONTH,
        tooltip="Pilih Tanggal",
        on_click=open_datepicker # <-- INI KUNCINYA
    )


    tabel_stok = ft.DataTable(
        columns=[
            ft.DataColumn(ft.Text("📦 Nama Barang", weight=ft.FontWeight.BOLD, color=ft.Colors.BLUE_800)),
            ft.DataColumn(ft.Text("📊 Jumlah Stok", weight=ft.FontWeight.BOLD, color=ft.Colors.BLUE_800), numeric=True),
        ],
        rows=[],
        border=ft.border.all(2, ft.Colors.BLUE_300),
        border_radius=10,
        vertical_lines=ft.border.BorderSide(1, ft.Colors.BLUE_200),
        horizontal_lines=ft.border.BorderSide(1, ft.Colors.BLUE_200),
        heading_row_color=ft.Colors.BLUE_100,
        heading_row_height=50,
        data_row_min_height=45,
    )

    # --- Functions to Group Transactions by Month/Year ---
    
    def get_transaction_periods():
        """Mendapatkan daftar periode (bulan-tahun) dari transaksi yang ada"""
        periods = set()
        for trx in inventaris.riwayat_transaksi:
            period = trx['tanggal'].strftime('%m-%Y')
            periods.add(period)
        return sorted(periods, reverse=True)
    
    def get_period_name(period_str):
        """Mengkonversi periode '01-2025' menjadi 'Januari 2025'"""
        month, year = period_str.split('-')
        month_names = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                      'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
        return f"{month_names[int(month)-1]} {year}"
    
    def create_monthly_table(transactions, table_type="masuk"):
        """Membuat tabel untuk transaksi dalam periode tertentu"""
        colors = {
            "masuk": {"border": ft.Colors.GREEN_300, "bg": ft.Colors.GREEN_50, "header": ft.Colors.GREEN_100, "text": ft.Colors.GREEN_800},
            "keluar": {"border": ft.Colors.RED_300, "bg": ft.Colors.RED_50, "header": ft.Colors.RED_100, "text": ft.Colors.RED_800}
        }[table_type]
        
        label_harga = "💰 Harga Beli" if table_type == "masuk" else "💰 Harga Jual"
        icon_barang = "📦" if table_type == "masuk" else "📤"
        
        # Hitung total keseluruhan
        total_keseluruhan = sum(trx['jumlah'] * trx['harga'] for trx in transactions)
        
        def hapus_transaksi_handler(e, trx_to_delete):
            """Handler untuk menghapus transaksi"""
            def konfirmasi_hapus(e):
                # Cari index transaksi dalam riwayat lengkap
                try:
                    index_transaksi = None
                    for i, trx in enumerate(inventaris.riwayat_transaksi):
                        if (trx['tanggal'] == trx_to_delete['tanggal'] and 
                            trx['nama'] == trx_to_delete['nama'] and 
                            trx['jumlah'] == trx_to_delete['jumlah'] and 
                            trx['harga'] == trx_to_delete['harga'] and 
                            trx['jenis'] == trx_to_delete['jenis']):
                            index_transaksi = i
                            break
                    
                    if index_transaksi is not None:
                        success, message = inventaris.hapus_transaksi(index_transaksi)
                        if success:
                            show_snackbar(f"✅ {message}")
                            update_all_tables()
                        else:
                            show_snackbar(f"❌ {message}", is_error=True)
                    else:
                        show_snackbar("❌ Transaksi tidak ditemukan!", is_error=True)
                except Exception as ex:
                    show_snackbar(f"❌ Error: {ex}", is_error=True)
                
                dialog.open = False
                page.update()
            
            def batal_hapus(e):
                dialog.open = False
                page.update()
            
            dialog = ft.AlertDialog(
                modal=True,
                title=ft.Text("⚠️ Konfirmasi Hapus", weight=ft.FontWeight.BOLD),
                content=ft.Text(
                    f"Yakin ingin menghapus transaksi ini?\n\n"
                    f"📅 Tanggal: {trx_to_delete['tanggal'].strftime('%d-%m-%Y')}\n"
                    f"📦 Barang: {trx_to_delete['nama']}\n"
                    f"🔢 Jumlah: {trx_to_delete['jumlah']}\n"
                    f"💰 Harga: Rp {trx_to_delete['harga']:,}\n"
                    f"📋 Jenis: {trx_to_delete['jenis']}\n\n"
                    f"⚠️ Stok akan disesuaikan otomatis.",
                    size=14
                ),
                actions=[
                    ft.TextButton("❌ Batal", on_click=batal_hapus),
                    ft.ElevatedButton("🗑️ Hapus", on_click=konfirmasi_hapus, 
                                     bgcolor=ft.Colors.RED_600, color=ft.Colors.WHITE),
                ],
                actions_alignment=ft.MainAxisAlignment.END,
            )
            page.overlay.append(dialog)
            dialog.open = True
            page.update()
        
        # Membuat rows dengan kolom total dan tombol hapus untuk setiap item
        data_rows = []
        for trx in reversed(transactions):
            total_item = trx['jumlah'] * trx['harga']
            data_rows.append(ft.DataRow(cells=[
                ft.DataCell(ft.Text(trx['tanggal'].strftime('%d-%m-%Y'), color=ft.Colors.BLACK)),
                ft.DataCell(ft.Text(trx['nama'], color=ft.Colors.BLACK)),
                ft.DataCell(ft.Text(str(trx['jumlah']), color=ft.Colors.BLACK)),
                ft.DataCell(ft.Text(f"Rp {trx['harga']:,}".replace(',', '.'), color=ft.Colors.BLACK)),
                ft.DataCell(ft.Text(f"Rp {total_item:,}".replace(',', '.'), color=ft.Colors.BLACK, weight=ft.FontWeight.BOLD)),
                ft.DataCell(
                    ft.IconButton(
                        icon=ft.Icons.DELETE,
                        icon_color=ft.Colors.RED_600,
                        tooltip="Hapus transaksi ini",
                        on_click=lambda e, trx_data=trx: hapus_transaksi_handler(e, trx_data)
                    )
                ),
            ]))
        
        table = ft.DataTable(
            columns=[
                ft.DataColumn(ft.Text("📅 Tanggal", weight=ft.FontWeight.BOLD, color=colors["text"])),
                ft.DataColumn(ft.Text(f"{icon_barang} Nama Barang", weight=ft.FontWeight.BOLD, color=colors["text"])),
                ft.DataColumn(ft.Text("🔢 Jumlah", weight=ft.FontWeight.BOLD, color=colors["text"]), numeric=True),
                ft.DataColumn(ft.Text(label_harga, weight=ft.FontWeight.BOLD, color=colors["text"]), numeric=True),
                ft.DataColumn(ft.Text("💵 Total", weight=ft.FontWeight.BOLD, color=colors["text"]), numeric=True),
                ft.DataColumn(ft.Text("🗑️ Hapus", weight=ft.FontWeight.BOLD, color=colors["text"])),
            ],
            rows=data_rows,
            border=ft.border.all(2, colors["border"]),
            border_radius=10,
            vertical_lines=ft.border.BorderSide(1, colors["border"]),
            horizontal_lines=ft.border.BorderSide(1, colors["border"]),
            heading_row_color=colors["header"],
            heading_row_height=50,
            data_row_min_height=45,
        )
        
        # Container untuk tabel dan total
        return ft.Column([
            table,
            ft.Container(
                content=ft.Row([
                    ft.Text("TOTAL KESELURUHAN:", size=16, weight=ft.FontWeight.BOLD, color=colors["text"]),
                    ft.Text(f"Rp {total_keseluruhan:,}".replace(',', '.'), size=18, weight=ft.FontWeight.BOLD, color=colors["text"])
                ], alignment=ft.MainAxisAlignment.END),
                padding=ft.padding.all(10),
                bgcolor=colors["header"],
                border_radius=ft.border_radius.only(bottom_left=10, bottom_right=10),
                border=ft.border.only(left=ft.BorderSide(2, colors["border"]), 
                                    right=ft.BorderSide(2, colors["border"]), 
                                    bottom=ft.BorderSide(2, colors["border"]))
            )
        ])

    def create_riwayat_content(transaction_type):
        """Membuat konten riwayat untuk masuk atau keluar"""
        periods = get_transaction_periods()
        if not periods:
            return ft.Container(
                content=ft.Text("Belum ada transaksi.", size=16, color=ft.Colors.GREY_600),
                padding=20
            )
        
        colors = {
            "masuk": {"title": ft.Colors.GREEN_700, "border": ft.Colors.GREEN_300, "bg": ft.Colors.GREEN_50},
            "keluar": {"title": ft.Colors.RED_700, "border": ft.Colors.RED_300, "bg": ft.Colors.RED_50}
        }[transaction_type]
        
        title = f"📦 Riwayat Barang Masuk" if transaction_type == "masuk" else f"📤 Riwayat Barang Keluar"
        jenis = "MASUK" if transaction_type == "masuk" else "KELUAR"
        
        sub_tabs = []
        for period in periods:
            period_name = get_period_name(period)
            
            period_transactions = [
                trx for trx in inventaris.riwayat_transaksi 
                if trx['jenis'] == jenis and trx['tanggal'].strftime('%m-%Y') == period
            ]
            
            if period_transactions:
                monthly_table = create_monthly_table(period_transactions, transaction_type)
                
                # Buat tab dengan warna khusus untuk September 2025
                if "September 2025" in period_name:
                    tab = ft.Tab(
                        content=ft.ListView(controls=[monthly_table], height=450),
                        tab_content=ft.Text(period_name, color=ft.Colors.RED, weight=ft.FontWeight.BOLD)
                    )
                else:
                    tab = ft.Tab(
                        text=period_name,
                        content=ft.ListView(controls=[monthly_table], height=450)
                    )
                
                sub_tabs.append(tab)
        
        if not sub_tabs:
            return ft.Container(content=ft.Text("Belum ada transaksi.", size=16, color=ft.Colors.GREY_600), padding=20)
        
        return ft.Container(
            content=ft.Column([
                ft.Text(title, size=24, weight=ft.FontWeight.BOLD, color=colors["title"]),
                ft.Container(
                    content=ft.Text(
                        "Pilih periode bulan-tahun untuk melihat riwayat transaksi.",
                        size=14, color=ft.Colors.GREY_700, text_align=ft.TextAlign.JUSTIFY
                    ),
                    padding=ft.padding.only(bottom=20)
                ),
                ft.Tabs(selected_index=0, animation_duration=200, tabs=sub_tabs, height=500),
            ]),
            padding=20
        )


    def update_all_tables():
        # Update tabel stok
        tabel_stok.rows.clear()
        for nama, jumlah in sorted(inventaris.stok_barang.items()):
            tabel_stok.rows.append(ft.DataRow(cells=[
                ft.DataCell(ft.Text(nama, color=ft.Colors.BLACK)),
                ft.DataCell(ft.Text(str(jumlah), color=ft.Colors.BLACK))
            ]))
        
        refresh_dynamic_tabs()
        page.update()
        
    def refresh_dynamic_tabs():
        """Refresh konten tab dinamis berdasarkan data terbaru"""
        main_tabs = None
        for child in page.controls:
            if hasattr(child, 'content') and hasattr(child.content, 'tabs'):
                main_tabs = child.content
                break
        
        if main_tabs and len(main_tabs.tabs) > 3:
            main_tabs.tabs[2].content = create_riwayat_content("masuk")
            main_tabs.tabs[3].content = create_riwayat_content("keluar")
    
    def clear_input_fields():
        txt_nama_barang.value = ""
        txt_jumlah.value = ""
        txt_harga.value = ""
        txt_nama_barang.focus()
        page.update()

    def show_snackbar(message, is_error=False):
        page.snack_bar = ft.SnackBar(
            content=ft.Text(message),
            bgcolor=ft.Colors.RED_500 if is_error else ft.Colors.GREEN_500,
        )
        page.snack_bar.open = True
        page.update()

    # --- Event Handlers ---

    def validate_input():
        """Validasi input form dan return (nama, jumlah, harga, tanggal) atau None jika error"""
        nama = txt_nama_barang.value.strip()
        jumlah_str = txt_jumlah.value.strip()
        harga_str = extract_number_from_rupiah(txt_harga.value.strip())
        
        if not nama:
            show_snackbar("Nama barang tidak boleh kosong!", is_error=True)
            return None
        if not jumlah_str:
            show_snackbar("Jumlah tidak boleh kosong!", is_error=True)
            return None
        if not harga_str:
            show_snackbar("Harga tidak boleh kosong!", is_error=True)
            return None
            
        try:
            jumlah = int(jumlah_str)
            harga = int(harga_str)
            tanggal = datetime.strptime(txt_tanggal.value, '%d-%m-%Y').date()
            return nama, jumlah, harga, tanggal
        except ValueError as ve:
            if "invalid literal" in str(ve):
                show_snackbar("Jumlah dan Harga harus berupa angka yang valid!", is_error=True)
            else:
                show_snackbar("Format tanggal tidak valid! Gunakan format dd-mm-yyyy", is_error=True)
            return None

    def catat_transaksi_handler(jenis):
        """Handler umum untuk catat transaksi"""
        try:
            data = validate_input()
            if not data:
                return
                
            nama, jumlah, harga, tanggal = data
            sukses, pesan = inventaris.catat_transaksi(nama, jumlah, harga, tanggal, jenis)
            
            if sukses:
                show_snackbar(pesan)
                update_all_tables()
                clear_input_fields()
            else:
                show_snackbar(pesan, is_error=True)
        except Exception as e:
            show_snackbar(f"Terjadi kesalahan: {str(e)}", is_error=True)

    def catat_masuk_click(e):
        catat_transaksi_handler('MASUK')

    def catat_keluar_click(e):
        catat_transaksi_handler('KELUAR')
    
    # --- Layout Definition ---
    
    tab_transaksi_content = ft.Container(
        content=ft.Column(
            [
                ft.Text("💼 Formulir Transaksi", size=24, weight=ft.FontWeight.BOLD, color=ft.Colors.BLUE_800),
                ft.Container(
                    content=ft.Text(
                        "Gunakan formulir ini untuk mencatat barang masuk dan keluar. "
                        "Pastikan semua data diisi dengan benar sebelum menyimpan transaksi.",
                        size=14,
                        color=ft.Colors.GREY_700,
                        text_align=ft.TextAlign.JUSTIFY
                    ),
                    padding=ft.padding.only(bottom=20)
                ),
                txt_nama_barang,
                ft.Row([txt_jumlah, txt_harga]),
                ft.Row([
                    ft.Text("Tanggal Transaksi:", size=16, color=ft.Colors.BLACK),
                    txt_tanggal,
                    ft.IconButton(icon=ft.Icons.CALENDAR_MONTH, on_click=open_datepicker)
                ]),
                ft.Container(height=20),
                ft.Row(
                    [
                        ft.ElevatedButton("Catat Barang Masuk", on_click=catat_masuk_click, icon=ft.Icons.ADD, color=ft.Colors.BLACK, bgcolor=ft.Colors.GREEN),
                        ft.ElevatedButton("Catat Barang Keluar", on_click=catat_keluar_click, icon=ft.Icons.REMOVE, color=ft.Colors.BLACK, bgcolor=ft.Colors.ORANGE),
                    ],
                    alignment=ft.MainAxisAlignment.CENTER,
                ),
            ]
        ),
        padding=20,
    )

    tab_stok_content = ft.Container(
        content=ft.Column(
            [
                ft.Text("📊 Laporan Stok Barang", size=24, weight=ft.FontWeight.BOLD, color=ft.Colors.BLUE_700),
                ft.Container(
                    content=ft.Text(
                        "Berikut adalah ringkasan stok barang yang tersedia saat ini. "
                        "Data akan otomatis terupdate setiap kali ada transaksi masuk atau keluar.",
                        size=14,
                        color=ft.Colors.GREY_700,
                        text_align=ft.TextAlign.JUSTIFY
                    ),
                    padding=ft.padding.only(bottom=20)
                ),
                ft.ListView(controls=[tabel_stok], height=400),
            ]
        ),
        padding=20,
    )

    tab_riwayat_masuk_content = create_riwayat_content("masuk")
    tab_riwayat_keluar_content = create_riwayat_content("keluar")
    
    page.appbar = ft.AppBar(
        leading=ft.Icon(ft.Icons.INVENTORY_2_OUTLINED, color=ft.Colors.BLACK),
        leading_width=40,
        title=ft.Text("Manajemen Inventaris", weight=ft.FontWeight.BOLD, color=ft.Colors.BLACK),
        center_title=False,
        bgcolor=ft.Colors.GREEN_700,
        actions=[
            ft.Image(
                src="assets/logo_inventaris.png",
                width=40,
                height=40,
                fit=ft.ImageFit.CONTAIN,
            ),
            ft.Container(width=10)
        ],
    )

    tabs = ft.Tabs(
    selected_index=0,
    animation_duration=300,
    tabs=[
        ft.Tab(
            tab_content=ft.Icon(ft.Icons.EDIT_NOTE, color=ft.Colors.BLUE_900, tooltip="Input transaksi barang masuk dan keluar"),
            text="Transaksi",
            content=tab_transaksi_content
        ),
        ft.Tab(
            tab_content=ft.Icon(ft.Icons.BAR_CHART, color=ft.Colors.BLUE_900, tooltip="Lihat laporan stok barang yang tersedia"),
            text="Laporan Stok",
            content=tab_stok_content
        ),
        ft.Tab(
            tab_content=ft.Icon(ft.Icons.ARROW_DOWNWARD, color=ft.Colors.GREEN_700, tooltip="Riwayat semua barang yang masuk"),
            text="Riwayat Masuk",
            content=tab_riwayat_masuk_content
        ),
        ft.Tab(
            tab_content=ft.Icon(ft.Icons.ARROW_UPWARD, color=ft.Colors.RED_700, tooltip="Riwayat semua barang yang keluar"),
            text="Riwayat Keluar",
            content=tab_riwayat_keluar_content
        ),
    ],
    expand=1,
)
    page.add(
        ft.Container(
            content=tabs,
            expand=True,
            padding=0, # Hilangkan padding default agar hijau muda mengisi penuh
            margin=0,
            bgcolor=ft.Colors.LIGHT_GREEN_50, 
        )
    )
    
    update_all_tables()

    def handle_window_event(e):
        if e.data == "close":
            print("Jendela ditutup, menyimpan data...")
            inventaris.simpan_data() # Panggil fungsi simpan
            page.window_destroy()

    # Menghubungkan fungsi di atas ke jendela aplikasi
    page.on_window_event = handle_window_event

if __name__ == "__main__":
    ft.app(target=main, assets_dir="assets") 