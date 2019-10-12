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
                                        <th>Member</th>
                                        <th>Receiver</th>
                                        <th>Nominal</th>
                                        <th>Ending Saldo</th>
										<th>Tanggal</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($transfers as $item)
                                    <tr>
                                        <td>
                                            {{$item->id}}
                                        </td>
                                        <td>
                                            {{$item->members_id}} {{$item->sender}}
                                        </td>
                                        <td>
                                            {{$item->receiver}}
                                        </td>
                                        <td>
                                            Rp. {{ number_format($item->nominal, 0, ".", ".")}}
                                        </td>
                                        <td>
                                            Rp. {{ number_format($item->ending_saldo, 0, ".", ".")}}
                                        </td>
                                        <td>
                                            {{$item->date}}
                                        </td>
                                        <td>
                                            {{$item->status}}
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
