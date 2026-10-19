<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">

    <style>
        @page {
            margin: 20px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .border {
            border: 1px solid #000;
        }

        .border td,
        .border th {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
        }

        .title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .subtitle {
            text-align: center;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .label {
            width: 140px;
            font-weight: bold;
            vertical-align: top;
        }

        .section {
            margin-top: 10px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .small {
            font-size: 11px;
        }

        .alamat {
            border-bottom: 1px dashed #000;
            padding-bottom: 4px;
        }
    </style>

</head>

<body>

    <div class="title">
        BUKTI PESANAN BARANG
    </div>

    <div class="subtitle">
        No. SPK : {{ $spk->spk_number ?? '-' }}
    </div>

    <table style="width:100%;">

        <tr>
            <td class="label">Nama Pemesan</td>
            <td>{{ $spk->quotation->nama_customer ?? '-' }}</td>
        </tr>

        <tr>
            <td></td>
            <td>
                <div style="width:450px;border-bottom:1px dashed #000;padding-bottom:3px;">
                    {{ $spk->quotation->alamat_customer ?? '-' }}
                </div>
            </td>
        </tr>

        <tr>
            <td class="label">Dikirim Kepada</td>
            <td>{{ $spk->quotation->penerima ?? $spk->quotation->nama_customer ?? '-' }}</td>
        </tr>

        <tr>
            <td></td>
            <td>{{ $spk->quotation->penerima ?? $spk->quotation->alamat ?? '-' }}</td>
        </tr>

    </table>

    <div style="margin-top:15px;"></div>

    <table style="width:100%;">

        <tr>

            <!-- KIRI -->
            <td width="50%" valign="top">

                <table style="width:100%;">

                    <tr>
                        <td class="label">Judul Cetak</td>
                        <td>: {{ $spk->quotation->judul_cetak ?? '-' }}</td>
                    </tr>

                    <tr>
                        <td class="label">Ukuran</td>
                        <td>: {{ $spk->quotation->ukuran ?? '-' }}</td>
                    </tr>

                    <tr>
                        <td class="label" valign="top">Jenis Kertas</td>
                        <td valign="top">
                            :
                            <ul style="margin:0;padding-left:18px;display:inline-block;">
                                @if(isset($spk->quotation->barang) && $spk->quotation->barang)
                                    <li>{{ $spk->quotation->barang->barang ?? '-' }}</li>
                                @endif
                            </ul>
                        </td>
                    </tr>

                    <tr>
                        <td class="label">Isi / Box</td>
                        <td>: {{ $spk->quotation->perbox ?? '-' }}</td>
                    </tr>

                    <tr>
                        <td class="label">Jumlah Box</td>
                        <td>: {{ number_format($spk->quotation->jumlah_box ?? 0) }}</td>
                    </tr>

                    <tr>
                        <td class="label">Harga / Box</td>
                        <td>: Rp {{ number_format($spk->quotation->harga_per_box ?? 0, 0, ',', '.') }}</td>
                    </tr>

                    <tr>
                        <td class="label">Sisa Bayar</td>
                        <td>: Rp {{ number_format($spk->quotation->total_amount ?? 0, 0, ',', '.') }}</td>
                    </tr>

                    <input type="hidden" id="total" value="{{ number_format($spk->quotation->total_amount ?? 0, 0, ',', '.') }}">

                    <tr>
                        <td class="label">Terbilang</td>
                        <td>: 
                            @php
                                function penyebut($nilai) {
                                    $nilai = abs($nilai);
                                    $huruf = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];
                                    $temp = "";
                                    if ($nilai < 12) {
                                        $temp = " " . $huruf[$nilai];
                                    } else if ($nilai < 20) {
                                        $temp = penyebut($nilai - 10) . " Belas";
                                    } else if ($nilai < 100) {
                                        $temp = penyebut($nilai / 10) . " Puluh" . penyebut($nilai % 10);
                                    } else if ($nilai < 200) {
                                        $temp = " Seratus" . penyebut($nilai - 100);
                                    } else if ($nilai < 1000) {
                                        $temp = penyebut($nilai / 100) . " Ratus" . penyebut($nilai % 100);
                                    } else if ($nilai < 2000) {
                                        $temp = " Seribu" . penyebut($nilai - 1000);
                                    } else if ($nilai < 1000000) {
                                        $temp = penyebut($nilai / 1000) . " Ribu" . penyebut($nilai % 1000);
                                    } else if ($nilai < 1000000000) {
                                        $temp = penyebut($nilai / 1000000) . " Juta" . penyebut($nilai % 1000000);
                                    } else if ($nilai < 1000000000000) {
                                        $temp = penyebut($nilai / 1000000000) . " Milyar" . penyebut($nilai % 1000000000);
                                    } else if ($nilai < 1000000000000000) {
                                        $temp = penyebut($nilai / 1000000000000) . " Triliun" . penyebut($nilai % 1000000000000);
                                    }
                                    return $temp;
                                }

                                function terbilang_php($nilai) {
                                    if($nilai < 0) {
                                        $hasil = "minus " . trim(penyebut($nilai));
                                    } else {
                                        $hasil = trim(penyebut($nilai));
                                    }
                                    return $hasil ? $hasil . " Rupiah" : "Nol Rupiah";
                                }

                                $totalAmount = $spk->quotation->total_amount ?? 0;
                            @endphp

                            {{ terbilang_php($totalAmount) }}
                        </td>
                    </tr>

                    <tr>
                        <td class="label">Keterangan</td>
                        <td>: {{ $spk->keterangan ?? '-' }}</td>
                    </tr>

                </table>

            </td>

            <!-- KANAN -->
            <td width="50%" valign="top">

                <table style="width:100%;">

                    <tr>
                        <td class="label">Tanggal Pesan</td>
                        <td>: {{ \Carbon\Carbon::parse($spk->created_at)->format('d-m-Y') }}</td>
                    </tr>

                    <tr>
                        <td class="label">No. Invoice</td>
                        <td>
                            : {{ $spk->no_invoice }}
                        </td>
                    </tr>

                    <tr>
                        <td class="label">Jumlah Ply</td>
                        <td>: {{ $spk->quotation->jumlah_ply ?? '-' }}</td>
                    </tr>

                    <tr>
                        <td class="label">Perporasi</td>
                        <td>: {{ $spk->quotation->perporasi ?? '-' }}</td>
                    </tr>

                    <tr>
                        <td class="label">Banyaknya</td>
                        <td>: {{ number_format($spk->quotation->quantity ?? 0) }} Box</td>
                    </tr>

                    <tr>
                        <td class="label">Total Order</td>
                        <td>
                            : <strong>Rp {{ number_format($spk->quotation->total_amount ?? 0, 0, ',', '.') }}</strong>
                        </td>
                    </tr>

                    <tr>
                        <td class="label">No. Film</td>
                        <td>: {{ $spk->quotation->film ?? '-' }}</td>
                    </tr>

                    <tr>
                        <td class="label">Salesman</td>
                        <td>: {{ $spk->quotation->sales->name ?? '-' }}</td>
                    </tr>

                    <tr>
                        <td class="label">Cabang</td>
                        <td>: {{ $spk->quotation->cabang ?? '-' }}</td>
                    </tr>

                    <tr>
                        <td class="label">Tipe Order</td>
                        <td>: {{ $spk->quotation->tipe_pemesanan ?? '-' }}</td>
                    </tr>

                </table>

            </td>

        </tr>

    </table>

    <div style="margin-top:15px;"></div>
</body>

</html>