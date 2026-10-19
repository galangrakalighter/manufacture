<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">

    <style>

        body{
            font-family: DejaVu Sans,sans-serif;
            font-size:12px;
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
            padding:4px 0;
        }

        .items{
            margin-top:20px;
        }

        .items th,
        .items td{
            border:1px solid #000;
            padding:8px;
        }

        .items th{
            background:#e5e5e5;
        }

        .center{
            text-align:center;
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
    PRODUCTION
</div>

<table class="info">

<tr>
    <td width="160">Nomor SPK</td>
    <td>: {{ $spk->spk_number }}</td>
</tr>

<tr>
    <td>Tanggal</td>
    <td>: {{ \Carbon\Carbon::parse($spk->created_at)->format('d F Y') }}</td>
</tr>

<tr>
    <td>No Quotation</td>
    <td>: {{ $spk->warehouse->quotation->quotation_number }}</td>
</tr>

<tr>
    <td>Customer</td>
    <td>: {{ $spk->warehouse->quotation->nama_customer }}</td>
</tr>
<tr>
    <td>No Film</td>
    <td>: {{ $spk->warehouse->quotation->film }}</td>
</tr>

<tr>
    <td>PIC Production</td>
    <td>: {{ $spk->pic->name }}</td>
</tr>

</table>

<table class="items">

<thead>

<tr>

<th width="40">No</th>

<th>Nama Barang</th>

<th width="80">Qty</th>

<th width="120">Status Produksi</th>

</tr>

</thead>

<tbody>

<tr>

<td class="center">
1
</td>

<td class="center">
{{ $spk->warehouse->quotation->barang->barang }}
</td>

<td class="center">
{{ $spk->warehouse->quotation->quantity }}
</td>

<td class="center">

@if($spk->status==0)
Belum Diproduksi
@elseif($spk->status==1)
Sedang Diproduksi
@elseif($spk->status==2)
Selesai
@endif

</td>

</tr>

</tbody>

</table>

<div style="margin-top:25px">

<strong>Catatan Produksi</strong>

<div style="
border:1px solid #000;
padding:10px;
height:80px;
margin-top:8px;
">

{{ $spk->catatan }}

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

PIC Production

<br><br><br><br>

<span class="line"></span>

</td>

</tr>

</table>

</body>
</html>