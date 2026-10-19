<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">

    <style>
        @page{
            margin:25px;
        }

        body{
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #000;
        }

        table{
            width: 100%;
            border-collapse: collapse;
        }

        .border{
            border: 1px solid #000;
        }

        .border th,
        .border td{
            border: 1px solid #000;
            padding: 7px;
        }

        .title{
            font-size: 22px;
            font-weight: bold;
            text-align: center;
        }

        .subtitle{
            text-align: center;
            margin-bottom: 20px;
        }

        .label{
            width: 140px;
            font-weight: bold;
            vertical-align: top;
        }

        .text-right{
            text-align: right;
        }

        .text-center{
            text-align: center;
        }

        .bold{
            font-weight: bold;
        }

        .footer{
            margin-top: 40px;
        }
    </style>
</head>

<body>

    <div class="title">
        SURAT PENAWARAN
    </div>

    <div class="subtitle">
        No. Penawaran : {{ $po->quotation_number }}
    </div>

    <table>
        <tr>
            <td class="label">Tanggal</td>
            <td>{{ \Carbon\Carbon::parse($po->tanggal_pesan)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td class="label">Kepada Yth.</td>
            <td>{{ $po->nama_customer }}</td>
        </tr>
        <tr>
            <td></td>
            <td>{{ $po->alamat_customer }}</td>
        </tr>
    </table>

    <br>

    Dengan hormat,

    <br><br>

    Terima kasih atas kepercayaan Bapak/Ibu kepada perusahaan kami.
    Bersama surat ini kami sampaikan penawaran harga sebagai berikut.

    <br><br>

    <table class="border">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Nama Barang / Judul Cetak</th>
                <th width="15%">Qty</th>
                <th width="20%">Harga 1 Box</th>
                <th width="20%">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td class="text-center">
                    {{ $po->barang->barang ?? 'Barang Cetak' }} 
                </td>
                <td class="text-center">
                    {{ number_format($po->quantity) }}
                </td>
                <td class="text-right">
                    @php
                        // Menghitung harga satuan berdasarkan total_amount dibagi quantity
                        $hargaSatuan = $po->quantity > 0 ? $po->total_amount / $po->quantity : 0;
                    @endphp
                    Rp {{ number_format($hargaSatuan, 0, ',', '.') }}
                </td>
                <td class="text-right">
                    Rp {{ number_format($po->total_amount, 0, ',', '.') }}
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right bold">
                    TOTAL PENAWARAN
                </td>
                <td class="text-right bold">
                    Rp {{ number_format($po->total_amount, 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>

    <br>

    <table>
        <tr>
            <td class="label">
                Terbilang
            </td>
            <td>
                {{ $po->total_amount }} Rupiah
            </td>
        </tr>
        <tr>
            <td class="label">
                Masa Berlaku
            </td>
            <td>
                14 Hari sejak tanggal penawaran
            </td>
        </tr>
        <tr>
            <td class="label">
                Pembayaran
            </td>
            <td>
                {{ ucfirst($po->tipe_pemesanan) }}
            </td>
        </tr>
    </table>

    <div class="footer">
        Demikian surat penawaran ini kami sampaikan. Besar harapan kami dapat bekerja sama dengan Bapak/Ibu. Atas perhatian dan kepercayaannya kami ucapkan terima kasih.

        <br><br><br><br>

        <table>
            <tr>
                <td class="text-center">
                    Hormat Kami
                    <br><br><br><br><br>
                    <b>{{ $po->sales->name ?? 'Salesman' }}</b>
                </td>
                <td class="text-center">
                    Menyetujui
                    <br><br><br><br><br>
                    <b>{{ $po->nama_customer }}</b>
                </td>
            </tr>
        </table>
    </div>

</body>

</html>