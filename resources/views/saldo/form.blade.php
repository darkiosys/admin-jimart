<div class="form-group {{ $errors->has('user_id') ? 'has-error' : ''}}">
    <label for="user_id" class="control-label">{{ 'User Id' }}</label>
    <input class="form-control" name="user_id" type="text" id="user_id" value="{{ isset($saldo->user_id) ? $saldo->user_id : ''}}" >
    {!! $errors->first('user_id', '<p class="help-block">:message</p>') !!}
</div>
<div class="form-group {{ $errors->has('admin_id') ? 'has-error' : ''}}">
    <label for="admin_id" class="control-label">{{ 'Admin Id' }}</label>
    <input class="form-control" name="admin_id" type="text" id="admin_id" value="{{ isset($saldo->admin_id) ? $saldo->admin_id : ''}}" >
    {!! $errors->first('admin_id', '<p class="help-block">:message</p>') !!}
</div>
<div class="form-group {{ $errors->has('saldo') ? 'has-error' : ''}}">
    <label for="saldo" class="control-label">{{ 'Saldo' }}</label>
    <input class="form-control" name="saldo" type="number" id="saldo" value="{{ isset($saldo->saldo) ? $saldo->saldo : ''}}" >
    {!! $errors->first('saldo', '<p class="help-block">:message</p>') !!}
</div>
<div class="form-group {{ $errors->has('jumlah_transfer') ? 'has-error' : ''}}">
    <label for="jumlah_transfer" class="control-label">{{ 'Jumlah Transfer' }}</label>
    <input class="form-control" name="jumlah_transfer" type="number" id="jumlah_transfer" value="{{ isset($saldo->jumlah_transfer) ? $saldo->jumlah_transfer : ''}}" >
    {!! $errors->first('jumlah_transfer', '<p class="help-block">:message</p>') !!}
</div>
<div class="form-group {{ $errors->has('no_rek') ? 'has-error' : ''}}">
    <label for="no_rek" class="control-label">{{ 'No Rek' }}</label>
    <input class="form-control" name="no_rek" type="text" id="no_rek" value="{{ isset($saldo->no_rek) ? $saldo->no_rek : ''}}" >
    {!! $errors->first('no_rek', '<p class="help-block">:message</p>') !!}
</div>
<div class="form-group {{ $errors->has('status') ? 'has-error' : ''}}">
    <label for="status" class="control-label">{{ 'Status' }}</label>
    <input class="form-control" name="status" type="text" id="status" value="{{ isset($saldo->status) ? $saldo->status : ''}}" >
    {!! $errors->first('status', '<p class="help-block">:message</p>') !!}
</div>


<div class="form-group">
    <input class="btn btn-primary" type="submit" value="{{ $formMode === 'edit' ? 'Update' : 'Create' }}">
</div>
