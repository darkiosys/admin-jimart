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
                                            <a href="#" onclick="setid({{$item->id}})" class="btn btn-success" data-toggle="modal" data-target="#gp">Ganti Password</a>
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
    <div class="modal fade" id="gp" style="display: none;">
        <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span></button>
            </div>
            <form method="post" action="/user/changepassword">
                <div class="modal-body">
                        <input type="hidden" name="id" id="id">
                        <input type="text" name="password" class="form-control" placeholder="masukan password baru">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default pull-left" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Ganti Password</button>
                </div>
            </form>
        </div>
        <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <script>
        function setid(val) {
            document.getElementById("id").value = val;
        }
    </script>
@endsection
