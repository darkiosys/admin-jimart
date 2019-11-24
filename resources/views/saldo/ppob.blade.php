@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Transaksi PPOB</div>
                    <div class="card-body">
						<div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nama Member</th>
                                        <th>Transaksi</th>
                                        <th>Tujuan</th>
                                        <th>Total Tagihan</th>
                                        <th>Ending Saldo</th>
                                        <th>Produk</th>
                                        <th>Tanggal Transaksi</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($ppob as $item)
                                    <?php
                                        if($item->status == "Gagal") {
                                    ?>
                                    <tr style="background-color: red">
                                    <?php
                                        } else {
                                            echo "<tr>";
                                        }
                                    ?>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{$item->members_id}}</td>
                                        <td>{{$item->trx_name}}</td>
                                        <td>{{$item->no_hp}}</td>
                                        <td>{{$item->total_tagihan}}</td>
                                        <td>{{$item->ending_saldo}}</td>
                                        <td>{{$item->product_code}}</td>
                                        <td>{{$item->trx_date}}</td>
                                        <td>{{$item->status}}</td>
                                        <td>
                                            <a class="btn btn-info btn-sm"  href="/ppob/delete?id={{$item->id}}" onclick="return confirm(&quot;Hapus Transaksi?&quot;)">delete</a> <br /><br />
                                            @if($item->status == "Berhasil")
                                            <a class="btn btn-danger btn-sm"  href="/ppob/return?id={{$item->id}}" onclick="return confirm(&quot;Transaksi Gagal, Kembalikan Saldo?&quot;)">return</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            <div class="pagination-wrapper"> {!! $ppob->appends(['search' => Request::get('search')])->render() !!} </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
