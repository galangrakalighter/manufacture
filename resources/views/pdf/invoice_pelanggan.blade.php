<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">

    <style>

        body{

            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #000;

        }

        .header-table{

            width: 100%;
            margin-bottom: 20px;

        }

        .title{

            font-size: 22px;
            font-weight: bold;
            text-transform: uppercase;

        }

        .company-info{

            font-size: 11px;
            line-height: 1.4;

        }

        .invoice-details{

            text-align: right;

        }

        table{

            width: 100%;
            border-collapse: collapse;

        }

        .info td{

            padding: 3px 0;

        }

        .items{

            margin-top: 25px;

        }

        .items th{

            background: #e5e5e5;
            border: 1px solid #000;
            padding: 8px;

        }

        .items td{

            border: 1px solid #000;
            padding: 8px;

        }

        .center{

            text-align: center;

        }

        .right{

            text-align: right;

        }

        .signature{

            margin-top: 50px;

        }

        .signature td{

            width: 50%;
            text-align: center;

        }

        .line{

            margin-top: 60px;
            display: inline-block;
            width: 180px;
            border-top: 1px solid #000;

        }

    </style>

</head>

<body>

    <!-- Header Invoice -->
    <table class="header-table">
        <tr>
            <td width="50%">
                <div class="title">INVOICE</div>
                <div class="company-info" style="margin-top: 5px;">
                    <strong>Nama Perusahaan Anda</strong><br>
                    Alamat Perusahaan, Kota<br>
                    Telp: 0812-3456-7890 | Email: info@perusahaan.com
                </div>
            </td>
            <td width="50%" class="invoice-details">
                <table class="info">
                    <tr>
                        <td width="100" style="text-align: right;">No Invoice :</td>
                        <td style="font-weight: bold;">{{$spk->no_invoice}}</td>
                    </tr>
                    <tr>
                        <td style="text-align: right;">Tanggal :</td>
                        <td>{{ \Carbon\Carbon::now()->format('d F Y') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Informasi Customer -->
    <div style="margin-bottom: 20px; border: 1px solid #ccc; padding: 10px; border-radius: 5px;">
        <strong>Kepada Yth:</strong><br>
        <div style="font-size: 14px; font-weight: bold; margin-top: 3px;">{{ $spk->quotation->nama_customer }}</div>
    </div>

    <!-- Tabel Item Tagihan -->
    <table class="items">
        <thead>
            <tr>
                <th width="40">No</th>
                <th>Keterangan / Jenis Barang</th>
                <th width="70">Jumlah</th>
                <th width="110">Harga Satuan</th>
                <th width="120">Total Harga</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="center">1</td>
                <td>
                    {{ $spk->quotation->barang->barang ?? '-' }}
                </td>
                <td class="center">
                    {{ $spk->quotation->quantity ?? 0 }}
                </td>
                <td class="right">
                    Rp {{ number_format($spk->quotation->harga ?? $spk->quotation->price ?? 0, 0, ',', '.') }}
                </td>
                <td class="right">
                    Rp {{ number_format($spk->quotation->total_harga ?? (($spk->quotation->harga ?? $spk->quotation->price ?? 0) * ($spk->quotation->quantity ?? 0)), 0, ',', '.') }}
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="right" style="padding: 8px; border: 1px solid #000; font-weight: bold;">Grand Total :</td>
                <td class="right" style="padding: 8px; border: 1px solid #000; font-weight: bold;">
                    Rp {{ number_format($spk->quotation->total_harga ?? (($spk->quotation->harga ?? $spk->quotation->price ?? 0) * ($spk->quotation->quantity ?? 0)), 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- Catatan & Informasi Pembayaran -->
    <div style="margin-top: 20px;">
        <strong>Informasi Pembayaran / Catatan:</strong>
        <div style="margin-top: 5px; border: 1px solid #000; padding: 10px; min-height: 50px;">
            Silakan melakukan transfer pembayaran melalui rekening berikut:<br>
            - **Bank BCA** a.n Nama Perusahaan (No. Rek: 1234567890)<br>
            {{ $spk->catatan ?? $spk->note ?? '-' }}
        </div>
    </div>

    <!-- Tanda Tangan -->
    <table class="signature">
        <tr>
            <td>
                Hormat Kami,
                <br><br><br><br>
                <span class="line"></span>
                <br>Finance Department
            </td>
            <td>
                Penerima / Customer,
                <br><br><br><br>
                <span class="line"></span>
                <br>({{ $spk->quotation->nama_customer }})
            </td>
        </tr>
    </table>

</body>

</html>