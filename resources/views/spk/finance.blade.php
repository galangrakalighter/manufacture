<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">

    <style>

        body{

            font-family: DejaVu Sans, sans-serif;
            font-size:12px;
            color:#000;

        }

        .title{

            text-align:center;
            font-size:20px;
            font-weight:bold;

        }

        .subtitle{

            text-align:center;
            margin-bottom:25px;

        }

        table{

            width:100%;
            border-collapse:collapse;

        }

        .info td{

            padding:3px 0;

        }

        .items{

            margin-top:20px;

        }

        .items th{

            background:#e5e5e5;
            border:1px solid #000;
            padding:8px;

        }

        .items td{

            border:1px solid #000;
            padding:8px;

        }

        .center{

            text-align:center;

        }

        .right{

            text-align:right;

        }

        .signature{

            margin-top:60px;

        }

        .signature td{

            width:50%;
            text-align:center;

        }

        .line{

            margin-top:60px;
            display:inline-block;
            width:180px;
            border-top:1px solid #000;

        }

    </style>

</head>

<body>

    <div class="title">
        SURAT PERINTAH KERJA
    </div>

    <div class="subtitle">
        FINANCE
    </div>

    <table class="info">

        <tr>
            <td width="150">Nomor SPK</td>
            <td>: {{ $spk->spk_number }}</td>
        </tr>

        <tr>
            <td>Tanggal</td>
            <td>: {{ \Carbon\Carbon::parse($spk->created_at)->format('d F Y') }}</td>
        </tr>

        <tr>
            <td>Customer</td>
            <td>: {{ $spk->quotation->nama_customer }}</td>
        </tr>

    </table>

    <table class="items">

        <thead>

            <tr>

                <th width="40">No</th>

                <th>Jenis Kertas / Barang</th>

                <th width="70">Jumlah</th>

                <th width="110">Harga Perbox</th>

                <th width="110">Total Harga</th>

            </tr>

        </thead>

        <tbody>

            <tr>

                <td class="center">
                    1
                </td>

                <td>
                    {{ $spk->quotation->barang->barang }}
                </td>

                <td class="center">
                    {{ $spk->quotation->quantity }}
                </td>

                <td class="right">
                    Rp {{ number_format($spk->quotation->harga ?? $spk->quotation->price ?? 0, 0, ',', '.') }}
                </td>

                <td class="right">
                    Rp {{ number_format($spk->quotation->total_harga ?? (($spk->quotation->harga ?? $spk->quotation->price ?? 0) * $spk->quotation->quantity), 0, ',', '.') }}
                </td>

            </tr>

        </tbody>

        <tfoot>
            <tr>
                <td colspan="4" class="right" style="padding: 8px; border: 1px solid #000; font-weight: bold;">Grand Total :</td>
                <td class="right" style="padding: 8px; border: 1px solid #000; font-weight: bold;">
                    Rp {{ number_format($spk->quotation->total_harga ?? (($spk->quotation->harga ?? $spk->quotation->price ?? 0) * $spk->quotation->quantity), 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>

    </table>

    <div style="margin-top:25px">

        <strong>Catatan</strong>

        <div style="
            margin-top:8px;
            border:1px solid #000;
            height:80px;
            padding:10px;
        ">

            {{ $spk->catatan ?? $spk->note }}

        </div>

    </div>

    <table class="signature">

        <tr>

            <td>

                Dibuat Oleh

                <br><br><br><br>

                <span class="line"></span>

            </td>

            <td>

                Diterima Finance

                <br><br><br><br>

                <span class="line"></span>

            </td>

        </tr>

    </table>

</body>

</html>