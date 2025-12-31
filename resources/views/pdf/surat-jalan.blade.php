<!DOCTYPE html>
<html>
<head>
    <title>Surat Jalan - {{ $distribusi->nomor_surat_jalan }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table, th, td { border: 1px solid black; padding: 8px; }
        .ttd-box { margin-top: 50px; width: 100%; }
        .ttd { width: 30%; float: left; text-align: center; }
        .ttd-right { float: right; }
    </style>
</head>
<body>
    <div class="header">
        <h1>SURAT JALAN PENGIRIMAN MAKANAN</h1>
        <p>SIGIZI - Sistem Gizi Terintegrasi</p>
        <hr>
    </div>

    <p><strong>No. Surat Jalan:</strong> {{ $distribusi->nomor_surat_jalan }}</p>
    <p><strong>Tanggal Kirim:</strong> {{ \Carbon\Carbon::parse($distribusi->tanggal_kirim)->format('d F Y') }}</p>
    <p><strong>Tujuan:</strong> {{ $distribusi->sekolah->nama_sekolah }}</p>

    <h3>Rincian Pengiriman</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Menu</th>
                <th>Jumlah Porsi</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>{{ $distribusi->menu->nama_menu }}</td>
                <td>{{ $distribusi->jumlah_porsi }} Porsi</td>
                <td>{{ $distribusi->catatan ?? '-' }}</td>
            </tr>
        </tbody>
    </table>

    <div class="ttd-box">
        <div class="ttd">
            <p>Pengirim (Dapur)</p>
            <br><br><br>
            <p>(.........................)</p>
        </div>
        <div class="ttd ttd-right">
            <p>Penerima (Sekolah)</p>
            <br><br><br>
            <p>(.........................)</p>
        </div>
    </div>
</body>
</html>