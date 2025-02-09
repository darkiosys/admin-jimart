@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Transaksi</div>
                    <div class="card-body">
						@if(Auth::user()->role == 0)
                        <a href="{{ url('/saldo/create') }}" class="btn btn-success btn-sm" title="Add New Saldo">
							<i class="fa fa-plus" aria-hidden="true"></i> Transaksi
                        </a>
						@endif
                        <form method="GET" action="{{ url('/saldo') }}" accept-charset="UTF-8" class="form-inline my-2 my-lg-0 float-right" role="search">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" placeholder="Search..." value="{{ request('search') }}">
                                <span class="input-group-append">
                                    <button class="btn btn-secondary" type="submit">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </span>
                            </div>
                        </form>

                        <br/>
                        <br/>
						@if(Auth::user()->role == 0)
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th><th>No Rek</th><th>Transaksi</th><th>Nominal Transfer</th>
										@if(Auth::user()->role == 1)
										<th>Actions</th>
										@else
										<th>Status</th>
										@endif
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($transaksi as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>$item->id</td>
                                        <td>0</td>
                                        <td>0</td>
										@if(Auth::user()->role == 1)
                                        <td>
                                            <a href="{{ url('/saldo/' . $item->id) }}" title="View Saldo"><button class="btn btn-info btn-sm"><i class="fa fa-eye" aria-hidden="true"></i> View</button></a>
                                            <a href="{{ url('/saldo/' . $item->id . '/edit') }}" title="Edit Saldo"><button class="btn btn-primary btn-sm"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit</button></a>

                                            <form method="POST" action="{{ url('/saldo' . '/' . $item->id) }}" accept-charset="UTF-8" style="display:inline">
                                                {{ method_field('DELETE') }}
                                                {{ csrf_field() }}
                                                <button type="submit" class="btn btn-danger btn-sm" title="Delete Saldo" onclick="return confirm(&quot;Confirm delete?&quot;)"><i class="fa fa-trash-o" aria-hidden="true"></i> Delete</button>
                                            </form>
                                        </td>
										@else
										<td>
                                            @if($item->status == 0)
												Menunggu verifikasi
											@elseif($item->status == 1)
												Sudah Di verifikasi
											@endif
                                        </td>
										@endif
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
						@else
						<div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Kode Transaksi</th>
                                        <th>Nominal Transfer</th>
                                        <th>Transfer Ke</th>
                                        <th>Bukti Transfer</th>
										@if(Auth::user()->role == 1)
										<th>Actions</th>
										@else
										<th>Status</th>
										@endif
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($transaksi as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{$item->id}}</td>
										<td>{{$item->total_transfer}}</td>
										<td>{{$item->no_rek}}</td>
                                        @if($item->bukti_transfer)
                                            <td><a href="http://jimart.store/admin/public/{{$item->bukti_transfer}}" target="_blank">Cek Bukti</a></td>
                                        @else
                                            <td>Belum Terlampir</td>
                                        @endif
										@if(Auth::user()->role == 1)
                                        <td>
											@if($item->status == 0)
												<form method="POST" action="{{ url('/transaksi/verifikasi' . '/' . $item->id) }}" accept-charset="UTF-8" style="display:inline">    
													{{ csrf_field() }}
													<button type="submit" class="btn btn-primary btn-sm" title="Verifikasi" onclick="return confirm(&quot;Verifikasi transaksi?&quot;)"><i class="fa fa-trash-o" aria-hidden="true"></i> Verifikasi</button>
												</form>
											@elseif($item->status == 1)
												Sudah Di verifikasi
                                            @elseif($item->status == 2)
												Sudah Di kirim oleh penjual
                                            @elseif($item->status == 3)
												Sudah Di terima oleh pembeli
											@endif
                                        </td>
										@else
										<td>
                                            @if($item->status == 0)
												Menunggu verifikasi
											@elseif($item->status == 1)
												Sudah Di verifikasi
											@endif
                                        </td>
										@endif
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
						@endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
