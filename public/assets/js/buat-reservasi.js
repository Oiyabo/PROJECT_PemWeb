let dpPembayaranSelesai = false;

function openConfirm() {
    document.getElementById("confirmPopup").style.display = "flex";
}

function closeConfirm() {
    document.getElementById("confirmPopup").style.display = "none";
}

function submitReservasi() {
    if (!dpPembayaranSelesai) {
        alert('Anda harus melakukan pembayaran DP terlebih dahulu!');
        return;
    }
    document.getElementById("formReservasi").submit();
}

function openPaymentDP(totalDP, jenisKendaraan) {
    document.getElementById('totalDPAmount').textContent = 'Rp ' + formatNumber(totalDP);
    document.getElementById('paymentDPModal').style.display = 'flex';
}

function closePaymentDP() {
    document.getElementById('paymentDPModal').style.display = 'none';
}

function submitPaymentDP() {
    // Ambil nilai dari form
    const totalDP = document.getElementById('totalDPInput').value;
    
    // Logika pembayaran DP (untuk sekarang)
    // Dalam implementasi final, ini akan terhubung ke payment gateway
    
    // Simpan pembayaran DP ke session/database
    const formData = new FormData();
    formData.append('totalDP', totalDP);
    
    // Untuk sekarang, langsung mark sebagai berhasil
    // Di kemudian hari ini akan terhubung ke payment gateway
    dpPembayaranSelesai = true;
    
    closePaymentDP();
    
    // Tampilkan status pembayaran
    document.getElementById('dpPaymentStatus').style.display = 'block';
    
    // Enable tombol kirim reservasi
    document.getElementById('btnKirimReservasi').disabled = false;
    
    // Disable tombol bayar DP
    document.getElementById('btnBayarDP').disabled = true;
    document.getElementById('btnBayarDP').style.opacity = '0.5';
    document.getElementById('btnBayarDP').textContent = '✓ DP Sudah Dibayar';
}

function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}