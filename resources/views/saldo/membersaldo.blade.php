@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Saldo</div>
                    <div class="card-body">
						@if(Auth::user()->role == 0)
                        <a href="{{ url('/saldo/create') }}" class="btn btn-success btn-sm" title="Add New Saldo">
							<i class="fa fa-plus" aria-hidden="true"></i> Topup Saldo
                        </a>
						@endif
						<div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nama Member</th>
                                        <th>Sponsor</th>
                                        <th>Username</th>
                                        <th>Saldo</th>
										<th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($members as $item)
                                    <tr>
                                        <td>
                                            {{$item->id}}
                                        </td>
                                        <td>
                                            {{$item->first_name}} {{$item->last_name}}
                                        </td>
                                        <td>
                                            {{$item->sponsor}}
                                        </td>
                                        <td>
                                            {{$item->username}}
                                        </td>
                                        <td>
                                        Rp. {{ number_format($item->saldo, 0, ".", ".")}}
                                        </td>
                                        <td>
                                            <a href="/api/hapusmember/{{$item->id}}" onclick="return confirm('Yakin akan hapus member?')" class="btn btn-danger">Hapus Member</a>
                                            <a href="/api/hapussaldomember/{{$item->id}}" onclick="return confirm('Yakin akan kosongkan saldo member?')" class="btn btn-warning">Kosongkan Saldo</a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            <div class="pagination-wrapper"> </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
